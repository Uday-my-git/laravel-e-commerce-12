@if (Session::has('error'))   
   <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <strong>{{ Session::get('error') }}</strong> 

      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   </div>
@endif


@if (Session::has('success'))  
   <div class="alert alert-primary d-flex align-items-center" role="alert">
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:hre="#info-fill"/></svg>

      <div>
         {{ Session::get('success') }}
      </div>
   </div>
@endif