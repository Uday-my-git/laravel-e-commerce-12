@extends('front-end.layouts.app')

@section('content')

<style>
   .success-wrapper{
      background: linear-gradient(135deg,#f0fff4,#ecfdf5);
      min-height: 70vh;
      display:flex;
      align-items:center;
   }
   .success-card{
      border-radius: 18px;
      overflow: hidden;
   }
   .success-icon{
      width:90px;
      height:90px;
      border-radius:50%;
      background:#22c55e;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:42px;
      color:#fff;
      box-shadow:0 10px 25px rgba(34,197,94,.35);
   }
   .badge-soft{
      background:rgba(34,197,94,.1);
      color:#16a34a;
      font-weight:600;
   }
   .divider{
      border-top:1px dashed #dee2e6;
   }
   .action-btn{
      min-width:180px;
   }
</style>

@php
   $orderId = request()->get('orderId');
@endphp

<section class="success-wrapper py-5">
   <div class="container">
      <div class="row justify-content-center">
         <div class="col-lg-7 col-md-9">
            <div class="card success-card shadow-lg border-0">
               <div class="card-body text-center p-5">
                  <div class="d-flex justify-content-center mb-4">
                     <div class="success-icon">✓</div>
                  </div>

                  <h1 class="fw-bold text-success mb-2">Payment Successful!</h1>
                  <p class="text-muted fs-5">
                     Thank you for your purchase. Your order has been confirmed 🎉
                  </p>

                  <span class="badge badge-soft px-3 py-2 mt-2">
                     Transaction Completed Securely
                  </span>

                  <div class="divider my-4"></div>
                  <!-- Order Info -->
                  <div class="row text-start g-3">
                     <div class="col-6">
                        <small class="text-muted">Transaction ID</small>
                        <div class="fw-semibold">#{{ $payment->transaction_id }}</div>
                     </div>
                     <div class="col-6">
                        <small class="text-muted">Order ID</small>
                        <div class="fw-semibold">#{{ $order->id }}</div>
                     </div>
                     
                     <div class="col-6">
                        <small class="text-muted">Session ID:</small>
                        <div class="fw-semibold">#{{ $session->id ?? $order->stripe_payment_intent ?? 'N/A' }}</div>
                     </div>
                     

                     <div class="col-6">
                        <small class="text-muted">Payment Status</small>
                        <div>
                           <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                              {{ strtoupper($order->payment_status ?? 'Pending') }}
                           </span>
                        </div>
                     </div>

                     <div class="col-6">
                        <small class="text-muted">Amount Paid</small>
                        <div class="fw-semibold">
                           @if (isset($session->amount_total))
                              {{ number_format($session->amount_total / 100, 2) }}
                              {{ strtoupper($session->currency ?? 'usd') }}
                           @else
                              {{ number_format($order->grand_total ?? 0, 2) }} USD
                           @endif
                        </div>
                     </div>

                     <div class="col-6">
                        <small class="text-muted">Payment Method</small>
                        <div class="fw-semibold">
                           @if ($session->payment_method_types[0] === 'card')
                              {{ ucfirst($session->payment_method_types[0]) }}       
                           @else
                              {{ number_format($order->payment_method_types[0]) }}    
                           @endif
                        </div>
                     </div>
                  </div>

                  <div class="divider my-4"></div>

                  <!-- Trust Message -->
                  <div class="alert alert-success border-0 text-start">
                     <strong>What happens next?</strong>
                     <ul class="mb-0 mt-2 ps-3">
                        <li>Your order is being processed</li>
                        <li>You’ll receive a confirmation email</li>
                        <li>Our team will prepare your shipment</li>
                     </ul>
                  </div>

                  <!-- Actions -->
                  <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                     <a href="{{ route('front-end.shop') }}" class="btn btn-outline-primary action-btn">
                        🛍 Continue Shopping
                     </a>

                     <a href="{{ route('front-end.home') }}" class="btn btn-primary action-btn">
                        🏠 Go to Home
                     </a>

                     <a href="{{ route('front-end.orderGet') }}" class="btn btn-outline-secondary action-btn">
                        📦 View Orders
                     </a>
                  </div>

                  <!-- Support -->
                  <div class="mt-4 text-muted small">
                     Need help? <a href="{{ route('front-end.home') }}" class="text-decoration-none fw-semibold">Contact Support</a>
                  </div>

               </div>
            </div>
         </div>
      </div>
   </div>
</section>

@endsection