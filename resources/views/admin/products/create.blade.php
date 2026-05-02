@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Create Product</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('product.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>

</section>

<section class="content">
   <form action="" name="productForm" id="product-form" method="POST">
      <div class="container-fluid">
         <div class="row">
            <div class="col-md-8">
               <div class="card mb-3">
                  <div class="card-body">								
                     <div class="row">
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="title">Title</label>
                              <input type="text" name="title" id="title" class="form-control" placeholder="Title">
                              <p class="error"></p>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="title">Slug</label>
                              <input type="text" name="slug" id="slug" class="form-control" placeholder="Slug" readonly
                              <p class="error"></p>	
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="description">Description</label>
                              <textarea name="description" id="description" cols="30" rows="10" class="summernote" placeholder="Description"></textarea>
                              <p class="error"></p>	
                           </div>
                        </div>     
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="short_description">Short Description</label>
                              <textarea name="short_description" id="short_description" cols="30" rows="10" class="summernote" placeholder="Short Description"></textarea>
                              <p class="error"></p>	
                           </div>
                        </div>    
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="shipping_returns">Shipping Returns</label>
                              <textarea name="shipping_returns" id="shipping_returns" cols="5" rows="5" class="summernote" placeholder="Shipping Returns"></textarea>
                              <p class="error"></p>	
                           </div>
                        </div>                                          
                     </div>
                  </div>	                                                                      
               </div>
               <div class="card mb-3">
                  <div class="card-body">
                     <h2 class="h4 mb-3">Media</h2>								
                     <div id="image" class="dropzone dz-clickable">
                        <div class="dz-message needsclick">    
                           <br>Drop files here or click to upload.<br><br>                                            
                        </div>
                     </div>
                  </div>	                                                                      
               </div>
               <div class="row" id="product-gallery">

               </div>
               <div class="card mb-3">
                  <div class="card-body">
                     <h2 class="h4 mb-3">Pricing</h2>								
                     <div class="row">
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="price">Price</label>
                              <input type="text" name="price" id="price" class="form-control" placeholder="Price">	
                              <p class="error"></p>	
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="compare_price">Compare at Price</label>
                              <input type="text" name="compare_price" id="compare_price" class="form-control" placeholder="Compare Price">
                              <p class="text-muted mt-3">
                                 To show a reduced price, move the product’s original price into Compare at price. Enter a lower value into Price.
                              </p>	
                           </div>
                        </div>                                            
                     </div>
                  </div>	                                                                      
               </div>
               <div class="card mb-3">
                  <div class="card-body">
                     <h2 class="h4 mb-3">Inventory</h2>								
                     <div class="row">
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="sku">SKU (Stock Keeping Unit)</label>
                              <input type="text" name="sku" id="sku" class="form-control" placeholder="sku">	
                              <p class="error"></p>	
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="barcode">Barcode</label>
                              <input type="text" name="barcode" id="barcode" class="form-control" placeholder="Barcode">	
                              <p class="error"></p>	
                           </div>
                        </div>   
                        <div class="col-md-12">
                           <div class="mb-3">
                              <div class="custom-control custom-checkbox">
                                 <input type="hidden" name="track_qty" value="No">

                                 <input type="checkbox" name="track_qty" class="custom-control-input" id="track_qty" value="Yes" checked>
                                 <label for="track_qty" class="custom-control-label">Track Quantity</label>
                              </div>
                           </div>
                           <div class="mb-3">
                              <input type="number" min="0" name="qty" id="qty" class="form-control" placeholder="Qty">	
                              <p class="error"></p>	
                           </div>
                        </div>                                         
                     </div>
                  </div>	                                                                      
               </div>
            </div>
            {{--------------- Product category or fetch product data section ---------------}}
            <div class="col-md-4">
               <div class="card mb-3">
                  <div class="card-body">	
                     <h2 class="h4 mb-3">Product status</h2>
                     <div class="mb-3">
                        <select name="status" id="status" class="form-control">
                           <option value="1">Active (Deafult)</option>
                           <option value="0">Block</option>
                        </select>
                     </div>
                  </div>
               </div> 
               <div class="card">
                  <div class="card-body">	
                     <h2 class="h4  mb-3">Product category</h2>
                     <div class="mb-3">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control">
                           <option value="">Select Category</option>
                           
                           @if ($categories->isNotEmpty())
                              @foreach ($categories as $category)
                                 <option value="{{ $category->id }}">{{ $category->name }}</option>    
                              @endforeach 
                           @endif
                        </select>
                        <p class="error"></p>	
                     </div>
                     <div class="mb-3">
                        <label for="category">Sub category</label>
                        <select name="sub_category" id="sub_category_id" class="form-control">
                           <option value="">Select Sub-Category</option>
                        </select>
                     </div>
                  </div>
               </div> 
               <div class="card mb-3">
                  <div class="card-body">	
                     <h2 class="h4 mb-3">Product brand</h2>
                     <div class="mb-3">
                        <select name="brand" id="brand" class="form-control">
                           <option value="">Select Product Brand</option>
                           
                           @if ($brands->isNotEmpty())
                              @foreach ($brands as $brand)
                                 <option value="{{ $brand->id }}">{{ $brand->name }}</option>    
                              @endforeach 
                           @endif
                        </select>
                        <p class="error"></p>	
                     </div>
                  </div>
               </div> 
               <div class="card mb-3">
                  <div class="card-body">	
                     <h2 class="h4 mb-3">Featured product</h2>
                     <div class="mb-3">
                        <select name="is_featured" id="is_featured" class="form-control">
                           <option value="No">No (Deafult)</option>
                           <option value="Yes">Yes</option>                                                
                        </select>
                     </div>
                  </div>
               </div>       
               <div class="card mb-3">
                  <div class="card-body">	
                     <h2 class="h4 mb-3">Related product</h2>
                     <div class="mb-3">
                        <select name="related_products[]" id="related-products" class="form-control related_products w-10" multiple></select>
                     </div>
                  </div>
               </div>                                 
            </div>
         </div>
         
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('product.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </div>
   </form>
</section>
@endsection 

@section('custom-js')
<script>

Dropzone.autoDiscover = false;   

$(function () {

   // Create form data submit
   $('#product-form').submit(function (e) {
      e.preventDefault()
      $('button[type=submit]').prop('disabled', true);

      let formData = $(this).serializeArray();

      $.ajax({
         method: 'POST',
         url: '{{ route("product.store") }}',
         data: formData,
         dataType: 'JSON',
         success: function (response) {
            $('button[type=submit]').prop('disabled', false);

            if (response['status']) {
               window.location.href = '{{ route("product.listing") }}';
            } else {
               let errors = response['errors'];

               $('.error').removeClass('invalid-feedback').html('');
               $('input[type="text"], input[type="number"], select').removeClass('is-invalid');

               $.each(errors, function (key, value) {
                  $(`#${key}`).addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(value); 
               });
            }
         },
         error: function(error) {
            alert('something gone wrong.....')
         }
      });
   });


   $('#category').change(function () {
      let category_id = $(this).val();

      $.ajax({
         method: 'GET',
         url: '{{ route("prodcutSubCategory") }}',
         data: 'category_id='+category_id,
         dataType: 'JSON',
         success: function (response) {
            // $('#sub_category_id').find('option').not(':first').remove();    // method 1
            $('#sub_category_id').html('<option value="">Select Sub-Category</option>');  // method 2
            
            $.each(response.subCategory, function (key, items) {
               $('#sub_category_id').append(`<option value="${items.id}">${items.name}</option>`);
            });
         },
         error: function(error) {
            alert('something gone wrong.....')
         }
      });
   });

   // Slug generate
   $('#title').change(function () {
      let slugVal = $(this).val();
      $('button[type=submit]').prop('disabled', true);
      
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
   });
   

   // Dropzone image upload in temprery folder
   const dropzone = $("#image").dropzone({ 
      url: "{{ route('temp-images-create') }}",
      maxFiles: 6,
      paramName: 'image',
      addRemoveLinks: true,
      acceptedFiles: "image/jpeg,image/png,image/gif",
      headers: {
         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }, 
      success: function(file, response){
         // $("#image_id").val(response.image_id);yh
         
         let html = `<div class='col-md-3' id="img-row-${response.image_id}">
            <div class="card">
               <input type="hidden" name="image_array[]" value="${response.image_id}">
               
               <img src="${response.image_path}" class="card-img-top" alt="...">

               <div class="card-body">
                  <a href="javascript:void(0)" class="btn btn-danger" onclick="deleteProductImg(${response.image_id})">Delete</a>
               </div>
            </div>   
         </div>`;

         $('#product-gallery').append(html);
      }
   });


   // Related pfoducts for dropdowan
   $('.related_products').select2({
      ajax: {
         url: '{{ route("product.getRelatedProduct") }}',
         dataType: 'json',
         tags: true,
         multiple: true,
         minimumInputLength: 3,
         processResults: function (data) {
            return {
               results: data.tags
            };
         }
      }
   });   


});


function deleteProductImg(id) 
{  
   $('#img-row-'+id).remove();
}


</script>
@endsection
