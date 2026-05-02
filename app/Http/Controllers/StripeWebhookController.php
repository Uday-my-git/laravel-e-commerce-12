<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Stripe webhook received');

        $payload = $request->getContent();

        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload');
            return response('Invalid payload', 400);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature');
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            Log::info('Checkout session completed', [
                'session_id' => $session->id
            ]);

            $orderId = $session->metadata->order_id ?? null;

            if (!$orderId) {
                Log::error('Order ID missing in metadata');
                return response('No order id', 400);
            }

            $order = Order::with('items')->find($orderId);

            if (!$order) {
                Log::error('Order not found', ['order_id' => $orderId]);
                return response('Order not found', 404);
            }

            // Idempotency protection (prevents double processing)
            if ($order->payment_status === 'paid') {
                Log::info('Order already paid', ['order_id' => $orderId]);
                return response('Already processed', 200);
            }

            \DB::beginTransaction();

            try {
    
                // ✅ Update payment status
                $order->payment_status = 'paid';
                // $order->stripe_session_id = $session->id;
                // $order->payment_intent = $session->payment_intent ?? null;
                $order->save();

                // Update stock
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);

                    if ($product && $product->track_qty === 'Yes') {
                        $product->qty = max(0, $product->qty - $item->qty);
                        $product->save();
                    }
                }

                // ✅ Send email (safe)
                try {
                    orderEmail($order->id, 'customer');
                } catch (\Exception $mailError) {
                    Log::error('Email failed', ['error' => $mailError->getMessage()]);
                }

                \DB::commit();

                Log::info('Payment processed successfully', [
                    'order_id' => $order->id,
                    'session_id' => $session->id
                ]);

            } catch (\Exception $e) {

                \DB::rollBack();

                Log::error('Webhook processing failed', [
                    'error' => $e->getMessage(),
                    'order_id' => $orderId
                ]);

                return response('Processing failed', 500);
            }
        }

        return response('Webhook handled', 200);
    }
}