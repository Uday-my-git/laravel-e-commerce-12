@extends('admin.layouts.app')

@section('content')

<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Shipping List</h1>
         </div>
      </div>
   </div>
</section>

<section class="content">
   @if ($errors->any())
      <div class="alert alert-danger">
         <ul>
            @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
            @endforeach
         </ul>
      </div>
   @endif

   <div class="container-fluid">
      <form action="{{ route('shipping.store') }}" name="shippingForm" id="shipping-form" method="POST">
         @csrf
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-4">
                     <div class="mb-3">
                        <select name="country_id" class="form-control" id="country_id">
                           <option value="">Select Country</option>   
                           
                           @if ($countrires->isNotEmpty())
                              @foreach ($countrires as $country)
                                 <option value="{{ $country->id }}"> {{ $country->name }} </option>
                              @endforeach

                              <option value="rest_of_world" style="color: rgb(204, 10, 10)">Rest Of World</option>
                           @endif
                        </select>	
                        @error('country_id')
                           <p>{{ $message }}</p>
                        @enderror
                     </div>
                  </div>
                  <div class="col-md-4"">
                     <input type="text" name="amount" class="form-control" id="amount" placeholder="amount enter in (dollar)$">
                     @error('amount')
                        <p>{{ $message }}</p>
                     @enderror
                  </div>	
                  <div class="col-md-4"">
                     <button type="submit" class="btn btn-primary">Create</button>
                  </div>		
               </div>
            </div>							
         </div>
         
      </form>   
   </div>
</section>
<section class="content">
   <div class="container-fluid">
      @include('admin.message')

      <div class="card">
         <form action="" method="get">
            <div class="card-header">
               <button type="button" class="btn btn-default" onclick="window.location.href='{{ route('shipping.listing') }}'">Reset</button>
               <div class="card-tools">
                  <div class="input-group input-group" style="width: 250px;">
                     <input type="text" name="search" class="form-control float-right" value="{{ Request::get('search') }}" placeholder="Search">
      
                     <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                           <i class="fas fa-search"></i>
                        </button>
                     </div>
                     </div>
               </div>
            </div>
         </form>
         <div class="card-body table-responsive p-0">								
            <table class="table table-hover text-nowrap">
               <thead>
                  <tr>
                     <th width="60">ID</th>
                     <th>Country Id</th>
                     <th>Amount</th>
                     <th width="100">Action</th>
                  </tr>
               </thead>
               <tbody>
                  @if ($shipping->isNotEmpty()) 
                     @forelse ($shipping as $shippingItem)
                        <tr>
                           <td>{{ $shippingItem->id }}</td>
                           <td>{{ ($shippingItem->country_id == 'rest_of_world') ? 'Rest Of World' : $shippingItem->countryName }}</td>
                           <td>R.s. {{ $shippingItem->amount }}</td>
                           <td>
                              <form action="{{ route('shipping.delete', $shippingItem->id) }}" method="post">
                                 @csrf
                                 @method('DELETE')
                                 <a class="btn btn-primary" href="{{ route('shipping.edit',$shippingItem->id) }}">Edit</a>

                                 <button type="submit" class="btn btn-danger">Delete</button>
                              </form>
                           </td>
                        </tr>     
                     @empty
                        <tr><td style="text-align: center; color:rgb(221, 62, 62)" colspan="7">No Shipping Data Found !!!</td></tr>
                     @endforelse
                  @endif   
               </tbody>
            </table>										
         </div>
         <div class="card-footer clearfix">
            {{ $shipping->links() }}
         </div>
      </div>
   </div>
</section>
@endsection
