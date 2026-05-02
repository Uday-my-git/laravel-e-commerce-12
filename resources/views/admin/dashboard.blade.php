@extends('admin.layouts.app')

@section('content')

<section class="content-header">					
   <div class="container-fluid">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Dashboard</h1>
         </div>
         <div class="col-sm-6"> 
         </div>
      </div>
   </div>
</section>

<section class="content">
   @include('admin.message')
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-4 col-6">
            <div class="small-box card">
               <div class="inner d-flex justify-content-between align-items-center">
                  <div>
                     <h3>{{ number_format($totalOrders) }}</h3>
                     <p>Total Orders</p>
                  </div>
                  <div class="text-right text-danger">
                     <h3>{{ number_format($cancelledOrders) }}</h3>
                     <p class="mb-0">Cancelled Orders</p>
                  </div>
                  <div class="text-right text-warning">
                     <h3>{{ number_format($pendingOrders) }}</h3>
                     <p class="mb-0">Pending Orders</p>
                  </div>
                  <div class="text-right text-info">
                     <h3>{{ number_format($deliverdOrders) }}</h3>
                     <p class="mb-0">Deliverd Orders</p>
                  </div>
                  <div class="text-right text-primary">
                     <h3>{{ number_format($shippedOrders) }}</h3>
                     <p class="mb-0">Shipped Orders</p>
                  </div>
               </div>
               <div class="icon">
                  <i class="ion ion-bag"></i>
               </div>
               <a href="{{ route('orders.listing') }}" class="small-box-footer text-dark">
                  More info <i class="fas fa-arrow-circle-right"></i>
               </a>
            </div>
         </div>
         <div class="col-lg-4 col-6">							
            <div class="small-box card">
               <div class="inner">
                  <h3>{{ number_format($totalCustomers) }}</h3>
                  <p>Total Customers</p>
               </div>
               <div class="icon">
                  <i class="ion ion-stats-bars"></i>
               </div>
               <a href="{{ route('users.listing') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
         </div>
         
         <div class="col-lg-4 col-6">							
            <div class="small-box card">
               <div class="inner">
                  <h3>{{ $totalProducts }}</h3>
                  <p>Total Products</p>
               </div>
               <div class="icon">
                  <i class="ion ion-person-add"></i>
               </div>
               <a href="{{ route('product.listing') }}" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
         </div>
         <div class="col-lg-4 col-6">							
            <div class="small-box card">
               <div class="inner">
                  <h3>R.s {{ number_format($totalRevenue, 2) }}</h3>
                  <p>Total Revenue</p>
               </div>
               <div class="icon">
                  <i class="ion ion-person-add"></i>
               </div>
               <a href="#" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
         </div>
         <div class="col-lg-4 col-6">							
            <div class="small-box card">
               <div class="inner">
                  <h3>R.s {{ number_format($revenueThisMonth, 2) }}</h3>
                  <p>This Month Revenue ({{ $thisMonthName }})</p>
               </div>
               <div class="icon">
                  <i class="ion ion-person-add"></i>
               </div>
               <a href="#" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
         </div>
         <div class="col-lg-4 col-6">							
            <div class="small-box card">
               <div class="inner">
                  <h3>R.s {{ number_format($lastThisMonth, 2) }}</h3>
                  <p>Last Month Revenue ({{ $lastMonthName }})</p>
               </div>
               <div class="icon">
                  <i class="ion ion-person-add"></i>
               </div>
               <a href="#" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
         </div>
         <div class="col-lg-4 col-6">							
            <div class="small-box card">
               <div class="inner">
                  <h3>R.s {{ number_format($revenueLastThirtyDays, 2) }}</h3>
                  <p>Last 30 days of Revenue</p>
               </div>
               <div class="icon">
                  <i class="ion ion-person-add"></i>
               </div>
               <a href="#" class="small-box-footer text-dark">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
         </div>
      </div>
   </div>					
</section>

@endsection

@section('custom-js')
<script>

   console.log('j.s loaded proper....');
   
</script>
@endsection