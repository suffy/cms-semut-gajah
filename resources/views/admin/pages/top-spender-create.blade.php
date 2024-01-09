@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<div class="heading-section">
    <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-4">
            <a 
                href="
                    @if($account_role == 'manager')
                        {{url('/manager/top-spender')}}
                    @elseif($account_role == 'superadmin')
                        {{url('/superadmin/top-spender')}}
                    @elseif($account_role == 'admin')
                        {{url('/admin/top-spender')}}
                    @endif
                " 
                class="btn btn-blue"
            ><span class="fa fa-arrow-left"></span> &nbsp Kembali</a>
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
                        {{url('manager/top-spender/store')}}
                    @elseif($account_role == 'admin')
                        {{url('admin/top-spender/store')}}
                    @elseif($account_role == 'superadmin')
                        {{url('superadmin/top-spender/store')}}
                    @endif
                    " 
                    method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Judul</label>
                        <div class="col-sm-12">
                            <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" placeholder="Judul..." value="{{old('title')}}">
                            <div class="invalid-feedback">
                                {{ $errors->first('title') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('start') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Periode Awal</label>
                        <div class="col-sm-12">
                            <input type="text" name="start" class="form-control {{ $errors->has('start') ? 'is-invalid' : '' }}" placeholder="Periode Awal..." value="{{old('start')}}" onfocus="(this.type='date')" onblur="(this.type='text')">
                            <div class="invalid-feedback">
                                {{ $errors->first('start') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('end') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Periode Akhir</label>
                        <div class="col-sm-12">
                            <input type="text" name="end" class="form-control {{ $errors->has('end') ? 'is-invalid' : '' }}" placeholder="Periode Akhir.." value="{{old('end')}}" onfocus="(this.type='date')" onblur="(this.type='text')">
                            <div class="invalid-feedback">
                                {{ $errors->first('end') }}
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
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('banner') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Banner</label>
                        <div class="col-sm-12">
                            <input type="file" name="banner" class="{{ $errors->has('banner') ? 'is-invalid' : '' }}">
                            <div class="invalid-feedback">
                                {{ $errors->first('banner') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Filter By</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('site_code') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Site Code <span class="text-small">* boleh dikosongkan</span></label>
                        <div class="col-sm-12 mt-2">
                            <select class="form-control {{ $errors->has('site_code') ? 'is-invalid' : '' }}" aria-label="Default select example" name="site_code" id="site_code"></select>
                            <div class="invalid-feedback">
                                {{ $errors->first('site_code') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('brand_id') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Brand <span class="text-small">* boleh dikosongkan</span></label>
                        <div class="col-sm-12">
                            <select class="form-control" id="brand_id" name="brand_id">
                                <option selected disabled>Pilih Brand</option>
                                @foreach($brand as $row => $value)
                                    <option value="{{$value}}">{{ucwords($row)}}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->first('brand_id') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('product_id') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Product <span class="text-small">* boleh dikosongkan</span></label>
                        <div class="col-sm-12 mt-2">
                            <select class="form-control" aria-label="Default select example" name="product_id" id="product_id"></select>
                            <div class="invalid-feedback">
                                {{ $errors->first('product_id') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('reward') ? 'has-error' : '' }} row">
                        <label class="col-sm-12 col-form-label">Reward</label>
                        <div class="col-sm-12">
                            <select class="form-control {{ $errors->has('reward') ? 'is-invalid' : '' }}" id="reward" name="reward">
                                <option disabled selected>Pilih Reward</option>
                                <option value="cash">Cash</option>
                                <option value="point">Point</option>
                            </select>
                            <div class="invalid-feedback">
                                {{ $errors->first('reward') }}
                            </div>
                        </div>
                    </div>
                </div>
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
            <div class="row">
                <div class="col-md-12">
                    <hr>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Reward By Rank</label>
                    </div>
                    <div class="row" id="rankReward">

                    </div>
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
    $(document).ready(function () {
        $('#site_code').select2({
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: @if(auth()->user()->account_role == 'manager')
                        "{{url('manager/all-mapping-site')}}"
                    @elseif(auth()->user()->account_role == 'superadmin')
                        "{{url('superadmin/all-mapping-site')}}"
                    @elseif(auth()->user()->account_role == 'admin')
                        "{{url('admin/all-mapping-site')}}"
                @endif,
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.kode,
                                id: item.kode
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#product_id').select2({
            placeholder: "Pilih Product",
            ajax: {
                url: 'all-product',
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
                cache: true,
                error: function(error) {
                    console.log(error);
                }
            }
        });
    });

    function showList() {
        var limit = $('#limit').val();
        $('#rankReward').html('');
        for(let i = 0; i < limit; i++) {
            $('#rankReward').append(
                '<div class="col-md-12">' +
                '<div class="col-md-4">' +
                '<div class="form-group row">' +
                '<label class="col-sm-12 col-form-label">Rank ' + (i+1) + ' Reward</label>' +
                '<div class="col-sm-12">' +
                '<input type="hidden" name="pos[]" value="' + (i+1) + '">' +
                '<input type="text" name="nominal[]" class="form-control" placeholder="Nominal..." value="">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
        }
    }
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
    
    .text-small{
        font-size: 8pt;
        color: red;
    }
</style>
@stop