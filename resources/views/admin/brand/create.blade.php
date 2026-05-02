@extends('admin.layouts.app')

@section('content')
    
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Create Brands</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('ai.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
      <form action="" name="crateBradForm" id="crate-brand-form" method="POST">
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Brands Name">	
                        <p class="error"></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Brands Slug" readonly>	
                        <p class="error"></p>
                     </div>
                  </div>																	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="email">Status</label>
                        <select name="status" class="form-control" id="status">
                           <option value="0">Deactive (Default)</option>
                           <option value="1">Active</option>
                        </select>
                     </div>
                  </div>																	
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('brand.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </form>
   </div>
</section>

@endsection 

@section('custom-js')
<script>

   $('#crate-brand-form').submit(function (e) {
      e.preventDefault();
      $('button[type=submit]').prop('disabled', true);

      $.ajax({
         method: 'POST',
         url: '{{ route("brand.store") }}',
         // data: 'name='+name+'&slug='+slug+'&status='+status,
         data: $(this).serialize(),
         success: function (response) {
            $('button[type=submit]').prop('disabled', false);
            
            if (response.status === true) {
               $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               
               location.href = '{{ route("brand.listing") }}';
            } else {
               let error = response.errors;
               
               if (error.name) {
                  $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.name);
               } else {
                  $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               }

               if (error.slug) {
                  $('#slug').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.slug);
               } else {
                  $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               }
            }
         }, 
         error: function (jqXHR, exception) {
            console.log('error occured in brand???');
         }
      });
   });

   $('#name ').change(function () {
      const slugVal = $(this).val();

      if (slugVal !== '') {
         $.ajax({
            method: 'GET',
            url: '{{ route("getSlug") }}',
            data: 'title='+slugVal,
            success: function (response) {
               if (response.status === true) {
                  $('#slug').val(response.slug);
               }
            }
         });
      }
   })


   
</script>
@endsection