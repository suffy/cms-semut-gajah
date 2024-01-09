@extends('public.layout.member-layout')

@section('member-content')

<section class="panel" id="panel-user">
<header class="panel-heading">
    <b>User Detail</b>
</header>
<div class="panel-body">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                @if($user->photo=="")
                <img src="{{asset('user-photo.png')}}" class="img-fluid">
                @else
                <img src="{{asset($user->photo)}}" class="img-fluid">
                @endif
                <div class="status-user">
                    <hr>
                        Account Status :
                        @if($user->account_status=="1")
                            <i class="status status-success pull-right"><span class="fa fa-check"></span> Aktif</i>
                        @else
                            <i class="status status-danger pull-right"><span class="fa fa-close red"></span></i>
                        @endif
                        <br><br>

                    <hr>
                </div>
                <a href="javascript:void(0)" class="btn btn-primary btn-sm button-edit-user" data-toggle="modal" data-target="#editUser"><i class="glyphicon glyphicon-edit"></i> Edit User</a>

            </div>
            <div class="col-md-8">
                <h4>Informasi Member</h4>
                <div class="form-group">
                    <b>Nama :</b>
                    <p>{!! $user->name !!}</p>
                </div>
                <div class="form-group">
                    <b>Email :</b>
                    <p>{!! $user->email !!}</p>
                </div>
                <div class="form-group">
                    <b>Telephone :</b>
                    <p>{!! $user->phone!!}</p>
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

                <br><br>
            </div>

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
            <form id="updateUser" action="{{url('member/profile/'.$user->id)}}" method="post" enctype="multipart/form-data">
                    @method('put')
                    @csrf
                    <div class="form-group">
                        @if($user->photo=="")
                        <img class="img-thumbnail" id="photo" src="{{asset('user-photo.jpg')}}" style="margin: 10px 0; display: block">
                        @else
                        <img class="img-thumbnail" id="photo" src="{{asset($user->photo)}}" style=" max-width: 150px; margin: 10px 0; display: block">
                        @endif
                        <br>
                        <input type="file" class="form-control" name="photo">
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Name</label>
                        <div class="col-sm-12">
                            <input type="hidden" id="input-id-edit" name="id" value="" required>
                            <input type="text" id="input-name-edit" name="name" class="form-control" placeholder="Name" value="{{$user->name}}" required>
                            <input type="hidden" name="url" value="{{Request::url()}}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Phone</label>
                            <div class="col-sm-12">
                                <input type="number" id="input-phone-edit" name="phone" class="form-control" placeholder="Phone" value="{{$user->phone}}" required>
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
                            <input type="text" id="input-password-edit" name="password" class="form-control" placeholder="Password" value="">
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
                            <button type="submit" class="btn btn-primary">Update User</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /Modal content-->
    </div>
</div>

<style>
    #panel-user .status-user{
        font-size: 10pt;
    }

    #panel-user h4{
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
