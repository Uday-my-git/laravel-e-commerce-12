<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\OrderEmail;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $paymentId;

    public $tries = 3;
    public $timeout = 60;

    /**
     * Create a new job instance.
    */
    public function __construct($orderId, $paymentId)
    {
        $this->orderId   = $orderId;
        $this->paymentId = $paymentId;
    }

    /**
     * Execute the job.
    */
    public function handle(): void
    {
        try {
            $order = Order::with('items')->find($this->orderId);

            if (!$order) {
                Log::error('Order not found', ['order_id' => $this->orderId]);
                return;
            }

            $payment = Payment::where('order_id', $order->id)->first();

            // Prevent duplicate email
            if ($order->email_send || $order->payment_status !== 'paid') {
                Log::info('Email already sent or order not paid, skipping', [
                    'order_id' => $order->id
                ]);
                return;
            }

            if (!$order->email) {
                Log::warning('Order has no email, skipping', [
                    'order_id' => $order->id
                ]);
                return;
            }

            $mailData = [
                'subject'   => 'Thanks for your order',
                'order'     => $order,
                'payment'   => $payment,
                'paymentId' => $this->paymentId,
                'userType'  => 'customer'
            ];

            // $ccRecipients  = 'uday.thakur626@gmail.com';
            // $bccRecipients = 'thakuruday95@gmail.com';

            Mail::to($order->email)->queue(new OrderEmail($mailData));

            Mail::to(config('mail.admin_email'))->queue(new OrderEmail([
                ...$mailData,
                'userType' => 'admin'
            ]));

            $order->email_send = true;
            $order->save();

        } catch (\Throwable $th) {
            Log::error('Failed to send order email', [
                'order_id' => $this->orderId,
                'email'    => $order->email,
                'error'    => $th->getMessage(),
            ]);
            throw $th; // Re-throw so queue retries up to $tries = 3
        }
        
    }
}
