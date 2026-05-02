@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Users Edit</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('pages.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <form action="{{ route('pages.edit', $pages->id) }}" name="editPagesForm" id="edit-pages-form" method="POST">
      @csrf

      <div class="container-fluid">
         <div class="row">
            <div class="col-md-12">
               <div class="card mb-3">
                  <div class="card-body">								
                     <div class="row">
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="name">Name</label>
                              <input type="name" name="name" id="name" class="form-control" placeholder="Name" value="{{ $pages->name }}">
                              <p class="error"></p>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="slug">Slug</label>
                              <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug" value="{{ $pages->slug }}" readonly>
                              <p class="error"></p>	
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="content">Content</label>
                              <textarea name="content" id="content" cols="30" rows="10" class="summernote" placeholder="Content">
                                 {{ $pages->content }}
                              </textarea>
                              <p class="error"></p>	
                           </div>
                        </div>                           
                     </div>
                  </div>	                                                                      
               </div>
            </div>
         </div>
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('pages.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </div>
   </form>
</section>
@endsection 

@section('custom-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

$(document).ready(function () {
   
   $('#edit-pages-form').submit(function (e) {
      e.preventDefault();

      $.ajax({
         method: 'PUT', 
         url: '{{ route("pages.update", $pages->id) }}',
         data: $(this).serializeArray(),
         dataType: 'JSON',
         success: function (response){
            if (response['status']) {
               $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               $('#content').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');

               $('#edit-pages-form')[0].reset();

               Swal.fire({
                  position: 'top-end',
                  icon: 'success',
                  title: response['msg'] || 'Saved!',
                  showConfirmButton: false,
                  timer: 1500
               }).then(function () {
                  window.location.href = response.redirect || '{{ route("pages.listing") }}';
               });

            } else {
               let error = response['errors'];
               
               if (error.name) {
                  $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.name);
               } else {
                  $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }

               if (error.slug) {
                  $('#slug').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.slug);
               } else {
                  $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }

               if (error.content) {
                  $('#content').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.content);
               } else {
                  $('#content').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
               }
            }
         },
         error: function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX error', textStatus, errorThrown, jqXHR.responseText);
            Swal.fire('Error', 'Something went wrong. Check console.', 'error');
         }
      });
   });


   // Genereate Slug
   $('#name').change(function () {
      let slugVal = $(this).val();
      
      $.ajax({
         method: 'get', 
         url: '{{ route("getSlug") }}',
         data: 'title=' +slugVal,
         dataType: 'JSON',
         success: function (response){
            $('#slug').val(response['slug']);
         }
      });
   });

});


</script>
@endsection
