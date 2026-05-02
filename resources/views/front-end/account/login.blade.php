@extends('front-end.layouts.app')

@section('content')
<main>
   <section class="section-5 pt-3 pb-3 mb-3 bg-white">
      <div class="container">
         <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
               <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
               <li class="breadcrumb-item">Login</li>
            </ol>
         </div>
      </div>
   </section>

   <section class="section-10">
      <div class="container">

         @if (Session::has('success'))
            <div class="alert alert-success">
               {{ Session::get('success') }}
            </div>
         @endif

         @if (Session::has('error'))
            <div class="alert alert-danger">
               {{ Session::get('error') }}
            </div>
         @endif

         <div class="login-form">    
            <form action="{{ route("account.authenticate") }}" method="post">
               @csrf
               <h4 class="modal-title">Login to Your Account</h4>

               <div class="form-group">
                  <input type="text" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" />
                  @error('email')
                     <div class="invalid-feedback"> {{ $message }} </div>
                  @enderror
               </div>
               <div class="form-group">
                  <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" />
                  @error('password')
                     <div class="invalid-feedback"> {{ $message }} </div>  
                  @enderror
               </div>
               <div class="form-group small">
                  <a href="{{ route('front-end.forgotPassword') }}" class="forgot-link">Forgot Password?</a>
               </div> 
                  <div class="flex items-center justify-end mt-4 align-middle">
               </div>
               
               <input type="submit" class="btn btn-dark btn-block btn-lg" value="Login"> 
               
               <div class="flex items-center justify-end mt-4 align-middle">
                  <a href="{{ route('front-end.auth.google') }}">
                     <img src="https://developers.google.com/identity/images/btn_google_signin_dark_normal_web.png" class="social_img" style="margin-left: 3em;">
                  </a> <br><br>

                  <a href="{{ route('front-end.auth.facebook') }}" class="btn btn-dark" style="background:blue;color:white;">
                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 640 640"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 440 146.7 540.8 258.2 568.5L258.2 398.2L205.4 398.2L205.4 320L258.2 320L258.2 286.3C258.2 199.2 297.6 158.8 383.2 158.8C399.4 158.8 427.4 162 438.9 165.2L438.9 236C432.9 235.4 422.4 235 409.3 235C367.3 235 351.1 250.9 351.1 292.2L351.1 320L434.7 320L420.3 398.2L351 398.2L351 574.1C477.8 558.8 576 450.9 576 320z"/></svg>
                     Login with Facebook
                  </a>
                  
                  <a href="{{ route('front-end.auth.github') }}" class="btn btn-dark" style="color:white;">
                     <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 30 30">
                        <path fill="#FFFFFF" d="M15,3C8.373,3,3,8.373,3,15c0,5.623,3.872,10.328,9.092,11.63C12.036,26.468,12,26.28,12,26.047v-2.051 c-0.487,0-1.303,0-1.508,0c-0.821,0-1.551-0.353-1.905-1.009c-0.393-0.729-0.461-1.844-1.435-2.526 c-0.289-0.227-0.069-0.486,0.264-0.451c0.615,0.174,1.125,0.596,1.605,1.222c0.478,0.627,0.703,0.769,1.596,0.769 c0.433,0,1.081-0.025,1.691-0.121c0.328-0.833,0.895-1.6,1.588-1.962c-3.996-0.411-5.903-2.399-5.903-5.098 c0-1.162,0.495-2.286,1.336-3.233C9.053,10.647,8.706,8.73,9.435,8c1.798,0,2.885,1.166,3.146,1.481C13.477,9.174,14.461,9,15.495,9 c1.036,0,2.024,0.174,2.922,0.483C18.675,9.17,19.763,8,21.565,8c0.732,0.731,0.381,2.656,0.102,3.594 c0.836,0.945,1.328,2.066,1.328,3.226c0,2.697-1.904,4.684-5.894,5.097C18.199,20.49,19,22.1,19,23.313v2.734 c0,0.104-0.023,0.179-0.035,0.268C23.641,24.676,27,20.236,27,15C27,8.373,21.627,3,15,3z"></path>
                     </svg>
                     Login with Github
                  </a>
               </div> 
            </form>			
            
            <div class="text-center small">Don't have an account? <a href="{{ route('account.userRegister') }}">Sign up</a></div>
         </div>
      </div>
   </section>
</main>
@endsection