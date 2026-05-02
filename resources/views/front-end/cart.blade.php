@extends('front-end.layouts.app')

@section('content')
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
   <div class="container">
      <div class="light-font">
         <ol class="breadcrumb primary-color mb-0">
            <li class="breadcrumb-item"><a class="white-text" href="{{ route('front-end.home') }}">Home</a></li>
            <li class="breadcrumb-item"><a class="white-text" href="{{ route('front-end.shop') }}">Shop</a></li>
            <li class="breadcrumb-item">Cart</li>
         </ol>
      </div>
   </div>
</section>

<section class="section-9 pt-4">
   <div class="container">
      <div class="row">

         @if (Session::has('success'))
            <div class="col-md-12">
               <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  {!! Session::get('success') !!}

                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>   
            </div>
         @endif
         @if (Session::has('error'))
            <div class="col-md-12">
               <div class="alert alert-danger alert-dismissible fade show" role="alert" id="fade-out">
                  {{ Session::get('error') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>   
            </div>
         @endif

         @if (Cart::count() > 0)
            <div class="col-md-8">
               <div class="table-responsive">
                  <table class="table" id="cart">
                     <thead>
                        <tr>
                           <th>Item</th>
                           <th>Price</th>
                           <th>Quantity</th>
                           <th>Total</th>
                           <th>Remove</th>
                        </tr>
                     </thead> 
                  
                     <tbody>
                        @foreach ($cartContent as $cartItem)
                           <tr>
                              <td class="text-start">
                                 <div class="d-flex align-items-center">

                                    @if (!empty($cartItem->options->product_images_fun->image))
                                       <img src="{{ asset('uploads/product/small/' . $cartItem->options->product_images_fun->image) }}">
                                    @else
                                       <img src="{{ asset("admin-assets/img/default-150x150.png") }}">
                                    @endif
                                    
                                    <h2> {{ $cartItem->name }} </h2>
                                 </div>
                              </td>
                              <td> R.s.{{ $cartItem->price }} </td>
                              <td>
                                 <div class="input-group quantity mx-auto" style="width: 100px;">
                                    <div class="input-group-btn">
                                       <button class="btn btn-sm btn-dark btn-minus p-2 pt-1 pb-1 sub" data-id="{{ $cartItem->rowId }}">
                                          <i class="fa fa-minus"></i>
                                       </button>
                                    </div>

                                    <input type="text" class="form-control form-control-sm  border-0 text-center" value="{{ $cartItem->qty }}">

                                    <div class="input-group-btn">
                                       <button class="btn btn-sm btn-dark btn-plus p-2 pt-1 pb-1 add" data-id="{{ $cartItem->rowId }}">
                                          <i class="fa fa-plus"></i>
                                       </button>
                                    </div>
                                 </div>
                              </td>
                              <td> R.s.{{ $cartItem->price * $cartItem->qty }} </td>
                              <td>
                                 <button class="btn btn-sm btn-danger" onclick="deleteCartFun('{{ $cartItem->rowId }}')">
                                    <i class="fa fa-times"></i>
                                 </button>
                              </td>
                           </tr>   
                        @endforeach     
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="col-md-4">
               <div class="card cart-summery">
                  <div class="card-body">
                     <div class="sub-title">
                        <h2 class="bg-white">Cart Summery</h3>
                     </div>
                     <div class="d-flex justify-content-between pb-2">
                        <div>Subtotal</div>

                        <div>R.s.{{ Cart::subtotal() }}</div>
                     </div>
                     <div class="pt-2">
                        <a href="{{ route('front-end.checkout') }}" class="btn-dark btn btn-block w-100">Proceed to Checkout</a>
                     </div>
                  </div>
               </div>
            </div>
         @else
            <div class="col-md-12">
               <div class="card">
                  <div class="card-body d-flex justify-content-center align-item-center">
                     <h5>Your Card Is Empty Now </h5>
                  </div>
               </div>
            </div>
         @endif

      </div>
   </div>
</section>
@endsection

@section('custom-js')
    
<script>

   $(function () {

      $('.add').click(function () {
         const qtyInput = $(this).parent().prev();
         let currentQty = parseInt(qtyInput.val());
         
         if (currentQty < 10) {
            qtyInput.val(currentQty + 1);
            let rowId = $(this).data('id');
            let newQty = qtyInput.val();  
            
            updateCartFun(rowId, newQty);
         }
      });

      $('.sub').click(function () {
         const qtyInput = $(this).parent().next();
         let currentQty = parseInt(qtyInput.val());

         if (currentQty > 1) {
            qtyInput.val(currentQty - 1);
            let rowId = $(this).data('id');
            let newQty = qtyInput.val();

            updateCartFun(rowId, newQty);
         }
      });

   });

     
   function updateCartFun(rowId, qty)
   {
      $.ajax({
         method: 'post',
         url: '{{ route("front-end.updateCart") }}',
         data: 'rowId=' +rowId+'&qty=' +qty,
         dataType: 'JSON',
         success: function (data) {
            window.location.href = '{{ route("front-end.cart") }}';
         }
      });
   }


   function deleteCartFun(rowId)
   {
      if (confirm('Aru You Sure To Delete Product??')) {
         $.ajax({
            method: 'DELETE',
            url: '{{ route("front-end.deleteCartItems") }}',
            data: 'rowId=' +rowId,
            dataType: 'json',
            success: function (data) {
               window.location.href = '{{ route("front-end.cart") }}';
            }
         });
      }
   }

  

</script>
@endsection 

