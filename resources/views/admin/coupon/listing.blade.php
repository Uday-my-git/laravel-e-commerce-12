@extends('admin.layouts.app')

@section('content')

<!-- Toastr JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>

<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Coupon Code</h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('coupon.create') }}" class="btn btn-primary">New Coupon Code</a>
         </div>
      </div>
   </div>
</section>

@if (session('success'))
   <script>
      iziToast.success({
         title: 'Success',
         position: 'topCenter',
         message: "{{ session('success') }}",
      });
   </script>   
@endif

@if (session('error'))
   <script>
      iziToast.error({
         title: 'Error',
         message: "{{ session('error') }}",
      });
   </script>   
@endif

<!-- Main content -->
<section class="content">
   <div class="container-fluid">
      {{-- @include('admin.message') --}}

      <div class="card">
         <form action="" method="GET">
            <div class="card-header">
               <button type="button" class="btn btn-default" onclick="window.location.href='{{ route('coupon.listing') }}'">Reset</button>
               &nbsp;
               <button class="btn btn-danger delete_all d-none" id="delete-all">Delete All Selected</button>
               
               <div class="card-tools">
                  <div class="input-group input-group" style="width: 250px;">
                     <input type="text" name="search" class="form-control float-right" value="{{ Request::get('search') }}" placeholder="Search">
      
                     <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                           <i class="fas fa-search"></i>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </form>
         <div class="card-body table-responsive p-0">								
            <table class="table table-hover text-nowrap">
               <thead>
                  <tr>
                     <th width="50px"><input type="checkbox" id="select_all_btn"></th>
                     <th width="60">ID</th>
                     <th>Coupon Code</th>
                     <th>Name</th>
                     <th>Max Usese</th>
                     <th>Max Uses User</th>
                     <th>Type</th>
                     <th>Min Amount</th>
                     <th>Status</th>
                     <th>Starts At</th>
                     <th>Expires At</th>
                     <th width="100">Action</th>
                  </tr>
               </thead>
               <tbody>
                  @if ($couponCode->isNotEmpty())
                     @foreach ($couponCode as $discountCode)
                        <tr id="tr_{{ $discountCode->id }}">

                           <td><input type="checkbox" class="sub_chk" data-id="{{ $discountCode->id }}"></td>
                           <td>{{ $discountCode->id }}</td>
                           <td>{{ $discountCode->coupon_code }}</td>
                           <td>{{ $discountCode->name }}</td>
                           <td>{{ $discountCode->max_uses }}</td>
                           <td>{{ $discountCode->max_uses_user }}</td>
                           <td>
                              @if ($discountCode->type == 'fixed')
                                 R.s {{ $discountCode->discount_amount }}
                              @else
                                 {{ $discountCode->discount_amount }}%
                              @endif
                           </td>
                           <td>R.s {{ $discountCode->min_amount }}</td>
                           <td>
                              @if ($discountCode->status == 1)
                                 <svg class="text-success-500 h-6 w-6 text-success" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                 </svg>
                              @else
                                 <svg class="text-danger h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                 </svg>
                              @endif   
                           </td>
                           <td>{{ (!empty($discountCode->starts_at)) ? \Carbon\Carbon::parse($discountCode->starts_at)->format('d/m/Y H:i:s') : '' }}</td>
                           <td>{{ (!empty($discountCode->expires_at)) ? \Carbon\Carbon::parse($discountCode->expires_at)->format('d/m/Y H:i:s') : '' }}</td>
                           <td>
                              <a href="{{ route('coupon.edit', $discountCode->id) }}">
                                 <svg class="filament-link-icon w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                 </svg>
                              </a>
                              <a href="javascript:void(0)" class="text-danger w-4 h-4 mr-1" onclick="deleteCouponFun('{{ $discountCode->id }}')">
                                 <svg wire:loading.remove.delay="" wire:target="" class="filament-link-icon w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path	ath fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                 </svg>
                              </a>
                           </td>
                        </tr>  
                     @endforeach 
                  @else
                     <tr><td style="text-align: center; color:rgb(221, 62, 62)" colspan="10">No Coupon Code Availabel</td></tr>
                  @endif    
               </tbody>
            </table>										
         </div>
         <div class="card-footer clearfix">
            {{ $couponCode->links() }}
         </div>
      </div>
   </div>
</section>
@endsection

@section('custom-js')
<script>

   $(document).ready(function () {

      // Select all checkboxes
      $('#select_all_btn').on('click', function (e) {
         if ($(this).is(':checked', true)) {
            $('.sub_chk').prop('checked', true);
            $('#delete-all').removeClass('d-none');
         } else {
            $('.sub_chk').prop('checked', false);
            $('#delete-all').addClass('d-none');
         }
      });   


      //  Check or uncheck "select all", if one of the listed checkbox items is checked/unchecked
      $('.sub_chk').on('click', function () {
         if ($('.sub_chk:checked').length == $('.sub_chk').length) {
            $('#select_all_btn').prop('checked',true);
         } else {
            $('#select_all_btn').prop('checked',false);
         }
         
         // Show/hide Delete button when single checkbox are selected or not
         if ($('.sub_chk:checked').length > 0) {
            $('#delete-all').removeClass('d-none');
         } else {
            $('#delete-all').addClass('d-none');
         }
      });


      $('.delete_all').on('click', function (e) {
         var allValues = [];
         
         $('.sub_chk:checked').each(function () {
            allValues.push($(this).attr('data-id'));
         });

         if (allValues.length > 0) {
            if (confirm('Are you sure you want to delete this row?')) {
               var join_selected_values = allValues.join(',');
            
               $.ajax({
                  type: 'DELETE',
                  url: '{{ route("coupon.deleteAll") }}',
                  data: 'ids=' + join_selected_values,
                  success: function (response) {
                     if (response['status']) {
                        $('.sub_chk:checked').each(function () {
                           $(this).parents('tr').remove();
                        });
                     }
                  }
               });
            } 
         } else {
            iziToast.error({
               title: 'Error',
               message: "Please select at least one row. ??",
            });
         }
      });

   });

   function deleteCouponFun(id) 
   {
      if (confirm('Are You Sure To Delete ??')) {
         let url = '{{ route("coupon.destroy", "ID") }}';
         let newUrl = url.replace('ID', id);

         $.ajax({
            method: 'DELETE',
            url: newUrl,
            success: function(data) {
               if (data.status) { 
                  window.location.href = '{{ route("coupon.listing") }}';
               } 
            },
            error: function (jqXHR, exception) {
               iziToast.error({
                  title: 'Error',
                  message: "Data not get in success function ??",
               });
            }
         });
      }
    }


</script>
@endsection