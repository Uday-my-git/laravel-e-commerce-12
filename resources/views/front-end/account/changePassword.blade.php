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

<section class=" section-11 ">
   <div class="container  mt-5">
      <div class="row">
         
         <div class="col-md-3">
            @include('front-end.account.common.sidebar')
         </div>

         <div class="col-md-9">
            <div class="card">
               <form action="" name="changePassword" id="change-password" method="POST">

                  <div class="card-header">
                     <h2 class="h5 mb-0 pt-2 pb-2">Change Password:-</h2>
                  </div>
                  @include('front-end.account.common.message')

                  <div class="card-body p-4">
                     <div class="row">
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

                        <div class="d-flex">
                           <button type="submit" class="btn btn-dark" id="btn-update">Update Now</button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</section>
@endsection

@section('custom-js')
<script>

   $(function () {

      $('#change-password').submit(function (e) {
         e.preventDefault();

         $('#btn-update').prop('disabled', true);
         $('#btn-update').html('please wait...');
         
         let old_password = $('#old_password').val();
         let new_password = $('#new_password').val();
         let confirm_password = $('#confirm_password').val();
         
         $.ajax({
            method: 'post',
            url: '{{ route("front-end.processChangePassword") }}',
            // data: $('#change-password').serialize(),
            data: 'old_password='+old_password+'&new_password='+new_password+'&confirm_password='+confirm_password,
            dataType: 'json',
            success: function (response) {               
               if (response['status']) {
                  $('#btn-update').prop('disabled', false);
                  window.location.href = '{{ route("front-end.changePassword") }}';
               } else {
                  let errors = response['errors'];

                  $.each(errors, function (key, value) {
                     $(`#${key}`).addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(value);
                  });

                  if (response['pwdErrs'] == false) {
                     window.location.href = '{{ route("front-end.changePassword") }}';
                  } 
               }               
               
            }
         });
      });


   })

</script>
@endsection
