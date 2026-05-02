@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Create Users</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('users.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<style>
   .danger {
      color: rgb(167, 29, 29);
   }
</style>

<section class="content">
   <div class="container-fluid">
      <form action="{{ route('users.store') }}" name="userForm" method="POST">
         @csrf
         
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required>	

                        @error('name')
                           <p class="danger">{{ $message }}</p>
                        @enderror
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" class="form-control @error('name') is-invalid @enderror"" placeholder="Email" required>	
                        @error('email')
                           <p class="danger">{{ $message }}</p>
                        @enderror
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="mobile">Mobile</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('name') is-invalid @enderror"" placeholder="Mobile" required>	
                        @error('name')
                           <p class="danger">{{ $message }}</p>
                        @enderror
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="role">Role</label>
                        <select name="role" class="form-control @error('name') is-invalid @enderror"" id="role">
                           <option value="">Select Role Permission</option>

                           <option value="2">Admin</option>
                           <option value="1">User</option>
                        </select>	
                        @error('name')
                           <p class="danger">{{ $message }}</p>
                        @enderror
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="status">Status</label>
                        <select name="status" class="form-control" id="status">

                           <option value="0">Deactive</option>
                           <option value="1">Active</option>
                        </select>	
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"" placeholder="Password">	
                        @error('password')
                           <p class="danger">{{ $message }}</p>
                        @enderror
                     </div>
                  </div>	
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('users.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </form>
   </div>
</section>
@endsection