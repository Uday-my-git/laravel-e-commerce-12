<!DOCTYPE html>
<html class="no-js" lang="en_AU" />
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Laravel Online Shop</title>
	<meta name="description" content="" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1, user-scalable=no" />

	<meta name="HandheldFriendly" content="True" />
	<meta name="pinterest" content="nopin" />

	<meta property="og:locale" content="en_AU" />
	<meta property="og:type" content="website" />
	<meta property="fb:admins" content="" />
	<meta property="fb:app_id" content="" />
	<meta property="og:site_name" content="" />
	<meta property="og:title" content="" />
	<meta property="og:description" content="" />
	<meta property="og:url" content="" />
	<meta property="og:image" content="" />
	<meta property="og:image:type" content="image/jpeg" />
	<meta property="og:image:width" content="" />
	<meta property="og:image:height" content="" />
	<meta property="og:image:alt" content="" />

	<meta name="twitter:title" content="" />
	<meta name="twitter:site" content="" />
	<meta name="twitter:description" content="" />
	<meta name="twitter:image" content="" />
	<meta name="twitter:image:alt" content="" />
	<meta name="twitter:card" content="summary_large_image" />
	

	<link rel="stylesheet" type="text/css" href="{{ asset('front-end-assets/css/slick.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('front-end-assets/css/slick-theme.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('front-end-assets/css/ion.rangeSlider.min.css') }}" />

	<!-- Toastr + iziToast -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
	<link href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css" rel="stylesheet">

	<!-- Fonts -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;500&family=Raleway:ital,wght@0,400;0,600;0,800;1,200&family=Roboto+Condensed:wght@400;700&family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet">

	<!-- Your style last -->
	<link rel="stylesheet" type="text/css" href="{{ asset('front-end-assets/css/style.css') }}" />

	<!-- Fav Icon -->
	<link rel="shortcut icon" type="image/x-icon" href="#" />

	<meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body data-instant-intensity="mousedown">

<div class="bg-light top-header">        
	<div class="container">
		<div class="row align-items-center py-3 d-none d-lg-flex justify-content-between">
			<div class="col-lg-4 logo">
				<a href="{{ route('front-end.home') }}" class="text-decoration-none">
					<span class="h1 text-uppercase text-primary bg-dark px-2">E-</span>
					<span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Commerce</span>
				</a>
			</div>
			<div class="col-lg-6 col-6 text-left  d-flex justify-content-end align-items-center">
				
				@if (Auth::check())	
					<a href="{{ route('account.profile') }}" class="nav-link text-dark">Welcome {{ Auth::user()->name }}</a>
				@else
					<a href="{{ route('account.login') }}" class="nav-link text-dark">Login/Register</a>
				@endif
				
				<form action="{{ route('front-end.shop') }}" name="search" method="GET">					
					<div class="input-group">
						<input type="text" name="search" class="form-control" aria-label="Amount (to the nearest dollar)" placeholder="Search For Products" id="search"
						value="{{ Request::get('search') }}">

						<button type="submit" class="input-group-text">
							<i class="fa fa-search"></i>
					  	</button>
					</div>
				</form>
			</div>		
		</div>
	</div>
</div>

<header class="bg-dark">
	<div class="container">
		<nav class="navbar navbar-expand-xl" id="navbar">
			<a href="{{ route('front-end.home') }}" class="text-decoration-none mobile-logo">
				<span class="h2 text-uppercase text-primary bg-dark">Online</span>
				<span class="h2 text-uppercase text-white px-2">SHOP</span>
			</a>
			<button class="navbar-toggler menu-btn" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <!-- <span class="navbar-toggler-icon icon-menu"></span> -->
            <i class="navbar-toggler-icon fas fa-bars"></i>
    		</button>
    		<div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
               <!-- <li class="nav-item">
                  <a class="nav-link active" aria-current="page" href="index.php" title="Products">Home</a>
        			</li> -->

					@if (getCategoryFun()->isNotEmpty())
						@foreach (getCategoryFun() as $category)

							<li class="nav-item dropdown">
								<button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
									{{ $category->name }}
								</button>

								@if ($category->sub_category_fun->isNotEmpty())
									<ul class="dropdown-menu dropdown-menu-dark">
										@foreach ($category->sub_category_fun as $sub_category)
											<li><a href="{{ route('front-end.shop', [$category->slug, $sub_category->slug]) }}" class="dropdown-item nav-link">{{ $sub_category->name }}</a></li>
										@endforeach
									</ul>
								@else 
									<ul class="dropdown-menu dropdown-menu-dark">
										<li><a class="dropdown-item nav-link" href="#">No Sub Categoty Found</a></li>
									</ul>
								@endif
							
							</li>		
						@endforeach
					@else
						<li><ul style="text-align: center; color:#FFFFFF">Category Not Availabel</ul></li>
					@endif
				
            </ul>      			
        	</div>   
        	<div class="right-nav py-0">
				<a href="{{ url('/cart') }}" class="ml-3 d-flex align-items-center cart-link">
					<div class="cart-icon">
						<i class="fas fa-shopping-cart"></i>

						@if (Cart::content()->count() > 0)
							<span class="cart-count">
								{{ Cart::content()->count() }}
							</span> 
						@endif
					</div>
				</a>
			</div>
	
      </nav>
  	</div>
</header>

<main>

	@yield('content')

</main>

<footer class="bg-dark mt-5">
	<div class="container pb-5 pt-3">
		<div class="row">
			<div class="col-md-4">
				<div class="footer-card">
					<h3>Get In Touch</h3>
					<p>No dolore ipsum accusam no lorem. <br>
					123 Street, New York, USA <br>
					exampl@example.com <br>
					000 000 0000</p>
				</div>
			</div>

			<div class="col-md-4">
				<div class="footer-card">
					<h3>Important Links</h3>
					<ul>
						@forelse (staticPagesFun() as $staticPage)
							<li><a href="{{ route('front-end.pages', $staticPage->slug) }}" title="{{ $staticPage->name }}">{{ $staticPage->name }}</a></li>	
						@empty
							<li><a href="#" title="About">Links Not Availabel</a></li>
						@endforelse		
					</ul>
				</div>
			</div>

			<div class="col-md-4">
				<div class="footer-card">
					<h3>My Account</h3>
					<ul>
						<li><a href="{{ route("account.login") }}" title="Sell">Login</a></li>
						<li><a href="{{ route("account.userRegister") }}" title="Advertise">Register</a></li>
						<li><a href="#" title="Contact Us">My Orders</a></li>						
					</ul>
				</div>
			</div>			
		</div>
	</div>
	<div class="copyright-area">
		<div class="container">
			<div class="row">
				<div class="col-12 mt-3">
					<div class="copy-right text-center">
						<p>© Copyright {{ date('Y') }} E-Commerce ShopWay. All Rights Reserved</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>

{{-------- Wishlist Modal --------}}

<div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
      </div>
      {{-- <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div> --}}
    </div>
  </div>
</div>

{{-- <button type="button" class="btn btn-primary btn-floating btn-lg border-cirlce" id="btn-back-to-top">
    <i class="fas fa-arrow-up"></i>
</button> --}}

<!-- jQuery first -->
<script src="{{ asset('front-end-assets/js/jquery-3.6.0.min.js') }}"></script>

<!-- Bootstrap -->
<script src="{{ asset('front-end-assets/js/bootstrap.bundle.5.1.3.min.js') }}"></script>

<!-- Toastr (after jQuery) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- iziToast -->
<script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>

<!-- jQuery Validation -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>

<!-- Other plugins -->
<script src="{{ asset('front-end-assets/js/instantpages.5.1.0.min.js') }}"></script>
<script src="{{ asset('front-end-assets/js/lazyload.17.6.0.min.js') }}"></script>
<script src="{{ asset('front-end-assets/js/slick.min.js') }}"></script>
<script src="{{ asset('front-end-assets/js/ion.rangeSlider.min.js') }}"></script>

<!-- Your custom scripts last -->
<script src="{{ asset('front-end-assets/js/custom.js') }}"></script>

<script>

   window.onscroll = function() {myFunction()};

   var navbar = document.getElementById("navbar");
   var sticky = navbar.offsetTop;

   function myFunction() 
	{
      if (window.pageYOffset >= sticky) {
        navbar.classList.add("sticky")
      } else {
        navbar.classList.remove("sticky");
      }

	}

	// CSRF Token fir adddToCart Method
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	// Add To Cart Funcationality
	function addToCart(id) 
   {
      $.ajax({
         method: 'POST',
         url: '{{ route("front-end.addToCart") }}',
         data: 'id=' +id,
         dataType: 'JSON',
         success: function (response) {
            if (response.status === true) {
               location.href = '{{ route("front-end.cart") }}';
               
            } else {
               iziToast.error({
                  title: 'Product Exist',
                  position: 'bottomRight',
                  color: '#FF5733',
                  message: response['msg'],
               });
            }
         }
      });
   }


	// Add To Wishlist Funcationality
	function addToWishlist(productId) 
   {
      $.ajax({
         method: 'POST',
         url: '{{ route("addToWishlist") }}',
         data: 'productId=' +productId,
         dataType: 'JSON',
         success: function (response) {
            if (response.status === true) {
					$('.modal-title').html(response.productTitle);
					$('#wishlistModal .modal-body').html(response.msg);
					$('#wishlistModal').modal('show');
               
            } else {
               iziToast.error({
                  title: 'Product Exist',
                  position: 'bottomRight',
                  color: '#FF5733',
                  message: response['msg'],
               });
					location.href = '{{ route("account.login") }}';
            }
         }
      });
   }


</script>


	@yield('custom-js')

</body>
</html>