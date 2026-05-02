@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Edit Coupon Code</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('coupon.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
     <form action="" name="discountForm" id="discountForm" method="POST">
         @csrf

         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Coupon Code</label>
                        <input type="text" name="coupon_code" id="coupon_code" class="form-control" value="{{ $couponCode->coupon_code ?? '' }}" placeholder="Coupon Code">	
                        <p></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $couponCode->name ?? '' }}" placeholder="Coupon Name">	
                        <p></p>
                     </div>
                  </div>		
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control" id="description" cols="30" rows="3" placeholder="Short Description">
                           {{ $couponCode->description ?? '' }}
                        </textarea>
                        <p></p>
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses">Max Uses</label>
                        <input type="text" name="max_uses" id="max_uses" class="form-control" value="{{ $couponCode->max_uses ?? '' }}" placeholder="Max Uses">	
                        <p></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses">Type</label>
                        <select class="form-control" name="type" id="type">
                           <option value="fixed" @selected($couponCode->type == 'fixed') >Fixed (Default)</option>   
                           <option value="percent" @selected($couponCode->type == 'percent') >Percentage</option>   
                        </select>	
                     </div>
                  </div>  
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses">Discount Amount</label>
                        <input type="text" name="discount_amount" id="discount_amount" class="form-control" value="{{ $couponCode->discount_amount ?? '' }}" placeholder="Discount Amount">	
                        <p></p>
                     </div>
                  </div>               
                  
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses">Min Amount</label>
                        <input type="text" name="min_amount" id="min_amount" class="form-control" value="{{ $couponCode->min_amount ?? '' }}" placeholder="Min Amount">	
                        <p></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses_user">Max Uses User</label>
                        <input type="text" name="max_uses_user" id="max_uses_user" class="form-control" value="{{ $couponCode->max_uses_user ?? '' }}" placeholder="Max Uses">	
                        <p></p>
                     </div>
                  </div>

                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses">Starts At</label>
                        <input type="text" name="starts_at" id="starts_at" class="form-control" value="{{ $couponCode->starts_at ?? '' }}" placeholder="Select Date & Time" autocomplete="off">	
                        <p></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="max_uses">Expires At</label>
                        <input type="text" name="expires_at" id="expires_at" class="form-control" value="{{ $couponCode->expires_at ?? '' }}" placeholder="Select Date & Time" autocomplete="off">	
                        <p></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" id="status-id">
                           
                           <option value="1" {{ $couponCode->status == 1 ? 'selected' : '' }} >Active (Default)</option>   
                           <option value="0" {{ $couponCode->status == 0 ? 'selected' : '' }} >Deactive</option>   
                        </select>	
                     </div>
                  </div>									
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" name="submitForm" id="couponCodeForm1" class="btn btn-primary">Update</button>
            <a href="{{ route("coupon.listing") }}" class="btn btn-outline-dark ml-3" >Cancel</a>
         </div>
      </form>   
   </div>
</section>
@endsection

@section('custom-js')
<script>

   $(document).ready(function() {
      // Datetime picker
      $('#starts_at').datetimepicker({
         format:'Y-m-d H:i:s',
      });

      $('#expires_at').datetimepicker({
         format:'Y-m-d H:i:s',
      });

      // form submiting
      $('#discountForm').submit(function (e) {
         e.preventDefault();

         $.ajax({
            method: 'PUT',
            url: '{{ route("coupon.update", $couponCode->id) }}',
            data: $('#discountForm').serializeArray(),
            dataType: 'JSON',
            success: function(data) {
               
               if (data.status) {
                  $('#discountForm')[0].reset();
                 
                  window.location.href = '{{ route("coupon.listing") }}';
                  
               } else {
                  var error = data.errors;

                  if (error.coupon_code) {
                     $('#coupon_code').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.coupon_code);
                  } else {
                     $('#coupon_code').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.name) {
                     $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.name);
                  } else {
                     $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.description) {
                     $('#description').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.description);
                  } else {
                     $('#description').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.max_uses) {
                     $('#max_uses').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.max_uses);
                  } else {
                     $('#max_uses').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.discount_amount) {
                     $('#discount_amount').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.discount_amount);
                  } else {
                     $('#discount_amount').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.min_amount) {
                     $('#min_amount').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.min_amount);
                  } else {
                     $('#min_amount').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.max_uses_user) {
                     $('#max_uses_user').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.max_uses_user);
                  } else {
                     $('#max_uses_user').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.starts_at) {
                     $('#starts_at').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.starts_at);
                  } else {
                     $('#starts_at').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }

                  if (error.expires_at) {
                     $('#expires_at').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.expires_at);
                  } else {
                     $('#expires_at').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  }
               }
            }
         });
      });




   });




</script>
@endsection