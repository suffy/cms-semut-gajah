@extends('admin.layout.template')

@section('content')

    <section class="panel">
        <header class="panel-heading">
            User
        </header>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            <a href="#" data-toggle="modal" data-target="#newUser" class="btn btn-blue">New User</a><br><br>
                            <table class="table default-table dataTable">
                                <thead>
                                <tr  align="center">
                                    <td>No</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Created At</td>
                                    <td>Last Login At</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>
                                @php $no=1;@endphp
                                @foreach($user as $u)
                                    <tr>
                                        <td width="50px">{{$no}}</td>
                                        <td>{{$u->name}}</td>
                                        <td>{{$u->email}}</td>
                                        <td>{{ Carbon\Carbon::parse($u->created_at)->formatLocalized('%A, %d %B %Y %H:%I:%S')}}</td>
                                        <td>@if(isset($u->last_login)){{ Carbon\Carbon::parse($u->last_login)->formatLocalized('%A, %d %B %Y %H:%I:%S')}}@else new account @endif</td>
                                        <td width="150px">
                                            <a href="javascript:void(0)" data-id="{{$u->id}}" data-name="{{$u->name}}" data-email="{{$u->email}}" class="btn btn-green btn-sm button-edit-user" data-toggle="modal" data-target="#editUser">Edit</a>
                                            <a href="{{url('admin/delete-user/'.$u->id)}}" class="btn btn-red btn-sm" onclick="return confirm('Delete Data?')">Delete</a>
                                        </td>
                                    </tr>
                                    @php $no=$no+1;@endphp
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </section>


    <!-- Modal -->
    <div id="newUser" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New User</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url('admin/store-user')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Email</label>
                            <div class="col-sm-12">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Password</label>
                            <div class="col-sm-12">
                                <input type="password" name="password" class="form-control" placeholder="Password" value="" required>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save User</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>


    <div id="editUser" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Farmer</h4>
                </div>
                <div class="modal-body">
                    <form action="{{url('admin/update-user')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="hidden" id="input-id-edit" name="id" value="" required>
                                <input type="text" id="input-name-edit" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Email</label>
                            <div class="col-sm-12">
                                <input type="email" id="input-email-edit" name="email" class="form-control" placeholder="Email" value="" required>
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
                                <button type="submit" class="btn btn-blue">Update User</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <style>
        .default-table pre{
            font-family: sans-serif;
            line-height: 18pt;
            background: #fff;
            border: 1px solid #f9f9f9;

        }

        .table-outer{
            overflow: auto;
        }

        .table-inner{
            width: 100%;
        }
    </style>


    <script>
        $('.button-edit-user').on('click', function(){
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var email = $(this).attr('data-email');


            $('#input-id-edit').val(id);
            $('#input-name-edit').val(name);
            $('#input-email-edit').val(email);
        });

    </script>


    

@stop
