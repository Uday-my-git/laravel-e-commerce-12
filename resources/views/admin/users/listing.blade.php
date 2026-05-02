@extends('admin.layouts.app')

@section('content')
<!-- Toastr JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"></script>

<section class="content-header">					
   <div class="container-fluid my-2">
      <div class="row mb-2">
         <div class="col-sm-6">
            <h1>Add Users </h1>
         </div>
         <div class="col-sm-6 text-right">
            <a href="{{ route('users.create') }}" class="btn btn-primary">New User</a>
         </div>
      </div>
   </div>
</section>

<section class="content">
   <!---------------- View Modal Data ---------------->
   <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
         <div class="modal-content shadow">
            <div class="modal-header">
               <h5 class="modal-title">View User Info</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <div class="row g-3">
                  <div class="col-md-6">
                     <small class="text-muted d-block">Full Name</small>
                     <p class="fw-semibold" id="name"></p>
                  </div>
                  <div class="col-md-6">
                     <small class="text-muted d-block">Email Address</small>
                     <p class="fw-semibold" id="email"></p>
                  </div>
                  <div class="col-md-6">
                     <small class="text-muted d-block">Mobile Number</small>
                     <p class="fw-semibold" id="phone"></p>
                  </div>

                  <div class="col-md-6">
                     <small class="text-muted d-block">Google Id</small>
                     <p class="fw-semibold" id="google_id"></p>
                  </div>

                  <div class="col-md-6">
                     <small class="text-muted d-block">Facebook Id</small>
                     <p class="fw-semibold" id="facebook_id"></p>
                  </div>

                  <div class="col-md-6">
                     <small class="text-muted d-block">Github Id</small>
                     <p class="fw-semibold" id="github_id"></p>
                  </div>
                  <div class="col-md-6">
                     <small class="text-muted d-block">Role</small>
                     <span id="role">Admin</span>
                  </div>
                  <div class="col-md-6">
                     <small class="text-muted d-block">Status</small>
                     <span id="status"></span>
                  </div>

                  <div class="col-md-6">
                    <small class="text-muted d-block mb-1">Change  Status</small>
                     <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="active" value="1" checked>
                        <label class="form-check-label" for="active">Active</label>
                     </div>
                     <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="inactive" value="0">
                        <label class="form-check-label" for="inactive">Inactive</label>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <small class="text-muted d-block">Created At</small>
                     <p class="fw-semibold" id="created_at"></p>
                  </div>
                  <div class="col-md-6">
                     <small class="text-muted d-block">Updated At</small>
                     <p class="fw-semibold" id="updated_at"></p>
                  </div>
                   
               </div>
            </div>

            <div class="modal-footer">
               <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>

   <div class="container-fluid">
      @include('admin.message')

      <div class="card">
         <form action="" method="GET">
            <div class="card-header">
               <button type="button" class="btn btn-default" onclick="window.location.href='{{ route('users.listing') }}'">Reset</button>

               <div class="card-tools">
                  <div class="input-group input-group" style="width: 250px;">
                     <input type="text" name="search" class="form-control float-right" placeholder="Search" value="{{ Request::get('search') }}">
      
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
                     <th>Name</th>
                     <th>Email</th>
                     <th>Role</th>
                     <th>Status</th>
                     <th>Change Status</th>
                     <th width="100">Created At</th>
                     <th width="100">Updated At</th>
                     <th width="100">Action</th>
                  </tr>
               </thead>
               <tbody>
                  @if ($users->isNotEmpty())
                     @foreach ($users as $user)
                        <tr>
                           <td>{{ $user->id }}</td>
                           <td>{{ $user->name }}</td>
                           <td>{{ $user->email }}</td>
                           <td>
                              @if ($user->role == 1)
                                 <span class="badge bg-success">User</span>
                              @else
                                 <span class="badge bg-primary">Admin</span>
                              @endif
                           </td>
                           <td>
                              @if ($user->status == 1)
                                 <span class="badge bg-info">Active</span>
                              @else
                                 <span class="badge bg-danger">Deactive</span>
                              @endif
                           </td>
                           <td>
                              @if ($user->role == 2)
                                 <span class="badge bg-primary">Admin</span>
                              @else    
                                 <input data-id="{{ $user->id }}" class="toggle-class" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" {{ $user->status ? 'checked' : '' }}>
                              @endif
                           </td>
                           <td>{{ $user->created_at }}</td>
                           <td>{{ $user->updated_at }}</td>
                           <td>
                              <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                 @csrf
                                 @method('DELETE')

                                 <a href="javascript:void(0);" data-url="{{ route('users.data', $user->id) }}" class="btn btn-info" id="viewModalBtn">Show</a>


                                 {{-- <button type="button" class="btn btn-info" id="viewModalBtn" data-bs-toggle="modal" data-bs-target="#viewModal">View</button> --}}

                                 <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Edit</a>

                                 <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this row data ??')">Delete</button>
                              </form>
                           </td>
                        </tr>
                     @endforeach
                  @else
                     <tr><td style="text-align: center; color:black" colspan="10">Users Not Found</td></tr>
                  @endif
                 
               </tbody>
            </table>										
         </div>
         <div class="card-footer clearfix">
            {{ $users->links() }}
         </div>
      </div>
   </div>
</section>

@endsection

@section('custom-js')
<script>

   $(function () {

      $('body').on('click', '#viewModalBtn', function (e) {
         e.preventDefault();

         let newUrl = $(this).data('url');

         // let url = "{{ route('users.data', ":id") }}";
         // let newUrl = url.replace(':id', id)
         
         $.ajax({
            type: "GET",
            url: newUrl,
            dataType: "json",
            success: function(data){
               if (data.status === true) {
                  $('#name').html(data.userData.name);
                  $('#email').html(data.userData.email);
                  $('#phone').html(data.userData.phone);
                  $('#google_id').html(data.userData.google_id ?? 'Not Availabel');
                  $('#facebook_id').html(data.userData.facebook_id ?? 'Not Availabel');
                  $('#github_id').html(data.userData.github_id ?? 'Not Availabel');
                  $('#role').html(data.role);
                  $('#status').html(data.userStatus);
                  $('#created_at').html(data.userData.created_at);
                  $('#updated_at').html(data.userData.updated_at);

               } else {
                  iziToast.error({
                     title: 'Somethng wrond with !!',
                     message: data.msg,
                  });
               }
               $('#viewModal').modal('show');
            }
         });
      });

   });

   $('.toggle-class').change(function() {
      var status = $(this).prop('checked') == true ? 1 : 0; 
      var user_id = $(this).data('id'); 

      $.ajax({
         type: "GET",
         dataType: "json",
         url: "{{ route('users.changeStatus') }}",
         data: {'status': status, 'user_id': user_id},
         success: function(data){
            window.location.reload(true);
         }

      });

   })


</script>
@endsection