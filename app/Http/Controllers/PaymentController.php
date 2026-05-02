<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Country;
use App\Models\User;
use App\Models\apartment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomerAddress;
use App\Models\ShippingCharge;
use App\Models\WebhookEvent;
use App\Models\Payment;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Charge;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Jobs\ProcessStripeWebhook;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class PaymentController extends Controller
{
    // Private Helper Methods
    public function stripeClient()
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    /*
        --------------- Create Stripe Checkout Session ---------------
    */
    // public function creapaymentIdteStripeSession_12345(Order $order)
    // {
    //     $grandTotal = (int) round($order->grand_total * 100);

    //     if ($grandTotal <= 0) {
    //         Log::error('Invalid grand total for Stripe session', ['order_id' => $order->id, 'grand_total' => $order->grand_total]);
    //         throw new \Exception('Invalid order total for payment processing.');
    //     }  

    //     $stripe = $this->stripeClient();

    //     $payment = Payment::where('order_id', $order->id)
    //         ->where('payment_gateway', 'stripe')
    //         ->whereNotNull('payment_intent_id')
    //         ->first()
    //     ;

    //     if ($payment->payment_intent_id) {
    //         Log::info('Stripe checkout session already exists:- ', $payment->payment_intent_id);

    //         return $stripe->checkout->sessions->retrieve($payment->payment_intent_id);

    //     } else {
    //         $session = $stripe->checkout->sessions->create(
    //             [
    //                 'mode' => 'payment',
    //                 "payment_method_types" => ["card"],
    //                 'customer_creation' => 'always',
    //                 // 'expires_at' => time() + (30 * 60),

    //                 'metadata' => [
    //                     'order_id' => (string) $order->id,
    //                     'user_id'  => (string) $order->user_id,
    //                 ],

    //                 'payment_intent_data' => [
    //                     'metadata' => [
    //                         'order_id' => (string) $order->id,
    //                         'user_id'  => (string) $order->user_id,
    //                     ],
    //                 ],

    //                 'line_items' => [[
    //                     'price_data' => [
    //                         'currency' => 'usd',
    //                         'product_data' => [
    //                             'name' => 'Order id#' . $order->id,
    //                         ],
    //                         'unit_amount' => $grandTotal,
    //                     ],
    //                     'quantity' => 1,
    //                 ]],

    //                 'success_url' => route('front-end.success') . '?session_id={CHECKOUT_SESSION_ID}&orderId=' . $order->id,
    //                 'cancel_url'  => route('front-end.cancel') . '?orderId=' . $order->id,
    //             ], 
    //             [
    //                 // Idempotency key to prevent duplicate sessions for the same order
    //                 'idempotency_key' => 'checkout-session-order-' . $order->id,
    //             ]
    //         );

    //         Log::info('Stripe Session Created', [
    //             'order_id'   => $order->id,
    //             'session_id' => $session->id,
    //             'idempotency_key' => 'checkout-session-order-' . $order->id
    //         ]);

    //         return $session;
    //     }
    // }

    // /******************** SUCCESS PAGE ********************/
    // public function success_12345(Request $request)
    // {
    //     $sessionId = $request->get('session_id');
    //     $orderId   = $request->get('orderId');

    //     if (!$sessionId) {abort(404);}

    //     try {
    //         $stripe = $this->stripeClient();
    //         $session = $stripe->checkout->sessions->retrieve($sessionId);

    //     } catch (\Throwable $th) {
    //         Log::error('Stripe session retrieval failed', ['session_id' => $sessionId]);
    //         abort(404);
    //     }

    //     // only clear cart if payment is confirmed
    //     $order = Order::findOrFail($orderId);

    //     if ($session->payment_status === 'paid') {
    //         // Queue processing
    //         // ProcessStripeWebhook::dispatch($orderId);

    //         Cart::destroy();
    //         session()->forget('couponCode');

    //         return view('front-end.thankuPage-stripe', compact('session', 'order'));
    //     } else {
    //         Log::warning('Payment not completed for session', ['session_id' => $sessionId]);
    //         return redirect()->route('front-end.cart')->withErrors('Payment not completed. Please try again.');
    //     }  

    // }

    // /******************* CANCEL PAGE *******************/
    // public function cancel_12345(Request $request)
    // {
    //     $orderId = (int) $request->orderId;

    //     if ($orderId) {
    //         $order = Order::find($orderId);

    //         if (!$order) {
    //             return redirect()->route('front-end.checkout')->with('error', 'Order not found.');
    //         }

    //         $isExpiredByTime = $order->created_at && $order->created_at->lte(now()->subMinutes(2));

    //         if ($order->payment_status === 'paid') {
    //             return redirect()->route('front-end.success', [
    //                 'orderId' => $order->id,
    //             ]);
    //         }

    //         if ($order->payment_status === 'not_paid' && $isExpiredByTime) {
    //             $order->payment_status = 'expired';
    //             $order->status = 'cancelled';
    //             $order->save();

    //             Log::info('Stripe payment expired by timeout', [
    //                 'order_id'       => $orderId,
    //                 'payment_status' => $order->payment_status
    //             ]);

    //             return redirect()->route('front-end.checkout')->with('error','Payment expired.');
    //         }

    //         if ($order->payment_status === 'pending') {
    //             $order->payment_status = 'cancelled';
    //             $order->status = 'payment_cancelled';
    //             $order->save();

    //             Log::info('Stripe payment cancelled by customer', [
    //             'order_id'       => $orderId,
    //             'payment_status' => $order->payment_status
    //             ]);

    //             return redirect()->route('front-end.checkout')->with('error','Payment cancelled.');
    //         }

    //         if (in_array($order->payment_status, ['expired', 'cancelled'], true)) {
    //             return redirect()->route('front-end.checkout')->with('error','Payment Expired.');
    //         }
    //     } 
    //     return view('front-end.cancel');
    // }

    // /********** Resolve Order & User from Stripe Event **********/
    // private function resolveOrderAndUser_12345($event)
    // {
    //     $object = $event->data->object;

    //     $orderId = $userId = null;

    //     // 1) PaymentIntent events
    //     if (isset($object->metadata->order_id)) {
    //         $orderId = $object->metadata->order_id ?? null;
    //         $userId  = $object->metadata->user_id ?? null;
    //     }

    //     // 2) Checkout session → resolve via payment_intent
    //     if (!$orderId && isset($object->payment_intent)) {
    //         try {
    //             $stripe = $this->stripeClient();
    //             $pi = $stripe->paymentIntents->retrieve($object->payment_intent);

    //             $orderId = $pi->metadata->order_id ?? null;
    //             $userId  = $pi->metadata->user_id ?? null;

    //         } catch (\Exception $e) {
    //             Log::error("Stripe PI retrieval failed: " . $e->getMessage());
    //         }
    //     }

    //     return [$orderId, $userId];
    // }

    // /************* WEBHOOK HANDLER (PRODUCTION CRITICAL) *************/
    // public function webhook_12345(Request $request)
    // {
    //     $userId = $orderId = $payload = $payment = null;

    //     $payload   = $request->getContent();
    //     $sigHeader = $request->header('Stripe-Signature');
    //     $secret    = config('services.stripe.webhook_secret');

    //     // 1. Verify signature first  
    //     try {
    //         $event = Webhook::constructEvent($payload, $sigHeader, $secret);         

    //     } catch (SignatureVerificationException | \UnexpectedValueException $e) {
            
    //         Log::critical('POSSIBLE TAMPERING: Webhook signature verification failed', [
    //             'payload' => $payload, 
    //             'ip'      => $request->ip()
    //         ]);
    //         return response()->json(['error' => 'Invalid Signature Or Webhook error'], 400);
    //     }

    //     // Only process/store meaningful ones Or Filter unwanted events
    //     $allowedEvents = [
    //         'checkout.session.completed',
    //         'payment_intent.succeeded',
    //         'payment_intent.payment_failed',
    //         'charge.refunded'
    //     ];

    //     if ( !in_array($event->type, $allowedEvents) ) {
    //         Log::info('Ignored event:' . $event->type);
    //         return response()->json(['ignored' => true], 200);
    //     }

    //     // Duplicate check FIRST (before transaction)
    //     if ( WebhookEvent::where('event_id', $event->id)->exists() ) {
    //         Log::info('Duplicate webhook ignored', ['event_id' => $event->id]);
    //         return response()->json(['status' => 'duplicate'], 200);   
    //     }

    //     // Resolve order/user
    //     [$orderId, $userId] = $this->resolveOrderAndUser($event);

    //     $paymentId = null;
        
    //     try {
    //         DB::transaction(function () use ($event, $orderId, $userId, &$paymentId) {
    //             switch ($event->type) {

    //             case 'checkout.session.completed':
    //                 // 1. process payment
    //                 $paymentId = $this->handleCheckoutCompleted($event, $orderId, $userId);

    //                 if ($paymentId) {
    //                     ProcessStripeWebhook::dispatch($orderId, $paymentId)->delay(now()->addSeconds(5));  // Queue processing
    //                 }
    //                 break;

    //             case 'payment_intent.payment_failed':
    //                 $this->handlePaymentFailed($orderId);
    //                 break;

    //             case 'charge.refunded':
    //                 // process refund
    //                 $this->handleRefund($event, $orderId);

    //                 break;

    //             case 'payment_intent.succeeded':
    //                 [$orderId, $userId] = $this->resolveOrderAndUser($event); 

    //                 $this->handlePaymentSuccess($orderId, $userId);

    //                 break;

    //             default:
    //                 Log::info('Default Unhandled event type', ['type' => $event->type]);
    //                 break;
    //             }
    //         });
            
    //         $this->storeWebhookEvent($event, $orderId, $userId, $payload, $paymentId);

    //         return response()->json(['status' => 'success']);

    //     } catch (\Exception $e) {
    //         Log::error('Webhook transaction failed: ' . $e->getMessage(), [
    //             'event_type' => $event->type ?? null,
    //             'order_id'   => $orderId ?? null,
    //         ]);

    //         return response()->json(['error' => 'Webhook transaction error'], 500);
    //     }
    // }

    // public function handleCheckoutCompleted_12345($event, $orderId, $userId)
    // {
    //     $session = $event->data->object;
    //     $paymentIntentId = $session->payment_intent;

    //     if (!$paymentIntentId || !$orderId) {
    //         Log::error('Missing Payment Intent', ['order_id' => $orderId]);
    //         throw new \Exception("Missing Payment Intent or Order ID in Webhook");
    //     }

    //     $uniqueId      = str_replace('.', '', uniqid());
    //     $transactionId = 'TXN ID-' . strtoupper($uniqueId) . '-' . $orderId;

    //     try {
    //         $stripe        = $this->stripeClient();
    //         $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

    //     } catch (\Throwable $th) {

    //         Log::error('Stripe payment intent retrieval failed', [
    //             'payment_intent' => $paymentIntentId,
    //             'error' => $th->getMessage(),
    //         ]);
    //         throw $th;
    //     }

    //     $order = Order::lockForUpdate()->find($orderId);  // lockForUpdate() row for safety 

    //     if (!$order) {
    //         Log::error('Order not found during webhook', ['order_id' => $orderId]);
    //         return null;
    //     }

    //     if ($order->payment_status === 'paid') {
    //         $existingPayment = Payment::where('payment_intent_id', $paymentIntentId)->where('payment_gateway', 'stripe')->first();
            
    //         return $existingPayment?->id;
    //     }

    //     // Do not convert expired/cancelled orders to paid.
    //     if (in_array($order->payment_status, ['expired', 'cancelled'], true)) {
    //         Log::warning('Ignoring late Stripe success for non-payable order', [
    //             'order_id' => $orderId,
    //             'payment_status' => $order->payment_status,
    //             'payment_intent' => $paymentIntentId,
    //         ]);
    //         return null;
    //     }

    //     $order->payment_status = 'paid';
    //     $order->status         = 'confirmed';      
    //     $order->save();

    //     Log::info('Order payment status updated to paid:- ', ['payment_status' => $order->payment_status]);

    //     // create payment record
    //     $payment = Payment::updateOrCreate(
    //         [
    //             'payment_intent_id' => $paymentIntentId, // idempotency key
    //             'payment_gateway' => 'stripe'   
    //         ],
    //         [
    //             'order_id'       => $order->id,
    //             'user_id'        => $userId,                        
    //             'transaction_id' => $transactionId,
    //             'reference_id'   => $paymentIntent->latest_charge ?? null,
    //             'amount'         => $order->grand_total,
    //             'currency'       => $paymentIntent->currency ?? 'usd',
    //             'status'         => 'succeeded',
    //             'payload'        => json_encode($paymentIntent->toArray()),
    //         ]
    //     );

    //     Log::info('Payment completed', ['payment_id' => $payment->id]);

    //     return $payment->id;
    // }

    // public function handlePaymentSuccess_12345($orderId, $userId)
    // {
    //     $order = Order::find($orderId);

    //     if ($order) {
    //         Log::info('payment_intent.succeeded - Order Found', ['order_id' => $order->id]);

    //     } else {
    //         Log::warning('payment_intent.succeeded - Order Not Found', ['order_id' => $orderId]);
    //     }

    // }

    // public function storeWebhookEvent_12345($event, $orderId, $userId, $payload, $paymentId = null)
    // {
    //     $paymentIntentId = $stripeCustomerId = null;

    //     $object = $event->data->object;

    //     // Store webhook event (idempotency)

    //     WebhookEvent::updateOrCreate(
    //         [
    //             'event_id' => $event->id,
    //             'payment_gateway'  => 'stripe'
    //         ],
    //         [
    //             'order_id'        => $orderId,
    //             'user_id'         => $userId,
    //             'payment_id'      => $paymentId,
    //             'event_type'      => $event->type,
    //             'resource_id'     => $resourceId,
    //             'processed'       => true,
    //             'payload'         => json_encode($payload),
    //         ]
    //     );
    // }

    // public function handleRefund_12345($event, $orderId)
    // {
    //     $charge = $event->data->object;
    //     $paymentIntentId = $charge->payment_intent ?? null;

    //     if (!$paymentIntentId) {
    //         Log::error('Missing payment intent in refund');
    //         return response()->json(['status' => 'missing_pi'], 200);
    //     }

    //     $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();

    //     if (!$payment) {
    //         Log::warning('Payment not found for refund', ['pi_id' => $paymentIntentId]);
    //     }

    //     $refundId     = $charge->refunds->data[0]->id ?? null;
    //     $refundAmount = ($charge->amount_refunded ?? 0) / 100;

    //     Refund::updateOrCreate(
    //         [
    //             'refund_transaction_id' => $refundId,
    //             'payment_gateway' => 'stripe'
    //         ],
    //         [
    //             'order_id'          => $payment->order_id,
    //             'payment_id'        => $payment->id,
    //             'payment_reference' => $paymentIntentId,
    //             'amount'            => $refundAmount,
    //             'currency'          => $charge->currency ?? 'usd',
    //             'status'            => 'succeeded',
    //             'payload'           => json_encode($charge->toArray()),
    //         ]
    //     );

    //     $payment->status = 'refunded';
    //     $payment->save();

    //     // Update order status (IMPORTANT)
    //     $order = Order::find($payment->order_id);
        
    //     if ($order) {
    //         $order->payment_status = 'refunded';
    //         $order->save();
    //     }

    //     Log::info('Refund processed', [
    //         'order_id' => $payment->order_id
    //     ]);

    //     Log::info('Refund processed', ['order_id' => $payment->order_id]);
    // }

    // public function handlePaymentFailed_12345($orderId)
    // {
    //     $order = Order::find($orderId);

    //     if ($order && $order->payment_status === 'not_paid') {
    //         $order->payment_status = 'failed';
    //         $order->status = 'cancelled';
    //         $order->save();

    //         Log::info('Payment handle failed', ['order_id' => $orderId]);
    //     }
    //     return redirect()->route('front-end.home');
    // }

    /* 
      ======== Stripe PaymentIntent Method Integeration Here ========
    */
    public function createPaymentIntent(Order $order)
    {
        $paymentAmount = round((float) $order->grand_total, 2);
        $stripeAmount = (int) round($paymentAmount * 100);

        Log::info("grand_total_in_cents", [
            'grand_total' => $paymentAmount,
            'grand_total_in_cents' => $stripeAmount
        ]);

        if ($stripeAmount <= 0) {
            Log::error('Invalid grand total for payment intent', ['order_id' => $order->id, 'grand_total' => $order->grand_total]);
            throw new \Exception('Invalid order total for payment processing.');
        }  

        $stripe = $this->stripeClient();

        $payment = Payment::where('order_id', $order->id)
            ->where('payment_gateway', 'stripe')
            ->whereNotNull('payment_intent_id')
            ->first()
        ;

        if ($payment && $payment->payment_intent_id) {
            try {
                $existPaymentIntent = $stripe->paymentIntents->retrieve($payment->payment_intent_id);

                Log::info('Existing PaymentIntent found', [
                    'order_id'  => $order->id,
                    'intent_id' => $existPaymentIntent->id,
                    'status'    => $existPaymentIntent->status
                ]);

                // If payment already 'succeeded', 'requires_payment_method', 'processing', 'canceled' 
                // check amount mismatch FIRST, before any early returns 
                // If mismatch, ignore old intent and create a new one. This covers a changed order total.

                if ($existPaymentIntent->amount !== $stripeAmount || $existPaymentIntent->status === 'canceled') {
                    $existPaymentIntent = null;

                    Log::info('Existing PI reset', [
                        'order_id' => $order->id
                    ]);

                } elseif (in_array($existPaymentIntent->status, ['succeeded', 'requires_payment_method', 'processing'])) {
                    log::info('start existPaymentIntent in Array:- ', $existPaymentIntent->status);

                    return $existPaymentIntent;
                }
                
                // Only reaches here if status is requires_capture or requires_confirmation etc.
                if ($existPaymentIntent) {
                    log::info('requires_capture or requires_confirmation etc:- ', $existPaymentIntent->status);
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
                'amount' => $stripeAmount,
                'currency' => 'usd',
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
                'idempotency_key' => 'payment-intent-order-' . $order->id . '-' . $stripeAmount,
            ]
        );
        Log::info('Stripe PaymentIntent Created', [
            'order_id'        => $order->id,
            'intent_id'       => $paymentIntent->id,
            'idempotency_key' => 'payment-intent-order-' . $order->id . '-' . $stripeAmount
        ]);

        // save payment_intent_id immediately
        $payment = Payment::updateOrCreate(
            [
                'payment_intent_id' => $paymentIntent->id,
                'payment_gateway' => 'stripe'
            ],
            [
                'order_id'         => $order->id,
                'user_id'          => $order->user_id,
                'payment_gateway'  => 'stripe',
                'amount'           => $paymentAmount,
                'currency'         => 'usd',
                'status'           => 'pending',
                'payload'          => json_encode($paymentIntent->toArray()),
            ]
        );

        return $paymentIntent;
    }

    /******************** SUCCESS PAGE ********************/
    public function success(Request $request)
    {
        $orderId = $request->get('orderId');

        Log::info('new_success function', ['orderId' => $orderId]);

        if (!$orderId) { abort(404); }

        $order = Order::where('id', $orderId)->where('user_id', auth()->id())->firstOrFail();

        $stripe  = $this->stripeClient();

        $payment = Payment::where('order_id', $order->id)
            ->where('payment_gateway', 'stripe')
            ->whereNotNull('payment_intent_id')
            ->latest()
            ->first()
        ;

        if ($payment && $payment->payment_intent_id) {
            $paymentIntent = $stripe->paymentIntents->retrieve($payment->payment_intent_id);

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
                    $order->status = 'confirmed';
                    $order->save();

                    Log::info('paymentIntent of paymetnt status save:- ', $order->payment_status);
                }
            });
            // Refresh $order after transaction 
            $order = $order->fresh();
            Log::info('Order Referesh Command are Run.');
        } 

        if ($order->payment_status === 'paid') {
            Cart::destroy();
            session()->forget('couponCode');
        }

        return view('front-end.thankuPage-stripe', compact('order', 'payment'));
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

    /************* WEBHOOK HANDLER *************/
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
            'payment_intent.canceled',
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

        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->where('payment_gateway', 'stripe')->first();

        if (!$payment) {
            Log::error('Payment not found', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return null;
        }

        $order = Order::lockForUpdate()->find($payment->order_id);            
        
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

        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->where('payment_gateway', 'stripe')->first();

        if (!$payment) {
            Log::error('Payment not found for this PaymentIntent', [
                'payment_intent_id' => $paymentIntent->id
            ]);

            return null;
        }

        $transactionId = 'TXN-' . strtoupper(uniqid()) . '-' . $orderId;

        $order->payment_status = 'paid';
        $order->status         = 'confirmed';
        $order->save();

        $payment->update([
            'transaction_id'    => $transactionId,
            'payment_intent_id' => $paymentIntent->id,
            'reference_id'      => $paymentIntent->latest_charge ?? null,
            'status'            => 'succeeded',
            'payload'           => json_encode($paymentIntent->toArray()),
        ]);   

        DB::afterCommit(function () use ($orderId, $payment) {
            ProcessStripeWebhook::dispatch($orderId, $payment->id)->delay(now()->addSeconds(5));
        });

        return $payment->id;
    }

    public function storeWebhookEvent($event, $orderId, $userId, $payload, $paymentId = null)
    {
        $object = $event->data->object;

        WebhookEvent::updateOrCreate(
            [
                'event_id' => $event->id,
                'payment_gateway'   => 'stripe'
            ],
            [   
                'order_id'        => $orderId,
                'user_id'         => $userId,
                'payment_id'      => $paymentId,
                'payment_gateway' => 'stripe',
                'event_type'      => $event->type,
                'resource_id'     => $object->id ?? null,
                'processed'       => true,
                'payload'         => json_encode($payload),
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
        }

        $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();

        if (!$payment) {
            Log::warning('Payment not found for refund', ['pi_id' => $paymentIntentId]);
            return;
        }

        $refund   = $charge->refunds->data[0] ?? null;
        $refundId = $refund->id ?? null;

        $refundAmount = ($refund) ? (($refund->amount ?? 0) / 100) : 0;

        Refund::updateOrCreate(
            [
                'stripe_refund_id' => $refundId ?? null,
                'payment_gateway'   => 'stripe'
            ],
            [
                'order_id'              => $payment->order_id,
                'payment_id'            => $payment->id,
                'payment_intent_id' => $payment->payment_intent_id,
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

    // private function authorizeEnvironment()
    // {
    //     return config('services.authorize.env') === 'production'
    //         ? \net\authorize\api\constants\ANetEnvironment::PRODUCTION
    //         : \net\authorize\api\constants\ANetEnvironment::SANDBOX;
    // }

    // private function authorizeMerchantAuthentication()
    // {
    //     $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    //     $merchantAuthentication->setName(config('services.authorize.login_id'));
    //     $merchantAuthentication->setTransactionKey(config('services.authorize.transaction_key'));

    //     return $merchantAuthentication;
    // }

    // private function verifyAuthorizeWebhookSignature(string $payload, ?string $signature): bool
    // {
    //     $signatureKey = (string) config('services.authorize.signature_key');

    //     if ($payload === '' || empty($signature) || $signatureKey === '') {
    //         return false;
    //     }

    //     $key = ctype_xdigit($signatureKey) && strlen($signatureKey) % 2 === 0
    //         ? hex2bin($signatureKey)
    //         : $signatureKey;

    //     $expected = 'sha512=' . hash_hmac('sha512', $payload, $key);

    //     return hash_equals(strtolower($expected), strtolower($signature));
    // }

    // private function getAuthorizeTransactionDetails(string $transactionId)
    // {
    //     $anetRequest = new AnetAPI\GetTransactionDetailsRequest();
    //     $anetRequest->setMerchantAuthentication($this->authorizeMerchantAuthentication());
    //     $anetRequest->setTransId($transactionId);

    //     $controller = new AnetController\GetTransactionDetailsController($anetRequest);

    //     return $controller->executeWithApiResponse($this->authorizeEnvironment());
    // }

    // private function authorizePayloadFromResponse($response): string
    // {
    //     return json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
    // }

    // private function markAuthorizeOrderPaid(Order $order, Payment $payment): void
    // {
    //     if ($order->payment_status !== 'paid') {
    //         $order->payment_status = 'paid';
    //     }

    //     $order->status = 'confirmed';
    //     $order->save();

    //     $payment->status = 'succeeded';
    //     $payment->save();
    // }

    // private function verifyAuthorizeTransactionForOrder(string $transactionId, ?Order $order): array
    // {
    //     $detailsResponse = $this->getAuthorizeTransactionDetails($transactionId);

    //     if ($detailsResponse === null || $detailsResponse->getMessages()->getResultCode() !== 'Ok') {
    //         return [
    //             'verified' => false,
    //             'msg'      => 'Authorize.Net transaction details could not be verified.',
    //             'response' => $detailsResponse,
    //         ];
    //     }

    //     $transaction      = $detailsResponse->getTransaction();
    //     $transactionOrder = $transaction?->getOrder();
    //     $invoiceNumber    = $transactionOrder?->getInvoiceNumber();
    //     $authAmount       = (float) ($transaction?->getAuthAmount() ?? 0);
    //     $settleAmount     = (float) ($transaction?->getSettleAmount() ?? 0);
    //     $amount = $authAmount > 0 ? $authAmount : $settleAmount;
    //     $status = strtolower((string) $transaction?->getTransactionStatus());

    //     $validStatuses = [
    //         'authorizedpendingcapture',
    //         'capturependingsettlement',
    //         'capturedpendingsettlement',
    //         'settledsuccessfully',
    //     ];

    //     if ($order && $invoiceNumber && (string) $invoiceNumber !== (string) $order->id) {
    //         return [
    //             'verified' => false,
    //             'msg'      => 'Authorize.Net invoice number does not match order.',
    //             'response' => $detailsResponse,
    //         ];
    //     }

    //     if ($order && $amount > 0 && abs($amount - (float) $order->grand_total) > 0.01) {
    //         return [
    //             'verified' => false,
    //             'msg'      => 'Authorize.Net amount does not match order total.',
    //             'response' => $detailsResponse,
    //         ];
    //     }

    //     if ($status && !in_array(str_replace(' ', '', $status), $validStatuses, true)) {
    //         return [
    //             'verified' => false,
    //             'msg'      => 'Authorize.Net transaction status is not payable.',
    //             'response' => $detailsResponse,
    //         ];
    //     }

    //     return [
    //         'verified' => true,
    //         'msg'      => 'verified',
    //         'response' => $detailsResponse,
    //         'transaction' => $transaction,
    //     ];
    // }

    // public function authorizeWebhook(Request $request)
    // {
    //     $payload   = $request->getContent();
    //     $signature = $request->header('X-ANET-Signature');

    //     if (!$this->verifyAuthorizeWebhookSignature($payload, $signature)) {
    //         Log::critical('Authorize.Net webhook signature verification failed', [
    //             'ip' => $request->ip(),
    //             'signature' => $signature,
    //         ]);

    //         return response()->json(['error' => 'Invalid signature'], 401);
    //     }

    //     $event = json_decode($payload, true);

    //     if (!is_array($event)) {
    //         return response()->json(['error' => 'Invalid payload'], 400);
    //     }

    //     $eventId       = $event['notificationId'] ?? null;
    //     $eventType     = $event['eventType'] ?? 'unknown';
    //     $transactionId = data_get($event, 'payload.id');
    //     $invoiceNumber = data_get($event, 'payload.invoiceNumber');
    //     $authAmount    = data_get($event, 'payload.authAmount');

    //     if (!$eventId || !$transactionId) {
    //         WebhookEvent::create([
    //             'order_id'        => null,
    //             'payment_id'      => null,
    //             'user_id'         => null,
    //             'payment_gateway' => 'authorize_net',
    //             'event_id'        => $eventId,
    //             'event_type'      => $eventType,
    //             'resource_id'     => $transactionId,
    //             'processed'       => false,
    //             'payload'         => $event,
    //         ]);

    //         return response()->json(['status' => 'ignored'], 200);
    //     }

    //     if (WebhookEvent::where('event_id', $eventId)->where('payment_gateway', 'authorize_net')->exists()) {
    //         return response()->json(['status' => 'duplicate'], 200);
    //     }

    //     try {
    //         DB::transaction(function () use ($event, $eventId, $eventType, $transactionId, $invoiceNumber, $authAmount, &$webhook) {
    //             $payment = Payment::where('payment_gateway', 'authorize_net')
    //                 ->where('transaction_id', $transactionId)
    //                 ->lockForUpdate()
    //                 ->first();

    //             $order = $payment
    //                 ? Order::lockForUpdate()->find($payment->order_id)
    //                 : Order::lockForUpdate()->find($invoiceNumber);

    //             if (!$payment && $order) {
    //                 $payment = Payment::create([
    //                     'order_id'          => $order->id,
    //                     'user_id'           => $order->user_id,
    //                     'payment_gateway'   => 'authorize_net',
    //                     'payment_intent_id' => $transactionId,
    //                     'transaction_id'    => $transactionId,
    //                     'amount'            => $authAmount ?: $order->grand_total,
    //                     'currency'          => 'USD',
    //                     'status'            => 'pending',
    //                     'payload'           => json_encode($event),
    //                 ]);
    //             }

    //             $webhook = WebhookEvent::create([
    //                 'order_id'        => $order?->id,
    //                 'payment_id'      => $payment?->id,
    //                 'user_id'         => $order?->user_id,
    //                 'payment_gateway' => 'authorize_net',
    //                 'event_id'        => $eventId,
    //                 'event_type'      => $eventType,
    //                 'resource_id'     => $transactionId,
    //                 'processed'       => false,
    //                 'payload'         => $event,
    //             ]);

    //             if (!$payment || !$order) {
    //                 Log::warning('Authorize.Net webhook received without matching order/payment', [
    //                     'event_id'       => $eventId,
    //                     'transaction_id' => $transactionId,
    //                     'invoice_number' => $invoiceNumber,
    //                 ]);

    //                 return;
    //             }

    //             if (in_array($eventType, [
    //                 'net.authorize.payment.authcapture.created',
    //                 'net.authorize.payment.priorAuthCapture.created',
    //                 'net.authorize.payment.capture.created',
    //             ], true)) {
    //                 $verification = $this->verifyAuthorizeTransactionForOrder($transactionId, $order);

    //                 if (!$verification['verified']) {
    //                     $payment->status  = 'failed';
    //                     $payment->payload = $this->authorizePayloadFromResponse($verification['response']);
    //                     $payment->save();

    //                     $order->status = 'payment_verification_failed';
    //                     $order->save();

    //                     Log::error('Authorize.Net webhook transaction verification failed', [
    //                         'event_id'       => $eventId,
    //                         'transaction_id' => $transactionId,
    //                         'message'        => $verification['msg'],
    //                     ]);

    //                     return;
    //                 }

    //                 $payment->amount  = $authAmount ?: $payment->amount;
    //                 $payment->payload = $this->authorizePayloadFromResponse($verification['response']);

    //                 $this->markAuthorizeOrderPaid($order, $payment);

    //             } elseif ($eventType === 'net.authorize.payment.void.created') {
    //                 $payment->status  = 'failed';
    //                 $payment->payload = json_encode($event);
    //                 $payment->save();

    //                 $order->status = 'payment_voided';
    //                 $order->save();

    //             } elseif ($eventType === 'net.authorize.payment.refund.created') {
    //                 $payment->status  = 'refunded';
    //                 $payment->payload = json_encode($event);
    //                 $payment->save();

    //                 $order->status = 'refunded';
    //                 $order->save();
    //             }

    //             $webhook->payment_id = $payment->id;
    //             $webhook->order_id   = $order->id;
    //             $webhook->user_id    = $order->user_id;
    //             $webhook->processed  = true;
    //             $webhook->save();
    //         });

    //         return response()->json(['status' => 'success'], 200);

    //     } catch (\Throwable $th) {
    //         Log::error('Authorize.Net webhook processing failed', [
    //             'event_id'   => $eventId,
    //             'event_type' => $eventType,
    //             'error'      => $th->getMessage(),
    //         ]);

    //         return response()->json(['error' => 'Webhook processing failed'], 500);
    //     }
    // }

    // public function authorizeCharge(Request $request, $order)
    // {
    //     // Create a MerchantAuthenticationType object with the authentication details
    //     // which are availale in the config.php file
    //     $merchantAuthentication = $this->authorizeMerchantAuthentication();

    //     // Create the payment object for a payment nonce
    //     $opaqueData = new AnetAPI\OpaqueDataType();
    //     $opaqueData->setDataDescriptor($request->dataDescriptor);
    //     $opaqueData->setDataValue($request->dataValue);

    //     $paymentOne = new AnetAPI\PaymentType();

    //     Log::info("Data of opaqueData", [
    //         'opaqueData' => $opaqueData
    //     ]);

    //     $paymentOne->setOpaqueData($opaqueData);

    //     // Create a TransactionRequestType object and add the previous objects to it
    //     $transactionRequestType = new AnetAPI\TransactionRequestType();

    //     $transactionRequestType->setTransactionType("authCaptureTransaction"); 
    //     $transactionRequestType->setAmount($order->grand_total);
    //     $transactionRequestType->setPayment($paymentOne);

    //     $orderType = new AnetAPI\OrderType();
    //     $orderType->setInvoiceNumber((string) $order->id);
    //     $orderType->setDescription('Order #' . $order->id);
    //     $transactionRequestType->setOrder($orderType);

    //     // Assemble the complete transaction request
    //     $anetRequest = new AnetAPI\CreateTransactionRequest();

    //     $anetRequest->setMerchantAuthentication($merchantAuthentication);
    //     $anetRequest->setTransactionRequest($transactionRequestType);

    //     // Create the controller and get the response
    //     $controller = new AnetController\CreateTransactionController($anetRequest);

    //     $response = $controller->executeWithApiResponse($this->authorizeEnvironment());
        
    //     if ($response === null) {
    //         Log::error('Authorize response is null', ['order_id' => $order->id]);

    //         $order->status = 'payment_failed';
    //         $order->save();

    //         Payment::create([
    //             'order_id'        => $order->id,
    //             'user_id'         => $order->user_id,
    //             'payment_gateway' => 'authorize_net',
    //             'amount'          => $order->grand_total,
    //             'currency'        => 'USD',
    //             'status'          => 'failed',
    //             'payload'         => null,
    //         ]);

    //         return response()->json([
    //             'status' => false,
    //             'msg' => 'Payment gateway did not return a response. Please try again.'
    //         ]);
    //     }

    //     // Since the API request was successful, look for a transaction response
    //     // and parse it to display the results of authorizing the card
    //     $tresponse = $response->getTransactionResponse();

    //     if ($response->getMessages()->getResultCode() == "Ok") {
    //         if ($tresponse !== null && $tresponse->getMessages() !== null) {
    //             $order->payment_status = 'paid';
    //             $order->save();

    //             $payment = Payment::updateOrCreate(
    //                 [
    //                     'payment_gateway' => 'authorize_net',
    //                     'transaction_id' => $tresponse->getTransId(),
    //                 ],
    //                 [
    //                     'order_id' => $order->id,
    //                     'user_id'  => $order->user_id,
    //                     'payment_intent_id' => $tresponse->getTransId(),
    //                     'amount'   => $order->grand_total,
    //                     'currency' => 'USD',
    //                     'status'   => 'succeeded',
    //                     'payload'  => $this->authorizePayloadFromResponse($response),
    //                 ]
    //             );

    //             WebhookEvent::updateOrCreate(
    //                 [
    //                     'event_id' => 'authorize-direct-' . $tresponse->getTransId(),
    //                     'payment_gateway' => 'authorize_net',
    //                 ],
    //                 [
    //                     'order_id'    => $order->id,
    //                     'payment_id'  => $payment->id,
    //                     'user_id'     => $order->user_id,
    //                     'event_type'  => 'direct.authCaptureTransaction',
    //                     'resource_id' => $tresponse->getTransId(),
    //                     'processed'   => true,
    //                     'payload'     => json_decode($this->authorizePayloadFromResponse($response), true),
    //                 ]
    //             );

    //             Log::info('Authorize response', (array)$response);
                
    //             return response()->json([
    //                 'status'   => true,
    //                 'orderId'  => $order->id,
    //                 'redirect' => route('front-end.thankuPage')
    //             ]);
    //         } else {
    //             $errorMsg = 'Unknown error';

    //             if ($tresponse !== null && $tresponse->getErrors() !== null) {
    //                 $errorMsg = $tresponse->getErrors()[0]->getErrorText() ?? $errorMsg;
    //             }

    //             Log::error('Authorize Transaction Error', [
    //                 'error' => $errorMsg
    //             ]);

    //             $order->status = 'payment_failed';
    //             $order->save();

    //             Payment::create([
    //                 'order_id'        => $order->id,
    //                 'user_id'         => $order->user_id,
    //                 'payment_gateway' => 'authorize_net',
    //                 'amount'          => $order->grand_total,
    //                 'currency'        => 'USD',
    //                 'status'          => 'failed',
    //                 'payload'         => $this->authorizePayloadFromResponse($response),
    //             ]);

    //             return response()->json([
    //                 'status' => false,
    //                 'msg'    => 'Payment failed',
    //                 'error'  => $errorMsg
    //             ]);
    //         }
    //     } else {
    //         $errorMsg = $response->getMessages()->getMessage()[0]->getText();

    //         Log::error('Authorize General Error', [
    //             'error' => $errorMsg
    //         ]);

    //         $order->status = 'payment_failed';
    //         $order->save();

    //         Payment::create([
    //             'order_id'        => $order->id,
    //             'user_id'         => $order->user_id,
    //             'payment_gateway' => 'authorize_net',
    //             'amount'          => $order->grand_total,
    //             'currency'        => 'USD',
    //             'status'          => 'failed',
    //             'payload'         => $this->authorizePayloadFromResponse($response),
    //         ]);

    //         return response()->json([
    //             'status' => false,
    //             'msg'  => $errorMsg
    //         ]);
    //     }
        
    // }

    

}
