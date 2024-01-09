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
                            {{url('/manager/redeem-point')}}
                        @elseif($account_role == 'superadmin')
                            {{url('/superadmin/redeem-point')}}
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
    <section class="panel col-md-6">
        <header class="panel-heading">
            Form Tambah Produk Redeem
        </header>
        <div class="card-body">
            <form action="
                        @if($account_role == 'manager')
                            {{url('manager/redeem-point/store')}}
                        @elseif($account_role == 'superadmin')
                            {{url('superadmin/redeem-point/store')}}
                        @endif
                        " 
                        method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('product_by') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Produk Dari</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('product_by') ? 'is-invalid' : '' }}" name="product_by" aria-label="Default select example" id="product_by" onchange="showform()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="mpm" @if (old('product_by') == 'mpm') selected="selected" @endif>MPM</option>
                                    <option value="input" @if (old('product_by') == 'input') selected="selected" @endif>Input</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('product_by') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display:none;" id="row1">
                    <div class="col-md-8">
                        <div class="form-group {{ $errors->has('product_mpm') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Produk MPM</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('product_mpm') ? 'is-invalid' : '' }}" aria-label="Default select example" name="product_mpm" id="product_mpm"></select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('product_mpm') }}
                                </div>
                            </div> 
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('redeem_point_mpm') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Redeem Point</label>
                            <div class="col-sm-12">
                                <input type="number" step="1" min="1" name="redeem_point_mpm" class="form-control {{ $errors->has('redeem_point_mpm') ? 'is-invalid' : '' }}" placeholder="Redeem Point" value="{{old('redeem_point_mpm')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('redeem_point_mpm') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display:none;" id="row2">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('invoice_name') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Nama Invoice</label>
                            <div class="col-sm-12">
                                <input type="text" name="invoice_name" class="form-control {{ $errors->has('invoice_name') ? 'is-invalid' : '' }}" placeholder="Nama Invoice" value="{{old('invoice_name')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('invoice_name') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Nama Produk</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Nama Produk" value="{{old('name')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('name') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('redeem_point') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Redeem Point</label>
                            <div class="col-sm-12">
                                <input type="number" step="1" min="1" name="redeem_point" class="form-control {{ $errors->has('redeem_point') ? 'is-invalid' : '' }}" placeholder="Redeem Point" value="{{old('redeem_point')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('redeem_point') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('satuan_online') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Satuan</label>
                            <div class="col-sm-12">
                                <input type="text" name="satuan_online" class="form-control {{ $errors->has('satuan_online') ? 'is-invalid' : '' }}" placeholder="Satuan" value="{{old('satuan_online')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('satuan_online') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('qty') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Qty</label>
                            <div class="col-sm-12">
                                <input type="number" step="1" min="1" name="qty" class="form-control {{ $errors->has('qty') ? 'is-invalid' : '' }}" placeholder="Qty" value="{{old('qty')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('qty') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('photo_product') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Foto Produk</label>
                            <div class="col-12">
                                <input type="file" name="photo_product" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('redeem_desc') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Deskripsi</label>
                            <div class="col-12">
                                <textarea name="redeem_desc" class="form-control {{ $errors->has('redeem_desc') ? 'is-invalid' : '' }}" rows="5">{{old('redeem_desc')}}</textarea>
                                <div class="invalid-feedback">
                                    {{ $errors->first('redeem_desc') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('redeem_snk') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Syarat dan Ketentuan</label>
                            <div class="col-12">
                                <textarea name="redeem_snk" class="form-control {{ $errors->has('redeem_snk') ? 'is-invalid' : '' }}" rows="5">{{old('redeem_snk')}}</textarea>
                                <div class="invalid-feedback">
                                    {{ $errors->first('redeem_snk') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-blue float-right" style="width:200px;">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        $(document).ready(function () {
            $('#product_mpm').select2({
                placeholder: "Pilih Produk",
                ajax: {
                    url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/all-product')}}" 
                        @elseif(auth()->user()->account_role == 'superadmin') 
                            "{{url('superadmin/all-product')}}"
                    @endif,
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data){
                        return {
                            results: $.map(data, function(item){
                                return {
                                    text: item.kodeprod +' - '+ item.name,
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

        function showform() {
            var product_by = $('#product_by').val();
            if (product_by == 'mpm') {
                $('#row1').show();
                $('#row2').hide();
            } else if (product_by == 'input') {
                $('#row1').hide();
                $('#row2').show();
            }
        }
    </script>

    <style>
        .text-small{
            font-size: 8pt;
            color: red;
        }
    </style>
    
    @endsection
