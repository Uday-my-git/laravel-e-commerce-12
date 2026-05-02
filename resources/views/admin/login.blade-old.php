<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Laravel Shop :: Administrative Panel</title>
		<!-- Google Font: Source Sans Pro -->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
		<link rel="stylesheet" href="{{ asset('admin-assets/plugins//fontawesome-free/css/all.min.css') }}">
		<!-- Theme style -->
		<link rel="stylesheet" href="{{ asset('admin-assets/css/adminlte.min.css') }}">
		<link rel="stylesheet" href="{{ asset('admin-assets/css/custom.css') }}">

		<style>
			.a_tag {
				color: #920b0b;
			}
		</style>

	</head>
   <body class="hold-transition login-page">
		<div class="login-box">
			@include('admin.message')

			<div class="card card-outline card-primary">
			  	<div class="card-header text-center">
					<p class="h3">Administrative Panel</p>
			  	</div>
			  	<div class="card-body">

					<form action="{{ route('admin.authenticate') }}" name="loginForm" method="post">
						@csrf
				  		<div class="input-group mb-3">
							<input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Email">
							<div class="input-group-append">
					  			<div class="input-group-text">
									<span class="fas fa-envelope"></span>
					  			</div>
							</div>
							@error('email')
								<p class="invalid-feedback">{{ $message }}</p>
							@enderror
				  		</div>
				  		<div class="input-group mb-3">
							<input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password">
							<div class="input-group-append">
					  			<div class="input-group-text">
									<span class="fas fa-lock"></span>
					  			</div>
							</div>
							@error('password')
								<p class="invalid-feedback">{{ $message }}</p>
							@enderror
				  		</div>
				  		<div class="row">
							<!-- <div class="col-8">
					  			<div class="icheck-primary">
									<input type="checkbox" id="remember">
									<label for="remember">
						  				Remember Me
									</label>
					  			</div>
							</div> -->
							<!-- /.col -->
							<div class="col-4">
					  			<button type="submit" class="btn btn-primary btn-block">Login</button>
							</div>
				  		</div>
						<p class="mb-1 mt-3">
							<a href="forgot-password.html" class="a_tag">I forgot my password</a> &nbsp|
							<a href="{{ route('admin.newRegister') }}	">New Register Here</a>
						</p>					
					</form>
					{{-- Login with social media --}}
					<div class="mt-4 space-y-2">
						<a class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25" href="#">
							GitHub
						</a>
					
						<a class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25" href="#">
							Google
						</a>
					
						<a class="inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25" href="#">
							Facebook
						</a>
					</div>
			  	</div>
			</div>
		</div>
		
		<script src="{{ asset('admin-assets/plugins/jquery/jquery.min.js') }}"></script>
		<script src="{{ asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
		<script src="{{ asset('admin-assets/js/adminlte.min.js') }}"></script>
	</body>
</html>