@extends('admin.layout.template')

@section('content')
<header class="panel-heading">
    Detail User Register
</header>
<input type="text" id="role" value="{{auth()->user()->account_role}}" hidden>
<input type="text" id="user_id" value="{{$user->id}}" hidden>
<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <a href="
                        @if(auth()->user()->account_role == 'manager')
                            {{url('manager/customers/approval')}}
                        @elseif(auth()->user()->account_role == 'superadmin')
                            {{url('superadmin/customers/approval')}}
                        @elseif(auth()->user()->account_role == 'admin')
                            {{url('admin/customers/approval')}}
                        @elseif(auth()->user()->account_role == 'distributor')
                            {{url('distributor/customers/approval')}}
                        @endif
                " class="btn btn-blue">
                    <i class="fa fa-arrow-left"></i></a>&nbsp Kembali
            </header>

            {{-- <div class="card-body form-activity row ml-2">
                <div class="">
                    <form method="post" action="
                        @if(auth()->user()->account_role == 'manager')
                                {{url('manager/approval/update/')}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('superadmin/approval/update/')}}
                            @elseif(auth()->user()->account_role == 'admin')
                                {{url('admin/approval/update/')}}
                            @elseif(auth()->user()->account_role == 'distributor')
                                {{url('distributor/approval/update/')}}
                            @endif
                        ">
                        @csrf
                        <input name="user_id" type="hidden" value="{{$user->id}}">
                        <input name="status" type="hidden" value="1">
                        <button type="submit" class="btn btn-success" onclick="return confirm('setujui?')"><i
                                class="fa fa-check"></i> Setujui</button>
                    </form>
                </div>
                <div class="ml-2">
                    <form method="post" action="
                    @if(auth()->user()->account_role == 'manager')
                            {{url('manager/approval/update/')}}
                        @elseif(auth()->user()->account_role == 'superadmin')
                            {{url('superadmin/approval/update/')}}
                        @elseif(auth()->user()->account_role == 'admin')
                            {{url('admin/approval/update/')}}
                        @elseif(auth()->user()->account_role == 'distributor')
                            {{url('distributor/approval/update/')}}
                        @endif
                    ">
                        @csrf
                        <input name="user_id" type="hidden" value="{{$user->id}}">
                        <input name="status" type="hidden" value="0">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('tolak?')"><i
                                class="fa fa-ban"></i> Tolak</button>
                    </form>
                </div>
            </div> --}}

            <div class="card-body">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 col-sm-5 col-xs-12">

                            <div class="box-order">
                                <div class="order-header">
                                    Detail User
                                </div>
                                <div class="order-body">
                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Tanggal Registrasi</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{date('d M Y', strtotime($user->created_at))}}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Nama</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $user->name }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>No. Telp</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $user->phone }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Alamat</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ ucwords($user->user_address->first()->address) }},
                                            {{ ucwords($user->user_address->first()->kelurahan) }},
                                            {{ ucwords($user->user_address->first()->kecamatan) }},
                                            {{ ucwords($user->user_address->first()->kota) }},
                                            {{ ucwords($user->user_address->first()->provinsi) }}
                                            {{ $user->user_address->first()->kode_pos }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>ShareLoc</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $user->shareloc }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Site ID</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $user->site_code }}
                                            {{-- <select name="site_id" id="site_id" class="form-control" required>
                                                <option value="">Site Code</option>
                                                @foreach ($mappingSites as $mappingSite)
                                                <option value="{{ $mappingSite->kode }}"
                                            {{$user->site_code == $mappingSite->kode ? 'selected': ''}}>
                                            {{ $mappingSite->kode }} </option>
                                            @endforeach
                                            </select> --}}
                                        </div>
                                    </label>
                                    {{-- <div class="message">
                                        <form action="
                                        @if(auth()->user()->account_role == 'manager')
                                        {{url('manager/approval/sendmessage/')}}
                                        @elseif(auth()->user()->account_role == 'superadmin')
                                            {{url('superadmin/approval/sendmessage/')}}
                                        @elseif(auth()->user()->account_role == 'admin')
                                            {{url('admin/approval/sendmessage/')}}
                                        @elseif(auth()->user()->account_role == 'distributor')
                                            {{url('distributor/approval/sendmessage/')}}
                                        @endif
                                        " method="post">
                                        @csrf
                                            <label class=""><span class="text-danger">*</span> Kirim pesan Whatsapp ke
                                                No.Telp {{$user->phone}}</label>
                                            <textarea name="message"
                                                class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}"
                                                rows="5">Hello {{$user->name}}, Mohon maaf registrasi anda dalam status pending karena terdapat berkas yang kurang sesuai, Silahkan login ke akun anda dan lakukan pembaharuan berkas untuk mengaktifkan akun.</textarea>
                                            <br>
                                            <input type="text" name="phone" hidden value="{{$user->phone}}">
                                            <button type="submit" class="btn btn-primary"
                                                onclick="return confirm('Kirim pesan ini ?')"><i class="fa fa-send"></i>
                                                Kirim</button>
                                        </form>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 col-sm-7 col-xs-12">
                            <div class="box-order-items">
                                @if (!is_null($user->photo_ktp))
                                <div class="order-header">
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">Foto KTP</span>
                                        <a href="javascript:void(0)" class="btn btn-warning btn-edit-foto"
                                            data-toggle="modal" data-target="#editfoto" data-id="{{$user->id}}"
                                            data-tipe="photo_ktp"
                                            data-name="KTP-{{str_replace(' ', '_', $user->user_address->first()->shop_name)}}-{{$user->phone}}"
                                            data-directory="images/register/ktp" data-title="Edit Foto KTP"
                                            data-imgOld="{{$user->photo_ktp}}"
                                            data-images="{{asset($user->photo_ktp)}}">Edit</a>
                                    </div>
                                    <div class="col-8">
                                        <img src="{{asset($user->photo_ktp)}}" alt="photo ktp" style="max-width: 100%;">
                                    </div>
                                </div>
                                <br>
                                @endif
                                @if (!is_null($user->photo_npwp))
                                <div class="order-header">
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">Foto NPWP</span>
                                        <a href="javascript:void(0)" class="btn btn-warning btn-edit-foto"
                                            data-toggle="modal" data-target="#editfoto" data-id="{{$user->id}}"
                                            data-tipe="photo_npwp"
                                            data-name="NPWP-{{str_replace(' ', '_', $user->user_address->first()->shop_name)}}-{{$user->phone}}"
                                            data-directory="images/register/npwp" data-title="Edit Foto NPWP"
                                            data-imgOld="{{$user->photo_npwp}}"
                                            data-images="{{asset($user->photo_npwp)}}">Edit</a>
                                    </div>
                                    <div class="col-8">
                                        <img src="{{asset($user->photo_npwp)}}" alt="photo ktp"
                                            style="max-width: 100%;">
                                    </div>
                                </div>
                                <br>
                                @endif
                                <div class="order-items">
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">Foto Selfie KTP/NPWP</span>
                                        <a href="javascript:void(0)" class="btn btn-warning btn-edit-foto"
                                            data-toggle="modal" data-target="#editfoto" data-id="{{$user->id}}"
                                            data-tipe="selfie_ktp"
                                            data-name="Selfie-{{str_replace(' ', '_', $user->user_address->first()->shop_name)}}-{{$user->phone}}"
                                            data-directory="images/register/selfie" data-title="Edit Foto Selfie KTP"
                                            data-imgOld="{{$user->selfie_ktp}}"
                                            data-images="{{asset($user->selfie_ktp)}}">Edit</a>
                                    </div>
                                    <div class="col-8">
                                        <img src="{{asset($user->selfie_ktp)}}" alt="photo selfie"
                                            style="max-width: 100%;">
                                    </div>
                                </div>
                                <br>
                                <div class="order-items">
                                    <div class="d-flex justify-content-between">
                                        <span class="font-weight-bold">Foto Toko</span>
                                        <a href="javascript:void(0)" class="btn btn-warning btn-edit-foto"
                                            data-toggle="modal" data-target="#editfoto" data-id="{{$user->id}}"
                                            data-tipe="photo_toko"
                                            data-name="Toko-{{str_replace(' ', '_', $user->user_address->first()->shop_name)}}-{{$user->phone}}"
                                            data-directory="images/register/toko" data-title="Edit Foto TOKO"
                                            data-imgOld="{{$user->photo_toko}}"
                                            data-images="{{asset($user->photo_toko)}}">Edit</a>
                                    </div>
                                    <div class="col-8">
                                        <img src="{{asset($user->photo_toko)}}" alt="toko" style="max-width: 100%;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div id="editfoto" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <div id="title"></div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-foto" action="
                    @if(auth()->user()->account_role == 'manager')
                            {{url('manager/approval/photo-edit')}}
                        @elseif(auth()->user()->account_role == 'superadmin')
                            {{url('superadmin/approval/photo-edit')}}
                        @elseif(auth()->user()->account_role == 'admin')
                            {{url('admin/approval/photo-edit')}}
                        @elseif(auth()->user()->account_role == 'distributor')
                            {{url('distributor/approval/photo-edit')}}
                        @endif
                    " method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <img class="img img-responsive" id="edit-image" src="" width="150px"><br><br>
                            <input type="text" hidden name="edit_id" id="edit-id">
                            <input type="text" hidden name="edit_name" id="edit-name">
                            <input type="text" hidden name="edit_old" id="edit-old">
                            <input type="text" hidden id="edit-tipe" name="edit_tipe">
                            <input type="text" hidden id="edit-directory" name="edit_directory">
                            <input type="file" name="photo" id="photo-new">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Update foto</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="btn btn-danger" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    $(document).on('change', '#site_id', function () {
        var select = $('#site_id option:selected').val()
        var role = $('#role').val()
        var id = $('#user_id').val();
        $.ajax({
            url: `/${role}/approve/sitecodeAjax`,
            type: "POST",
            data: {
                select: select,
                id: id
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.status == 200) {
                    window.location.reload();
                }
            }
        });
    });

    $('.btn-edit-foto').on('click', function () {
        var id = $(this).attr('data-id');
        var tipe = $(this).attr('data-tipe');
        var name = $(this).attr('data-name');
        var images = $(this).attr('data-images');
        var title = $(this).attr('data-title');
        var old = $(this).attr('data-imgOld');
        var directory = $(this).attr('data-directory');

        $('#edit-id').val(id);
        $('#edit-tipe').val(tipe);
        $('#edit-name').val(name);
        $('#edit-old').val(old);
        $('#edit-directory').val(directory);
        $('#edit-image').attr('src', images);
        $('#title').html(`<h4 class="modal-title">${title}</h4>`)
    })

    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // $(previewId).css('src', e.target.result );
                $(previewId).attr('src', e.target.result);
                $(previewId).hide();
                $(previewId).fadeIn(850);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#photo-new").change(function () {
        readURL(this, '#edit-image');
    });
</script>
@stop