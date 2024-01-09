@extends('admin.layout.template')

@section('content')

@php
    $account_role = Auth()->user()->account_role;
@endphp

<section class="panel">
    <header class="panel-heading">
        <b>Access</b>
    </header>
    <div class="card-body">
        <form action="{{ url($account_role . '/access/update') }}" method="POST">
            @csrf
            @method('put')
            <label for="name">Name</label>
            <div class="form-group row">
                <div class="col-md-4">
                    <select class="form-control" name="user_id" id="name">
                        @foreach ($usersAccess as $userAccess)
                            <option value="{{ $userAccess->id }}">{{ $userAccess->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <label for="account-role">Jabatan</label>
            <div class="form-group row">
                <div class="col-md-4">
                    <select class="form-control" name="account_role" id="account-role">
                        <option value="manager">Manager</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="admin">Admin</option>
                        <option value="distributor">Distributor</option>
                    </select>
                </div>
            </div>
            {{-- <div class="form-group row">
                <div class="col-md-4">
                    <label class="checkbox-inline"><input type="checkbox" value="laporan"> Laporan</label>
                    <label class="checkbox-inline ml-5"><input type="checkbox" value="hak akses"> Hak Akses</label>
                    <label class="checkbox-inline"><input type="checkbox" value="custom order"> Custom Order</label>
                    <label class="checkbox-inline ml-5"><input type="checkbox" value="purchasing"> Purchasing</label>
                </div>
            </div> --}}
            <button type="submit" class="btn btn-primary">Update</button>
        </form>

        <a href="javascript:void(0)" class="btn btn-blue mt-5" data-toggle="modal" data-target="#newUser" ><span class="fa fa-plus"></span> Create New</a>
        <a 
            href="
                @if(auth()->user()->account_role == 'manager')
                    {{url('manager/access-import')}}
                @elseif(auth()->user()->account_role == 'superadmin')
                    {{url('superadmin/access-import')}}
                @elseif(auth()->user()->account_role == 'admin')
                    {{url('admin/access-import')}}
                @endif
            " 
            class="btn btn-blue mt-5"
        >Import User</a>
        <br>

        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-outer table-responsive">
                    <div class="table-inner">
                        <table class="table default-table dataTable">
                            <thead>
                            <tr  align="center">
                                <td>Name</td>
                                <td>Email</td>
                                <td>Account Role</td>
                                <td>Site Name</td>
                                <td>Action</td>
                            </tr>
                            </thead>
                            <tbody>
                            {{-- @php $no=1;@endphp --}}
                            @foreach($users as $user)
                                <tr>
                                    <td class="text-center">{{ $user->name }}</td>
                                    <td class="text-center">{{ $user->email }}</td>
                                    <td class="text-center">{{ $user->account_role }}</td>
                                    <td class="align-middle"><span id="label-mapping-site-{{$user->id}}">@if($user->site_name != null){{ $user->site_name->branch_name }}@endif
                                        </span><a href="javascript:void(0)" class="pull-right" data-id="{{$user->id}}" data-toggle="modal" data-target="#mappingSiteModal" onclick="setIdMappingSite('{{$user->id}}')">Edit <span class="fa fa-edit"></span></a>
                                    </td>
                                    <td width="50px">
                                        <a 
                                            href="
                                                @if(auth()->user()->account_role == 'manager')
                                                    {{url('manager/access-detail/' . $user->id)}}
                                                @elseif(auth()->user()->account_role == 'superadmin')
                                                    {{url('superadmin/access-detail/' . $user->id)}}
                                                @elseif(auth()->user()->account_role = 'admin')
                                                    {{url('admin/access-detail/' . $user->id)}}
                                                @endif
                                            " 
                                            data-id="" 
                                            data-name="" 
                                            data-email="" 
                                            class="btn btn-green btn-sm button-edit-user"
                                        >Detail</a>
                                    </td>
                                </tr>
                                {{-- @php $no=$no+1;@endphp --}}
                            @endforeach
                            </tbody>
                        </table>

                        {{$users->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL START --}}
    <div id="newUser" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">New User</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url($account_role . '/access')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control" placeholder="Name" value="{{old('name')}}" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('name') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Email</label>
                            <div class="col-sm-12">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="{{old('email')}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('email') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Account Role</label>
                            <div class="col-sm-12">
                                <select name="account_role" id="account-role" class="form-control">
                                    <option value="manager" {{ old('account_role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                    <option value="superadmin" {{ old('account_role') == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                                    <option value="admin" {{ old('account_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="distributor" {{ old('account_role') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Password</label>
                            <div class="col-sm-12">
                                <input type="password" name="password" class="form-control" placeholder="Password" value="{{old('password')}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('password') }} </span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div id="mappingSiteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Mapping Site</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user-id-mapping-site">
                    <select name="mapping_site" id="mapping-site" class="form-control" required></select>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-blue btn-update-mapping-site">Save</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    function setIdMappingSite(user_id) {
        $('#user-id-mapping-site').val(user_id);
    }

    @if (count($errors) > 0)
        $('#newUser').modal('show');
    @endif

    $(document).ready(function () {
        $('#mapping-site').select2({
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: 'customers/all-mapping-site',
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.branch_name +' - '+ item.nama_comp,
                                id: item.kode
                            }
                        })
                    };
                },
                cache: true
            }
        });
    });

    // mapping site
    $('.btn-update-mapping-site').on('click', function(){
        var user_id = $('#user-id-mapping-site').val();
        var mapping_site = $('#mapping-site').val()

        $.ajax({
            url: @if(auth()->user()->account_role == 'manager')
                        "{{url('manager/access/update-mapping-site')}}"
                    @else 
                        "{{url('superadmin/access/update-mapping-site')}}"
                @endif,
            mimeType: "multipart/form-data",
            type: "POST",
            data: {
                mapping_site: mapping_site,
                user_id: user_id,
                _token: '{{csrf_token()}}'
            },
            dataType: "json",
            success: function(data){
                console.log(data)
                $('#label-mapping-site-'+user_id).html(data.data.site.branch_name);
                showNotif("Mapping Site updated");
                // $('#form-salesman-erp-'+customerCode).toggle();
                $('#mappingSiteModal').modal('hide');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });

    })
</script>
<style>
    .text-small{
        font-size: 8pt;
        color: red;
    }
</style>
@stop
