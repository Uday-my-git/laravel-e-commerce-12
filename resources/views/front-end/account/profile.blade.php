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
               <form action="{{ route('account.updateProfile') }}" name="userProfile" method="POST">
                  @csrf

                  <div class="card-header">
                     <h2 class="h5 mb-0 pt-2 pb-2">Personal Information</h2>
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
                           <label for="name">Name</label>
                           <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" placeholder="Enter Your Name" 
                           value="{{ $profile->name }}">

                           @error('name')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3">            
                           <label for="email">Email</label>
                           <input type="text" name="email" class="form-control @error('email', 'email1') is-invalid @enderror" id="email" placeholder="Enter Your Email" 
                           value="{{ $profile->email }}">

                           @error('email', 'email1')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3">                                    
                           <label for="phone">Phone</label>
                           <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" id="phone" placeholder="Enter Your Phone" 
                           value="{{ $profile->phone }}">

                           @error('phone')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>

                        {{-- <div class="mb-3">                                    
                           <label for="phone">Address</label>
                           <textarea name="address" id="address" class="form-control" cols="30" rows="5" placeholder="Enter Your Address"></textarea>
                        </div> --}}

                        <div class="d-flex">
                           <button type="submit" class="btn btn-dark">Update Now</button>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
            <div class="card">
               <form action="{{ route('account.updateAddress') }}" name="updateAddress" method="POST">
                  @csrf

                  <div class="card-header">
                     <h2 class="h5 mb-0 pt-2 pb-2">Customer Address</h2>
                  </div>
   
                  <div class="card-body p-4">
                     <div class="row">
                        <div class="col-md-6 mb-3">               
                           <label for="first_name">First Name</label>
                           <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" id="first_name" placeholder="Enter Your Name" 
                           value="{{ $customerAddress->first_name ?? '' }}">

                           @error('first_name')
                              <div class="alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-3">               
                           <label for="last_name">Last Name</label>
                           <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" id="first_name" placeholder="Enter Your Last Name" 
                           value="{{ $customerAddress->last_name ?? '' }}">

                           @error('last_name')
                              <div class="alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-3">            
                           <label for="email">Email</label>
                           <input type="text" name="email" class="form-control @error('email', 'email2') is-invalid @enderror" id="email" placeholder="Enter Your Email" 
                           value="{{ $customerAddress->email ?? '' }}">

                           @error('email', 'email2')
                              <div class="alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-3">            
                           <label for="country">Country</label>
                           <select name="country_id" id="country_id" class="form-control">
                              <option value="">Select Country</option>

                              @foreach ($countries as $countriy)
                                 <option value="{{ $countriy->id }}" {{ ($customerAddress?->country_id == $countriy->id) ? 'selected' : '' }}>{{ $countriy->name }}</option> 
                              @endforeach
                           </select>

                           @error('country_id')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-3">                                    
                           <label for="mobile">Mobile</label>
                           <input type="number" name="mobile" class="form-control @error('mobile') is-invalid @enderror" id="phone" placeholder="Enter Your Phone" 
                           value="{{ $customerAddress->mobile ?? '' }}">

                           @error('phone')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                         <div class="col-md-6 mb-3">                                    
                           <label for="mobile">Apartment</label>
                           <input type="text" name="apartment" class="form-control @error('apartment') is-invalid @enderror" id="phone" placeholder="Enter Your Apartment" 
                           value="{{ $customerAddress->apartment ?? '' }}">
      
                           @error('apartment')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3">                                    
                           <label for="address">Address</label>
                           <textarea name="address" id="address" class="form-control" cols="10" rows="2" placeholder="Enter Your Address">
                              {{ $customerAddress->address ?? '' }}
                           </textarea>
                           
                           @error('phone')
                           <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                       
                        <div class="col-md-6 mb-3">                                    
                           <label for="city">City</label>
                           <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" id="phone" placeholder="Enter Your City" 
                           value="{{ $customerAddress->city ?? '' }}">
      
                           @error('city')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="col-md-6 mb-3">                                    
                           <label for="state">State</label>
                           <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" id="phone" placeholder="Enter Your State" 
                           value="{{ $customerAddress->state ?? '' }}">
      
                           @error('apartment')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3">                                    
                           <label for="zip">Zip Code</label>
                           <input type="number" name="zip" class="form-control @error('zip') is-invalid @enderror" id="phone" placeholder="Enter Your Zip" 
                           value="{{ $customerAddress->zip ?? '' }}">
      
                           @error('zip')
                              <div class="alert alert-danger">{{ $message }}</div>
                           @enderror
                        </div>
                        
                        <div class="d-flex">
                           <button type="submit" class="btn btn-dark">Update Now</button>
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