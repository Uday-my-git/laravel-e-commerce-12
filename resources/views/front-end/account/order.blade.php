@extends('front-end.layouts.app')

@section('content')
   <section class="section-5 pt-3 pb-3 mb-3 bg-white">
      <div class="container">
         <div class="light-font">
               <ol class="breadcrumb primary-color mb-0">
                  <li class="breadcrumb-item"><a class="white-text" href="#">My Account</a></li>
                  <li class="breadcrumb-item">Settings</li>
               </ol>
         </div>
      </div>
   </section>

   <section class=" section-11">
      <div class="container mt-5">
         <div class="row">
            <div class="col-md-3">
               @include('front-end.account.common.sidebar')
            </div>
            <div class="col-md-9">
               <div class="card">
                  <div class="card-header">
                     <h2 class="h5 mb-0 pt-2 pb-2">My Orders</h2>
                  </div>
                  <div class="card-body p-4">
                     <div class="table-responsive">
                        <table class="table">
                           <thead> 
                              <tr>
                                 <th>Orders #</th>
                                 <th>Date Purchased</th>
                                 <th>Payment Status</th>
                                 <th>Status</th>
                                 <th>Total</th>
                              </tr>
                           </thead>
                           <tbody>
                              @if ($order->isNotEmpty())
                                 @foreach ($order as $orderItem)
                                    <tr>
                                       <td>
                                          <button class="btn btn-info">
                                             <a href="{{ route('front-end.get_orderDetail', $orderItem->id) }}">{{ $orderItem->id }}</a>
                                          </button>
                                       </td>
                                       <td>{{ \Carbon\Carbon::parse($orderItem->created_at)->format('d M, Y') }}</td>
                                       <td>
                                          @if ($orderItem->payment_status == 'paid')
                                             <strong>Online Paid</strong>
                                          @else
                                             <strong>COD</strong> 
                                          @endif
                                       </td>
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
                                       <td>R.S {{ number_format($orderItem->grand_total, 2) }}</td>
                                    </tr>       
                                 @endforeach    
                              @else
                                 <tr><td style="text-align: center; color:rgb(221, 62, 62)" colspan="4">No Any Order Found</td></tr>
                              @endif                    
                           </tbody>
                        </table>
                     </div>                            
                  </div>
                  <div class="card-footer clearfix">
                     {{ $order->links() }}
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>
    
@endsection