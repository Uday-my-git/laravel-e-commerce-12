@extends('admin.layouts.app')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>

	<section class="content-header">					
      <div class="container-fluid my-2">
         <div class="row mb-2">
            <div class="col-sm-6">
               <h1>Order: #{{ $orders->id }}</h1>
            </div>
            <div class="col-sm-6 text-right">
               <a onclick="window.close();" class="btn btn-primary">Close</a>
            </div>
         </div>
      </div>
   </section>

   <section class="content">
      <div class="container-fluid">
         <div class="row">
            <div class="col-md-9">
               <div class="card">
                  @include('admin.message')

                  <div class="card-header pt-3">
                     <div class="row invoice-info">
                        <div class="col-sm-4  invoice-col">
                           <h1 class="h5 mb-3">Shipping Address</h1>
                           <address>
                              <strong>{{ $orders->first_name }} {{ $orders->last_name }}</strong><br>

                              {{ $orders->address }}<br>
                              {{ $orders->city }}, {{ $orders->state }}, {{ $orders->countriesName }}<br>
                              Zip:- {{ $orders->zip }} <br>
                              Phone: {{ $orders->mobile }}<br>
                              Email: {{ $orders->email }}
                           </address>

                           <strong>Shipped Date</strong> <br>

                           @if (!empty($orders->shipped_date))
                              {{ \Carbon\Carbon::parse($orders->shipped_date)->format('d M, Y') }}
                           @else
                              n/a
                           @endif
                        </div>  
                        <div class="col-sm-4 invoice-col">
                           <b>Invoice #{{ $orders->id }}</b><br>
                           <br>

                           <b>Order ID:</b> {{ $orders->id }}<br>
                           <b>Transaction ID:</b> {{ $orders->transaction_id ?? 'n/a' }}<br>
                           <b>Total:</b> R.s {{ number_format($orders->grand_total, 2) }}<br>

                           @if ($orders->payment_status == 'paid')
                              <b>Payment Status: </b>Online Paid (Stripe Payment)
                           @else
                              <b>Payment Status: </b>COD 
                           @endif

                           <br>

                           <b>Status:</b> 

                           @if ($orders->status == 'pending')
                              <span class="badge bg-danger">Pending</span>
                           @elseif ($orders->status == 'shipped') 
                              <span class="badge bg-info">Shipped</span>
                           @elseif ($orders->status == 'deliverd') 
                              <span class="badge bg-success">Delivered</span> 
                           @else 
                              <span class="badge bg-danger">Cancelled</span>
                           @endif
                           <br>
                        </div>

                        <div class="col-sm-4 invoice-col">
                           <a href="{{ route('pdf.downloadPDF', $orders->id) }}" class="btn btn-outline-secondary fw-semibold px-4">
                              <i class="ti ti-download"></i> Download PDF
                           </a>
                        </div>
                     </div>
                  </div>
                  {{-------------- Product Calculation Section --------------}}
                  <div class="card-body table-responsive p-3">								
                     <table class="table table-striped">
                        <thead>
                           <tr>
                              <th>Product</th>
                              <th width="100">Price</th>
                              <th width="100">Qty</th>                                        
                              <th width="100">Total</th>
                           </tr>
                        </thead>
                        <tbody>
                           @forelse ($orderItems as $orderItem)
                              <tr>
                                 <td>{{ $orderItem->name }}</td>
                                 <td>{{ number_format($orderItem->price, 2) }}</td>
                                 <td>{{ $orderItem->qty }}</td>
                                 <td>{{ number_format($orderItem->total, 2) }}</td>
                              </tr>
                           @empty
                              <tr><td style="text-align: center; color:black" colspan="10">Orders Items Not Found</td></tr>
                           @endforelse
                     
                           <tr>
                              <th colspan="3" class="text-left">Subtotal:</th>
                              <td>R.s {{ number_format($orders->subtotal, 2) }}</td>
                           </tr>
                           
                           <tr>
                              <th colspan="3" class="text-left">Shipping:</th>
                              <td>R.s {{ number_format($orders->shipping, 2) }}</td>
                           </tr>
                           <tr>
                              <th colspan="3" class="text-left">Discount {{ (!empty($orders->coupon_code)) ? '('.$orders->coupon_code.')' : '' }}</th>
                              <td>R.s {{ number_format($orders->discount, 2) }}</td>
                           </tr>
                           <tr>
                              <th colspan="3" class="text-left">Grand Total:</th>
                              <td>R.s {{ number_format($orders->grand_total, 2) }}</td>
                           </tr>
                        </tbody>
                     </table>								
                  </div>                            
               </div>
            </div>
            <div class="col-md-3">
               <div class="card">
                  <form action="" name="changeOrderStatusForm" id="change-order-status" method="post">
                     <div class="card-body">
                        <h2 class="h4 mb-3">Order Status</h2>
                        <div class="mb-3">
                           <select name="status" id="status" class="form-control">
                              <option value="pending" {{ $orders->status == 'pending' ? 'selected' : '' }}>Pending</option>
                              <option value="shipped" {{ $orders->status == 'shipped' ? 'selected' : '' }} >Shipped</option>
                              <option value="deliverd" {{ $orders->status == 'deliverd' ? 'selected' : '' }} >Delivered</option>
                              <option value="cancelled" {{ $orders->status == 'cancelled' ? 'selected' : '' }} >Cancelled</option>
                           </select>
                        </div>

                        <h2 class="h4 mb-3">Shipped Date</h2>
                        <div class="mb-3">
                           <input type="text" name="shipped_date" class="form-control" value="{{ \Carbon\Carbon::parse($orders->shipped_date)->format('d M, Y, H:i:s') }}" id="shipped-date" autocomplete="off" placeholder="Shipped Date">
                        </div>
                        <div class="mb-3">
                           <button class="btn btn-primary">Update Now</button>
                        </div>
                     </div>
                  </form>
               </div>
               <div class="card">
                  <div class="card-body">
                     <form action="" name="sendEmailInvoice" id="send-email-invoice" method="POST">
                        <h2 class="h4 mb-3">Send Inovice Email</h2>
                        <div class="mb-3">
                           <select name="userType" id="userType" class="form-control">
                              <option value="">Select Email Send</option>        

                              <option value="customer">Customer Email Send</option>                                                
                              <option value="admin">Admin Email Send</option>
                           </select>
                        </div>
                        <div class="mb-3">                               
                        
                           <button type="submit" class="btn btn-primary" id="send-email-btn">
                              <span class="spinner-grow spinner-grow-sm d-none" id="btnLoader" aria-hidden="true"></span>
                              <span id="btnText" role="status">Send Email</span>
                           </button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>

@endsection

@section('custom-js')
<script>

$(document).ready(function() {
   
   $('#shipped-date').datetimepicker({
      format:'Y-m-d H:i:s',
   });

   $('#change-order-status').submit(function (e) {
      e.preventDefault();

      if (confirm('Are you sure to change status ??')) {
         $.ajax({
            type: 'POST',
            url: '{{ route("changeOrderStatus", $orders->id) }}',
            data: $(this).serializeArray(),
            success: function (response) {
               if (response.status) {
                  window.location.href = '{{ route("orders.orderDetail", $orders->id) }}';
               }
            },
            error: function (jqXHR, exception) {
               alert('error');
            }
         });
      }
   });

   // Send email invoice
   $('#send-email-btn').click(function (e) {
      e.preventDefault();

      let btn = $(this);
      let spinner = $('#btnLoader');
      let btnText = $('#btnText');

      let userType = $('#userType').val();
      
      if (userType == '' || !userType) {
         iziToast.error({
            title: 'Error',
            position: 'topCenter',
            message: 'Please select email send type !!',
         });
         return false;
      }  

      btn.prop('disabled', true);
      spinner.removeClass('d-none');
      btnText.text('Please wait...');

      $.ajax({
         type: 'POST',
         url: '{{ route("sendEmailInvoice", $orders->id) }}',
         data: {
            userType: userType,
            _token: '{{ csrf_token() }}'
         },
         success: function (response) {
            btn.prop('disabled', false);

            if (response.status == true) {     
               window.location.href = '{{ route("orders.orderDetail", $orders->id) }}';
            } else {
               window.location.href = '{{ route("orders.orderDetail", $orders->id) }}';
            }
         },
         error: function (jqXHR, exception) {
            alert('error');
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            btnText.text('Send Email');
         }
      });
      
   });



});

</script>
@endsection