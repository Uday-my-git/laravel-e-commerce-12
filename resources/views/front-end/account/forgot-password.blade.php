@extends('front-end.layouts.app')

@section('content')
<main>
   <section class="section-5 pt-3 pb-3 mb-3 bg-white">
      <div class="container">
         <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
               <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
               <li class="breadcrumb-item">Forgot password</li>
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
            <tr>
               <td align="center" style="padding:30px;">
                 <a target="_blank" href="https://viewstripo.email" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:underline;color:#2CB543;font-size:14px"><img class="adapt-img" src="https://tlr.stripocdn.email/content/guids/CABINET_2af5bc24a97b758207855506115773ae/images/80731620309017883.png" alt="Eid al-Adha" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" title="Eid al-Adha" height="373" width="560"></a>
               </td>
            </tr> 
            
            <form action="{{ route("front-end.forgotPasswordProcess") }}" name="sendEmailForm" id="send-email-form" method="post">
               @csrf
               
               <h4 class="modal-title">Forgot password to Your Account</h4>
               <p style="font-size: 0.80rem">
                  Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
               </p>

               <div class="form-group">
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Your Email" />
                  @error('email')
                     {{ $message }}
                  @enderror
               </div>

               <input type="submit" class="btn btn-dark btn-block btn-lg" value="Send Email">              
            </form>	

            <div class="text-center small">For Login, Click Here <a href="{{ route('account.userRegister') }}">Login Now</a></div>
         </div>
      </div>
   </section>
</main>
@endsection