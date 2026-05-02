<?php

namespace App\Traits;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Refund;
use App\Models\WebhookEvent;
use App\Models\Payment;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;    

trait StripePaymentTrait
{
   // Private Helper Methods
   public function stripeClient()
   {
      return new StripeClient(config('services.stripe.secret'));
   }

   // Create Stripe Checkout Session
   public function createStripeSession(Order $order)
   {
      $grandTotal = (int) round($order->grand_total * 100);

      if ($grandTotal <= 0) {
         Log::error('Invalid grand total for Stripe session', ['order_id' => $order->id, 'grand_total' => $order->grand_total]);
         throw new \Exception('Invalid order total for payment processing.');
      }  

      $stripe = $this->stripeClient();

      $session = $stripe->checkout->sessions->create(
         [
            'mode' => 'payment',
            'customer_creation' => 'always',
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
            'cancel_url'  => route('front-end.cancel'),
         ], [
            // Idempotency key to prevent duplicate sessions for the same order
            'idempotency_key' => 'order- #' . $order->id,
         ]
      );

      Log::info('Initiating Stripe Session', [
         'order'      => $order->id, 
         'amount'     => $order->grand_total,
         'session_id' => $session->id,
      ]);

      // Save session immediately
      $order->stripe_session_id = $session->id;
      $order->save();

      return $session;
   }

   /******************** SUCCESS PAGE ********************/

   public function success(Request $request)
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
         Cart::destroy();
         session()->forget('couponCode');

         return view('front-end.thankuPage-stripe', compact('session', 'order'));
      } else {
         Log::warning('Payment not completed for session', ['session_id' => $sessionId]);
         return redirect()->route('front-end.cart')->withErrors('Payment not completed. Please try again.');
      }  

   }

   /******************* CANCEL PAGE *******************/

   public function cancel()
   {
      return view('stripe.cancel');
   }

   /********** Resolve Order & User from Stripe Event **********/

   private function resolveOrderAndUser($event)
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
   public function webhook(Request $request)
   {
      $userId = $orderId = $payload = $payment = null;

      $payload   = $request->getContent();
      $sigHeader = $request->header('Stripe-Signature');
      $secret    = config('services.stripe.webhook_secret');

      // 1. Verify signature
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
         
         // if ($order->id) {
         //    orderEmail($order->id, 'customer');
         // }

         return response()->json(['status' => 'success']);

      } catch (\Exception $e) {
         Log::error('Webhook transaction failed: ' . $e->getMessage(), [
            'event_type' => $event->type ?? null,
            'order_id'   => $orderId ?? null,
         ]);

         return response()->json(['error' => 'Webhook transaction error'], 500);
      }
   }

   public function handleCheckoutCompleted($event, $orderId, $userId)
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
            'payment_intent' => $paymentIntentId
         ]);
         throw $e;
      }

      $order = Order::lockForUpdate()->find($orderId);  // lockForUpdate() row for safety 

      if (!$order) {
         Log::error('Order not found during webhook', ['order_id' => $orderId]);
         return null;
      }

      if ($order->payment_status == 'not_paid') {
         $order->payment_status        = 'paid';
         $order->status                = 'confirmed';   
         $order->stripe_session_id     = $session->id;
         $order->stripe_payment_intent = $paymentIntentId;
         $order->transaction_id        = $transactionId;
         $order->stripe_customer_id    = $paymentIntent->customer ?? null;
         $order->save();
      }

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

   public function handlePaymentSuccess($orderId, $userId)
   {
      $order = Order::find($orderId);

      if ($order) {
         Log::info('payment_intent.succeeded - Order Found', ['order_id' => $order->id]);

      } else {
         Log::warning('payment_intent.succeeded - Order Not Found', ['order_id' => $orderId]);
      }

   }

   public function storeWebhookEvent($event, $orderId, $userId, $payload, $paymentId = null)
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

   public function handleRefund($event, $orderId)
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
         ['stripe_refund_id' => $refundId->id ?? null],
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
      // $pi = $event->data->object;

      $order = Order::find($orderId);

      if ($order && $order->payment_status !== 'failed') {
         $order->payment_status = 'failed';
         $order->save();

         Log::info('Payment failed', ['order_id' => $orderId]);
      }
   }


}