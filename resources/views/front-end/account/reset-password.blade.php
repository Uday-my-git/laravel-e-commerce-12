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

<section class="section-11 ">   
   <div class="container mt-5">
      <div class="row">
         <div class="col-md-9">
            <div class="card">
               <form action="{{ route("front-end.processResetPassword") }}" name="changePassword"  method="POST">
                  @csrf
                  <input type="hidden" name="token" value="{{ $token->token }}">

                  <div class="card-header">
                     <h2 class="h5 mb-0 pt-2 pb-2">Change Password:-</h2>
                  </div>
                  @include('front-end.account.common.message')

                  @if ($errors->any())
                     <div class="alert alert-danger">
                        <ul>
                           @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                           @endforeach
                        </ul>
                     </div>
                  @endif

                  <div class="card-body p-4">
                     <div class="row">
                        <div class="mb-3">               
                           <label for="password">New Password</label>
                           <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" placeholder="Enter Your New Password" value="">

                           @error('new_password')
                              {{ $message }}
                           @enderror
                        </div>
                        <div class="mb-3">               
                           <label for="again_password">Enter Again Password</label>
                           <input type="password" name="confirm_password" class="form-control @error('confirm_password') is-invalid @enderror" id="confirm_password" placeholder="Enter Again New Password" value="">

                           @error('confirm_password')
                              {{ $message }}
                           @enderror
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
