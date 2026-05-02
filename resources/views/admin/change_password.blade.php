@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Change Password Form:-</h1>
         </div>
         {{-- <div class="col-sm-6 text-right">
            <a href="#" class="btn btn-primary">Back</a>
         </div> --}}
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
      @include('admin.message')

      <form action="" name="chagePasswordForm" id="chage-password-form" method="POST">
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-12">
                     <div class="mb-3">               
                        <label for="password">Old Password</label>
                        <input type="password" name="old_password" class="form-control @error('password') is-invalid @enderror" id="old_password" placeholder="Enter Your Password" value="">
                        <p class="error"></p>
                     </div>
                     <div class="mb-3">               
                        <label for="new_password">New Password</label>
                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" placeholder="Enter Your New Password" value="">
                        <p class="error"></p>
                     </div>
                     <div class="mb-3">               
                        <label for="again_password">Enter Again Password</label>
                        <input type="password" name="confirm_password" class="form-control @error('confirm_password') is-invalid @enderror" id="confirm_password" placeholder="Enter Again New Password" value="">
                        <p class="error"></p>
                     </div>
                  </div>			
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary" id="chage-password-form-btn">Update Password</button>
         </div>
      </form>
   </div>
</section>
@endsection

@section('custom-js')
<script>
   
$(document).ready(function () {

   $('#chage-password-form-btn').click(function (e) {
      e.preventDefault();

      $('button[type=submit]').prop('disabled', true);
      
      $.ajax({
         method: 'POST',
         url: '{{ route("admin.chagePasswordFormProcess") }}',
         data: $('#chage-password-form').serializeArray(),
         dataType: 'JSON',
         success: function (response) {
            $('button[type=submit]').prop('disabled', false);

            if (response.status === true) {
               $('#old_password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               $('#new_password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               $('#confirm_password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               
               window.location.href = '{{ route("admin.chagePasswordForm") }}';

            } else {
               if (response.pwdErrs == false) {
                  window.location.href = '{{ route("admin.chagePasswordForm") }}';
               }
               const errors = response.errors;

               if (errors.old_password) {
                  $('#old_password').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.old_password);
               } else {
                  $('#old_password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }

               if (errors.new_password) {
                  $('#new_password').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.new_password);
               } else {
                  $('#new_password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }

               if (errors.confirm_password) {
                  $('#confirm_password').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.confirm_password);
               } else {
                  $('#confirm_password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }

            
            }
         }, 
         error: function (jqXHR, exception) {
            console.log('error occured ??');
         }
      });
   });
})

</script>
@endsection 