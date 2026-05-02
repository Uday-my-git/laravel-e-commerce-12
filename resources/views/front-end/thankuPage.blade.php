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
      width:60px;
      height:60px;
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

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- jQuery (required for Toastr) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@if (session('success'))
   <script>
      toastr.success("{{ session('success') }}");
   </script>
@endif 

<section class="section-5 pt-3 pb-3 mb-3 bg-white">
   <div class="container">
      <div class="light-font">
         <ol class="breadcrumb primary-color mb-0">
            <li class="breadcrumb-item"><a class="white-text" href="{{ route('front-end.home') }}">Home</a></li>
            <li class="breadcrumb-item"><a class="white-text" href="{{ route('front-end.shop') }}">Shop</a></li>
            <li class="breadcrumb-item">Thank You Page</li>
         </ol>
      </div>
   </div>
</section>

{{-- <section class="section-9 pt-4">
   <div class="container">
      <div class="row">
         <div class="col-md-12">
            <div class="card-body d-flex justify-content-center align-item-center">
               @php
                  $orderId = request()->get('orderId');
               @endphp
               
               <h2>🎉 Thank you for your order!</h2>
               
            </div>

            <p class="card-body d-flex justify-content-center align-item-center">Your order ID is &nbsp;<strong>#{{ $orderId }}</strong></p>

         </div>
         
      </div>
   </div>
</section> --}}

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
                        <div class="fw-semibold">#{{ $payment->transaction_id ?? 'N/A' }}</div>
                     </div>
                     <div class="col-6">
                        <small class="text-muted">Order ID</small>
                        <div class="fw-semibold">#{{ $order->id }}</div>
                     </div>
                     
                     <div class="col-6">
                        <small class="text-muted">Payment Intent ID:</small>
                        <div class="fw-semibold">#{{ $payment->payment_intent_id ?? 'N/A' }}</div>
                     </div>
                     

                     <div class="col-6">
                        <small class="text-muted">Payment Status</small>
                        <div>
                           <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }}">
                              @if ($order->payment_status === 'not_paid')
                                 C.O.D (Pending)
                              @else
                                 {{ strtoupper($payment->payment_gateway . ' ' . $order->payment_status) }}  
                              @endif
                           </span>
                        </div>
                     </div>

                     <div class="col-6">
                        <small class="text-muted">Amount Paid</small>
                        <div class="fw-semibold">
                           ${{ number_format($order->grand_total ?? 0, 2) }} (USD)
                        </div>
                     </div>

                     <div class="col-6">
                        <small class="text-muted">Created At</small>
                        <div class="fw-semibold">
                           {{ $order->created_at }}
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

{{-- @section('custom-js')
<script>

   const urlParams = new URLSearchParams(window.location.search);
   const orderId = urlParams.get('orderId');

</script>
@endsection --}}




