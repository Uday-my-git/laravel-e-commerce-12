@extends('front-end.layouts.app')

@section('content')
    
<section class="section-1">
   <div id="carouselExampleIndicators" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="false">
      <div class="carousel-inner">
         <div class="carousel-item active">
            <picture>
               <source media="(max-width: 799px)" srcset="{{ asset('front-end-assets/images/carousel-1-m.jpg') }}" />
               <source media="(min-width: 800px)" srcset="{{ asset('front-end-assets/images/carousel-1.jpg') }}" />
               <img src="{{ asset('front-end-assets/images/carousel-1.jpg') }} " alt="" />
            </picture>

            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
               <div class="p-3">
                  <h1 class="display-4 text-white mb-3">Kids Fashion</h1>
                  <p class="mx-md-5 px-5">Lorem rebum magna amet lorem magna erat diam stet. Sadips duo stet amet amet ndiam elitr ipsum diam</p>
                  <a class="btn btn-outline-light py-2 px-4 mt-3" href="#">Shop Now</a>
               </div>
            </div>
         </div>
         <div class="carousel-item">
            <picture>
               <source media="(max-width: 799px)" srcset="{{ asset('front-end-assets/images/carousel-2-m.jpg') }}" />
               <source media="(min-width: 800px)" srcset="{{ asset('front-end-assets/images/carousel-2.jpg') }}" />
               <img src="{{ asset('front-end-assets/images/carousel-2.jpg') }} " alt="" />
            </picture>

            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
               <div class="p-3">
                  <h1 class="display-4 text-white mb-3">Womens Fashion</h1>
                  <p class="mx-md-5 px-5">Lorem rebum magna amet lorem magna erat diam stet. Sadips duo stet amet amet ndiam elitr ipsum diam</p>
                  <a class="btn btn-outline-light py-2 px-4 mt-3" href="#">Shop Now</a>
               </div>
            </div>
         </div>
         <div class="carousel-item">
            <!-- <img src="images/carousel-3.jpg" class="d-block w-100" alt=""> -->

            <picture>
               <source media="(max-width: 799px)" srcset="{{ asset('front-end-assets/images/carousel-3-m.jpg') }}" />
               <source media="(min-width: 800px)" srcset="{{ asset('front-end-assets/images/carousel-3.jpg') }}" />
               <img src="{{ asset('front-end-assets/images/carousel-1.jpg') }} " alt="" />
            </picture>

            <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
               <div class="p-3">
                  <h1 class="display-4 text-white mb-3">Shop Online at Flat 70% off on Branded Clothes</h1>
                  <p class="mx-md-5 px-5">Lorem rebum magna amet lorem magna erat diam stet. Sadips duo stet amet amet ndiam elitr ipsum diam</p>
                  <a class="btn btn-outline-light py-2 px-4 mt-3" href="#">Shop Now</a>
               </div>
            </div>
         </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
         <span class="carousel-control-prev-icon" aria-hidden="true"></span>
         <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
         <span class="carousel-control-next-icon" aria-hidden="true"></span>
         <span class="visually-hidden">Next</span>
      </button>
   </div>
</section>

<section class="section-2">
   <div class="container">
      <div class="row">
         <div class="col-lg-3">
            <div class="box shadow-lg">
               <div class="fa icon fa-check text-primary m-0 mr-3"></div>
               <h2 class="font-weight-semi-bold m-0">Quality Product</h5>
            </div>                    
         </div>
         <div class="col-lg-3 ">
            <div class="box shadow-lg">
               <div class="fa icon fa-shipping-fast text-primary m-0 mr-3"></div>
               <h2 class="font-weight-semi-bold m-0">Free Shipping</h2>
            </div>                    
         </div>
         <div class="col-lg-3">
            <div class="box shadow-lg">
               <div class="fa icon fa-exchange-alt text-primary m-0 mr-3"></div>
               <h2 class="font-weight-semi-bold m-0">14-Day Return</h2>
            </div>                    
         </div>
         <div class="col-lg-3 ">
            <div class="box shadow-lg">
               <div class="fa icon fa-phone-volume text-primary m-0 mr-3"></div>
               <h2 class="font-weight-semi-bold m-0">24/7 Support</h5>
            </div>                    
         </div>
      </div>
   </div>
</section>

{{-------------------------- Categories Section --------------------------}}
<section class="section-3">
   <div class="container">
      <div class="section-title">
         <h2>Categories</h2>
      </div>  

      <div class="row pb-3">

         @if (getCategoryFun()->isNotEmpty())
            @foreach (getCategoryFun() as $category)
               <div class="col-lg-3">
                  <div class="cat-card">
                     <div class="left">
                        @if ($category->image != '')
                           <img src="{{ asset('uploads/category/thumb/' . $category->image) }}" alt="" class="img-fluid">
                        @endif
                     </div>
                     <div class="right">
                        <div class="cat-data">
                           <h2>{{ $category->name }}</h2>
                        </div>
                     </div>
                  </div>
               </div>
            @endforeach 
         @else
         @endif   
      </div>
   </div>
</section>

{{------------------------ Featured Products Section ------------------------}}
<section class="section-4 pt-5">
   <div class="container">
      <div class="section-title">
         <h2>Featured Products</h2>
      </div>    
      <div class="row pb-3">

            @if ($featuredProducts->isNotEmpty())
               @foreach ($featuredProducts as $featuredProduct)
                  @php
                     $featuredImg = $featuredProduct->product_images_fun->first();
                  @endphp  
                  <div class="col-md-3">
                     <div class="card product-card">
                        <div class="product-image position-relative">
                           <a href="{{ route('front-end.product', $featuredProduct->slug) }}" class="product-img">

                              @if (!empty($featuredImg->image))
                                 <img src="{{ asset('uploads/product/small/' . $featuredImg->image) }}" alt="" class="card-img-top">
                              @else
                                 <img class="card-img-top" src="{{ asset('admin-assets/img/default-150x150.png') }}" alt="">    
                              @endif
                           </a>

                           <a href="javascript:void(0)" class="whishlist" onclick="addToWishlist({{ $featuredProduct->id }})"><i class="far fa-heart"></i></a>                            

                           <div class="product-action">
                              {{--------- Check if any product in stock or Out Of Stock ---------}}

                              @if ($featuredProduct->track_qty == 'Yes')
                                 @if ($featuredProduct->qty > 0)
                                    <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $featuredProduct->id }})">
                                       <i class="fa fa-shopping-cart"></i> Add To Cart
                                    </a>  
                                 @else
                                    <a class="btn btn-dark" href="javascript:void(0);">
                                       Out Of Stock
                                    </a>  
                                 @endif
                              @else
                                 <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $featuredProduct->id }})">
                                    <i class="fa fa-shopping-cart"></i> Add To Cart
                                 </a>  
                              @endif                          
                           </div>
                        </div>                        
                        <div class="card-body text-center mt-3">
                           <a class="h6 link" href="{{ route('front-end.product', $featuredProduct->slug) }}">{{ $featuredProduct->title }}</a>
                           <div class="price mt-2">
                              <span class="h5"><strong>R.s.{{ $featuredProduct->price }}</strong></span>
                              <span class="h6 text-underline"><del>R.s.{{ $featuredProduct->cpompare_price }}</del></span>
                           </div>
                        </div>                        
                     </div>                                               
                  </div>  
               @endforeach
            @else
                  
            @endif   
            
      </div>
   </div>
</section>

{{------------------------------------ Latest Produsts Section ------------------------------------}}
<section class="section-4 pt-5">
   <div class="container">
      <div class="section-title">
         <h2>Latest Produsts</h2>
      </div>    
      <div class="row pb-3">

         @if ($latestProducts->isNotEmpty())
            @foreach ($latestProducts as $latestProduct)
               @php
                  $lateProductsImg = $latestProduct->product_images_fun->first();
               @endphp

               <div class="col-md-3">
                  <div class="card product-card">
                     <div class="product-image position-relative">
                        <a href="{{ route('front-end.product', $latestProduct->slug) }}" class="product-img">

                           @if (!empty($lateProductsImg->image))
                              <img src="{{ asset('uploads/product/small/' . $lateProductsImg->image) }}" alt="" class="card-img-top">
                           @else
                              <img src="{{ asset('admin-assets/img/default-150x150.png') }}" class="img-thumbnail">
                           @endif
                        </a>

                        <a href="javascript:void(0)" class="whishlist" onclick="addToWishlist({{ $latestProduct->id }})"><i class="far fa-heart"></i></a>                              

                        {{--------------------------- Add To Cart Featured ---------------------------}}
                        <div class="product-action">
                           {{--------- Check if any product in stock or Out Of Stock ---------}}
                              
                           @if ($latestProduct->track_qty == 'Yes')
                              @if ($latestProduct->qty > 0)
                                 <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $latestProduct->id }})">
                                    <i class="fa fa-shopping-cart"></i> Add To Cart
                                 </a>  
                              @else
                                 <a class="btn btn-dark" href="javascript:void(0);">
                                    Out Of Stock
                                 </a>  
                              @endif
                           @else
                              <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{ $latestProduct->id }})">
                                 <i class="fa fa-shopping-cart"></i> Add To Cart
                              </a>  
                           @endif
                          
                        </div>
                     </div>                        
                     <div class="card-body text-center mt-3">
                        <a class="h6 link" href="{{ route('front-end.product', $latestProduct->slug) }}">{{ $latestProduct->title }}</a>
                        <div class="price mt-2">
                           <span class="h5"><strong>R.s.{{ $latestProduct->price }}</strong></span>
                           <span class="h6 text-underline"><del>R.s.{{ $latestProduct->compare_price }}</del></span>
                        </div>
                     </div>                        
                  </div>                                               
               </div>             
            @endforeach
         @else
         @endif
         
         
      </div>
   </div>
</section>

@endsection
