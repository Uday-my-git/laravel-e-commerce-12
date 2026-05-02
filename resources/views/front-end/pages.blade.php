@extends('front-end.layouts.app')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.css">

<section class="section-5 pt-3 pb-3 mb-3 bg-white">
   <div class="container">
      <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
               <li class="breadcrumb-item"><a class="white-text" href="{{ route('front-end.home') }}">Home</a></li>
               <li class="breadcrumb-item">{{ $pages->name ?? '' }}</li>
            </ol>
      </div>
   </div>
</section>

@switch($pages->slug)
   @case('contact-us-pages')
      <section class=" section-10">
         <div class="container">
            <div class="section-title mt-5 ">
               <h2>{{ $pages->name ?? '' }}</h2>
            </div>   
         </div>
      </section>
      <section>
         <div class="container">  
            {{-- @if (Session::has('success'))
               <div class="alert alert-success">
                  {{ Session::get('success') }}
               </div>
            @endif

            @if (Session::has('error'))
               <div class="alert alert-danger">
                  {{ Session::get('error') }}
               </div>
            @endif  --}}

            <div class="row">
               <div class="col-md-6 mt-3 pe-lg-5">
                  {!! $pages->content ?? '' !!}
               </div>

               <div class="col-md-6">
                  <form action="" name="contact-form" class="shake" role="form" method="post" id="sendContacForm">
                     <div class="mb-3">
                        <label class="mb-2" for="name">Name</label>
                        <input type="text" name="name" class="form-control" id="name" data-error="Please enter your name" required>
                        <p class="help-block with-errors"></p>
                     </div>
                     
                     <div class="mb-3">
                        <label class="mb-2" for="email">Email</label>
                        <input type="email" name="email" class="form-control" id="email" data-error="Please enter your Email" required>
                        <p class="help-block with-errors"></p>
                     </div>
                     
                     <div class="mb-3">
                        <label class="mb-2">Subject</label>
                        <input type="text" name="subject" class="form-control" id="subject" data-error="Please enter your message subject">
                        <p class="help-block with-errors"></p>
                     </div>
                     
                     <div class="mb-3">
                        <label for="message" class="mb-2">Message</label>
                        <textarea name="message" class="form-control" rows="3" id="message" data-error="Write your message"></textarea>
                        <div class="help-block with-errors"></div>
                     </div>
                     
                     <div class="form-submit">
                        <button type="submit" class="btn btn-dark" id="form-submit">
                           <i class="material-icons mdi mdi-message-outline"></i> Send Message
                        </button>

                        <div id="msgSubmit" class="h3 text-center hidden"></div>
                        <div class="clearfix"></div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </section> 
      @break
   @default
      <section class=" section-10">
         <div class="container">
            <h1 class="my-3">{{ $pages->name ?? '' }}</h1>
            <p>{!! $pages->content ?? '' !!}</p>
         </div>
      </section>
@endswitch

{{-- @if ($pages->slug == 'contact-us-pages')
   <section class=" section-10">
      <div class="container">
         <div class="section-title mt-5 ">
            <h2>{{ $pages->name ?? '' }}</h2>
         </div>   
      </div>
   </section>
   <section>
      <div class="container">          
         <div class="row">
            <div class="col-md-6 mt-3 pe-lg-5">
               {!! $pages->content ?? '' !!}
            </div>

            <div class="col-md-6">
               <form action="" name="contact-form" class="shake" role="form" id="contactForm" method="post">
                  <div class="mb-3">
                     <label class="mb-2" for="name">Name</label>
                     <input type="text" name="name" class="form-control" id="name" required data-error="Please enter your name">
                     <div class="help-block with-errors"></div>
                  </div>
                  
                  <div class="mb-3">
                     <label class="mb-2" for="email">Email</label>
                     <input type="email" name="email" class="form-control" id="email" required data-error="Please enter your Email">
                     <div class="help-block with-errors"></div>
                  </div>
                  
                  <div class="mb-3">
                     <label class="mb-2">Subject</label>
                     <input type="text" name="subject" class="form-control" id="msg_subject" required data-error="Please enter your message subject">
                     <div class="help-block with-errors"></div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="message" class="mb-2">Message</label>
                     <textarea name="message" class="form-control" rows="3" id="message" required data-error="Write your message"></textarea>
                     <div class="help-block with-errors"></div>
                  </div>
                  
                  <div class="form-submit">
                     <button class="btn btn-dark" type="submit" id="form-submit">
                        <i class="material-icons mdi mdi-message-outline"></i> Send Message
                     </button>

                     <div id="msgSubmit" class="h3 text-center hidden"></div>
                     <div class="clearfix"></div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </section>
@else
   <section class=" section-10">
      <div class="container">
         <h1 class="my-3">{{ $pages->name ?? '' }}</h1>
         <p>{!! $pages->content ?? '' !!}</p>
      </div>
   </section>
@endif    --}}

@endsection

@section('custom-js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.js"></script>

<script>

$(document).ready(function () {

   $('#form-submit').click(function (e) {
      e.preventDefault();

      $('#form-submit').prop('disabled', true);
      $('#form-submit').html('please wait...');

      $.ajax({
         method: 'post', 
         url: '{{ route("front-end.sendContactForm") }}',
         data: $('#sendContacForm').serialize(),
         dataType: 'json',
         success: function (response) {
            $('#form-submit').prop('disabled', false);

            toastr.options = {
               "closeButton": false,
               "debug": false,
               "newestOnTop": false,
               "progressBar": true,
               "positionClass": "toast-top-full-width",
               "preventDuplicates": false,
               "onclick": null,
               "showDuration": "600",
               "hideDuration": "1000",
               "timeOut": "10000",
               "extendedTimeOut": "1000",
               "showEasing": "swing",
               "hideEasing": "linear",
               "showMethod": "fadeIn",
               "hideMethod": "fadeOut"
            }

            if (response['status']) {
               toastr.options.positionClass = "toast-top-right";
               toastr.success(response.msg);
               $('#sendContacForm')[0].reset();
              
            } else {
               let error = response['errors'];
               
               if (error) {
                  $.each(error, function (key, value) {
                     $(`#${key}`).addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(error.name);
                  });
                  
                  return false;
               }

               toastr.options.positionClass = "toast-top-full-width";
               toastr.error(response.msg);
            }
         }     
      });
   });


})


</script>
@endsection