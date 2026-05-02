<!doctype html>
<html lang="en">
   <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

      <link rel="stylesheet" href="{{ asset('admin-assets/fonts/icomoon/style.css') }}">
      <link rel="stylesheet" href="{{ asset('admin-assets/css/owl.carousel.min.css') }}">
      <!-- Bootstrap CSS -->
      <link rel="stylesheet" href="{{ asset('admin-assets/css/bootstrap.min.css') }}">
      <!-- Style -->
      <link rel="stylesheet" href="{{ asset('admin-assets/css/style.css') }}">

      <title>Laravel Shop :: Administrative Panel</title>
   </head>
   <body>

   <div class="content">
      <div class="container">
         @include('admin.message')
         <div class="row">
            <div class="col-md-6">
               <img src="{{ asset('admin-assets/images/undraw_remotely_2j6y.svg') }}" alt="Image" class="img-fluid">
            </div>
            <div class="col-md-6 contents">
               <div class="row justify-content-center">

                  <div class="col-md-8">
                     <div class="mb-4">
                        <h3>Administrative Panel</h3>
                        <h2>Sign In</h2>
                        <p class="mb-4 text-danger">You have only 3 login attempt. So login with password carefully.</p>
                     </div>
                  
                     <form action="{{ route('admin.authenticate') }}" name="loginForm" method="post">
                        @csrf

                        <div class="form-group first">
                           <label for="email">Email</label>
                           <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}">

                           @error('email')
                              <p class="invalid-feedback">{{ $message }}</p>
                           @enderror
                        </div>
                        <div class="form-group last mb-4">
                           <label for="password">Password</label>
                           <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password">
                           
                           @error('password')
                              <p class="invalid-feedback">{{ $message }}</p>
                           @enderror
                        </div>
                        
                        <div class="d-flex mb-5 align-items-center">
                           <label class="control control--checkbox mb-0">
                              <span class="caption">Remember me</span>
                              <input type="checkbox" checked="checked" />

                              <div class="control__indicator"></div>
                           </label>

                           <span class="ml-auto"><a href="#" class="forgot-pass">Forgot Password</a></span> 
                        </div>

                        <input type="submit" class="btn btn-block btn-primary" value="Log In">

                        <span class="d-block text-left my-4 text-muted">&mdash; or login with &mdash;</span>
                        
                        {{-- Login with social media --}}
                        <div class="social-login">
                           <a href="#" class="facebook">
                              <span class="icon-facebook mr-3"></span> 
                           </a>
                           <a href="#" class="twitter">
                              <span class="icon-twitter mr-3"></span> 
                           </a>
                           <a href="#" class="google">
                              <span class="icon-google mr-3"></span> 
                           </a>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
            
         </div>
      </div>
   </div>
   
   <script src="{{ asset('admin-assets/plugins/jquery/jquery-3.3.1.min.js') }}"></script>
   <script src="{{ asset('admin-assets/plugins/jquery/popper.min.js') }}"></script>
   <script src="{{ asset('admin-assets/plugins/jquery/bootstrap.min.js') }}"></script>
   <script src="{{ asset('admin-assets/plugins/jquery/main.js') }}"></script>

   <script src="{{ asset('admin-assets/js/adminlte.min.js') }}"></script>
   </body>
</html>