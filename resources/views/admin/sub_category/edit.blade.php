@extends('admin.layouts.app')

@section('content')
    
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Edit Sub Category</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('sub_category.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <!-- Default box -->
   <div class="container-fluid">
      <form action="" name="subCategoryForm" id="editSub-category-form" method="POST">
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-12">
                     <div class="mb-3">
                        <label for="name">Category</label>
                        <select name="category" id="category_id" class="form-control">
                           <option value="">Select Category</option>
                           
                           @if ($categories->isNotEmpty())
                              @foreach ($categories as $category)
                                 <option value="{{ $category->id }}" {{ !empty($subCategories) && ($subCategories->category_id == $category->id) ? 'selected' : '' }} >{{ $category->name }}</option>
                              @endforeach 
                           @endif
                        </select>
                        <p class="error"></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Name" value="{{ old('name', $subCategories->name ?? '') }}">	
                        <p class="error"></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="email">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug" value="{{ old('slug', $subCategories->slug ?? '') }}" readonly>	
                     </div>
                  </div>									
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="status">Status</label>
                        <select name="status" class="form-control" id="status">
                           <option value="0" {{ ($subCategories->status == 0) ? 'selected': '' }} >Deactive (Default)</option>
                           <option value="1" {{ ($subCategories->status == 1) ? 'selected': '' }} >Active</option>
                        </select>
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="showHome">Show On Home</label>
                        <select name="showHome" class="form-control" id="showHome">
                           <option value="0" {{ ($subCategories->showHome == 'No') ? 'selected' : ''  }} >No (Default)</option>
                           <option value="1" {{ ($subCategories->showHome == 'Yes') ? 'selected' : ''  }} >Yes</option>
                        </select>	
                     </div>
                  </div>								
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Update Now</button>
            <a href="{{ route('sub_category.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </form>
   </div>
</section>

@endsection 

@section('custom-js')
<script>

$(function () {

   $('#editSub-category-form').submit(function (event) {
      event.preventDefault();
      $('button[type=submit]').prop('disabled', true);

      $.ajax({
         method: 'PUT',
         url: '{{ route("sub_category.update", $subCategories->id) }}',
         data: $(this).serializeArray(),
         dataType: 'JSON',
         success: function (response) {
            $('button[type=submit]').prop('disabled', false);

            if (response.status === true) {
               $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               $('#category_id').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();

               $('#editSub-category-form')[0].reset();
               window.location.href = '{{ route("sub_category.listing") }}';

            } else {
               const errors = response['errors'];

               if (response['notFound'] === true) {
                  window.location.href = '{{ route("sub_category.listing") }}';
               }
               
               if (errors.name) {
                  $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
               } else {
                  $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               }

               if (errors.category) {
                  $('#category_id').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.category);
               } else {
                  $('#category_id').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html();
               }
            }
         },
         error: function (jqXHR, exception) {
            console.log("errror!! occured");
         }
      });
   });

   $('#name').change(function () {
      const slugVal = $(this).val();
      $('button[type=submit]').prop('disabled', true);
      
      if (slugVal != '') {
         $.ajax({
            method: 'GET',
            url: '{{ route("getSlug") }}',
            data: 'title='+slugVal,
            success: function (response) {
               $('button[type=submit]').prop('disabled', false);

               if (response.status === true) {
                  $('#slug').val(response.slug);
               }
            }
         });
      }
   });


});

</script>    

@endsection