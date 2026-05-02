@extends('front-end.layouts.app')

@section('content')

<section class="section-5 pt-3 pb-3 mb-3 bg-white">
   <div class="container">
      <div class="light-font">
         <ol class="breadcrumb primary-color mb-0">
            <li class="breadcrumb-item">
               <a class="white-text" href="{{ route('front-end.home') }}">Home</a>
            </li>
            <li class="breadcrumb-item active">Shop</li>
         </ol>
      </div>
   </div>
</section>
<section class="section-6 pt-5">
   <div class="container">
      <div class="row">
         {{-------------------------- Category Filters Section --------------------------}}
         <div class="col-md-3 sidebar">
            <div class="sub-title">
               <h2>Categories </h3> 
            </div>
            <div class="card">
               <div class="card-body">
                  <div class="accordion accordion-flush" id="accordionExample">
                  
                     @if ($categorys->isNotEmpty())
                        @foreach ($categorys as $key => $category)
                           <div class="accordion-item">

                              @if ($category->sub_category_fun->isNotEmpty())
                                 <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $key }}" aria-expanded="false" aria-controls="collapseOne-{{ $key }}"> {{ $category->name }} </button>
                                 </h2>
                              @else
                                 {{-- if sub-category of product is empty then toggle (^) button or sub-category section is hide --}}
                                 <a href="{{ route('front-end.shop', $category->slug) }}" class="nav-item nav-link {{ ($categorySelected == $category->id) ? 'text-primary' : '' }}">{{ $category->name }}</a> 
                              @endif

                              @if ($category->sub_category_fun->isNotEmpty())
                                 <div id="collapseOne-{{ $key }}" class="accordion-collapse collapse {{ ($categorySelected == $category->id )? 'show' : '' }}" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                       <div class="navbar-nav">

                                          @foreach ($category->sub_category_fun as $key => $subCategory)
                                             <a href="{{ route('front-end.shop', [$category->slug, $subCategory->slug]) }}"
                                                class="nav-item nav-link {{ ($subCategorySelected == $subCategory->id) ? 'text-primary' : '' }}" >

                                                {{ $subCategory->name }}
                                             </a>
                                          @endforeach
                                       </div>
                                    </div>
                                 </div>
                              @endif
                           </div>
                        @endforeach
                     @endif
                  </div>
               </div>
            </div>

            {{--------------------------------------- Brand Filters Section ---------------------------------------}}
            <div class="sub-title mt-5">
               <h2>Brand </h3>
            </div>
            <div class="card">
               <div class="card-body">

                  @if ($brands->isNotEmpty())   
                     @foreach ($brands as $brand)
                        <div class="form-check mb-2">
                           <input {{ (in_array($brand->id, $brandsArray)) ? 'checked' : '' }} type="checkbox" name="brand[]" class="form-check-input brand-label" 
                           value="{{ $brand->id }}" id="brand-{{ $brand->id }}">
                           
                           <label class="form-check-label" for="brand-{{ $brand->id }}"> {{ $brand->name }} </label>
                        </div>  
                     @endforeach
                  @endif   
               </div>
            </div>

            {{--------------------------------------- Price range slider Section ---------------------------------------}}
            <div class="sub-title mt-5">
               <h2>Price </h3>
            </div>
            <div class="card">
               <div class="card-body">
                  <input type="text" class="js-range-slider" name="my_range" value="" />
               </div>
            </div>
         </div>

         <div class="col-md-9">  
            <div class="row pb-3">
               {{------------------------------ Sorting Filters Section ------------------------------}}
               <div class="col-12 pb-1">
                  <div class="d-flex align-items-center justify-content-end mb-4">
                     <div class="ml-2">
                        <select name="sort" class="form-control" id="sort">
                           <option value="latest" {{ ($sort == 'latest') ? 'selected' : '' }} >Latest</option>
                           <option value="old" {{ ($sort == 'old') ? 'selected' : '' }} >Old</option>
                           <option value="price_desc" {{ ($sort == 'price_desc') ? 'selected' : '' }} >Price High</option>
                           <option value="price_asc" {{ ($sort == 'price_asc') ? 'selected' : '' }} >Price Low</option>
                        </select>
                     </div>
                  </div>
               </div>

               {{------------------------------ Product Listing Section ------------------------------}}
               @if ($products->isNotEmpty())
                  @foreach ($products as $product)
                     @php
                        $productImg = $product->product_images_fun->first();
                     @endphp
                     
                     <div class="col-md-4">
                        <div class="card product-card">
                           <div class="product-image position-relative">
                              <a href="{{ route('front-end.product', $product->slug) }}" class="product-img">

                                 @if (!empty($productImg->image))
                                    <img class="card-img-top" src="{{ asset('uploads/product/small/' . $productImg->image) }}" alt="">
                                 @else
                                    <img src="{{ asset('admin-assets/img/default-150x150.png') }}" class="img-thumbnail">
                                 @endif
                              </a>
                             <a href="javascript:void(0)" class="whishlist" onclick="addToWishlist({{ $product->id }})"><i class="far fa-heart"></i></a>    

                              <div class="product-action">
                                 
                                 {{--------- Check if any product in stock or Out Of Stock ---------}}
                                 @if ($product->track_qty == 'Yes' && $product->qty > 0)
                                    <a class="btn btn-dark a_tag" href="javascript:void(0);" onclick="addToCart({{ $product->id }})">
                                       <i class="fa fa-shopping-cart"></i> Add To Cart 
                                    </a>
                                 @else
                                    <a class="btn btn-dark" href="javascript:void(0);">
                                       Out Of Stock
                                    </a> 
                                 @endif
                              </div>
                           </div>
                           <div class="card-body text-center mt-3">
                              <a class="h6 link" href="{{ route('front-end.product', $product->slug) }}"> {{ $product->title }} </a>
                              <div class="price mt-2">
                                 <span class="h5">
                                    <strong> R.s.{{ number_format($product->price, 2) }} </strong>
                                 </span>
                                 <span class="h6 text-underline">

                                    @if ($product->compare_price > 0)
                                       <del> R.s.{{ number_format($product->compare_price, 2) }} </del>
                                    @endif
                                 </span>
                              </div>
                           </div>
                        </div>
                     </div>
                  @endforeach
               @else
                  <p>Products Not Found</p>
               @endif   
            </div>
         </div>
         <div class="col-md-12 pt-5">
            <nav aria-label="Page navigation example">
               {{ $products->withQueryString()->links() }}
            </nav>
         </div>
      </div>
   </div>
</section>

@endsection

@section('custom-js')
<script>

   // Price range slider 
   rangeSlider = $(".js-range-slider").ionRangeSlider({
      type: "double",
      min: 0,
      max: 1000,
      from: {{ $minPrice }},
      step: 10,
      to: {{ $maxPrice }},
      skin: "round",
      max_postfix: "+",
      prefix: "R.s.",
      onFinish: function () {
         apply_filters();
      },
   });

   let slider = $(".js-range-slider").data("ionRangeSlider");

   // Apply brands filters 
   $('.brand-label').change(function () {
      apply_filters();
   });


   $('#sort').change(function () {
      apply_filters();
   })

   function apply_filters()
   {
      let brands = [];

      $('.brand-label').each(function () {
         if ($(this).is(':checked') == true) {
            brands.push($(this).val());
         }
      });

      console.log(brands.toString()); 

      // Start building the URL with the current page link
      let url = '{{ url()->current() }}?';

      // Brand filter apply
      if (brands.length > 0) {
         url += '&brand=' + brands.toString();
      }

      let search = $('#search').val();

      if (search.length > 0) {
         url += '&search=' +search;
      }

      // Add price range to the URL (from slider values)
      url += '&min_price=' + slider.result.from + '&max_price=' + slider.result.to;

      // Sorting filter apply
      url += '&sort=' + $('#sort').val();

      // Add selected brand IDs/names to the URL as a comma-separated string
      window.location.href = url;
   }



</script>
@endsection