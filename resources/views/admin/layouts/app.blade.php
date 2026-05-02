<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Laravel Shop :: Administrative Panel</title>

		<!-- Google Font -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

		<!-- Font Awesome -->
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">

		<!-- Bootstrap (comes with AdminLTE, no separate include needed) -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

		<!-- AdminLTE -->
		<link rel="stylesheet" href="{{ asset('admin-assets/css/adminlte.min.css') }}">

		<!-- Plugin CSS (after AdminLTE) -->
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins/dropzone/min/dropzone.min.css') }}">
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins/summernote/summernote.min.css') }}">
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins/select2/css/select2.min.css') }}">
		<link rel="stylesheet" href="{{ asset('admin-assets/css/datetimepicker.css') }}">

		<!-- Bootstrap Toggle (depends on Bootstrap CSS) -->
		<link rel="stylesheet" href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css">

		<!-- Custom CSS (ALWAYS LAST) -->
		<link rel="stylesheet" href="{{ asset('admin-assets/css/custom.css') }}">


		<meta name="csrf-token" content="{{ csrf_token() }}">
	</head>
	<body class="hold-transition sidebar-mini">
		<!-- Site wrapper -->
		<div class="wrapper">
			<!-- Navbar -->
			<nav class="main-header navbar navbar-expand navbar-white navbar-light">
				<!-- Right navbar links -->
				<ul class="navbar-nav">
					<li class="nav-item">
					  	<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
					</li>					
				</ul>
				<div class="navbar-nav pl-2">
					<ol class="breadcrumb p-0 m-0 bg-white">
						<li class="breadcrumb-item active">Dashboard</li>
					</ol>
				</div>
				
				<ul class="navbar-nav ml-auto">
					<li class="nav-item">
						<a class="nav-link" data-widget="fullscreen" href="#" role="button">
							<i class="fas fa-expand-arrows-alt"></i>
						</a>
					</li>
					<li class="nav-item dropdown">
						<a class="nav-link p-0 pr-3" data-toggle="dropdown" href="#">
							<img src="{{ asset('admin-assets/img/avatar5.png') }}" class='img-circle elevation-2' width="40" height="40" alt="">
						</a>
						<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-3">
							<h4 class="h4 mb-0"><strong>{{ Auth::guard('admin')->user()->name }}</strong></h4>

							<div class="mb-3">{{ Auth::guard('admin')->user()->email }}</div>

							<div class="dropdown-divider"></div>   

							<a href="#" class="dropdown-item">
								<i class="fas fa-user-cog mr-2"></i> Settings								
							</a>

							<div class="dropdown-divider"></div>

							<a href="{{ route('admin.chagePasswordForm') }}" class="dropdown-item">
								<i class="fas fa-lock mr-2"></i> Change Password
							</a>

							<div class="dropdown-divider"></div>
							
							<a href="{{ route('admin.logout') }}" class="dropdown-item text-danger">
								<i class="fas fa-sign-out-alt mr-2"></i> Logout							
							</a>							
						</div>
					</li>
				</ul>
			</nav>
			<!-- /.navbar -->
			<!-- Main Sidebar Container -->

         @include('admin.layouts.sidebar')
         
			<div class="content-wrapper">
				@yield('content')
			</div>

			<footer class="main-footer">
				<strong>Copyright &copy; 2014-2022 AmazingShop All rights reserved.
			</footer>
			
		</div>
		<!-- jQuery (MUST be first) -->
		<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>

		<!-- Bootstrap 4 -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

		<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

		<!-- AdminLTE -->
		<script src="{{ asset('admin-assets/js/adminlte.min.js') }}"></script>

		<!-- Plugins (depend on jQuery + Bootstrap) -->
		<script src="{{ asset('admin-assets/plugins/dropzone/min/dropzone.min.js') }}"></script>
		<script src="{{ asset('admin-assets/plugins/summernote/summernote.min.js') }}"></script>
		<script src="{{ asset('admin-assets/plugins/select2/js/select2.min.js') }}"></script>
		<script src="{{ asset('admin-assets/js/datetimepicker.js') }}"></script>

		<!-- Bootstrap Toggle (depends on Bootstrap + jQuery) -->
		<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

		<!-- SweetAlert2 (standalone, ok here) -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

		<!-- AdminLTE demo (OPTIONAL – remove in production) -->
		<script src="{{ asset('admin-assets/js/demo.js') }}"></script>


		<script>

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$('.summernote').summernote({
				height: '150px'
			});

		</script>

		@yield('custom-js')

	</body>
</html>