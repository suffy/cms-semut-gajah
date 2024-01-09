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
                            {{url('manager/redeem-point/update')}}
                        @elseif($account_role == 'superadmin')
                            {{url('superadmin/redeem-point/update')}}
                        @endif
                        " 
                        method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Produk Dari</label>
                            <div class="col-sm-12">
                                <input type="hidden" id="id" name="id" value="{{$product->id}}">
                                <select class="form-control" name="product_by" aria-label="Default select example" id="product_by" onchange="showform()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="mpm" @if ($product->brand != null) selected="selected" @endif>MPM</option>
                                    <option value="input" @if ($product->brand == null) selected="selected" @endif>Input</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display:none;" id="row1">
                    <div class="col-md-8">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Produk MPM</label>
                            <div class="col-sm-12">
                                <input type="text" name="product_mpm" class="form-control" value="{{ $product->name }}" readonly>
                            </div> 
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Redeem Point</label>
                            <div class="col-sm-12">
                                <input type="number" step="1" min="1" name="redeem_point_mpm" class="form-control" placeholder="Redeem Point" value="{{ $product->redeem_point }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display:none;" id="row2">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Nama Invoice</label>
                            <div class="col-sm-12">
                                <input type="text" name="invoice_name" class="form-control" placeholder="Nama Invoice" value="{{ $product->invoice_name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Nama Produk</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control" placeholder="Nama Produk" value="{{ $product->name }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Redeem Point</label>
                            <div class="col-sm-12">
                                <input type="number" step="1" min="1" name="redeem_point" class="form-control" placeholder="Redeem Point" value="{{ $product->redeem_point }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Satuan</label>
                            <div class="col-sm-12">
                                <input type="text" name="satuan_online" class="form-control" placeholder="Satuan" value="{{ $product->satuan_online}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Qty</label>
                            <div class="col-sm-12">
                                <input type="number" step="1" min="1" name="qty" class="form-control" placeholder="Qty" value="{{ $product->qty3 }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Foto Produk</label>
                            <div class="col-12">
                                <input type="file" name="photo_product" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Deskripsi</label>
                            <div class="col-12">
                                <textarea name="redeem_desc" class="form-control" rows="5">{{ $product->redeem_desc }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Syarat dan Ketentuan</label>
                            <div class="col-12">
                                <textarea name="redeem_snk" class="form-control" rows="5">{{ $product->redeem_snk }}</textarea>
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
            if($('#product_by').val() == 'mpm') {
                $('#row1').show();
                $('#row2').hide();
            } else if($('#product_by').val() == 'input') {
                $('#row1').hide();
                $('#row2').show();
            } else {
                $('#row1').hide();
                $('#row2').hide();
            }

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
