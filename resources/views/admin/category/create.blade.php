@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Create Category</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('categories.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
      <form action="" name="categoryForm" id="category-form" method="POST">
         <div class="card">
            <div class="card-body">								
               <div class="row">
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Name">	
                        <p class="error"></p>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug" readonly>	
                        <p class="error"></p>
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="image">Image Upload:-</label>
                        <input type="hidden" name="image_id" id="image_id">

                        <div id="image" class="dropzone dz-clickable">
                           <div class="dz-message needsclick">    
                              <br>Drop files here or click to upload.<br><br>                                            
                           </div>
                        </div>
                     </div>
                  </div>	
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="status">Status</label>
                        <select name="status" class="form-control" id="status">
                           <option value="0">Deactive (Default)</option>
                           <option value="1">Active</option>
                        </select>	
                     </div>
                  </div>								
                  <div class="col-md-6">
                     <div class="mb-3">
                        <label for="showHome">Show On Home</label>
                        <select name="showHome" class="form-control" id="showHome">
                           <option value="0">No (Default)</option>
                           <option value="1">Yes</option>
                        </select>	
                     </div>
                  </div>								
               </div>
            </div>							
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('categories.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </form>
   </div>
</section>
@endsection

@section('custom-js')
<script>

$(document).ready(function () {

   $('#category-form').submit(function (e) {
      e.preventDefault();
      $('button[type=submit]').prop('disabled', true);
      
      $.ajax({
         method: 'POST',
         url: '{{ route("categories.insert") }}',
         data: $(this).serializeArray(),
         dataType: 'JSON',
         success: function (response) {
            $('button[type=submit]').prop('disabled', false);

            if (response.status === true) {
               $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               
               $('#category-form')[0].reset();
               window.location.href = '{{ route("categories.listing") }}';

            } else {
               const errors = response.errors;

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
         }, 
         error: function (jqXHR, exception) {
            console.log('error occured ??');
         }
      });
   });
})

// genereate slug
$('#name').change(function () {
   const slugVal = $(this).val();
   $('button[type=submit]').prop('disabled', true);

   $.ajax({
      method: 'GET',
      url: '{{ route("getSlug") }}',
      data: 'title='+slugVal,
      dataType: 'JSON',
      success: function (response) {
         $('button[type=submit]').prop('disabled', false);

         if (response.status === true) {
            $('#slug').val(response.slug);
         }
      }
   });

});


// Dropzone image upload
Dropzone.autoDiscover = false;   

const dropzone = $("#image").dropzone({ 
   init: function() {
      this.on('addedfile', function(file) {
         if (this.files.length > 1) {
            this.removeFile(this.files[0]);
         }
      });
   },
   url: "{{ route('temp-images-create') }}",
   maxFiles: 1,
   paramName: 'image',
   addRemoveLinks: true,
   acceptedFiles: "image/jpeg,image/png,image/gif",
   headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
   }, success: function(file, response){
      $("#image_id").val(response.image_id);
   }
});

</script>
@endsection 