@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newVersion">Tambah Versi</a>
<br>
<br>
<section class="panel" id="listPage">
    <header class="panel-heading">
        App Version
    </header>
    <div class="card-body">
        <div class="table-responsive">
            <div class="scroll-table-outer">
                <div class="scroll-table-inner card-body">
                    <table class="table default-table dataTable">
                        <thead>
                            <tr align="center">
                                <td style="display:none;"></td>
                                <td>App Version</td>
                                <td>Description</td>
                                <td>Require Update</td>
                                <td>Optional Update</td>
                                <td>Maintenance</td>
                                <td width="75px">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($appVersions as $row)
                            <tr align="center">
                                <td style="display:none;">{{ $loop->iteration }}</td>
                                <td class="align-middle">{{$row->version}}</td>
                                <td class="align-middle text-left">
                                    {!! $row->description !!}
                                </td>
                                <td class="align-middle">
                                    <label class="switch">
                                        <input data-id="{{$row->id}}" class="toggle-class success slider-require" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ ($row->require_update == 'true') ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="align-middle">
                                    <label class="switch">
                                        <input data-id="{{$row->id}}" class="toggle-class success slider-optional" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ ($row->optional_update == 'true') ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="align-middle">
                                    <label class="switch">
                                        <input data-id="{{$row->id}}" class="toggle-class success slider-maintenance" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ ($row->maintenance == 'true') ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="javascript:void(0)" 
                                        class="btn btn-green btn-sm btn-edit-version" data-toggle="modal" 
                                        data-target="#editVersion"
                                        data-id="{{$row->id}}"
                                        data-version="{{$row->version}}"
                                        data-description="{{$row->description}}">Edit</a>
                                    <form method="post" enctype="multipart/form-data" action="{{url('superadmin/app-version/delete')}}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$row->id}}">
                                        <button type="submit" class="btn btn-sm btn-red" onclick="return confirm('Are you sure?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
                {{ $appVersions->appends(Request::all())->links() }}
            </div>
        </div>
    </div>
</section>

{{-- MODAL START --}}
<div id="newVersion" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-name">New App Version</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{url('superadmin/app-version/store')}}" method="post" enctype="multipart/form-data">
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">App Version Name</label>
                        <div class="col-sm-12">
                            @csrf
                            <input type="text" name="version" class="form-control" placeholder="Version" value="" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Description</label>
                        <div class="col-sm-12">
                            <textarea class="form-control" id="description" name="description" style="height: 300px;"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Save App Version</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="editVersion" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-name">Edit App Version</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{url('superadmin/app-version/update')}}" method="post" enctype="multipart/form-data">
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">App Version Name</label>
                        <div class="col-sm-12">
                            @csrf
                            <input type="hidden" id="edit-app-version-id" name="edit_app_version_id" class="form-control" required>
                            <input type="text" id="edit-version" name="edit_version" class="form-control" placeholder="Version" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Description</label>
                        <div class="col-sm-12">
                            <textarea class="form-control" id="edit-description" name="edit_description" style="height: 300px;"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Update App Version</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('.slider-require').change(function(){
        var require_update = $(this).prop('checked') == true ? 1 : 0;
        var app_version_id = $(this).data('id');

        $.ajax({
            type: "GET",
            dataType: "json",
            url: '/superadmin/app-version/require-update/'+app_version_id,
            data: {'require_update': require_update, 'app_version_id': app_version_id},
            success: function(data){
                console.log(data)
                showNotif("Perubahan status require update sukses")
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });
    });

    $('.slider-optional').change(function(){
        var optional_update = $(this).prop('checked') == true ? 1 : 0;
        var app_version_id = $(this).data('id');

        $.ajax({
            type: "GET",
            dataType: "json",
            url: '/superadmin/app-version/optional-update/'+app_version_id,
            data: {'optional_update': optional_update, 'app_version_id': app_version_id},
            success: function(data){
                console.log(data)
                showNotif("Perubahan status optional update sukses")
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });
    });

    $('.slider-maintenance').change(function(){
        var maintenance = $(this).prop('checked') == true ? 1 : 0;
        var app_version_id = $(this).data('id');

        $.ajax({
            type: "GET",
            dataType: "json",
            url: '/superadmin/app-version/maintenance/'+app_version_id,
            data: {'maintenance': maintenance, 'app_version_id': app_version_id},
            success: function(data){
                console.log(data)
                showNotif("Perubahan status maintenance sukses")
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });
    });
    
    $('.btn-edit-version').on('click', function(){
        var app_version_id = $(this).attr('data-id');
        var version = $(this).attr('data-version');
        var description = $(this).attr('data-description');

        $('#edit-app-version-id').val(app_version_id);
        $('#edit-version').val(version);
        tinymce.get('edit-description').setContent(description);
    })
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.0.6/jquery.tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#description'
    });

    tinymce.init({
        selector: '#edit-description'
    });
</script>
<style>
    .image-outer{
        width: 150px;
        height: 150px;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        border: 1px solid #f1f1f1;
        padding: 5px;
    }

    .image-outer img{
        position: absolute;
        width: 150px;
        height: auto;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
    }
</style>
@stop