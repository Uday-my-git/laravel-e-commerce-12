@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Update Product</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('product.listing') }}" class="btn btn-primary">Back</a>
         </div>
      </div>
   </div>

</section>

<section class="content">
   <form action="" name="editProductForm" id="edit-product-form" method="POST">
      <div class="container-fluid">
         <div class="row">
            <div class="col-md-8">
               <div class="card mb-3">
                  <div class="card-body">								
                     <div class="row">
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="title">Title</label>
                              <input type="text" name="title" id="title" class="form-control" value="{{ isset($products->title) ? ($products->title) : '' }}" placeholder="Title">	
                              <p class="error"></p>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="slug">Slug</label>
                              <input type="text" name="slug" id="slug" class="form-control" value="{{ $products->slug }}" placeholder="Slug" readonly>
                              <p class="error"></p>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="short_description">Short Description</label>
                              <textarea name="short_description" id="short_description" cols="10" rows="5" class="summernote" placeholder="Short Description">
                                 {{ isset($products->short_description) ? ($products->short_description) : '' }}
                              </textarea>
                              <p class="error"></p>	
                           </div>
                        </div>   
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="description">Description</label>
                              <textarea name="description" id="description" cols="30" rows="10" class="summernote" placeholder="Description">
                                 {{ isset($products->description) ? ($products->description) : '' }}
                              </textarea>
                              <p class="error"></p>
                           </div>
                        </div>   
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="shipping_returns">Shipping Returns</label>
                              <textarea name="shipping_returns" id="shipping_returns" cols="5" rows="5" class="summernote" placeholder="Shipping Returns">
                                 {{ isset($products->shipping_returns) ? ($products->shipping_returns) : '' }}
                              </textarea>
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
                  @if ($productImages->isNotEmpty())
                     @foreach ($productImages as $productImg)
                        <div class='col-md-3' id="img-row-{{ $productImg->id }}">
                           <div class="card">
                              <input type="hidden" name="image_array[]" value="{{ $productImg->id }}">
                              
                              <img src="{{ asset('uploads/product/small/' . $productImg->image) }}" class="card-img-top" alt="...">

                              <div class="card-body">
                                 <a href="javascript:void(0)" class="btn btn-danger" onclick="deleteProductImg({{ $productImg->id }})">Delete</a>
                              </div>
                           </div>   
                        </div>
                     @endforeach
                  @endif
               </div>
               <div class="card mb-3">
                  <div class="card-body">
                     <h2 class="h4 mb-3">Pricing</h2>								
                     <div class="row">
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="price">Price</label>
                              <input type="text" name="price" id="price" class="form-control" value="{{ optional($products)->price }}" placeholder="Price">	
                              <p class="error"></p>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="compare_price">Compare at Price</label>
                              <input type="text" name="compare_price" id="compare_price" class="form-control" value="{{ optional($products)->compare_price }}" placeholder="Compare Price">

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
                              <input type="text" name="sku" id="sku" class="form-control" value="{{ optional($products)->sku }}" placeholder="sku">	
                              <p class="error"></p>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="mb-3">
                              <label for="barcode">Barcode</label>
                              <input type="text" name="barcode" id="barcode" class="form-control" value="{{ optional($products)->barcode }}" placeholder="Barcode">	
                              <p class="error"></p>
                           </div>
                        </div>   
                        <div class="col-md-12">
                           <div class="mb-3">
                              <div class="custom-control custom-checkbox">
                                 <input type="hidden" name="track_qty" value="No">
                                 <input type="checkbox" name="track_qty" class="custom-control-input" id="track_qty" 
                                    value="Yes" {{ $products->track_qty == 'Yes' ? 'checked' : ''}} >

                                 <label for="track_qty" class="custom-control-label">Track Quantity</label>
                              </div>
                           </div>
                           <div class="mb-3">
                              <input type="number" min="0" name="qty" id="qty" class="form-control" placeholder="Qty" value="{{ optional($products)->qty }}">	
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
                           <option value="1" {{ $products->status == 1 ? 'selected' : '' }}>Active</option>
                           <option value="0" {{ $products->status == 0 ? 'selected' : '' }}>Block</option>
                        </select>
                     </div>
                  </div>
               </div> 
               <div class="card">
                  <div class="card-body">	
                     <h2 class="h4  mb-3">Product category</h2>
                     <div class="mb-3">
                        <label for="category">Category</label>
                        <select name="category" id="category" class="form-control" onchange="fetchSubCategories(this)">
                           <option value="">Select Category</option>

                           @if ($categories->isNotEmpty())
                              @foreach ($categories as $category)
                                 <option value="{{ $category->id }}" @selected($products->category_id == $category->id) >{{ $category->name }}</option>    
                              @endforeach 
                           @else
                              <option value="">No Category Found</option>
                           @endif
                        </select>
                        <p class="error"></p>
                     </div>
                     <div class="mb-3">
                        <label for="category">Sub category</label>
                        <select name="sub_category" id="sub_category_id" class="form-control">
                           <option value="">Select Category</option>

                           @if ($subCategories->isNotEmpty())
                              @foreach ($subCategories as $subCategory)
                                 <option value="{{ $subCategory->id }}" @selected($products->sub_category_id == $subCategory->id) >{{ $subCategory->name }}</option>    
                              @endforeach 
                           @else
                              <option value="">No Category Found</option>
                           @endif
                        </select>
                     </div>
                  </div>
               </div> 
               <div class="card mb-3">
                  <div class="card-body">	
                     <h2 class="h4 mb-3">Product brand</h2>
                     <div class="mb-3">
                        <select name="brand" id="brand" class="form-control">
                           <option value="">Select Brand</option>
                         
                           @if ($brands->isNotEmpty())
                              @foreach ($brands as $brand)
                                 <option value="{{ $brand->id }}" @selected($brand->id == $products->brand_id) >{{ $brand->name }}</option>    
                              @endforeach 
                           @else
                              <option value="">No Brand Found</option>
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
                           <option value="0" @selected($products->is_featured == 'No') >No</option>
                           <option value="1" @selected($products->is_featured == 'Yes') >Yes</option>                                                
                        </select>
                     </div>
                  </div>
               </div>  
               <div class="card mb-3">
                  <div class="card-body">	
                     <h2 class="h4 mb-3">Related product</h2>
                     <div class="mb-3">
                        <select name="related_products[]" class="form-control related_products w-10" multiple>

                           @if ($relatedProducts != '')
                              @foreach ($relatedProducts as $relatedProduct)
                                 <option value="{{ $relatedProduct->id }}" selected> {{ $relatedProduct->title }} </option>
                              @endforeach
                           @endif

                        </select>
                     </div>
                  </div>
               </div>                                    
            </div>
         </div>
         
         <div class="pb-5 pt-3">
            <button type="submit" class="btn btn-primary">Update Now</button>
            
            <a href="{{ route('product.listing') }}" class="btn btn-outline-dark ml-3">Cancel</a>
         </div>
      </div>
   </form>
</section>
@endsection 

@section('custom-js')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.4/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.22.4/dist/sweetalert2.min.css" rel="stylesheet">


<script>


$('#edit-product-form').submit(function (e) {
   e.preventDefault()
   $('#edit-product-form').prop('disabled', true);
   
   let formData = $(this).serializeArray();

   $.ajax({
      method: 'PUT',
      url: '{{ route("product.update", $products->id) }}',
      data: formData,
      dataType: 'JSON',
      success: function (response) {
         $('#edit-product-form').prop('disabled', false);

         if (response['status']) {
            window.location.href = '{{ route("product.listing") }}';

         } else {
            let errors = response['errors'];

            $('#error').removeClass('invalid-feedback').html('');
            $('input[type="text"], select, input[type="number"]').removeClass('is-invalid');

            if (response['notFound']) {
               window.location.href = '{{ route("product.listing") }}';
            }

            $.each(errors, function (key, value) {
               $(`#${key}`).addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(value); 
            });
         }
      },
      error: function(error) {
         alert('something gone wrong.....');
      }
   });
});


// fetch sub-category behalf on category, Method: 1
function fetchSubCategories(category) {
   let category_id = $(category).val();

   if (!category_id) {
      $('#sub_category_id').html('<option value="">Select Sub-Category</option>');
      return;
   }

   $.ajax({
      method: 'GET',
      url: '{{ route("prodcutSubCategory") }}',
      data: { category_id: category_id },
      dataType: 'JSON',
      success: function (response) {
         $('#sub_category_id').html('<option value="">Select Sub-Category</option>');
         
         $.each(response.subCategory, function (key, items) {
            $('#sub_category_id').append(`<option value="${items.id}">${items.name}</option>`);
         });
      },
      error: function(error) {
         alert('something went wrong...');
      }
   });
}


// fetch sub-category behalf on category, Method: 2
// $('#category').on('change', function () {
//    let category = $(this).val();
   
//    if (category != '') {
//       $.ajax({
//          method: 'GET',
//          url: '{{ route("prodcutSubCategory") }}',
//          data: 'category_id='+category,
//          dataType: 'JSON',
//          success: function (response) { 
//             if (response.status === true) { 
//                $('#sub_category_id').find('option').not(':first').remove();

//                $.each(response.subCategory, function (key, value) {
//                   $('#sub_category_id').append(`<option value="${value.id}">${value.name}</option>`);
//                });
//             }           
//          },
//          error: function(error) {
//             alert('something gone wrong.....')
//          }
//       });
//    }
// });


// Slug generate
$('#title').change(function () {
   let slugVal = $(this).val();
   
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


// Dropzone image uploadDropzone.autoDiscover = false;   
Dropzone.autoDiscover = false;   

const dropzone = $("#image").dropzone({ 
   url: "{{ route('prodcut-image.update') }}",
   maxFiles: 6,
   paramName: 'image',
   params: { 
      product_id: "{{ $products->id }}"
   },
   addRemoveLinks: true,
   acceptedFiles: "image/jpeg,image/png,image/gif",
   headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
   }, 
   success: function(file, response){
      // $("#image_id").val(response.image_id);
      
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


// delete product image
function deleteProductImg(id) 
{
   Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this image!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
      }).then((result) => {
      if (result.isConfirmed) {
         $.ajax({
            method: 'DELETE',
            url: '{{ route("prodcut-image.delete") }}',
            data: 'id='+id,
            success: function (response) {
               if (response.status === true) {
                  $('#img-row-'+id).remove();

                  Swal.fire({
                     title: "Deleted!",
                     text: response.msg,
                     icon: "success"
                  });
               } else {
                  Swal.fire({
                     icon: "error",
                     title: "Oops...",
                     text: response.msg,
                     footer: '<a href="#">Why do I have this issue?</a>'
                  });

                  // $('#img-row-'+id).remove();
               }
            }
         });
      }
   });
}


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
   
</script>
@endsection
