@extends('front-end.layouts.app')

@section('content')
<main>
   <section class="section-5 pt-3 pb-3 mb-3 bg-white">
      <div class="container">
         <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
               <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
               <li class="breadcrumb-item">Register</li>
            </ol>
         </div>
      </div>
   </section>

   <section class="section-10">
      <div class="container">
         <div class="login-form">    
            <form action="" name="userRegisterForm" id="userRegisterForm" method="post">
               @csrf
               <h4 class="modal-title">Register Now</h4>
               <div class="form-group">
                  <input type="text" name="name" class="form-control" placeholder="Name" id="name" />
                  <p class="errhttps://stage.techsimba.in/e-commerce-laravel/public/admin/orders/listing?search=21or"></p>
               </div>
               <div class="form-group">
                  <input type="text" name="email" class="form-control" placeholder="Email" id="email" />
                  <p class="error"></p>
               </div>
               <div class="form-group">
                  <input type="text" name="phone" class="form-control" placeholder="Phone" id="phone" />
                  <p class="error"></p>
               </div>
               <div class="form-group">
                  <input type="password" name="password" class="form-control" placeholder="Password" id="password" />
                  <p class="error"></p>
               </div>
               <div class="form-group">
                  <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" id="password_confirmation" />
                  <p class="error"></p>
               </div>
               <div class="form-group small">
                  <a href="#" class="forgot-link">Forgot Password?</a>
               </div> 

               <button type="submit" class="btn btn-dark btn-block btn-lg" value="Register">Register</button>
            </form>			

            <div class="text-center small">Already have an account? <a href="{{ route('account.login') }}">Login Now</a></div>
         </div>
      </div>
   </section>
</main>
@endsection

@section('custom-js')
<script>

   $(function () {

      $('#userRegisterForm').submit(function (e) {
         e.preventDefault();
         $('button[type="submit"]').prop('disabled', true);

         $.ajax({
            method: 'POST',
            url: '{{ route("account.registerProcess") }}',
            data: $(this).serializeArray(),
            dataType: 'JSON',
            success: function (data) {
               $('button[type="submit"]').prop('disabled', false);   

               if (data.status) {
                  $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  $('#email').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  $('#phone').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                  $('#password').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');

                  window.location.href = '{{ route("account.login") }}';
               } else {  
                  let error = data.errors;

                  $('.error').removeClass('invalid-feedback').html('');
                  $('input[type="text"]').removeClass('is-invalid');

                  $.each(error, function (key, value) {
                     $(`#${key}`).addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(value);
                  });
               }
            },
            error: function (jqXHR, exception) {
               alert('something gone wrong for submitting data!!');
            }
         });
      });


   });


</script>
@endsection