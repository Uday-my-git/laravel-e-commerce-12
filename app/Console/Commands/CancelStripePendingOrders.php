<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;


class CancelStripePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
    */
    protected $signature = 'app:cancel-stripe-pending-orders';

    /**
     * The console command description.
     *
     * @var string
    */
    protected $description = 'Expire unpaid Stripe orders older than 2 minutes';

    /**
     * Execute the console command.
    */

    /* PaymentIntents Method */
    public function handle()
    {
        Log::info('Cancel pending orders CRON JOB command started');

        $stripe = new StripeClient(config('services.stripe.secret'));

        $totalCount = 0;
        $cancellableStatuses = ['requires_payment_method', 'requires_confirmation', 'requires_action', 'requires_capture'];

        $orders = Order::where('payment_status', 'not_paid')
            ->whereNotNull('stripe_payment_intent')
            ->where('created_at', '<=', now()->subMinutes(10))
            ->chunk(200, function ($orders) use ($stripe, $cancellableStatuses, $totalCount) {        
                foreach ($orders as $order) {
                    try {
                        $paymentIntent = $stripe->paymentIntents->retrieve($order->stripe_payment_intent);
                        
                        $cancellableStatuses = ['requires_payment_method', 'requires_confirmation', 'requires_action', 'requires_capture'];

                        if (in_array($paymentIntent->status, $cancellableStatuses)) {
                            $stripe->paymentIntents->cancel($order->stripe_payment_intent);
                        }

                        $order->payment_status = 'expired';
                        $order->status = 'cancelled';
                        $order->save();

                        $totalCount++;

                        Log::info('Order expired by cron', ['order_id' => $order->id]);

                    } catch (\Throwable $th) {
                        Log::warning('Failed to cancel Stripe PaymentIntent from cron', [
                            'order_id' => $order->id,
                            'error'    => $th->getMessage()
                        ]);
                        continue;
                    }
                }
                Log::info('Cancel pending orders CRON JOB completed', [
                    'expired_orders_count' => $orders->count(),
                    'cutoff'               => now()->subMinutes(10)->toDateTimeString(),
                ]);
            });
        ;

        
    }

    /* Handle function here used only Checkout session method of stripe */
    // public function handle()
    // {
    //     Log::info('Cancel pending orders CRON JOB command started');
        
    //     $cutoff = now()->subMinutes(2);
        
    //     $orders = Order::where('payment_status', 'not_paid')
    //         ->whereNotNull('stripe_session_id')
    //         ->where('created_at', '<=', $cutoff)
    //         ->get()
    //     ;

    //     $stripe = new StripeClient(config('services.stripe.secret'));

    //     foreach ($orders as $order) {
    //         try {
    //             $session = $stripe->checkout->sessions->retrieve($order->stripe_session_id);

    //             if ($session && $session->status === 'open') {
    //                 $stripe->checkout->sessions->expire($order->stripe_session_id);
    //             }
    //         } catch (\Throwable $th) {
    //             Log::warning('Failed to expire Stripe session from cron', [
    //                 'order_id' => $order->id,
    //                 'stripe_session_id' => $order->stripe_session_id,
    //                 'error' => $th->getMessage(),
    //             ]);
    //         }

    //         $order->payment_status = 'expired';
    //         $order->status = 'cancelled';
    //         $order->save();

    //         Log::info('Order expired by cron', [
    //             'order_id' => $order->id,
    //             'stripe_session_id' => $order->stripe_session_id,
    //         ]);
    //     }

    //     Log::info('Cancel pending orders CRON JOB completed', [
    //         'expired_orders_count' => $orders->count(),
    //         'cutoff' => $cutoff->toDateTimeString(),
    //     ]);
    // }
}
