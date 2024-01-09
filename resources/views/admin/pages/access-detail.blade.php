@extends('admin.layout.template')
@section('content')

@if(auth()->user()->account_role == 'manager')
<a href="/manager/access" class="btn btn-default">Kembali</a>
@elseif(auth()->user()->account_role == 'superadmin')
<a href="/superadmin/access" class="btn btn-default">Kembali</a>
@endif

<br><br>
<section class="panel">


    <header class="panel-heading">
        <b>User Detail</b>
    </header>
    <div class="panel-body" id="panel-user">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    @if($user->photo=="")
                    <img src="{{asset('images/no-photo.jpg')}}" class="img-fluid">
                    @else
                    <img src="{{asset($user->photo)}}" class="img-fluid">
                    @endif
                    <hr>

                    <br>

                    <a href="javascript:void(0)" class="btn btn-blue btn-sm button-edit-user" data-toggle="modal" data-target="#editUser"><i class="glyphicon glyphicon-edit"></i> Edit User</a>
       
                    <form action="@if(auth()->user()->account_role == 'manager'){{ url('manager/access/'.$user->id) }}@else{{ url('superadmin/access/'.$user->id) }}@endif" method="POST"
                        style="display: inline-block;">
                        @method('delete')
                        <input type="hidden" name="url" value="{{url('superadmin/access')}}" required>
                        @csrf
                        <button type="submit" class="btn btn-red btn-sm" value="Delete"
                            onclick="return confirm('Delete Data?')"><i
                                class="fa fa-trash"></i> Hapus</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <h3>Informasi User</h3>
                    <div class="form-group">
                        <b>Nama :</b>
                        <p>{!! $user->name !!}</p>
                    </div>
                    <div class="form-group">
                        <b>Email :</b>
                        <p>{!! $user->email !!}</p>
                    </div>
                    <div class="form-group">
                        <b>Account Role :</b>
                        <p>{!! $user->account_role!!}</p>
                    </div>
                    <div class="form-group">
                        <b>Last Login :</b>
                        @if($user->last_login==null)
                            New Account
                        @else
                        <p>{{date('d F Y - H:i:s', strtotime($user->last_login)) }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <b>Registered :</b>
                        <p>{{date('d F Y - H:i:s', strtotime($user->created_at)) }}</p>
                    </div>
            </div>
            <div class="col-md-4">
            </div>
        </div>
    </div>
</section>


<div id="editUser" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit User</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
            <form id="updateUser" action="@if(auth()->user()->account_role == 'manager'){{url('manager/access/'.$user->id)}}@else{{url('superadmin/access/'.$user->id)}}@endif" method="post" enctype="multipart/form-data">
                    @method('put')
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Name</label>
                        <div class="col-sm-12">
                            <input type="hidden" id="input-id-edit" name="id" value="{{$user->id}}" required>
                            <input type="text" id="input-name-edit" name="name" class="form-control" placeholder="Name" value="{{$user->name}}" required>
                            <input type="hidden" name="url" value="{{Request::url()}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Email</label>
                        <div class="col-sm-12">
                        <input type="email" id="input-email-edit" name="email" class="form-control" placeholder="Email" value="{{$user->email}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Password</label>
                        <div class="col-sm-8">
                            <input type="text" id="input-password-edit" name="password" class="form-control"
                                placeholder="Password" value="">
                            *Kosongkan jika tidak diganti
                        </div>
                        <div class="col-sm-2">
                            <div id="loader-status-edit">

                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-default">Update User</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /Modal content-->
    </div>
</div>

<script>
    $(function() {
      $('#acc_status').change(function() {
          var account_status = $(this).prop('checked') == true ? 1 : 0;
          var user_id = $(this).data('id');

          $.ajax({
              type: "GET",
              dataType: "json",
              url: '/admin/change-status',
              data: {'account_status': account_status, 'user_id': user_id},
              success: function(data){
                console.log(data.success)
              }
          });
      })

      $('#owner_status').change(function() {
          var verified_as_owner = $(this).prop('checked') == true ? 1 : 0;
          var user_id = $(this).data('id');

          $.ajax({
              type: "GET",
              dataType: "json",
              url: '/admin/change-status',
              data: {'verified_as_owner': verified_as_owner, 'user_id': user_id},
              success: function(data){
                console.log(data.success)
              }
          });
      })

      $('#renter_status').change(function() {
          var verified_as_renter = $(this).prop('checked') == true ? 1 : 0;
          var user_id = $(this).data('id');

          $.ajax({
              type: "GET",
              dataType: "json",
              url: '/admin/change-status',
              data: {'verified_as_renter': verified_as_renter, 'user_id': user_id},
              success: function(data){
                console.log(data.success)
              }
          });
      })
    })
</script>

{{-- <script>
    $(document).ready(function () {
      var switchStatus = false;
      $("#acc_status").on('change', function() {
          var account_status = $(this).prop('checked') == true ? 1 : 0;
          var user_id = $(this).data('id');

          $.ajax({
              type: "GET",
              dataType: "json",
              url: '/admin/change-status',
              data: {'account_status': account_status, 'user_id': user_id},
              success: function(data){
                console.log(data.success)
              }
          });
      });
    });
</script> --}}



<style>
    /* SWITCH  */
    /* The switch - the box around the slider */
    .switch {
        position: relative;
        width: 60px;
        height: 34px;
        float: right;
    }

    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #007bff;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #007bff;
    }

    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    /* Radio button image  */
    .input-hidden {
        position: absolute;
        left: -9999px;
    }

    input[type="radio"]:checked + label > img,
    input[type="radio"] + label > img:hover {
        border: 1px solid #fff;
        box-shadow: 0 0 3px 3px #007bff;
    }

    /* Stuff after this is only to make things more pretty */
    input[type="radio"] + label > img {
        border: 1px solid #abced4;
        width: 150px;
        height: 150px;
        transition: 500ms all;
        cursor: pointer;
    }

    #panel-user h3{
        color: #000000;
        font-size: 11pt;
        font-weight: 600;
        background: #f1f1f1;
        padding: 10px;
        padding-left: 15px;
        padding-right: 15px;
    }

    .form-group b{
        font-size: 10pt;
        color: #999999;
        font-weight: 100;
    }
    .form-group p{
        color: #000000;
    }
</style>

@endsection
