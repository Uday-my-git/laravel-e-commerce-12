@extends('admin.layouts.app')

@section('content')
    
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Edit Brands</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('brand.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
      <form action="" name="subCategoryForm" method="POST" id="update-form">
         <input type="hidden" name="_method" value="PUT">

         @csrf
         @method('PUT')

         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Brands Name" value="{{ isset($brands->name) ? $brands->name : null }}">	
                        <p class="error"></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Brands Slug" value="{{ isset($brands->slug) ? $brands->name : null }}" readonly>	
                        <p class="error"></p>
                     </div>
                  </div>																	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="status">Slug</label>
                        <select name="status" id="status" class="form-control">
                           <option value="0" @selected(optional($brands)->status == 0) >Deactive (default)</option>
                           <option value="1" @selected(optional($brands)->status == 1)  >Active</option>
                        </select>
                     </div>
                  </div>																	
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary" id="btn-update-form">Update Now</button>
            <a href="{{ route('brand.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </form>
   </div>
</section>

@endsection 

@section('custom-js')
<script>

$(function () {

   $('#btn-update-form').on('click', function (e) {
      e.preventDefault();
      
      $.ajax({
         method: 'POST',
         url: '{{ route("brand.update", $brands->id) }}',
         data: $('#update-form').serialize() + '&_method=PUT',
         dataType: 'JSON',
         success: function (response) {
            if (response.status === true) {
               $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');

               window.location.href = '{{ route("brand.listing") }}';
            } else {
               const errors = response.errors;

               if (response['notFound']) {
                  window.location.href = '{{ route("brand.listing") }}';
               }

               if (errors.name) {
                  $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
               } else {
                  $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }

               if (errors.slug) {
                  $('#slug').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.slug);
               } else {
                  $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }
            }
         }
      });
   });


   // genereate slug
   $('#name').change(function () {
      const slugVal = $(this).val();
      
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
   });

});

</script>
@endsection 