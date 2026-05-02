<?php

namespace App\Traits;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Refund;
use App\Models\WebhookEvent;
use App\Models\Payment;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;    
use App\Jobs\ProcessStripeWebhook;

trait StripePaymentTrait
{
   // Private Helper Methods
   public function stripeClient()
   {
      return new StripeClient(config('services.stripe.secret'));
   }

   /*--------------- Create Stripe Checkout Session ---------------*/
   public function createStripeSession_12345(Order $order)
   {
      $grandTotal = (int) round($order->grand_total * 100);

      if ($grandTotal <= 0) {
         Log::error('Invalid grand total for Stripe session', ['order_id' => $order->id, 'grand_total' => $order->grand_total]);
         throw new \Exception('Invalid order total for payment processing.');
      }  

      $stripe = $this->stripeClient();

      if ($order->stripe_session_id) {
         Log::info('Stripe checkout session already exists:- ', $order->stripe_session_id);

         return $stripe->checkout->sessions->retrieve($order->stripe_session_id);

      } else {
         $session = $stripe->checkout->sessions->create(
            [
               'mode' => 'payment',
               "payment_method_types" => ["card"],
               'customer_creation' => 'always',
               // 'expires_at' => time() + (30 * 60),

               'metadata' => [
                  'order_id' => (string) $order->id,
                  'user_id'  => (string) $order->user_id,
               ],
               'payment_intent_data' => [
                  'metadata' => [
                     'order_id' => (string) $order->id,
                     'user_id'  => (string) $order->user_id,
                  ],
               ],

               'line_items' => [[
                  'price_data' => [
                     'currency' => 'usd',
                     'product_data' => [
                        'name' => 'Order id#' . $order->id,
                     ],
                     'unit_amount' => $grandTotal,
                  ],
                  'quantity' => 1,
               ]],

               'success_url' => route('front-end.success') . '?session_id={CHECKOUT_SESSION_ID}&orderId=' . $order->id,
               'cancel_url'  => route('front-end.cancel') . '?orderId=' . $order->id,
            ], [
               // Idempotency key to prevent duplicate sessions for the same order
               'idempotency_key' => 'checkout-session-order-' . $order->id,
            ]
         );

         Log::info('Stripe Session Created', [
            'order_id'   => $order->id,
            'session_id' => $session->id,
            'idempotency_key' => 'checkout-session-order-' . $order->id
         ]);

         // save session immediately
         $order->stripe_session_id = $session->id;
         $order->save();

         return $session;
      }
   }

   /******************** SUCCESS PAGE ********************/
   public function success_12345(Request $request)
   {
      $sessionId = $request->get('session_id');
      $orderId   = $request->get('orderId');

      if (!$sessionId) {abort(404);}

      try {
         $stripe = $this->stripeClient();
         $session = $stripe->checkout->sessions->retrieve($sessionId);

      } catch (\Throwable $th) {
         Log::error('Stripe session retrieval failed', ['session_id' => $sessionId]);
         abort(404);
      }

      // only clear cart if payment is confirmed
      $order = Order::where('id', $orderId)->where('stripe_session_id', $sessionId)->firstOrFail();

      if ($session->payment_status === 'paid') {
         // Queue processing
         // ProcessStripeWebhook::dispatch($orderId);

         Cart::destroy();
         session()->forget('couponCode');

         return view('front-end.thankuPage-stripe', compact('session', 'order'));
      } else {
         Log::warning('Payment not completed for session', ['session_id' => $sessionId]);
         return redirect()->route('front-end.cart')->withErrors('Payment not completed. Please try again.');
      }  

   }

   /******************* CANCEL PAGE *******************/
   public function cancel_12345(Request $request)
   {
      $orderId = (int) $request->orderId;

      if ($orderId) {
         $order = Order::find($orderId);

         if (!$order) {
            return redirect()->route('front-end.checkout')->with('error', 'Order not found.');
         }

         $isExpiredByTime = $order->created_at && $order->created_at->lte(now()->subMinutes(2));

         if ($order->payment_status === 'paid') {
            return redirect()->route('front-end.success', [
               'session_id' => $order->stripe_session_id,
               'orderId' => $order->id,
            ]);
         }

         if ($order->payment_status === 'not_paid' && $isExpiredByTime) {
            $order->payment_status = 'expired';
            $order->status = 'cancelled';
            $order->save();

            Log::info('Stripe payment expired by timeout', [
               'order_id'       => $orderId,
               'payment_status' => $order->payment_status
            ]);

            return redirect()->route('front-end.checkout')->with('error','Payment expired.');
         }

         if ($order->payment_status === 'not_paid') {
            $order->payment_status = 'cancelled';
            $order->status = 'payment_cancelled';
            $order->save();

            Log::info('Stripe payment cancelled by customer', [
               'order_id'       => $orderId,
               'payment_status' => $order->payment_status
            ]);

            return redirect()->route('front-end.checkout')->with('error','Payment cancelled.');
         }

         if (in_array($order->payment_status, ['expired', 'cancelled'], true)) {
            return redirect()->route('front-end.checkout')->with('error','Payment Expired.');
         }
      } 
      return view('front-end.cancel');
   }

   /********** Resolve Order & User from Stripe Event **********/
   private function resolveOrderAndUser_12345($event)
   {
      $object = $event->data->object;

      $orderId = $userId = null;

      // 1) PaymentIntent events
      if (isset($object->metadata->order_id)) {
         $orderId = $object->metadata->order_id ?? null;
         $userId  = $object->metadata->user_id ?? null;
      }

      // 2) Checkout session → resolve via payment_intent
      if (!$orderId && isset($object->payment_intent)) {
         try {
            $stripe = $this->stripeClient();
            $pi = $stripe->paymentIntents->retrieve($object->payment_intent);

            $orderId = $pi->metadata->order_id ?? null;
            $userId  = $pi->metadata->user_id ?? null;

         } catch (\Exception $e) {
            Log::error("Stripe PI retrieval failed: " . $e->getMessage());
         }
      }

      return [$orderId, $userId];
   }

   /************* WEBHOOK HANDLER (PRODUCTION CRITICAL) *************/
   public function webhook_12345(Request $request)
   {
      $userId = $orderId = $payload = $payment = null;

      $payload   = $request->getContent();
      $sigHeader = $request->header('Stripe-Signature');
      $secret    = config('services.stripe.webhook_secret');

      // 1. Verify signature first  
      try {
         $event = Webhook::constructEvent($payload, $sigHeader, $secret);         

      } catch (SignatureVerificationException | \UnexpectedValueException $e) {
         
         Log::critical('POSSIBLE TAMPERING: Webhook signature verification failed', [
            'payload' => $payload, 
            'ip'      => $request->ip()
         ]);
         return response()->json(['error' => 'Invalid Signature Or Webhook error'], 400);
      }

      // Only process/store meaningful ones Or Filter unwanted events
      $allowedEvents = [
         'checkout.session.completed',
         'payment_intent.succeeded',
         'payment_intent.payment_failed',
         'charge.refunded'
      ];

      if (!in_array($event->type, $allowedEvents)) {
         Log::info('Ignored event:' . $event->type);
         return response()->json(['ignored' => true], 200);
      }

      // Duplicate check FIRST (before transaction)
      if ( WebhookEvent::where('event_id', $event->id)->exists() ) {
         Log::info('Duplicate webhook ignored', ['event_id' => $event->id]);
         return response()->json(['status' => 'duplicate'], 200);   
      }

      // Resolve order/user
      [$orderId, $userId] = $this->resolveOrderAndUser($event);

      $paymentId = null;
      
      try {
         DB::transaction(function () use ($event, $orderId, $userId, &$paymentId) {
            switch ($event->type) {

               case 'checkout.session.completed':
                  // 1. process payment
                  $paymentId = $this->handleCheckoutCompleted($event, $orderId, $userId);

                  if ($paymentId) {
                     ProcessStripeWebhook::dispatch($orderId, $paymentId)->delay(now()->addSeconds(5));  // Queue processing
                  }
                  break;

               case 'payment_intent.payment_failed':
                  $this->handlePaymentFailed($orderId);
                  break;

               case 'charge.refunded':
                  // process refund
                  $this->handleRefund($event, $orderId);

                  break;

               case 'payment_intent.succeeded':
                  [$orderId, $userId] = $this->resolveOrderAndUser($event); 

                  $this->handlePaymentSuccess($orderId, $userId);

                  break;

               default:
                  Log::info('Unhandled event type', ['type' => $event->type]);
                  break;
            }
         });
         
         $this->storeWebhookEvent($event, $orderId, $userId, $payload, $paymentId);

         return response()->json(['status' => 'success']);

      } catch (\Exception $e) {
         Log::error('Webhook transaction failed: ' . $e->getMessage(), [
            'event_type' => $event->type ?? null,
            'order_id'   => $orderId ?? null,
         ]);

         return response()->json(['error' => 'Webhook transaction error'], 500);
      }
   }

   public function handleCheckoutCompleted_12345($event, $orderId, $userId)
   {
      $session = $event->data->object;
      $paymentIntentId = $session->payment_intent;

      if (!$paymentIntentId || !$orderId) {
         Log::error('Missing Payment Intent', ['order_id' => $orderId]);
         throw new \Exception("Missing Payment Intent or Order ID in Webhook");
      }

      $uniqueId      = str_replace('.', '', uniqid());
      $transactionId = 'TXN ID-' . strtoupper($uniqueId) . '-' . $orderId;

      try {
         $stripe        = $this->stripeClient();
         $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

      } catch (\Throwable $th) {

         Log::error('Stripe payment intent retrieval failed', [
            'payment_intent' => $paymentIntentId,
            'error' => $th->getMessage(),
         ]);
         throw $th;
      }

      $order = Order::lockForUpdate()->find($orderId);  // lockForUpdate() row for safety 

      if (!$order) {
         Log::error('Order not found during webhook', ['order_id' => $orderId]);
         return null;
      }

      if ($order->payment_status === 'paid') {
         $existingPayment = Payment::firstWhere('stripe_payment_intent', $paymentIntentId);
         return $existingPayment?->id;
      }

      // Do not convert expired/cancelled orders to paid.
      if (in_array($order->payment_status, ['expired', 'cancelled'], true)) {
         Log::warning('Ignoring late Stripe success for non-payable order', [
            'order_id' => $orderId,
            'payment_status' => $order->payment_status,
            'payment_intent' => $paymentIntentId,
         ]);
         return null;
      }

      $order->payment_status        = 'paid';
      $order->status                = 'confirmed';      
      $order->stripe_session_id     = $session->id;
      $order->stripe_payment_intent = $paymentIntentId;
      $order->transaction_id        = $transactionId;
      $order->stripe_customer_id    = $paymentIntent->customer ?? null;
      $order->save();

      // create payment record
      $payment = Payment::updateOrCreate(
         ['stripe_payment_intent' => $paymentIntentId],  // idempotency key
         [
            'order_id'              => $order->id,
            'user_id'               => $userId,                        
            'stripe_charge_id'      => $paymentIntent->latest_charge ?? null,
            'stripe_session_id'     => $session->id,
            'price'                 => $order->grand_total,
            'currency'              => $paymentIntent->currency ?? 'usd',
            'status'                => 'succeeded',
            'stripe_payload'        => $paymentIntent->toArray(),
         ]
      );

      Log::info('Payment completed', ['payment_id' => $payment->id]);

      return $payment->id;
   }

   public function handlePaymentSuccess_12345($orderId, $userId)
   {
      $order = Order::find($orderId);

      if ($order) {
         Log::info('payment_intent.succeeded - Order Found', ['order_id' => $order->id]);

      } else {
         Log::warning('payment_intent.succeeded - Order Not Found', ['order_id' => $orderId]);
      }

   }

   public function storeWebhookEvent_12345($event, $orderId, $userId, $payload, $paymentId = null)
   {
      $paymentIntentId = $stripeCustomerId = null;

      $object = $event->data->object;

      // Store webhook event (idempotency)

      WebhookEvent::updateOrCreate(
         ['event_id' => $event->id],
         [
            'order_id'              => $orderId,
            'user_id'               => $userId,
            'event_type'            => $event->type,
            'payment_id'            => $paymentId,
            'stripe_customer_id'    => $object->customer ?? null,
            'stripe_payment_intent' => $object->payment_intent ?? null,
            'stripe_object_id'      => $object->id ?? null,
            'processed'             => true,
            'payload'               => json_encode($event->data->object),
         ]
      );
   }

   public function handleRefund_12345($event, $orderId)
   {
      $charge = $event->data->object;
      $paymentIntentId = $charge->payment_intent ?? null;

      if (!$paymentIntentId) {
         Log::error('Missing payment intent in refund');
         return response()->json(['status' => 'missing_pi'], 200);
      }

      $payment = Payment::where('stripe_payment_intent', $paymentIntentId)->first();

      if (!$payment) {
         Log::warning('Payment not found for refund', ['pi_id' => $paymentIntentId]);
      }

      $refundId     = $charge->refunds->data[0]->id ?? null;
      $refundAmount = ($charge->amount_refunded ?? 0) / 100;

      Refund::updateOrCreate(
         ['stripe_refund_id' => $refundId ?? null],
         [
            'order_id'              => $payment->order_id,
            'payment_id'            => $payment->id,
            'stripe_payment_intent' => $payment->stripe_payment_intent,
            'price'                 => $refundAmount,
            'currency'              => $charge->currency ?? 'usd',
            'status'                => 'succeeded',
            'stripe_payload'        => $charge->toArray(),
         ]
      );

      $payment->status = 'refunded';
      $payment->save();

      Log::info('Refund processed', ['order_id' => $payment->order_id]);
   }

   public function handlePaymentFailed_12345($orderId)
   {
      $order = Order::find($orderId);

      if ($order && $order->payment_status === 'not_paid') {
         $order->payment_status = 'failed';
         $order->save();

         Log::info('Payment handle failed', ['order_id' => $orderId]);
      }
      return redirect()->route('front-end.home');
   }


   /* 
      ------- Stripe PaymentIntent Method Integeration Here -----
   */
   public function createPaymentIntent(Order $order)
   {
      $grandTotal = (int) round($order->grand_total * 100);

      if ($grandTotal <= 0) {
         Log::error('Invalid grand total for payment intent', ['order_id' => $order->id, 'grand_total' => $order->grand_total]);
         throw new \Exception('Invalid order total for payment processing.');
      }  

      $stripe = $this->stripeClient();

      if ($order->stripe_payment_intent) {
         try {
            $existPaymentIntent = $stripe->paymentIntents->retrieve($order->stripe_payment_intent);

            Log::info('Existing PaymentIntent found', [
               'order_id'  => $order->id,
               'intent_id' => $existPaymentIntent->id,
               'status'    => $existPaymentIntent->status
            ]);

            // If payment already 'succeeded', 'requires_payment_method', 'processing', 'canceled' 
            // check amount mismatch FIRST, before any early returns
            if ($existPaymentIntent->amount !== $grandTotal || $existPaymentIntent->status === 'canceled') {
               $order->stripe_payment_intent = null;
               $order->save();

               $existPaymentIntent = null;

            } elseif (in_array($existPaymentIntent->status, ['succeeded', 'requires_payment_method', 'processing'])) {
               return $existPaymentIntent;
            }
            
            // Only reaches here if status is requires_capture or requires_confirmation etc.
            if ($existPaymentIntent) {
               return $existPaymentIntent;
            }

         } catch (\Throwable $th) {
            Log::warning('Existing PaymentIntent retrieval failed, creating new one', [
               'order_id' => $order->id
            ]);
         }
      } 

      // CREATE NEW INTENT
      $paymentIntent = $stripe->paymentIntents->create(
         [
            'amount' => $grandTotal,
            'currency' => 'inr',
            'metadata' => [
               'order_id' => $order->id,
               'user_id'  => $order->user_id
            ],

            'automatic_payment_methods' => [
               'enabled' => true
            ],
         ],
         [
            // Idempotency key to prevent duplicate sessions for the same order
            'idempotency_key' => 'payment-intent-order-' . $order->id . '-' . $grandTotal,
         ]
      );
      Log::info('Stripe PaymentIntent Created', [
         'order_id'        => $order->id,
         'intent_id'       => $paymentIntent->id,
         'idempotency_key' => 'payment-intent-order-' . $order->id . '-' . $grandTotal
      ]);

      // save stripe_payment_intent immediately
      $order->stripe_payment_intent = $paymentIntent->id;
      $order->save();

      return $paymentIntent;
   }

   /******************** SUCCESS PAGE ********************/
   public function success(Request $request)
   {
      $orderId = $request->get('orderId');

      if (!$orderId) {abort(404);}

      $order = Order::where('id', $orderId)->where('user_id', auth()->id())->firstOrFail();

      $stripe  = $this->stripeClient();

      if ($order->stripe_payment_intent) {
         $paymentIntent = $stripe->paymentIntents->retrieve($order->stripe_payment_intent);
         
         Log::info('Success:- stripe payment intent retrieved', [
            'order_id' => $order->id,
            'status'   => $paymentIntent->status
         ]);

         // Lock inside transaction only
         DB::transaction(function () use ($orderId, $paymentIntent) {
            $order = Order::lockForUpdate()
               ->where('id', $orderId)
               ->where('user_id', auth()->id())
               ->firstOrFail()
            ;

            if ($paymentIntent->status === 'succeeded' && $order->payment_status !== 'paid') {
               $order->payment_status = 'paid';
               $order->save();
            }
         });
         // Refresh $order after transaction 
         $order = $order->fresh();
      } 

      // only clear cart if payment is confirmed
      if ($order->payment_status === 'paid') {
         Cart::destroy();
         session()->forget('couponCode');
      }

      return view('front-end.thankuPage-stripe', compact('order'));
   }

   public function cancel(Request $request)
   {
      $orderId = (int) $request->orderId;

      if ($orderId) {
         $order = Order::where('id', $orderId)->where('user_id', auth()->id())->first();

         if (!$order) {
            return redirect()->route('front-end.checkout')->with('error', 'Order not found.');
         }

         $isExpiredByTime = $order->created_at && $order->created_at->lte(now()->subMinutes(10));

         if ($order->payment_status === 'paid') {
            return redirect()->route('front-end.success', [
               'orderId' => $order->id,
            ]);
         }

         if ($order->payment_status === 'not_paid' && $isExpiredByTime) {
            $order->payment_status = 'expired';
            $order->status = 'cancelled';
            $order->save();

            Log::info('Stripe payment expired by timeout', [
               'order_id'       => $orderId,
               'payment_status' => $order->payment_status
            ]);

            return redirect()->route('front-end.checkout')->with('error','Payment expired.');
         }

         if ($order->payment_status === 'not_paid') {
            $order->payment_status = 'cancelled_by_user';
            $order->status = 'payment_cancelled';
            $order->save();

            Log::info('Stripe payment cancelled by customer', [
               'order_id'       => $orderId,
               'payment_status' => $order->payment_status
            ]);

            return redirect()->route('front-end.checkout')->with('error','Payment cancelled.');
         }

         if (in_array($order->payment_status, ['expired', 'cancelled'], true)) {
            return redirect()->route('front-end.checkout')->with('error','Payment Expired.');
         }
      } 
      return view('front-end.cancel');
   }

   /********** Resolve Order & User from Stripe Event **********/
   private function resolveOrderAndUser($event)
   {
      $object = $event->data->object;

      $orderId = $userId = null;

      if (isset($object->metadata->order_id)) {
         $orderId = $object->metadata->order_id ?? null;
         $userId  = $object->metadata->user_id ?? null;
      }

      return [$orderId, $userId];
   }

   /************* WEBHOOK HANDLER (PRODUCTION CRITICAL) *************/
   public function webhook(Request $request)
   {
      $userId = $orderId = $payload = $payment = null;

      $payload   = $request->getContent();
      $sigHeader = $request->header('Stripe-Signature');
      $secret    = config('services.stripe.webhook_secret');

      // 1. Verify signature first  
      try {
         $event = Webhook::constructEvent($payload, $sigHeader, $secret);         

      } catch (SignatureVerificationException | \UnexpectedValueException $e) {
         
         Log::critical('POSSIBLE TAMPERING: Webhook signature verification failed', [
            'payload' => $payload, 
            'ip'      => $request->ip()
         ]);
         return response()->json(['error' => 'Invalid Signature Or Webhook error'], 400);
      }

      // Only process/store meaningful ones Or Filter unwanted events
      $allowedEvents = [
         'payment_intent.succeeded',
         'payment_intent.payment_failed',
         'charge.refunded'
      ];

      if (!in_array($event->type, $allowedEvents)) {
         Log::info('Ignored Stripe event', [
            'event_type' => $event->type
         ]);
         return response()->json(['ignored' => true], 200);
      }

      // Duplicate check FIRST (before transaction)
      if ( WebhookEvent::where('event_id', $event->id)->exists() ) {
         Log::info('Duplicate webhook ignored', ['event_id' => $event->id]);
         return response()->json(['status' => 'duplicate'], 200);   
      }

      // Resolve order/user
      [$orderId, $userId] = $this->resolveOrderAndUser($event);

      $paymentId = null;
      
      try {
         DB::transaction(function () use ($event, $orderId, $userId, $payload, &$paymentId) {
            switch ($event->type) {
               case 'payment_intent.succeeded':
                  // [$orderId, $userId] = $this->resolveOrderAndUser($event); 

                  $paymentId = $this->handlePaymentSuccess($event, $orderId, $userId);

                  break;

               case 'payment_intent.payment_failed':
                  $this->handlePaymentFailed($orderId);

                  break;

               case 'charge.refunded':
                  // process refund
                  $this->handleRefund($event, $orderId);

                  break;

               default:
                  Log::info('Unhandled event type', ['type' => $event->type]);
                  break;
            }
            $this->storeWebhookEvent($event, $orderId, $userId, $payload, $paymentId);
         });
         
         return response()->json(['status' => 'success']);

      } catch (\Exception $e) {
         Log::error('Webhook transaction failed: ' . $e->getMessage(), [
            'event_type' => $event->type ?? null,
            'order_id'   => $orderId ?? null,
         ]);

         return response()->json(['error' => 'Webhook transaction error'], 500);
      }
   }

   public function handlePaymentSuccess($event, $orderId, $userId)
   {
      $paymentIntent = $event->data->object;

      $order = Order::lockForUpdate()->find($orderId);            
      
      if (!$order) {
         Log::error('Order not found during payment success', [
            'order_id' => $orderId
         ]);
         return null;
      }

      if ($order->payment_status === 'paid') {
         Log::info('Duplicate payment webhook ignored', [
            'order_id' => $orderId
         ]);
         return null;
      }

      if (in_array($order->payment_status, ['expired', 'cancelled', 'failed', 'cancelled_by_user'], true)) {
         Log::warning('Ignoring late Stripe payment', [
            'order_id' => $orderId,
            'payment_status' => $order->payment_status
        ]);
        return null;
      }

      if ($order->stripe_payment_intent !== $paymentIntent->id) {
         Log::warning('PaymentIntent mismatch', [
            'order_id' => $orderId,
            'expected' => $order->stripe_payment_intent,
            'received' => $paymentIntent->id
         ]);

         return null;
      }

      $transactionId = 'TXN-' . strtoupper(uniqid()) . '-' . $orderId;

      $order->payment_status        = 'paid';
      $order->status                = 'confirmed';
      $order->stripe_payment_intent = $paymentIntent->id;
      $order->transaction_id        = $transactionId;
      $order->save();

      $payment = Payment::updateOrCreate(
        ['stripe_payment_intent' => $paymentIntent->id],
        [
            'order_id'         => $order->id,
            'user_id'          => $userId,
            'stripe_charge_id' => $paymentIntent->latest_charge ?? null,
            'price'            => $order->grand_total,
            'currency'         => $paymentIntent->currency ?? 'usd',
            'status'           => 'succeeded',
            'stripe_payload'   => $paymentIntent->toArray(),
        ]
      );

      DB::afterCommit(function () use ($orderId, $payment) {
         ProcessStripeWebhook::dispatch($orderId, $payment->id)->delay(now()->addSeconds(5));
      });

      return $payment->id;
   }

   public function storeWebhookEvent($event, $orderId, $userId, $payload, $paymentId = null)
   {
      // $paymentIntentId = null;

      $object = $event->data->object;

      // Store webhook event (idempotency)

      WebhookEvent::updateOrCreate(
         ['event_id' => $event->id],
         [
            'order_id'              => $orderId,
            'user_id'               => $userId,
            'event_type'            => $event->type,
            'payment_id'            => $paymentId,
            'stripe_customer_id'    => $object->customer ?? null,
            'stripe_payment_intent' => $object->id ?? null,
            'stripe_object_id'      => $object->id ?? null,
            'processed'             => true,
            'payload'               => json_encode($event->toArray()),
         ]
      );
   }

   public function handleRefund($event, $orderId)
   {
      $charge = $event->data->object;
      $paymentIntentId = $charge->payment_intent ?? null;

      if (!$paymentIntentId) {
         Log::error('Missing payment intent in refund');
         return;
         // return response()->json(['status' => 'missing_pi'], 200);
      }

      $payment = Payment::where('stripe_payment_intent', $paymentIntentId)->first();

      if (!$payment) {
         Log::warning('Payment not found for refund', ['pi_id' => $paymentIntentId]);
         return;
      }

      $refund   = $charge->refunds->data[0] ?? null;
      $refundId = $refund->id ?? null;

      $refundAmount = ($refund) ? (($refund->amount ?? 0) / 100) : 0;

      Refund::updateOrCreate(
         ['stripe_refund_id' => $refundId ?? null],
         [
            'order_id'              => $payment->order_id,
            'payment_id'            => $payment->id,
            'stripe_payment_intent' => $payment->stripe_payment_intent,
            'price'                 => $refundAmount,
            'currency'              => $charge->currency ?? 'usd',
            'status'                => 'succeeded',
            'stripe_payload'        => $charge->toArray(),
         ]
      );

      $payment->status = 'refunded';
      $payment->save();

      Log::info('Refund processed', ['order_id' => $payment->order_id]);
   }

   public function handlePaymentFailed($orderId)
   {
      $order = Order::lockForUpdate()->find($orderId);

      if (!$order) {
         Log::warning('Order not found for payment failed', ['order_id' => $orderId]);
         return;
      }

      if (in_array($order->payment_status, ['not_paid'], true)) {
         $order->payment_status = 'failed';
         $order->save();

         Log::info('Payment marked as failed', ['order_id' => $orderId]);
         return;
      }

      Log::info('Payment failed webhook ignored — already resolved', [
         'order_id'       => $orderId,
         'payment_status' => $order->payment_status
      ]);
   }

   /* 
      -------------- Authorize.net --------------- 
   */

   public function createHostedPayment()
   {
      
   }


}
