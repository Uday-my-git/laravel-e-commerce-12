@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Edit Form Shipping Category</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('shipping.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <div class="card">
      @if ($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif
   </div>
   <div class="container-fluid">
      <form action="{{ route('shipping.update', $shipping->id) }}" name="shippingEditForm" id="shipping-edit-form" method="POST">
         @csrf
         @method('PUT')
         
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="country">Select Country</label>
                        <select name="country_id" class="form-control" id="country_id">
                           <option value="">Select Country</option>
                           
                           @if ($countrires->isNotEmpty())
                              @foreach ($countrires as $country)
                                 <option {{ ($shipping->country_id == $country->id) ? 'selected' : ''}} value="{{ $country->id }}">{{ $country->name }}</option>
                              @endforeach
                              
                              <option {{ $shipping->country_id == 'rest_of_world' ? 'selected' : ''}} value="rest_of_world">Rest Of World</option>
                           @endif
                        </select>
                        @error('country_id')
                           <p class="error"> {{ $message }} </p>
                        @enderror	  
                     </div>
                  </div>								
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Amount</label>
                        <input type="text" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Amount" 
                        value="{{ $shipping->amount }}">	
                        
                        @error('amount')
                           <p> {{ $message }} </p>
                        @enderror
                     </div>
                  </div>					
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('shipping.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </form>
   </div>
</section>
@endsection