@extends('front-end.layouts.app')

@section('content')

<style>
   .error {
      color: #9e0707;
   }

   .stripe-input-wrapper{
      position: relative;
   }

   #card-element{
      border: 1px solid #ced4da;
      border-radius: 4px;
      padding: 12px;
      background: white;
   }

   .card-icons{
      position: absolute;
      right: 10px;
      top: 8px;
   }

   .card-icons img{
      width: 32px;
      margin-left: 5px;
   }

   .secure-payment{
      font-size: 13px;
      color: #666;
   }
</style>
   
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>

<section class="section-5 pt-3 pb-3 mb-3 bg-white">
   <div class="container">
      <div class="light-font">
         <ol class="breadcrumb primary-color mb-0">
            <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
            <li class="breadcrumb-item"><a class="white-text" href="#">Shop</a></li>
            <li class="breadcrumb-item">Checkout</li>
         </ol>
      </div>
   </div>
</section>

<section class="section-9 pt-4">
   <div class="container">
      <form action="" name="checkoutForm" id="checkoutProcessForm" method="POST">
         <div class="row">
            <div class="col-md-6">
               <div class="sub-title">
                  <h2>Shipping Address</h2>
               </div>
               <div class="card shadow-lg border-0">
                  <div class="card-body checkout-form">
                     <div class="row">
                        <div class="col-md-12">
                           <div class="mb-3">
                              <input type="text" name="first_name" id="first_name" class="form-control error" placeholder="First Name" value="{{ (!empty($customerAddress)) ? $customerAddress->first_name : '' }}">
                           </div>            
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <input type="text" name="last_name" id="last_name" class="form-control error" placeholder="Last Name" value="{{ (!empty($customerAddress)) ? $customerAddress->last_name : '' }}">
                           </div>            
                        </div>
                        
                        <div class="col-md-12">
                           <div class="mb-3">
                              <input type="text" name="email" id="email" class="form-control error" placeholder="Email" value="{{ (!empty($customerAddress)) ? $customerAddress->email : '' }}">
                           </div>            
                        </div>

                        <div class="col-md-12">
                           <div class="mb-3">
                              <select name="country" id="country" class="form-control">
                                 <option value="">Select a Country</option>

                                 @if ($countries->isNotEmpty())
                                    @foreach ($countries as $countrie)
                                       <option {{ !empty($customerAddress) && $customerAddress->country_id == $countrie->id ? 'selected' : '' }} value="{{ $countrie->id }}"> {{ $countrie->name }} </option>
                                    @endforeach

                                    <option>Rest Of World</option>
                                 @endif   
                              </select>
                           </div>            
                        </div>

                        <div class="col-md-12">
                           <div class="mb-3">
                              <textarea name="address" id="address" cols="30" rows="3" placeholder="Address" class="form-control">
                                 {{ !empty($customerAddress->address) ? $customerAddress->address : "" }}
                              </textarea>
                           </div>            
                        </div>

                        <div class="col-md-12">
                           <div class="mb-3">
                              <input type="text" name="apartment" id="apartment" class="form-control error" placeholder="Apartment, suite, unit, etc. (optional)" 
                              value="{{ (!empty($customerAddress)) ? $customerAddress->apartment : '' }}">
                           </div>            
                        </div>

                        <div class="col-md-4">
                           <div class="mb-3">
                              <input type="text" name="city" id="city" class="form-control error" placeholder="City" 
                              value="{{ (!empty($customerAddress)) ? $customerAddress->city : '' }}">
                           </div>            
                        </div>

                        <div class="col-md-4">
                           <div class="mb-3">
                              <input type="text" name="state" id="state" class="form-control error" placeholder="State" 
                              value="{{ (!empty($customerAddress)) ? $customerAddress->first_name : '' }}">
                           </div>            
                        </div>
                        
                        <div class="col-md-4">
                           <div class="mb-3">
                              <input type="text" name="zip" id="zip" class="form-control error" placeholder="Zip" 
                              value="{{ (!empty($customerAddress)) ? $customerAddress->state : '' }}">
                           </div>            
                        </div>

                        <div class="col-md-12">
                           <div class="mb-3">
                              <input type="text" name="mobile" id="mobile" class="form-control error" placeholder="Mobile No." 
                              value="{{ (!empty($customerAddress)) ? $customerAddress->mobile : '' }}">
                           </div>            
                        </div>
                     
                        <div class="col-md-12">
                           <div class="mb-3">
                              <textarea name="notes" id="notes" cols="30" rows="2" placeholder="Order Notes (optional)" class="form-control error">
                                 Dummy Data
                              </textarea>
                           </div>            
                        </div>
                     </div>
                  </div>
               </div>    
            </div>
            <div class="col-md-6">
               <div class="sub-title">
                  <h2>Order Summery</h3>
               </div>                    
               <div class="card cart-summery">
                  <div class="card-body">

                     @foreach (Cart::content() as $cartItem)
                        <div class="d-flex justify-content-between pb-2">
                           <div class="h6"> {{ $cartItem->name }} X {{ $cartItem->qty }}</div>
                           <div class="h6">R.s {{ number_format($cartItem->price * $cartItem->qty, 2) }}</div>
                        </div>
                     @endforeach
                  
                     <div class="d-flex justify-content-between summery-end">
                        <div class="h6"><strong>Subtotal:-</strong></div>
                        <div class="h6"><strong>R.s {{ Cart::subtotal() }}</strong></div>
                     </div>
                     <div class="d-flex justify-content-between mt-2">
                        <div class="h6"><strong>Shipping Charges:-</strong></div>
                        <div class="h6"><strong id="shippingAmount">R.s {{ number_format($totalShippingCharges, 2) }}</strong></div>
                     </div>
                     <div class="d-flex justify-content-between mt-2">
                        <div class="h6"><strong>Discount Amount:-</strong></div>
                        <div class="h6"><strong id="discount-amount">R.s {{ number_format($discountCoupon, 2) }}</strong></div>
                     </div>
                     <div class="d-flex justify-content-between mt-2 summery-end">
                        <div class="h5"><strong>Total:-</strong></div>
                        <div class="h5" style="color: rgb(60, 10, 241)"><strong id="grandTotal">R.s {{ $grandTotal }}</strong></div>
                     </div>                            
                  </div>
               </div>   
               <div class="input-group apply-coupan mt-4">
                  <input type="text" name="coupon_code" class="form-control" placeholder="Coupon Code" id="coupon-codeId">
                  <button type="button" class="btn btn-dark" type="button" id="apply-discount-btn">Apply Coupon</button>
               </div>

               <div class="alert-danger" id="coupon-wrapper">
                  @if (Session::has('couponCode'))
                     <div class="mt-4" id="remove-coupon-response">
                        <strong>{{ Session::get('couponCode')->coupon_code }}</strong>
                        <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
                     </div>
                  @endif
               </div>
               
               <div class="card payment-form">   
                  <h3 class="card-title h5 mb-3">Payment Details:-</h3>

                  <div>
                     <input type="radio" name="payment_method" id="payment_method_one" value="cod" class="col-md-1">
                     <label for="cod_card_number" class="mb-2">COD</label>
                  </div>
         
                  <div>
                     <input type="radio" name="payment_method" id="payment_method_two" value="stripe" class="col-md-1">
                     <label for="stripe_card_number" class="mb-2">Stripe</label>
                  </div>
                  
                  {{------------------ Strip payment gateway ------------------}}
                  <div class="card-body p-0 d-none mt-3" id="card-payment-form">
                     <div class="mb-3">
                        <label class="mb-2">Card Number</label>
                        <div id="card-number" class="form-control"></div>
                     </div>

                     <div class="row">
                        <div class="col-md-6">
                           <label class="mb-2">Expiry Date</label>
                           <div id="card-expiry" class="form-control"></div>
                        </div>

                        <div class="col-md-6">
                           <label class="mb-2">CVC</label>
                           <div id="card-cvc" class="form-control"></div>
                        </div>
                     </div>

                     <div id="card-errors" class="text-danger mt-2"></div>

                     <div class="mt-2" style="font-size:13px;color:#666;">
                        🔒 Secure payment powered by Stripe
                     </div>

                  </div>
                  {{-- <div class="card-body p-0 d-none mt-3" id="card-payment-form">  
                     <div class="mb-3">
                        <label class="mb-2">Card Details</label>
                        <div id="card-element" class="form-control"></div>
                     </div>

                     <div id="card-errors" class="text-danger mt-2"></div>    

                     // custom payment form
                     <div class="mb-3">
                        <label for="card_number" class="mb-2">Card Number</label>
                        <input type="text" name="card_number" id="card_number" placeholder="Valid Card Number" class="form-control">
                     </div>
                     <div class="row">
                        <div class="col-md-6">
                           <label for="expiry_date" class="mb-2">Expiry Date</label>
                           <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YYYY/DD" class="form-control">
                        </div>
                        <div class="col-md-6">
                           <label for="expiry_date" class="mb-2">CVV Code</label>
                           <input type="text" name="expiry_date" id="expiry_date" placeholder="123" class="form-control">
                        </div>
                     </div>
                  </div>    --}}
                 
                  <div class="pt-4">
                     <button type="submit" class="btn-dark btn btn-block w-100" id="payNowBtn"> 
                        <div id="btnLoader" class="spinner-border spinner-border-sm text-light d-none" role="status">
                           <span class="visually-hidden">Loading...</span>
                        </div>
                        <span id="btnText">Pay Now</span>
                     </button>
                  </div>         
               </div>            
            </div>
         </div>
      </form>
   </div>
</section>

@endsection 

@section('custom-js')

<!-- Toastr JS -->
<script src="https://js.stripe.com/v3/"></script>


<script>

   const stripe = Stripe("{{ config('services.stripe.key') }}");
   const elements = stripe.elements();

   const style = {
      base: {
         fontSize: '16px',
         color: '#32325d',
         '::placeholder': {
            color: '#aab7c4'
         }
      }
   };

   const cardNumber = elements.create('cardNumber', { style: style });
   const cardExpiry = elements.create('cardExpiry', { style: style });
   const cardCvc = elements.create('cardCvc', { style: style });

   cardNumber.mount('#card-number');
   cardExpiry.mount('#card-expiry');
   cardCvc.mount('#card-cvc');

   function resetPayNowButton() 
   {
      let btn = $('#payNowBtn');
      let loader = $('#btnLoader');
      let btnText = $('#btnText');

      // 1. Start Loading State
      btn.prop('disabled', false);
      loader.addClass('d-none');
      btnText.text('Pay Now');
   }

   function stripeErrorHandler(event) 
   {
      if (event.error) {
         $('#card-errors').text(event.error.message); 
      } else {
         $('#card-errors').text('');
      }
   }

   cardNumber.on('change', stripeErrorHandler);
   cardExpiry.on('change', stripeErrorHandler);
   cardCvc.on('change', stripeErrorHandler);

   function submitCheckoutForm()
   {
      $.ajax({
         method: 'post',
         url: '{{ route("front-end.processCheckout") }}',
         data: $('#checkoutProcessForm').serialize(),
         dataType: 'json',
         success: function (data) {
            console.log(data);

            if (data.status === true) {
               // c.o.d
               if (data.redirect) {                  
                 window.location.href = data.redirect + '?orderId=' + data.orderId;
               }

               // stripe pay
               if (data.client_secret) {
                  stripe.confirmCardPayment(data.client_secret, {
                     payment_method: {
                        card: cardNumber,
                        billing_details: {
                           name: $('#first_name').val() + ' ' + $('#last_name').val(),
                           email: $('#email').val()
                        }
                     }
                  }).then(function(result) {
                     console.log('Stripe result ', result);
                     
                     if(result.error){
                        console.error(result.error);
                        $('#card-errors').text(result.error.message);
                        resetPayNowButton();

                     }else{
                        if(result.paymentIntent.status === 'succeeded') {
                           console.log('paymentIntent_status', result.paymentIntent.status);
                           // window.location.href = "/thankuPage?orderId=" + data.orderId;
                           setInterval(() => {
                              window.location.href = "{{ route('front-end.thankuPage') }}?orderId=" + data.orderId;
                           }, 5000);
                        }
                     }
                  });
               }
            } else {
               console.log(data);
               alert(data.msg ?? 'Checkout failed');
               $('#payNowBtn').prop('disabled', false);
            }
         },
         error: function (data) {
            console.log('Error:', data);
            $('#payNowBtn').prop('disabled', false);
            $('#card-errors').text('Something went wrong. Please try again.');
         }
      });
   }

   $(document).ready(function () {
      $('#payment_method_one').click(function () {
         if ($(this).is(':checked') == true) {
            $('#card-payment-form').addClass('d-none');
         }
      });

      $('#payment_method_two').click(function () {
         $('#card-payment-form').removeClass('d-none');
      });

      
      // Additional validation for name check 
      $.validator.addMethod("first_name", function(value, element) {
         return this.optional(element) || /^[a-zA-Z0-9]{3,20}$/.test(value);
      }, "first_name must be 3-20 alphanumeric characters.");


      $('#checkoutProcessForm').validate({
         rules: {
            first_name: {
               required: true,
               first_name: true,
            },
            last_name: 'required',
            email: {
               required: true,
               email: true,
               // remote: {
               //    type: 'get',
               //    url: '{{ route("front-end.emailValidation") }}',
               //    data: {
               //       email: function (data) {
               //          return $('#email').val();
               //       }
               //    }
               // }
            },
            country: 'required',
            city: 'required',
            state: 'required',
            zip: 'required',
            mobile: {
               required: true,
               minlength: 10,
               maxlength: 12
            },
         },
         messages: {
            // first_name: "Please specify your name",
            last_name: "Please specify your Last name",
            email: {
               required: "We need your email address to contact you",
               email: "Your email address must be in the format of name@domain.com",
               // remote: "This email address already in use in our database, choose other email id!!",
            },
            country: "Please specify your Country",
            city: "Please specify your City",
            state: "Please specify your State",
            zip: "Please specify your Zip Code",
            mobile: {
               required: "Please specify your 10 Mobile Number",
               minlength: "Please specify minimum 10 digit of Mobile Number",
               maxlength: "Please specify maximum 12 digit of Mobile Number"
            },
         },
         submitHandler: function(data) {
            let btn = $('#payNowBtn');
            let loader = $('#btnLoader');
            let btnText = $('#btnText');

            let paymentMethod = $('input[name="payment_method"]:checked').val();
            
            // Stripe card validation BEFORE loader & ajax
            if (paymentMethod === 'stripe') {
               if(cardNumber._complete === false) {
                  $('#card-errors').text('Please fill card details.');
                 
                  return;
               }
            }

            // Start Loading State
            loader.removeClass('d-none');
            btn.prop('disabled', true);
            btnText.text('Please Wait While For Processing...');
         
            if (paymentMethod === 'stripe') {
               submitCheckoutForm();

            } else if(paymentMethod === 'cod') {
               submitCheckoutForm();

            } else {
               alert('Please select payment method');
               loader.addClass('d-none');
               btnText.text('Pay Now');
               btn.prop('disabled', false);
            }
         }
      });

      
      // Shipping country code chage
      $('#country').change(function () {
         let countryId = $(this).val();

         $.ajax({
            method: 'GET',
            url: '{{ route("front-end.getOrderSummery") }}',
            data: 'country_id=' + countryId,
            dataType: 'JSON',
            success: function (data) {
               if (data.status === true) {
                  $('#shippingAmount').html('R.s ' +data.shippingCharge);
                  $('#grandTotal').html('R.s ' +data.grandTotal);
               }
            },
            error: function () {
               alert('error for country id...');
            }
         });
      });


      // Apply coupon code
      $('#apply-discount-btn').click(function () {
         let countryId = $('#country').val();
         let couponCode = $('#coupon-codeId').val();

         if (couponCode !== '') {
            $.ajax({
               method: 'POST',
               url: '{{ route("front-end.applyCouponCode") }}',
               data: 'country_id=' +countryId+ '&couponCode=' +couponCode,
               dataType: 'JSON',
               success: function (data) {     
                  if (data.status === true) {
                     $('#shippingAmount').html('R.s ' +data.shippingCharge);
                     $('#discount-amount').html('R.s ' +data.discountCoupon);
                     $('#grandTotal').html('R.s ' +data.grandTotal);
                     $('#coupon-wrapper').html(data.couponHTML);

                     iziToast.success({
                        title: 'OK',
                        message: 'Coupon code "'+couponCode+'" apply Successfully',
                     });
                  } else {
                     $('#coupon-wrapper').html('<strong class="text-denager">'+data.msg+'</strong>');

                     iziToast.error({
                        title: 'Error',
                        message: data.msg,
                     });
                  }
               },
               error: function () {
                  iziToast.error({
                     title: 'Error',
                     position: 'topCenter',
                     message: 'Error !! country id or coupon code value not found??',
                  });
               }
            });
         } else {
            iziToast.error({
               title: 'Coupon Code Error',
               position: 'topCenter',
               message: 'Enter coupon code value here!!',
            });
         }
      
      });


      // Remove coupon code
      $(document).on('click', '#remove-discount', function () {
         let countryId = $('#country').val();

         $.ajax({
            method: 'POST',
            url: '{{ route("front-end.removeCouponCode") }}',
            data: 'country_id=' +countryId,
            dataType: 'JSON',
            success: function (data) {               
               if (data.status === true) {                  
                  $('#shippingAmount').html('R.s ' +data.shippingCharge);
                  $('#discount-amount').html('R.s ' +data.discountCoupon);
                  $('#grandTotal').html('R.s ' +data.grandTotal);
                  $('#remove-coupon-response').html('');
                  $('#coupon-codeId').val('');

                  iziToast.success({
                     title: 'OK',
                     message: 'Coupon code remove Successfully',
                  });
               } else {
                  iziToast.error({
                     title: 'Error',
                     message: data.msg,
                  });
               }
            },
            error: function () {
               iziToast.error({
                  title: 'Error',
                  position: 'topCenter',
                  message: 'Error !! coupon code value not remove??',
               });
            }
         });
      });


   });


</script>
@endsection 