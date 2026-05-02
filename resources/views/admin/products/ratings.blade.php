@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>User Products Ratings</h1>
         </div>
         {{-- <div class="col-sm-6 text-right">
            <a href="{{ route('product.create') }}" class="btn btn-primary">New Product</a>
         </div> --}}
      </div>
   </div>
</section>

<section class="content">
   <div class="container-fluid">
      @include('admin.message')

      <div class="card">
         <form action="" name="search" method="GET">
            <div class="card-header">
               <div class="card-title">
                  <button type="button" class="btn btn-default" onclick="window.location.href='{{ route('product.productRatings') }}'">Reset</button>
               </div>
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
                     <th width="60">ID</th>
                     <th>Product Name</th>
                     <th width="60">Image</th>
                     <th>Username</th>
                     <th>Email</th>
                     <th>Comment</th>
                     <th>Rating</th>
                     <th width="100">Status</th>
                     <th width="100">Action</th>
                  </tr>
               </thead>
               <tbody>
                  @if ($ratings->isNotEmpty())
                     @foreach ($ratings as $rating)
                     @php
                        $productImages = $rating->product->mainImage->image;
                     @endphp
                        <tr>
                           <td>{{ $rating->id }}</td>
                           <td>{{ $rating->productTitle }}</td>
                           <td>
                              @if (!empty($productImages))
                                 <img src="{{ asset('uploads/product/small/' . $productImages) }}" alt="" width="60" height="60">
                              @else
                                 <img src="{{ asset('admin-assets/img/default-150x150.png') }} class="img-thumbnail>
                              @endif
                              
                           </td>
                           <td>{{ $rating->username }}</td>
                           <td>{{ $rating->email }}</td>
                           <td>{{ $rating->comment }}</td>
                           <td>{{ number_format($rating->rating, 2) }}</td>                    									
                           <td>
                              <input data-id="{{ $rating->id }}" class="toggle-class" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" {{ $rating->status ? 'checked' : '' }}>
                           </td>
                           <td>
                              <button type="button" class="btn btn-danger" onclick="deleteUserRatingFun({{ $rating->id }})">Delete</button>
                           </td>
                        </tr>
                     @endforeach 
                  @else
                     <tr><td style="text-align: center; color:rgb(221, 62, 62)" colspan="7">No Products Founds</td></tr>
                  @endif   
                
               </tbody>
            </table>										
         </div>
         <div class="card-footer clearfix">
            {{ $ratings->links() }}
         </div>

      </div>
   </div>
</section>
@endsection

@section('custom-js')
<script>

$(function () {
   
   $('.toggle-class').change(function () {
      let userId = $(this).data('id');
      let status = $(this).prop('checked') == true ? 1 : 0;
      
      $.ajax({
         type: 'get',
         url:'{{ route("product.changeStatustRating") }}',
         data: {'status': status, 'userId': userId},
         dataType: "json",
         success: function (response) {            
            if (response.status === true) {
               Swal.fire({
                  position: "top-end",
                  icon: "success",
                  title: response.msg,
                  showConfirmButton: false,
                  timer: 1500,
                  theme: 'dark'
               });
               setTimeout(() => {
                  location.reload(true);
               }, 2000);
            } else {
               location.reload(1);
            }
         }
      });
   });


});


function deleteUserRatingFun(id)
{
   if (confirm('Are You Sure To Delete Data ??')) {
      let url = '{{ route("product.deleteUserRating", "ID") }}';
      let newUrl = url.replace('ID', id);

      $.ajax({
         type: 'delete',
         url: newUrl,
         dataType: "json",
         success: function (response) {            
            if (response.status === true) {
               Swal.fire({
                  position: "top-end",
                  icon: "success",
                  title: response.msg,
                  showConfirmButton: false,
                  timer: 1500,
                  theme: 'dark'
               });
               setTimeout(() => {
                  location.reload(true);
               }, 2000);
            } else {
               location.reload(1);
            }
         }
      });
   }
}


</script>
@endsection

