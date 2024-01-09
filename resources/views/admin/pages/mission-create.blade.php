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
                        {{url('manager/missions/store')}}
                    @endif
                    " method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Nama</label>
                        <div class="col-sm-12">
                            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Judul..." value="{{old('name')}}">
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
                            <input type="text" name="reward" class="form-control {{ $errors->has('reward') ? 'is-invalid' : '' }}" placeholder="Reward..." value="{{old('reward')}}">
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
                            <input type="text" name="start_date" class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" placeholder="Periode Awal..." value="{{old('start_date')}}" onfocus="(this.type='date')" onblur="(this.type='text')">
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
                            <input type="text" name="end_date" class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}" placeholder="Periode Akhir.." value="{{old('end_date')}}" onfocus="(this.type='date')" onblur="(this.type='text')">
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
                            <textarea name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" cols="10" rows="10" style="margin-top: 0px; margin-bottom: 0px; height: 119px; resize:none;" placeholder="Description...">{{old('description')}}</textarea>
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
                            <input type="number" step="1" min="1" id="limit" name="limit" class="form-control {{ $errors->has('limit') ? 'is-invalid' : '' }}" placeholder="Limit..." value="{{old('limit')}}" onchange="showList()">
                            <div class="invalid-feedback">
                                {{ $errors->first('limit') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row-md-12">

                <hr>
                <div class="form-group row">
                    <label class="col-sm-12 col-form-label"></label>
                </div>
                <div class="row" id="rankReward" class="rankReward">

                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <button type="submit" class="btn btn-primary float-right">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
    $(document).ready(function() {
        $(document).on('change', '.type', function(event) {
            $("#rankReward .type").each(function(index) {
                var type = $(this).val();

                // $('#' + product-name' + count, '#items-list').select2();
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

    });

    function showList() {
        var limit = $('#limit').val();
        $('#rankReward').html('');

        for (let i = 0; i < limit; i++) {
            $('#rankReward').append(
                '<div class="row">' +
                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
                '<label> <b>Misi ' + (i + 1) + '</b></label>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
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
                '<div class="col-md-4">' +
                '<div class="form-group nama_group" id="nama_group_' + i + '" style="display:none">' +
                '<label>Grup</label>' +
                '<select name="nama_group[]" data-name="" class="form-control groups_' + i + '"></select>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
                '<div class="form-group product_id"   id="product_id_' + i + '" style="display:none">' +
                '<label>Product</label>' +
                '<select name="product_id[]" data-name="" class="form-control products_' + i + '"></select>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
                '<div class="form-group row qty"  id="qty_' + i + '" style="display:none">' +
                '<label class="col-sm-12 col-form-label">Jumlah</label>' +
                '<div class="col-sm-12">' +
                '<input type="hidden" name="id[]" value="">' +
                '<input  type="text" name="qty[]" id="jumlah_' + i + '" class="form-control" placeholder="Nominal..." value="">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
                '<div class="form-group row login_at" id="login_at_' + i + '" style="display:none">' +
                '<label class="col-sm-12 col-form-label">Informasi login</label>' +
                '<div class="col-sm-12">' +
                '<input   type="date" name="login_at[]" class="form-control"  value="">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +

                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
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