@extends('admin.layout.template')

@section('content')

@php
$account_role = auth()->user()->account_role;
@endphp

<div class="heading-section">
    <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-4">
            <a href="
                    @if($account_role == 'manager')
                        {{url('/manager/missions')}}
                    @endif
                " class="btn btn-blue"><span class="fa fa-arrow-left"></span> &nbsp Kembali</a>
        </div>
        <div class="col-md-8 col-sm-8 col-xs-8">

        </div>
    </div>
</div>
<br>
<section class="panel" id="create-new-voucher">
    <div class="card-body">
        <form action="
                    @if($account_role == 'manager')
                        {{url('manager/missions/update')}}
                    @endif
                    " method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Nama</label>
                        <div class="col-sm-12">
                            <input type="hidden" name="id" value="{{$mission->id}}">
                            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nama misi..." value="{{$mission->name}}">
                            <div class="invalid-feedback">
                                {{ $errors->first('name') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('reward') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Reward</label>
                        <div class="col-sm-12">
                            <input type="text" name="reward" class="form-control {{ $errors->has('reward') ? 'is-invalid' : '' }}" placeholder="Reward..." value="{{$mission->reward}}">
                            <div class="invalid-feedback">
                                {{ $errors->first('reward') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('start_date') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Periode Awal</label>
                        <div class="col-sm-12">
                            <input type="text" name="start_date" class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" placeholder="Periode Awal..." value="{{$mission->start_date}}" onfocus="(this.type='date')" onblur="(this.type='text')">
                            <div class="invalid-feedback">
                                {{ $errors->first('start_date') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('end_date') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Periode Akhir</label>
                        <div class="col-sm-12">
                            <input type="text" name="end_date" class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}" placeholder="Periode Akhir.." value="{{$mission->end_date}}" onfocus="(this.type='date')" onblur="(this.type='text')">
                            <div class="invalid-feedback">
                                {{ $errors->first('end_date') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Deskripsi</label>
                        <div class="col-sm-12">
                            <textarea name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" cols="10" rows="10" style="margin-top: 0px; margin-bottom: 0px; height: 119px; resize:none;" placeholder="Description...">{{$mission->description}}</textarea>
                            <div class="invalid-feedback">
                                {{ $errors->first('description') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('limit') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Limit</label>
                        <div class="col-sm-12">
                            <input type="number" step="1" min="0" id="limit" name="limit" class="form-control {{ $errors->has('limit') ? 'is-invalid' : '' }}" placeholder="Limit..." value="0" onchange="showList()">
                            <div class="invalid-feedback">
                                {{ $errors->first('limit') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @php($arr = []) 
                @foreach($mission->tasks as $row)
                @php(array_push($arr, $row->id))
                <div class="form-group col-md-6">
                    <div class="col-md-8 ">
                        <div class="row">
                            <label class="col-sm-12 col-form-label mt-3"><b>Misi</b></label>

                            <input type="hidden" name="mission_id[]" class="col-sm-12 form-control" value="{{$row->mission_id}}">
                        </div>
                    </div>

                    @if($row->type == 1 || $row->type == 2)

                    <div class="col-md-5 p-0">
                        <label class="col-sm-12">Type : {{ $row->type == 1 ? 'Pembelian Qty' : 'Pembelian Total Harga' }}</label>
                        <input type="hidden" name="type[]" class="col-sm-12 form-control" value="{{$row->type}}">
                    </div>
                    <div class="col-md-5 ">
                        <label class="col-sm-12 p-0">Nama</label>
                        <input name="names[]" class="col-sm-12 form-control" value="{{$row->name}}">
                    </div>
                    <div class="col-md-5">
                        <label class="col-sm-12 p-0">Product</label>
                        <input name="product_id[]" class="col-sm-12 form-control products_edit_{{$row->id}}" value="{{$row->product_id}}">
                    </div>
                    <div class="col-md-5">
                        <label class="col-sm-12 p-0">Grup</label>
                        <input name="nama_group[]" class="col-sm-12 form-control groups_edit_{{$row->id}}" value="{{$row->group_id}}">
                    </div>
                    <div class="col-md-5">
                        <label class="col-sm-12 p-0">Kuantitas</label>
                        <input name="qty[]" class="col-sm-12 form-control" value="{{$row->qty}}">
                    </div>
                    <div class="col-md-5">
                        <input name="login_at[]" type="hidden" class="col-sm-12 form-control" value="">
                    </div>
                    <div class="col-md-5">
                        <a href="javascript:void(0)" class="btn btn-red" onclick="hapus('{{$row->id}}')">Delete</a>
                    </div>
                    @else($row->type == 3)
                    <div class="col-md-5 p-0">
                        <label class="col-sm-12">Type : Login</label>
                        <input type="hidden" name="type[]" class="col-sm-12 form-control" value="3">
                    </div>
                    <div class="col-md-5">
                        <label class="col-sm-12 p-0">Informasi Login</label>
                        <input name="login_at[]" type="date" class="col-sm-12 form-control" value="{{$row->login_at}}">
                    </div>
                    <div class="col-md-5">
                        <label class="col-sm-12 p-0">Nama</label>
                        <input name="names[]" class="col-sm-12 form-control" value="{{$row->name}}">
                    </div>
                    <div class="col-md-5">
                        <input name="qty[]" type="hidden" class="col-sm-12 form-control" >
                    </div>
                    <div class="col-md-5">
                        <input type="hidden" name="product_id[]" class="col-sm-12 form-control" >
                    </div>
                    <div class="col-md-5">
                        <input type="hidden" name="nama_group[]" class="col-sm-12 form-control" >
                    </div>
                    <div class="col-md-5">
                        <a href="javascript:void(0)" class="btn btn-red" onclick="hapus('{{$row->id}}')">Delete</a>
                    </div>
                    @endif
                </div>
                @endforeach
                <div class="form-group col-md-6">
                    
                    <div class="row" id="rankReward" class="rankReward">
                    </div>
                </div>
            </div>
            <div class="row float-right">
                <button type="submit" class="btn btn-primary float-right">Simpan</button>
            </div>
        </form>
    </div>
</section>


<script>
    $(document).ready(function() {
        $(document).on('change', '.type', function(event) {
            $("#rankReward .type").each(function(index) {
                var type = $(this).val();

                $('#rankReward .products_' + index).select2({
                    placeholder: "Pilih Products",
                    ajax: {
                        url: @if(auth() -> user() -> account_role == 'manager')
                        "{{url('manager/customers/all-products')}}"
                        @elseif(auth() -> user() -> account_role == 'superadmin')
                        "{{url('superadmin/customers/all-products')}}",
                        @endif,
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.name,
                                        id: item.id
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                })

                $('#rankReward .groups_' + index).select2({
                    placeholder: "Pilih Groups",
                    ajax: {
                        url: @if(auth() -> user() -> account_role == 'manager')
                        "{{url('manager/customers/all-groups')}}"
                        @elseif(auth() -> user() -> account_role == 'superadmin')
                        "{{url('superadmin/customers/all-groups')}}",
                        @endif,
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.nama_group,
                                        id: item.nama_group,
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                })

                if (type === '1' || type === '2') {
                    if (type === '1') {
                        $('#jumlah_' + index).attr("placeholder", "Quantity")
                    } else if (type === '2') {
                        $('#jumlah_' + index).attr("placeholder", "Total Harga Pembelian")
                    }
                    $('#nama_group_' + index).show();
                    $('#product_id_' + index).show();
                    $('#qty_' + index).show();
                    $('#login_at_' + index).hide();
                    $('#names_' + index).show();
                } else if (type === '3') {
                    $('#nama_group_' + index).hide();
                    $('#product_id_' + index).hide();
                    $('#qty_' + index).hide();
                    $('#login_at_' + index).show();
                    $('#names_' + index).show();
                } else if (type === '') {
                    $('#nama_group_' + index).hide();
                    $('#product_id_' + index).hide();
                    $('#qty_' + index).hide();
                    $('#login_at_' + index).hide();
                    $('#names_' + index).hide();
                }
            });
        });
        let arr = {!! json_encode($arr) !!};
        for(let x = 0; x < arr.length; x++) {
            $(".products_edit_" + arr[x]).click(function() {
                // '<select name="product_id[]" data-name="" class="form-control products_' + i + '"></select>' +
                $(this).replaceWith('<select name="product_id[]" class="form-control products_edit_' + arr[x] + '"></select>');

                $(".products_edit_" + arr[x]).select2({
                    placeholder: "Pilih Products",
                    ajax: {
                        url: @if(auth()->user()->account_role == 'manager')
                                    "{{url('manager/customers/all-products')}}"
                                @elseif(auth()->user()->account_role == 'superadmin') 
                                    "{{url('superadmin/customers/all-products')}}",
                            @endif,
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data){
                            return {
                                results: $.map(data, function(item){
                                    return {
                                        text: item.name,
                                        id: item.id
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                })
            });
            $(".groups_edit_" + arr[x]).click(function() {
                // '<select name="product_id[]" data-name="" class="form-control products_' + i + '"></select>' +
                $(this).replaceWith('<select name="nama_group[]" class="form-control groups_edit_' + arr[x] + '"></select>');

                $(".groups_edit_" + arr[x]).select2({
                    placeholder: "Pilih Groups",
                    ajax: {
                        url: @if(auth() -> user() -> account_role == 'manager')
                        "{{url('manager/customers/all-groups')}}"
                        @elseif(auth() -> user() -> account_role == 'superadmin')
                        "{{url('superadmin/customers/all-groups')}}",
                        @endif,
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.nama_group,
                                        id: item.nama_group,
                                    }
                                })
                            };
                        },
                        cache: true
                    }
                })
            });
        }
    });

    function hapus(id) {
        if (confirm('Yakin Akan Menghapus Data ?')) {
            var formData = {
                _token: "{{ csrf_token() }}",
                id: id,
                url: "{{ Request::url() }}",
            };

            var id = id;

            $.ajax({
                type: 'POST',
                url: @if(auth() -> user() -> account_role == 'manager')
                '/manager/missions/deletetask'
                @elseif(auth() -> user() -> account_role == 'superadmin')
                '/superadmin/top-spender/delete'
                @elseif(auth() -> user() -> account_role == 'admin')
                '/admin/top-spender/delete'
                @endif,
                data: formData,
                dataType: "json",
                encode: true,
            }).done(function(data) {
                location.reload();
                showNotif("Data Berhasil Dihapus!");
            });
        } else {
            return false;
        }
    }

    function showList() {
        var limit = $('#limit').val();
        $('#rankReward').html('');
        for (let i = 0; i < limit; i++) {
            $('#rankReward').append(
                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<input type="hidden" name="mission_id[]" class="col-sm-12 form-control">' +
                '</div>' +
                '</div>' +

                '<div class="col-md-8 ">'+
                '<label class="col-sm-12 col-form-label">Misi</label>'+
                '</div>'+
                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<label>Tipe</label>' +
                '<select class="form-control type" name="type[]" id="type">' +
                '<option value=>-- Pilih Tipe --</option>' +
                '<option value="1">Pembelian Qty</option>' +
                '<option value="2">Pembelian Total Harga</option>' +
                '<option value="3">Login</option>' +
                '</select>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<div class="form-group nama_group" id="nama_group_' + i + '" style="display:none">' +
                '<label>Grup</label>' +
                '<select name="nama_group[]" data-name="" class="form-control groups_' + i + '"></select>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<div class="form-group product_id"   id="product_id_' + i + '" style="display:none">' +
                '<label>Product</label>' +
                '<select name="product_id[]" data-name="" class="form-control products_' + i + '"></select>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<div class="form-group row qty"  id="qty_' + i + '" style="display:none">' +
                '<label class="col-sm-12 col-form-label">Jumlah</label>' +
                '<div class="col-sm-12">' +
                '<input  type="text" name="qty[]" id="jumlah_' + i + '" class="form-control" placeholder="Nominal..." value="">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<div class="form-group row login_at" id="login_at_' + i + '" style="display:none">' +
                '<label class="col-sm-12 col-form-label">Informasi login</label>' +
                '<div class="col-sm-12">' +
                '<input   type="date" name="login_at[]" class="form-control"  value="">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-5">' +
                '<div class="form-group row name" id="names_' + i + '" style="display:none">' +
                '<label class="col-sm-12 col-form-label">Nama</label>' +
                '<div class="col-sm-12">' +
                '<input  type="text" name="names[]" class="form-control" placeholder="Nama..." value="">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
        }
    }
</script>
<style>
    .image-outer {
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

    .image-outer img {
        position: absolute;
        width: 150px;
        height: auto;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
    }

    .text-small {
        font-size: 8pt;
        color: red;
    }
</style>
@stop