@extends('admin.layouts.app')

@section('content')

<!-- Toastr JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>

<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Order</h1>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
      {{-- @include('admin.message') --}}

      <div class="card">
         <form action="" name="search" method="GET">
            <div class="card-header">
               <button type="button" class="btn btn-default" onclick="window.location.href='{{ route('orders.listing') }}'">Reset</button>

               <div class="card-tools">
                  <div class="input-group input-group" style="width: 250px;">
                     <input type="text" name="search" class="form-control float-right" placeholder="Search" value="{{ Request::get('search') }}">
                     
                     <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                           <i class="fas fa-search"></i>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </form>
         <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                  <tr>
                     <th>Order ID</th>
                     <th>Customer Name</th>
                     <th>Email</th>
                     <th>Phone</th>
                     <th>Status</th>
                     <th>Total</th>
                     <th width="100">Date Purchased</th>
                  </tr>
               </thead>
               <tbody>
                  @if ($orders->isNotEmpty())
                     @foreach ($orders as $orderItem)
                        <tr>
                           <td><a href="{{ route('orders.orderDetail', $orderItem->id) }}" class="btn btn-default"> {{ $orderItem->id }} </a></td>
                           <td>{{ $orderItem->name }}</td>
                           <td>{{ $orderItem->email }}</td>
                           <td>{{ $orderItem->mobile }}</td>
                           <td>
                              @if ($orderItem->status == 'pending')
                                 <span class="badge bg-danger">Pending</span>
                              @elseif ($orderItem->status == 'shipped') 
                                 <span class="badge bg-info">Shipped</span>
                              @elseif ($orderItem->status == 'deliverd') 
                                 <span class="badge bg-success">Delivered</span> 
                              @else 
                                 <span class="badge bg-danger">Cancelled</span>
                              @endif
                           </td>
                           <td>R.s {{ $orderItem->grand_total }}</td>
                           <td>{{ \Carbon\Carbon::parse($orderItem->created_at)->format('d M, Y') }}</td>
                        </tr>  
                     @endforeach 
                  @else
                     <tr><td style="text-align: center; color:black" colspan="10">Orders Not Found</td></tr>
                  @endif    
               </tbody>
            </table>										
         </div>
         <div class="card-footer clearfix">
            {{ $orders->links() }}
         </div>
      </div>
   </div>
</section>
@endsection