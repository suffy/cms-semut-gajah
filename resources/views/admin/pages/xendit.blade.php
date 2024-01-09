@extends('admin.layout.template')

@section('content')

@php
    $account_role = Auth()->user()->account_role;
@endphp

<section class="panel">
    <header class="panel-heading">
        <b>Xendit</b>
    </header>
    <div class="card-body">
        <a href="javascript:void(0)" class="btn btn-blue mt-5" data-toggle="modal" data-target="#newXendit" ><span class="fa fa-plus"></span> Create New</a>
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
                                <td>Xendit Business Name</td>
                                <td>Xendit Account Created</td>
                            </tr>
                            </thead>
                            <tbody>
                            {{-- @php $no=1;@endphp --}}
                            @foreach($usersXendit as $user)
                                <tr>
                                    <td class="text-center">{{ $user->site_name->kode }} | {{ $user->site_name->branch_name }} | {{ $user->site_name->nama_comp }}</td>
                                    <td class="text-center">{{ $user->email }}</td>
                                    <td class="text-center">{{ $user->xendit_business_name }}</td>
                                    <td class="text-center">{{ $user->xendit_created }}</td>
                                </tr>
                                {{-- @php $no=$no+1;@endphp --}}
                            @endforeach
                            </tbody>
                        </table>

                        {{$usersXendit->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL START --}}
    <div id="newXendit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">User Xendit Baru</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url($account_role . '/xendit')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <span class="text-medium">Harap pastikan bahwa email adalah email asli!<br>Data yang sudah terinput tidak bisa diubah maupun dihapus</span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('mapping_site') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Site</label>
                            <div class="col-sm-12">
                                <input type="hidden" id="user-id-mapping-site">
                                <select name="mapping_site" id="mapping-site" class="form-control" required></select>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('mapping_site') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('business_name') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Business Name</label>
                            <div class="col-sm-12">
                                <input type="text" id="business_name" name="business_name" class="form-control" placeholder="Business Name" value="{{old('business_name')}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('business_name') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Email</label>
                            <div class="col-sm-12">
                                <input type="email" id="email" name="email" class="form-control" placeholder="Email" value="{{old('email')}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('email') }} </span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-blue" onclick="confirmXenditModal()">Save</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    
    <div id="confirmXendit" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Confirm User Xendit</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url($account_role . '/xendit')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <span class="text-medium">Pastikan bahwa data dibawah sudah benar</span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('mapping_site') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label" id="xendit_site_label">Site Code</label>
                            <input type="hidden" id="xendit_site" name="xendit_site">
                            <label class="col-sm-12 col-form-label" id="xendit_business_name_label">Business Name</label>
                            <input type="hidden" id="xendit_business_name" name="xendit_business_name">
                            <label class="col-sm-12 col-form-label" id="xendit_email_label">Email</label>
                            <input type="hidden" id="xendit_email" name="xendit_email">
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

</section>

<script>
    $(document).ready(function () {
        $('#mapping-site').select2({
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: 'customers/all-mapping-site-xendit',
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.site_code + ' - ' + item.site_name.branch_name,
                                id: item.site_code
                            }
                        })
                    };
                },
                cache: true
            }
        });
    });
    
    function confirmXenditModal() {
        $('#newXendit').modal('hide');
        var site_code = $('#mapping-site').val();
        var business_name = $('#business_name').val();
        var email = $('#email').val();

        $('#mapping-site').empty();
        $('#business_name').val('');
        $('#email').val('');

        $('#xendit_site_label').text(site_code);
        $('#xendit_site').val(site_code);
        $('#xendit_business_name_label').text(business_name);
        $('#xendit_business_name').val(business_name);
        $('#xendit_email_label').text(email);
        $('#xendit_email').val(email);
        $('#confirmXendit').modal('show');

        console.log(site_code + ' ' + business_name + ' ' + email);
    }
</script>

<style>
    .text-small{
        font-size: 8pt;
        color: red;
    }
    .text-medium{
        font-size: 12pt;
        font-weight: bold;
        color: red;
    }
</style>
@stop
