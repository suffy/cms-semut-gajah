@extends('admin.layout.template')

@section('content')

    <div class="heading-section">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4">
                <a 
                    href="
                        @if(auth()->user()->account_role == 'manager')
                            {{url('/manager/promo')}}
                        @elseif(auth()->user()->account_role == 'superadmin')
                            {{url('/superadmin/promo')}}
                        @elseif(auth()->user()->account_role == 'admin')
                            {{url('/admin/promo')}}
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
                @if(auth()->user()->account_role == 'manager'){{url('manager/promo/update', $promos->id)}}
                @elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/promo/update', $promos->id)}}
                @elseif(auth()->user()->account_role == 'admin'){{url('admin/promo/update', $promos->id)}}
                @endif" 
            method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="promo_title" class="form-control" placeholder="Name" value="{{ $promos->title }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Contact</label>
                            <div class="col-sm-12">
                                <input type="text" name="contact" class="form-control" placeholder="Contact" value="{{ $promos->contact }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-6 col-form-label">Banner</label>
                            <label class="col-sm-6 col-form-label div-multiple" style="visibility:hidden;">Berlaku Kelipatan</label>
                            <div class="col-6">
                                <input type="file" name="banner">
                            </div>
                            <div class="col-6 div-multiple" style="padding-left:35px;visibility:hidden;">
                                <input class="form-check-input" id="multiple" name="multiple" type="checkbox" @if($promos->multiple == 1) checked @endif value="1"><label class="form-check-label">Check jika promo berlaku kelipatan</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Syarat dan Ketentuan</label>
                            <div class="col-sm-12">
                                <select class="form-control" name="termcondition" id="snk" onchange="showsnk()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="1" {{ $promos->termcondition == 1 ? 'selected' : '' }}>Minimal Jumlah Product</option>
                                    <option value="2" {{ $promos->termcondition == 2 ? 'selected' : '' }}>Minimal Transaksi</option>
                                    <option value="3" {{ $promos->termcondition == 3 ? 'selected' : '' }}>Minimal Jumlah Product dan Minimal Transaksi</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="snk1">
                            <label class="col-sm-12 col-form-label">Minimal Jumlah Product</label>
                            <div class="col-sm-12">
                                <select class="form-control" name="detail_termcondition" id="min" onchange="showmin()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="1" {{ $promos->detail_termcondition == 1 ? 'selected' : '' }}>Total Product</option>
                                    <option value="2" {{ $promos->detail_termcondition == 2 ? 'selected' : '' }}>Per Product</option>
                                    <option value="3" {{ $promos->detail_termcondition == 3 ? 'selected' : '' }}>Minimal SKU</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="min1">
                            <label class="col-sm-12 col-form-label">Total Product</label>
                            <div class="col-sm-12">
                                <input type="text" id="all_min_qty" name="all_min_qty" class="form-control" placeholder="Total Product" value="{{ $promos->min_qty }}">
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="min2">
                            <label class="col-sm-12 col-form-label">Minimal SKU</label>
                            <div class="col-sm-12">
                                <input type="text" id="min_sku" name="min_sku" class="form-control" placeholder="Minimal SKU" value="{{ $promos->min_sku }}">
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="snk2">
                            <label class="col-sm-12 col-form-label">Minimal Transaksi</label>
                            <div class="col-sm-12">
                                <input type="text" id="min_transaction" name="min_transaction" class="form-control" placeholder="Minimal Transaksi" value="{{ $promos->min_transaction }}">
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('all_transaction') ? 'has-error' : '' }} row" style="display:none;" id="snk3">
                            <label class="col-sm-12 col-form-label">Berlaku Untuk Seluruh Product</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('all_transaction') ? 'is-invalid' : '' }} " name="all_transaction" id="all_transaction" onchange="set_all()">
                                    <option value="0" selected="true">Tidak Aktif</option>
                                    <option value="1">Aktif</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('all_transaction') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('class_type') ? 'has-error' : '' }} row" id="ct">
                            <label class="col-sm-12 col-form-label">Class dan Type Khusus</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('class_type') ? 'is-invalid' : '' }} " name="class_type" id="class_type" onchange="set_class_type()">
                                    <option value="0">Tidak Aktif</option>
                                    <option value="1" {{ ($promos->class_cust != null && $promos->type_cust != null) ? 'selected' : '' }}>Aktif | Kombinasi</option>
                                    <option value="2" {{ ($promos->class_cust != null && $promos->type_cust == null) ? 'selected' : '' }}>Aktif | Class</option>
                                    <option value="3" {{ ($promos->class_cust == null && $promos->type_cust != null) ? 'selected' : '' }}>Aktif | Type</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('class_type') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('ct_class') ? 'has-error' : '' }} row" style="display:none;" id="ct1">
                            <label class="col-sm-12 col-form-label">Class</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('ct_class') ? 'is-invalid' : '' }} " name="ct_class" id="ct_class">
                                    <option selected="true" disabled="disabled">Pilih Class</option>
                                    <option value="RT" {{ $promos->class_cust == 'RT' ? 'selected' : '' }}>RT | Retail</option>
                                    <option value="SW" {{ $promos->class_cust == 'SW' ? 'selected' : '' }}>SW | Semi Grosir</option>
                                    <option value="WS" {{ $promos->class_cust == 'WS' ? 'selected' : '' }}>WS | Grosir</option>
                                    <option value="SO" {{ $promos->class_cust == 'SO' ? 'selected' : '' }}>SO | Star Outlet</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('ct_class') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('ct_type') ? 'has-error' : '' }} row" style="display:none;" id="ct2">
                            <label class="col-sm-12 col-form-label">Type</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('ct_type') ? 'is-invalid' : '' }} " name="ct_type" id="ct_type">
                                    <option selected="true" disabled="disabled">Pilih Type</option>
                                    <option value="APC" {{ $promos->type_cust == 'APC' ? 'selected' : '' }}>APC | Apotik Jaringan/KF/Guardian/Century</option>
                                    <option value="APT" {{ $promos->type_cust == 'APT' ? 'selected' : '' }}>APT | Apotik Reguler (Independen)</option>
                                    <option value="ECM" {{ $promos->type_cust == 'ECM' ? 'selected' : '' }}>ECM | E-Commerce</option>
                                    <option value="HPM" {{ $promos->type_cust == 'HPM' ? 'selected' : '' }}>HPM | Hypermarket</option>
                                    <option value="HRK" {{ $promos->type_cust == 'HRK' ? 'selected' : '' }}>HRK | Hotel Resto Kantin</option>
                                    <option value="MML" {{ $promos->type_cust == 'MML' ? 'selected' : '' }}>MML | Minimarket Lokal/Independen</option>
                                    <option value="MMN" {{ $promos->type_cust == 'MMN' ? 'selected' : '' }}>MMN | Minimarket Nasional</option>
                                    <option value="OTH" {{ $promos->type_cust == 'OTH' ? 'selected' : '' }}>OTH | Other (Diluar Type Yang Ada)</option>
                                    <option value="PBF" {{ $promos->type_cust == 'PBF' ? 'selected' : '' }}>PBF | Pedagang Besar Farmasi (PT)</option>
                                    <option value="PDC" {{ $promos->type_cust == 'PDC' ? 'selected' : '' }}>PDC | Pedagang Besar Consumer (PT.Non Farmasi)</option>
                                    <option value="SML" {{ $promos->type_cust == 'SML' ? 'selected' : '' }}>SML | Supermarket Lokal/Independen</option>
                                    <option value="SMN" {{ $promos->type_cust == 'SMN' ? 'selected' : '' }}>SMN | Supermarket Nasional</option>
                                    <option value="SNC" {{ $promos->type_cust == 'SNC' ? 'selected' : '' }}>SNC | Toko Snack</option>
                                    <option value="SUB" {{ $promos->type_cust == 'SUB' ? 'selected' : '' }}>SUB | Sub Distributor Resmi</option>
                                    <option value="TCO" {{ $promos->type_cust == 'TCO' ? 'selected' : '' }}>TCO | Toko Kosmetik</option>
                                    <option value="TJM" {{ $promos->type_cust == 'TJM' ? 'selected' : '' }}>TJM | Toko Jamu</option>
                                    <option value="TKL" {{ $promos->type_cust == 'TKL' ? 'selected' : '' }}>TKL | Toko Kelontong</option>
                                    <option value="TOB" {{ $promos->type_cust == 'TOB' ? 'selected' : '' }}>TOB | Toko Obat</option>
                                    <option value="TRD" {{ $promos->type_cust == 'TRD' ? 'selected' : '' }}>TRD | Trading (Insidentil)</option>
                                    <option value="WRG" {{ $promos->type_cust == 'WRG' ? 'selected' : '' }}>WRG | Warung (Jual Kebutuhan Sehari-hari)</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('ct_type') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Valid Date</label>
                            <div class="col-6">
                                <input type="text" name="start" placeholder="Date Start..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ $promos->start }}">
                            </div>
                            <div class="col-6">
                                <input type="text" name="end" placeholder="Date End..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" value="{{ $promos->end }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                <textarea name="description" class="form-control" cols="10" rows="10" style="margin-top: 0px; margin-bottom: 0px; height: 119px; resize:none;" placeholder="Description.">{{ $promos->description }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Kategori Reward</label>
                            <div class="col-sm-12">
                                <select class="form-control" name="category" aria-label="Default select example" id="kt" onchange="showkt()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="1" {{ $promos->category == 1 ? 'selected' : '' }}>Nominal</option>
                                    <option value="2" {{ $promos->category == 2 ? 'selected' : '' }}>Product</option>
                                    <option value="3" {{ $promos->category == 3 ? 'selected' : '' }}>Nominal dan Product</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="kt1">
                            <label class="col-sm-12 col-form-label">Nominal</label>
                            <div class="col-sm-12">
                                <select class="form-control" id="nom" name="detail_category" aria-label="Default select example" onchange="shownom()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="1" {{ $promos->detail_category == 1 ? 'selected' : '' }}>Point</option>
                                    <option value="2" {{ $promos->detail_category == 2 ? 'selected' : '' }}>Diskon</option>
                                    <option value="3" {{ $promos->detail_category == 3 ? 'selected' : '' }}>Potongan</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="nom1">
                            <label class="col-sm-12 col-form-label">Point</label>
                            <div class="col-sm-12">
                                <input type="text" id="point" name="point" class="form-control" placeholder="Point" @if($promos->detail_category == 1) value="{{ $promos->reward[0]->reward_point }}" @endif>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="nom2">
                            <label class="col-sm-12 col-form-label">Diskon</label>
                            <div class="col-sm-12">
                                <input type="number" min="0" max="100" step="0.1" id="diskon" name="discount" class="form-control" placeholder="Diskon" @if($promos->detail_category == 2) value="{{ $promos->reward[0]->reward_disc }}" @endif>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="nom3">
                            <label class="col-sm-12 col-form-label">Potongan</label>
                            <div class="col-sm-12">
                                <input type="text" id="potongan" name="nominal" class="form-control" placeholder="Potongan" @if($promos->detail_category == 3) value="{{ $promos->reward[0]->reward_nominal }}" @endif>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="nom4">
                            <label class="col-sm-12 col-form-label">Maksimal Potongan Diskon <span class="text-small">* kosong tidak apa-apa</span> </label>
                            <div class="col-sm-12">
                                <input type="number" id="maks" name="max" class="form-control" placeholder="Maksimal Potongan Diskon" @if($promos->detail_category == 2) value="{{ $promos->reward[0]->max }}" @endif>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="kt2">
                            <label class="col-sm-12 col-form-label">Product Reward</label>
                            <div class="col-sm-12">
                                <select class="form-control" aria-label="Default select example" id="select_product_reward"></select>
                            </div> 
                        </div>
                        <div class="form-group row" style="display:none;" id="kt3">
                            <label class="col-sm-12 col-form-label">Jumlah Product Reward</label>
                            <div class="col-sm-12">
                                <input type="text" id="product_reward_qty" class="form-control" placeholder="Jumlah Product Reward">
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="kt4">
                            <label class="col-sm-12 col-form-label">Satuan Product Reward</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="product_satuan" readonly>
                            </div>
                        </div>
                        <div class="form-group row" style="display:none;" id="kt5">
                            <div class="col-sm-12 ml-4">
                                <input class="form-check-input" id="reward_choose" name="reward_choose" type="checkbox" @if($promos->reward_choose == 1) checked @endif value="1"><label class="form-check-label ml-2">Check jika reward bisa memilih</label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-blue float-right btn-reward" onclick="setReward()" style="margin-top:20px;width:200px;display:none;">Tambah Reward</button>
                    </div>
                    <div class="col-md-12">
                        <h4 style="margin-top:20px;display:none;" id="tabelRewardTitle">List Reward</h4>
                        <table class="table default-table" id="tabelReward" style="display:none;">
                            <thead>
                                <tr align="center">
                                    <td>Name</td>
                                    <td>Qty</td>
                                    <td>Satuan</td>
                                    <td width="10">Action</td>
                                </tr>
                            </thead>
                            <tbody id="tabelDinamis2">
                                @foreach ($promos->reward as $row_reward)
                                    @if($row_reward->reward_product_id != null)
                                        <tr align="center" id="rowreward{{$loop->iteration}}">
                                            <td style="display:none;"><input type="text" style="outline:none;border:0;" name="product_reward_id[]" id="product_reward_id" value="{{$row_reward->reward_product_id}}"></td>
                                            <td><h6>{{ $row_reward->product->name }}</h6><input type="hidden" style="outline:none;border:0;" name="product_reward[]" id="product_reward" value="{{ $row_reward->product->name }}"></td>
                                            <td><h6>{{ $row_reward->reward_qty }}</h6><input type="hidden" style="outline:none;border:0;" name="product_reward_qty[]" id="product_reward_qty" value="{{ $row_reward->reward_qty }}"></td>
                                            <td><h6>{{ $row_reward->satuan }}</h6><input type="hidden" style="outline:none;border:0;" name="satuan_qty[]" id="satuan_qty" value="{{ $row_reward->satuan }}"></td>
                                            <td><button type="button" id="{{$loop->iteration}}" class="btn btn-danger btn-small remove_row_reward">&times;</button></td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Highlight</label>
                            <div class="col-sm-12">
                                <textarea class="editor" name="highlight">{{ $promos->highlight }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                    <h4 style="margin-top:20px;">List Product SKU</h4>
                    <table class="table default-table" id="tabelProduct">
                        <thead>
                            <tr align="center">
                                <td>Name</td>
                                <td>Brand</td>
                                <td>Qty</td>
                                <td>Satuan</td>
                                <td width="10">Action</td>
                            </tr>
                        </thead>
                        <tbody id="tabelDinamis">
                            @foreach ($promos->sku as $row_sku)
                                <tr class="hapus-all" align="center" id="row{{ $loop->iteration }}" data-check="checkbox{{ $row_sku->product->id }}">
                                    <td style="display:none;"><input type="text" style="outline:none;border:0;" name="product_id[]" id="product_id" value="{{ $row_sku->product_id }}"></td>
                                    <td><h6>{{ $row_sku->product->name }}</h6><input type="hidden" style="outline:none;border:0;" name="name[]" id="name" value="{{ $row_sku->product->name }}"></td>
                                    <td><h6>{{ ucwords(strtolower($row_sku->product->brand)) }}</h6><input type="hidden" style="outline:none;border:0;" name="brand[]" id="brand" value="{{ $row_sku->product->brand }}"></td>
                                    <td><input type="number" class="form-control input-qty" style="border:0;" name="product_min_qty[]" id="qty" placeholder="Qty" value="{{ $row_sku->min_qty }}" @if($promos->category != null && $promos->detail_termcondition != 2) readonly disabled @endif></td>
                                    <td><input type="text" class="form-control input-satuan" id="satuan" name="satuan[]" value="@if($row_sku->satuan != null) {{ $row_sku->satuan }} @else {{ $row_sku->product->satuan_online }} @endif" @if($promos->category != null && $promos->detail_termcondition != 2) disabled @endif readonly></td>
                                    <td><button type="button" id="{{ $loop->iteration }}" class="btn btn-danger btn-small" onclick="hapus('{{$loop->iteration}}','{{$row_sku->product_id}}')">&times;</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table><button type="button" class="btn btn-danger btn-small btn-remove-all" style="float:right;margin-top:5px;">Delete all</button>
                <button class="btn btn-blue float-right" style="position:absolute;right:50px;margin-top:60px;width:200px;">Save</button>
            </form>
            <hr style="margin-top:120px;">
            <h4 style="margin-top:20px; margin-bottom:20px;">Custom Product SKU</h4>
            <div class="row col-md-4 mb-4">
                <div class="col-md-6">
                    <select class="form-control" aria-label="Default select example" name="product_sku" id="product_sku"></select>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-blue btn-sku" onclick="setSku()">Tambah Product</button>
                </div>
            </div>
            <div class="row">
                @foreach($brand as $row => $value)
                    <div class="ml-3 mr-2 col-xs-6">
                        <div class="form-group">
                            <button class="btn btn-neutral btn-show" data-id="{{$value}}" data-name="{{$row}}">Pilih {{ ucwords($row) }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <hr>
            <div class="row">
                @foreach($groups as $row => $value)
                    <div class="ml-3 mr-2 col-xs-6">
                        <div class="form-group">
                            <button class="btn btn-neutral btn-show-sub-group" data-id="{{$value}}" data-name="{{$row}}">Pilih {{ ucwords(strtolower($row)) }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- <div class="row">
                <div class="ml-3 mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral btn-group" data-id="G0101">Pilih D1</button>
                    </div>
                </div>
                @foreach($D1 as $row => $value) 
                    <div class="mr-2 col-xs-6">
                        <div class="form-group">
                            <button class="btn btn-neutral btn-sub-group" data-id="{{$value}}" data-name="{{$row}}">{{ ucwords(strtolower($row))}}</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="ml-3 mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral btn-group">Pilih D2</button>
                    </div>
                </div>
                @foreach($D2 as $row => $value) 
                    <div class="mr-2 col-xs-6">
                        <div class="form-group">
                            <button class="btn btn-neutral btn-sub-group" data-id="{{$value}}" data-name="{{$row}}">{{ ucwords(strtolower($row))}}</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="ml-3 mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral btn-group">Pilih D3</button>
                    </div>
                </div>
                @foreach($D3 as $row => $value) 
                    <div class="mr-2 col-xs-6">
                        <div class="form-group">
                            <button class="btn btn-neutral btn-sub-group" data-id="{{$value}}" data-name="{{$row}}">{{ ucwords(strtolower($row))}}</button>
                        </div>
                    </div>
                @endforeach
            </div> --}}
            <hr>
                <div id="list-sub-group" class="row">

                </div>
            <hr>
            {{-- <div class="row">
                <div class="ml-3 mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Freshcare</button>
                    </div>
                </div>
                <div class="mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Hotin</button>
                    </div>
                </div>
                <div class="mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Madu</button>
                    </div>
                </div>
                <div class="mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Mywell</button>
                    </div>
                </div>
                <div class="mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Tresnojoyo</button>
                    </div>
                </div>
            </div>
            <hr> --}}
            {{-- <div class="row">
                <div class="ml-3 mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Pilkita</button>
                    </div>
                </div>
                <div class="mr-2 col-xs-6">
                    <div class="form-group">
                        <button class="btn btn-neutral">Pilih Other Marguna</button>
                    </div>
                </div>
            </div> --}}
            {{-- <hr> --}}
            <h5 class="mb-3"><u id="title"></u></h5>

            <div id="list-item" class="row">

            </div>
        </div>
    </section>
    <script>
        //Function onchange untuk menampilkan dan menghilangkan kolom inputan sesuai dengan syarat dan ketentuan yg dipilih
        function showsnk() {
            var snk = $('#snk').val();
            $('#min').prop('selectedIndex', 0);
            $('#all_min_qty').val("");
            $('#min_transaction').val("");
            $('#multiple').prop('checked', false); 
            if(snk == 1) {
                $('#snk1').show();
                $('#snk2').hide();
                $('#snk3').hide();
                $('.div-multiple').css('visibility', 'visible');
                $('.input-satuan').removeAttr("disabled");
                $('.input-qty').removeAttr("disabled readonly");
            } else if (snk == 2) {
                $('#snk1').hide();
                $('#min1').hide();
                $('#min2').hide();
                $('#snk2').show();
                $('#snk3').show();
                $('.div-multiple').css('visibility', 'hidden');
                $('.input-satuan').attr("disabled", true);
                $('.input-qty').attr({"disabled":true, "readonly":true});
            } else if (snk == 3) {
                $('#snk1').show();
                $('#snk2').show();
                $('#snk3').hide();
                $('.div-multiple').css('visibility', 'visible');
                $('.input-satuan').removeAttr("disabled");
                $('.input-qty').removeAttr("disabled readonly");
            } else {
                $('.input-satuan').removeAttr("disabled");
                $('.input-qty').removeAttr("disabled readonly");
                $('#snk1').hide();
                $('#snk2').hide();
                $('#snk3').hide();
                $('.div-multiple').css('visibility', 'hidden');
                $('.input-satuan').attr("disabled", true);
                $('.input-qty').attr({"disabled":true, "readonly":true});
            }
        }

        //Function onchange untuk menampilkan dan menghilangkan dropdown class dan type
        function set_class_type() {
            var ct = $('#class_type').val();
            $('#ct_class').prop('selectedIndex', 0);
            $('#ct_type').prop('selectedIndex', 0);
            if(ct == 1) {
                $('#ct1').show();
                $('#ct2').show();
            } else if(ct == 2) {
                $('#ct1').show();
                $('#ct2').hide();
            } else if(ct == 3) {
                $('#ct1').hide();
                $('#ct2').show();
            } else {
                $('#ct1').hide();
                $('#ct2').hide();
            }
        }
        
        //Function onchange untuk menampilkan dan menghilangkan kolom inputan sesuai dengan kategori reward yg dipilih
        function showkt() {
            var kt = $('#kt').val();
            $('#nom').prop('selectedIndex', 0);
            $('#point').val("");
            $('#diskon').val("");
            $('#potongan').val("");
            $('#reward_disc').val("");
            $('#select_product_reward').empty();
            $('#product_reward_qty').val("");
            $('#product_satuan').val("");
            $('#reward_choose').prop('checked', false); 
            if($('#kt').val() == "1") {
                $('#tabelReward').find("tr:not(:first)").remove();
            }
            if(kt == 1) {
                $('#kt1').show();
                $('#kt2').hide();
                $('#kt3').hide();
                $('#kt4').hide();
                $('#kt5').hide();
                $('.btn-reward').hide();
                $('#tabelReward').hide();
                $('#tabelRewardTitle').hide();
            } else if (kt == 2) {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
                $('#kt1').hide();
                $('#kt2').show();
                $('#kt3').show();
                $('#kt4').show();
                $('#kt5').show();
                $('.btn-reward').show();
                $('#tabelReward').show();
                $('#tabelRewardTitle').show();
            } else if(kt == 3) {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
                $('#kt1').show();
                $('#kt2').show();
                $('#kt3').show();
                $('#kt4').show();
                $('#kt5').show();
                $('.btn-reward').show();
                $('#tabelReward').show();
                $('#tabelRewardTitle').show();
            } else {
                $('#kt1').hide();
                $('#kt2').hide();
                $('#kt3').hide();
                $('#kt4').hide();
                $('#kt5').hide();
                $('.btn-reward').hide();
                $('#tabelReward').hide();
                $('#tabelRewardTitle').hide();
            }
        }
        
        //Function onchange untuk menampilkan dan menghilangkan kolom inputan sesuai dengan kategori reward nominal yg dipilih
        function shownom() {
            var nom = $('#nom').val();
            $('#point').val("");
            $('#diskon').val("");
            $('#potongan').val("");
            if(nom == 1) {
                $('#nom1').show();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
            } else if (nom == 2) {
                $('#nom1').hide();
                $('#nom2').show();
                $('#nom3').hide();
                $('#nom4').show();
            } else if(nom == 3) {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').show();
                $('#nom4').hide();
            } else {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
            }
        }
        
        //Function onchange untuk menampilkan dan menghilangkan kolom inputan sesuai dengan minimal product syarat dan ketentuan yg dipilih
        function showmin() {
            var min = $('#min').val();
            $('#all_min_qty').val("");
            if(min == 1) {
                $('.input-satuan').attr("disabled", true);
                $('.input-qty').val("");
                $('.input-qty').attr({"disabled":true, "readonly":true});
                $('#min1').show();
                $('#min2').hide();
            } else if (min == 2) {
                $('.input-satuan').removeAttr("disabled");
                $('.input-qty').removeAttr("disabled readonly");
                $('#min1').hide();
                $('#min2').hide();
            } else if (min == 3) {
                $('.input-satuan').attr("disabled", true);
                $('.input-qty').val("");
                $('.input-qty').attr({"disabled":true, "readonly":true});
                $('#min1').hide();
                $('#min2').show();
            } else {
                $('#min1').hide();
                $('#min2').hide();
            }
        }

        //Class btn-show pada button brand, onclick akan menampilkan product dari brand tersebut
        $('.btn-show').on('click', function(){
            var uid = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            showProduct(uid, name);
            // console.log(uid);
        })

        //Class btn-show-sub-group pada button group, onclick akan menampilkan subgroup dari group tersebut
        $('.btn-show-sub-group').on('click', function(){
            $('#title').html("");
            $('#list-item').html("");
            var uid = $(this).attr('data-id');
            showSubGroup(uid);
            // console.log(uid);
        })

        //Function untuk memasukkan seluruh data product dari seluruh group dengan menekan tombol pilih semua pada barisan subgroup
        function allSubGroup() {
            $('#title').html("");
            $('#list-item').html("");
            if($('.all-sub-group').attr("class") == "btn btn-neutral all-sub-group" || $('.all-sub-group').attr("class") == "btn all-sub-group btn-neutral") {
                $('.btn-show-sub-group-product').removeClass("btn-neutral");
                $('.btn-show-sub-group-product').addClass("btn-blue");
                $('.all-sub-group').removeClass("btn-neutral");
                $('.all-sub-group').addClass("btn-blue");
                var id = $('.all-sub-group').attr("data-id");
                $.ajax({
                    type: "GET",
                    url: @if(auth()->user()->account_role == 'manager')
                                "{{url('manager/promo/set-list-all?group_id=')}}"+id 
                            @elseif(auth()->user()->account_role == 'superadmin') 
                                "{{url('superadmin/promo/set-list-all?group_id=')}}"+id
                            @elseif(auth()->user()->account_role == 'admin') 
                                "{{url('admin/promo/set-list-all?group_id=')}}"+id  
                        @endif,
                    mimeType: "multipart/form-data",
                    beforeSend: function () {

                    },
                    success: function (response) {
                        var resp = JSON.parse(response).data;
                        var html = "";
                        var title = name.toUpperCase();

                        if(JSON.parse(response).status=="1"){
                            Object.keys(resp).forEach(function(key) {
                                setList(resp[key].id, resp[key].name, resp[key].brand, resp[key].satuan_online);
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        setTimeout(function () {
                            console.log(xhr.responseText)
                        }, 2000);
                    }
                });
            } else if ($('.all-sub-group').attr("class") == "btn all-sub-group btn-blue") {
                $('.btn-show-sub-group-product').removeClass("btn-blue");
                $('.btn-show-sub-group-product').addClass("btn-neutral");
                $('.all-sub-group').removeClass("btn-blue");
                $('.all-sub-group').addClass("btn-neutral");
                var id = $('.all-sub-group').attr("data-id");
                $.ajax({
                    type: "GET",
                    url: @if(auth()->user()->account_role == 'manager')
                                "{{url('manager/promo/set-list-all?group_id=')}}"+id 
                            @elseif(auth()->user()->account_role == 'superadmin') 
                                "{{url('superadmin/promo/set-list-all?group_id=')}}"+id
                            @elseif(auth()->user()->account_role == 'admin') 
                                "{{url('admin/promo/set-list-all?group_id=')}}"+id  
                        @endif,
                    mimeType: "multipart/form-data",
                    beforeSend: function () {

                    },
                    success: function (response) {
                        var resp = JSON.parse(response).data;
                        var html = "";
                        var title = name.toUpperCase();

                        if(JSON.parse(response).status=="1"){
                            Object.keys(resp).forEach(function(key) {
                                hapusAll(resp[key].id);
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        setTimeout(function () {
                            console.log(xhr.responseText)
                        }, 2000);
                    }
                });
            }
        };

        //Function untuk menampilkan product berdasarkan id brand yang dipilih
        function showProduct(id, name)
        {
            $.ajax({
                type: "GET",
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/promo/list-product?brand_id=')}}"+id 
                        @elseif(auth()->user()->account_role == 'superadmin')
                            "{{url('superadmin/promo/list-product?brand_id=')}}"+id
                        @elseif(auth()->user()->account_role == 'admin')
                            "{{url('admin/promo/list-product?brand_id=')}}"+id 
                    @endif,
                mimeType: "multipart/form-data",
                beforeSend: function () {

                },
                success: function (response) {
                    var resp = JSON.parse(response).data;
                    var html = "<div class='col-xl-4 col-lg-4 col-xs-6'><div class='form-check'><input class='form-check-input checkbox-remove' id='checkbox-all' type='checkbox' value='"+id+"' data-id='"+id+"' onclick=\"setListBrand('"+id+"')\"><label class='form-check-label'>Pilih Semua</label></div></div>";
                    var title = name.toUpperCase();

                    if(JSON.parse(response).status=="1"){
                        for(var i=0; i<resp.length; i++){
                            // html = html+"<div class='status status-info' style='margin-bottom: 3px'> <a href='javascript:void(0)' class='btn btn-xs btn-danger btn-remove-to' data-id='"+resp[i].id+"'>x</a> "+resp[i].name+"</div>"
                            html = html+"<div class='col-xl-4 col-lg-4 col-xs-6'><div class='form-check'><input class='form-check-input checkbox-remove' id='checkbox"+resp[i].id+"' type='checkbox' value='"+resp[i].id+"' data-id='"+resp[i].id+"' data-brand='"+resp[i].brand+"' data-name='"+resp[i].name+"' onclick=\"setList('"+resp[i].id+"','"+resp[i].name+"','"+resp[i].brand+"','"+resp[i].satuan_online+"')\"><label class='form-check-label'>"+resp[i].name+"</label></div></div>"
                        }
                    }
                    $('#title').html(title);
                    $('#list-item').html(html);
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
            });
        }

        //Function untuk menampilkan subgroup berdasarkan id dari group
        function showSubGroup(id)
        {
            $.ajax({
                type: "GET",
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/promo/list-sub-group?group_id=')}}"+id 
                        @elseif(auth()->user()->account_role == 'superadmin')
                            "{{url('superadmin/promo/list-sub-group?group_id=')}}"+id
                        @elseif(auth()->user()->account_role == 'admin')
                            "{{url('admin/promo/list-sub-group?group_id=')}}"+id  
                    @endif,
                mimeType: "multipart/form-data",
                beforeSend: function () {

                },
                success: function (response) {
                    var resp = JSON.parse(response).data;
                    var html = "<div class='ml-3 mr-2 col-xs-6'><div class='form-group'><button class='btn btn-neutral all-sub-group' data-id='"+ id +"' data-name='all' onclick='allSubGroup()'>Pilih Semua</button></div></div>";

                    if(JSON.parse(response).status=="1"){
                        Object.keys(resp).forEach(function(key) {
                                html = html+"<div class='ml-3 mr-2 col-xs-6'><div class='form-group'><button class='btn btn-neutral btn-show-sub-group-product' data-id='"+ resp[key].subgroup +"' data-name='"+ resp[key].nama_sub_group +"' onclick=\"showSubGroupProduct(this, '"+resp[key].subgroup+"','"+resp[key].nama_sub_group+"')\">Pilih "+ resp[key].nama_sub_group.toLowerCase().replace(/(?<= )[^\s]|^./g, a=>a.toUpperCase()) +"</button></div></div>"
                            });
                    }
                    $('#list-sub-group').html(html);
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
            });
        }

        //Function untuk menampilkan subgroup product berdasarkan id subgroup
        function showSubGroupProduct(obj, id, name)
        {
            if($('.all-sub-group').attr("class") == "btn all-sub-group btn-blue") {
                if($(obj).attr("class") == "btn btn-show-sub-group-product btn-blue") {
                    $(obj).removeClass("btn-blue");
                    $(obj).addClass("btn-neutral");
                    var id = $(obj).attr("data-id");
                    $.ajax({
                        type: "GET",
                        url: @if(auth()->user()->account_role == 'manager')
                                    "{{url('manager/promo/set-list-sub-group?subgroup=')}}"+id 
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    "{{url('superadmin/promo/set-list-sub-group?subgroup=')}}"+id
                                @elseif(auth()->user()->account_role == 'admin')
                                    "{{url('admin/promo/set-list-sub-group?subgroup=')}}"+id  
                            @endif,
                        mimeType: "multipart/form-data",
                        beforeSend: function () {
                        },
                        success: function (response) {
                            var resp = JSON.parse(response).data;
                            var html = "";
                            var title = name.toUpperCase();

                            if(JSON.parse(response).status=="1"){
                                Object.keys(resp).forEach(function(key) {
                                    hapusAll(resp[key].id);
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            setTimeout(function () {
                                console.log(xhr.responseText)
                            }, 2000);
                        }
                    });
                } else if ($(obj).attr("class") == "btn btn-show-sub-group-product btn-neutral" || $(obj).attr("class") == "btn btn-neutral btn-show-sub-group-product") {
                    $(obj).removeClass("btn-neutral");
                    $(obj).addClass("btn-blue");
                    var id = $(obj).attr("data-id");
                    $.ajax({
                        type: "GET",
                        url: @if(auth()->user()->account_role == 'manager')
                                    "{{url('manager/promo/set-list-sub-group?subgroup=')}}"+id 
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    "{{url('superadmin/promo/set-list-sub-group?subgroup=')}}"+id  
                                @elseif(auth()->user()->account_role == 'admin')
                                    "{{url('admin/promo/set-list-sub-group?subgroup=')}}"+id 
                            @endif,
                        mimeType: "multipart/form-data",
                        beforeSend: function () {

                        },
                        success: function (response) {
                            var resp = JSON.parse(response).data;
                            var html = "";
                            var title = name.toUpperCase();

                            if(JSON.parse(response).status=="1"){
                                Object.keys(resp).forEach(function(key) {
                                    setList(resp[key].id, resp[key].name, resp[key].brand, resp[key].satuan_online);
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            setTimeout(function () {
                                console.log(xhr.responseText)
                            }, 2000);
                        }
                    });
                }
            } else if ($('.all-sub-group').attr("class") == "btn all-sub-group btn-neutral" || $('.all-sub-group').attr("class") == "btn btn-neutral all-sub-group") {
                $.ajax({
                    type: "GET",
                    url: @if(auth()->user()->account_role == 'manager')
                                "{{url('manager/promo/sub-group-product?subgroup=')}}"+id 
                            @elseif(auth()->user()->account_role == 'superadmin')
                                "{{url('superadmin/promo/sub-group-product?subgroup=')}}"+id
                            @elseif(auth()->user()->account_role == 'admin')
                                "{{url('admin/promo/sub-group-product?subgroup=')}}"+id  
                        @endif,
                    mimeType: "multipart/form-data",
                    beforeSend: function () {

                    },
                    success: function (response) {
                        var resp = JSON.parse(response).data;
                        var html = "";
                        var title = name.toUpperCase();

                        if(JSON.parse(response).status=="1"){
                            Object.keys(resp).forEach(function(key) {
                                html = html+"<div class='col-xl-4 col-lg-4 col-xs-6'><div class='form-check'><input class='form-check-input checkbox-remove' id='checkbox"+resp[key].id+"' type='checkbox' value='"+resp[key].id+"' data-id='"+resp[key].id+"' data-brand='"+resp[key].brand+"' data-name='"+resp[key].name+"' onclick=\"setList('"+resp[key].id+"','"+resp[key].name+"','"+resp[key].brand+"','"+resp[key].satuan_online+"')\"><label class='form-check-label'>"+resp[key].name+"</label></div></div>"
                            });
                        }
                        $('#title').html(title);
                        $('#list-item').html(html);
                    },
                    error: function (xhr, status, error) {
                        setTimeout(function () {
                            console.log(xhr.responseText)
                        }, 2000);
                    }
                });
            }
        }
    </script>
    
    @if(!empty(Session::get('status')) && Session::get('status') == 1)
    <script>
        showNotif("{{Session::get('message')}}");
    </script>
    @endif
    <script>
        $(document).ready(function () {
            if($('#class_type').val() == 1) {
                $('#ct1').show();
                $('#ct2').show();
            } else if($('#class_type').val() == 2) {
                $('#ct1').show();
                $('#ct2').hide();
            } else if($('#class_type').val() == 3) {
                $('#ct1').hide();
                $('#ct2').show();
            } else {
                $('#ct1').hide();
                $('#ct2').hide();
            }

            //Mengecek data termcondition dari database dan menampilkan kolom inputan sesuai data
            if({{$promos->termcondition}} == 1) {
                $('#snk1').show();
                $('#snk2').hide();
                $('#snk3').hide();
                $('.div-multiple').css('visibility', 'visible');
            } else if ({{$promos->termcondition}} == 2) {
                $('#snk1').hide();
                $('#min1').hide();
                $('#min2').hide();
                $('#snk2').show();
                $('#snk3').show();
                $('.div-multiple').css('visibility', 'hidden');
            } else if ({{$promos->termcondition}} == 3) {
                $('#snk1').show();
                $('#snk2').show();
                $('#snk3').show();
                $('.div-multiple').css('visibility', 'visible');
            } else {
                $('#snk1').hide();
                $('#snk2').hide();
                $('#snk3').hide();
                $('.div-multiple').css('visibility', 'hidden');
            }

            //Mengecek data detai_termcondition dari database dan menampilkan kolom inputan sesuai data
            @isset($promos->detail_termcondition)
            if({{$promos->detail_termcondition}} == 1) {
                $('.input-satuan').attr("disabled", true);
                $('.input-qty').attr({"disabled":true, "readonly":true});
                $('#min1').show();
                $('#min2').hide();
            } else if ({{$promos->detail_termcondition}} == 2) {
                $('.input-satuan').removeAttr("disabled");
                $('.input-qty').removeAttr("disabled readonly");
                $('#min1').hide();
                $('#min1').hide();
            } else if({{$promos->detail_termcondition}} == 3) {
                $('.input-satuan').attr("disabled", true);
                $('.input-qty').attr({"disabled":true, "readonly":true});
                $('#min1').hide();
                $('#min2').show();
            } else {
                $('#min1').hide();
                $('#min2').hide();
            }
            @endisset

            //Mengecek data category dari database dan menampilkan kolom inputan sesuai data
            if({{$promos->category}} == 1) {
                $('#kt1').show();
                $('#kt2').hide();
                $('#kt3').hide();
                $('#kt4').hide();
                $('#kt5').hide();
                $('.btn-reward').hide();
                $('#tabelReward').hide();
                $('#tabelRewardTitle').hide();
            } else if ({{$promos->category}} == 2) {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
                $('#kt1').hide();
                $('#kt2').show();
                $('#kt3').show();
                $('#kt4').show();
                $('#kt5').show();
                $('.btn-reward').show();
                $('#tabelReward').show();
                $('#tabelRewardTitle').show();
            } else if({{$promos->category}} == 3) {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
                $('#kt1').show();
                $('#kt2').show();
                $('#kt3').show();
                $('#kt4').show();
                $('#kt5').show();
                $('.btn-reward').show();
                $('#tabelReward').show();
                $('#tabelRewardTitle').show();
            } else {
                $('#kt1').hide();
                $('#kt2').hide();
                $('#kt3').hide();
                $('#kt4').hide();
                $('#kt5').hide();
                $('.btn-reward').hide();
                $('#tabelReward').hide();
                $('#tabelRewardTitle').hide();
            }
            
            //Mengecek data detail_category dari database dan menampilkan kolom inputan sesuai data
            @isset($promos->detail_category)
            if({{$promos->detail_category}} == 1) {
                $('#nom1').show();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
            } else if ({{$promos->detail_category}} == 2) {
                $('#nom1').hide();
                $('#nom2').show();
                $('#nom3').hide();
                $('#nom4').show();
            } else if({{$promos->detail_category}} == 3) {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').show();
                $('#nom4').hide();
            } else {
                $('#nom1').hide();
                $('#nom2').hide();
                $('#nom3').hide();
                $('#nom4').hide();
            }
            @endisset

            $('#select_product_reward').select2({
                placeholder: "Pilih Product",
                ajax: {
                    url: @if(auth()->user()->account_role == 'manager')
                                "{{url('manager/promo/all-product')}}"
                            @elseif(auth()->user()->account_role == 'superadmin')
                                "{{url('superadmin/promo/all-product')}}"
                            @elseif(auth()->user()->account_role == 'admin')
                                "{{url('admin/promo/all-product')}}"
                        @endif,
                    type: "GET",
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
                    cache: true
                }
            });

            $('#product_sku').select2({
                placeholder: "Pilih Product",
                ajax: {
                    url: "{{url('manager/promo/all-product')}}",
                    type: "GET",
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data){
                        return {
                            results: $.map(data, function(item){
                                return {
                                    text: item.name,
                                    id: item.id +"#"+ item.satuan_online
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
            
            $('#select_product_reward').on('change', function() {
                var id_product = this.value;
                $('#product_satuan').html();
                $.ajax({
                    url: @if(auth()->user()->account_role == 'manager')
                                "{{url('manager/promo/satuan-product')}}" 
                            @elseif(auth()->user()->account_role == 'superadmin')
                                "{{url('superadmin/promo/satuan-product')}}"
                            @elseif(auth()->user()->account_role == 'admin')
                                "{{url('admin/promo/satuan-product')}}"  
                        @endif,
                    type: "POST",
                    data: {
                        id: id_product,
                        _token: '{{csrf_token()}}'
                    },
                    dataType: 'json',
                    success: function (result) {
                        $('#product_satuan').html();
                        $.each(result.satuan, function (key, value) {
                            $("#product_satuan").val(value.satuan_online);
                        });
                    }
                })
            })
        });

        //Function untuk menghapus seluruh isi pada tabel List Product
        $('.btn-remove-all').on('click', function(){
            $('#tabelProduct').find("tr:not(:first)").remove();
            $('.btn-show-sub-group-product').removeClass("btn-blue");
            $('.btn-show-sub-group-product').addClass("btn-neutral");
            $('.all-sub-group').removeClass("btn-blue");
            $('.all-sub-group').addClass("btn-neutral");
            $(".checkbox-remove").prop('checked', false); 
        })

        //Function untuk menampilkan seluruh product dari salah satu brand dengan id
        function setListBrand(id) {
            $.ajax({
                type: "GET",
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/promo/list-product?brand_id=')}}"+id 
                        @elseif(auth()->user()->account_role == 'superadmin')
                            "{{url('superadmin/promo/list-product?brand_id=')}}"+id  
                        @elseif(auth()->user()->account_role == 'admin')
                            "{{url('admin/promo/list-product?brand_id=')}}"+id
                    @endif,
                mimeType: "multipart/form-data",
                beforeSend: function () {

                },
                success: function (response) {
                    var resp = JSON.parse(response).data;
                    var html = "";

                    if(JSON.parse(response).status=="1"){
                        Object.keys(resp).forEach(function(key) {      
                            brand = resp[key].brand.toLowerCase().replace(/(?<= )[^\s]|^./g, a=>a.toUpperCase()); 

                            if($('#checkbox-all').is(':checked')) {
                                addProductSKU(resp[key].id, resp[key].name, brand, resp[key].satuan_online);
                            } else {
                                hapusAll(resp[key].id);
                            }
                        });
                    }
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
            });
        }

        //Function untuk menambahkan data ke tabel List Product
        function setList(id, name, brand, satuan_online) {
            brand = brand.toLowerCase().replace(/(?<= )[^\s]|^./g, a=>a.toUpperCase());
            var product_id = $('#product_id').val();
            $('#checkbox' + id + '').attr("disabled", true);
		    addProductSKU(id, name, brand, satuan_online);
        };

        var i = {{ $promos->sku->count() }};

        //Function untuk menambahkan data ke tabel List Product
        function addProductSKU(id, name, brand, satuan_online) {
            i++;
            if($('#snk').val() == 2 || $('#min').val() == 1) {
                $('#tabelDinamis').append('<tr class="hapus-all" align="center" id="row' + i + '" data-check="checkbox'+id+'"></td><td style="display:none;"><input type="text" style="outline:none;border:0;" name="product_id[]" id="product_id" value="' + id + 
                                                        '"></td><td><h6>'+name+'</h6><input type="hidden" style="outline:none;border:0;" name="name[]" id="name" value="' + name + 
                                                        '"></td><td><h6>'+brand+'</h6><input type="hidden" style="outline:none;border:0;" name="brand[]" id="brand" value="' + brand + 
                                                        '"></td><td><input type="number" class="form-control input-qty" style="border:0;" name="product_min_qty[]" id="qty" placeholder="Qty" disabled readonly>'+
                                                        '</td><td><input type="text" class="form-control input-satuan" id="satuan" name="satuan[]" value="' + satuan_online + 
                                                        '" disabled readonly></td><td><button type="button" id="' + i + '" class="btn btn-danger btn-small" onclick=\"hapus('+i+','+id+')\">&times;</button></td></tr>');
            } else {
                $('#tabelDinamis').append('<tr class="hapus-all" align="center" id="row' + i + '" data-check="checkbox'+id+'"></td><td style="display:none;"><input type="text" style="outline:none;border:0;" name="product_id[]" id="product_id" value="' + id + 
                                                        '"></td><td><h6>'+name+'</h6><input type="hidden" style="outline:none;border:0;" name="name[]" id="name" value="' + name + 
                                                        '"></td><td><h6>'+brand+'</h6><input type="hidden" style="outline:none;border:0;" name="brand[]" id="brand" value="' + brand + 
                                                        '"></td><td><input type="number" class="form-control input-qty" style="border:0;" name="product_min_qty[]" id="qty" placeholder="Qty">'+
                                                        '</td><td><input type="text" class="form-control input-satuan" id="satuan" name="satuan[]" value="' + satuan_online + 
                                                        '" readonly></td><td><button type="button" id="' + i + '" class="btn btn-danger btn-small" onclick=\"hapus('+i+','+id+')\">&times;</button></td></tr>');
            }
        };

        //Function untuk menghapus data dari tabel List Product
        function hapus(row_id, checkbox_id) {
            $('#checkbox' + checkbox_id + '').prop('checked', false).removeAttr("disabled");
            $('#row' + row_id + '').remove();
        };

        //Function untuk menghapus data dari tabel List Product
        function hapusAll(checkbox_id) {
            var id = $('.hapus-all[data-check="checkbox'+checkbox_id+'"]');
            row_id = id[0].attributes[2].value;
            $('#'+ row_id +'').remove();
        };

        // $(document).on('click', '.remove_row', function() {
        //     var row_id = $(this).attr("id");
        //     var data_check = $(this).attr("data-check");
        //     console.log(data_check);
        //     $('#checkbox' + data_checkbox + '').removeAttr("disabled");
        //     $('#row' + row_id + '').remove();
        // });

        //Function untuk menambahkan product pada tabel List Reward
        function setReward() {
            // var id2 = document.getElementById('product_reward').value;
            var product_reward_id = $('#select_product_reward').val();
            // var product_reward = document.getElementById('select2-product_reward-container').innerHTML;
            var product_reward = $('#select2-select_product_reward-container').html();
            // var product_satuan = document.getElementById('product_satuan').value;
            var product_satuan = $('#product_satuan').val();
            // var product_reward_qty = document.getElementById('product_reward_qty').value;
            var product_reward_qty = $('#product_reward_qty').val();
		    addProductReward(product_reward_id, product_reward, product_satuan, product_reward_qty);
            $('#select_product_reward').empty();
            $('#product_satuan').val('');
            $('#product_reward_qty').val('');
        };
        
        function setSku() {
            // var id2 = document.getElementById('product_reward').value;
            var product_sku = $('#product_sku').val().split("#");
            var product_sku_id = product_sku[0];
            var product_sku_satuan = product_sku[1];
            // var product_reward = document.getElementById('select2-product_reward-container').innerHTML;
            var product_sku_name = $('#select2-product_sku-container').html();
		    addProductSKU(product_sku_id, product_sku_name, "", product_sku_satuan);
            $('#product_sku').empty();
        };

        var j = {{ $promos->reward->count() }};

        //Function untuk menambahkan product pada tabel List Reward
        function addProductReward(product_reward_id, product_reward, product_satuan, product_reward_qty) {
            j++;
            $('#tabelDinamis2').append('<tr align="center" id="rowreward' + j + '"><td style="display:none;"><input type="text" style="outline:none;border:0;" name="product_reward_id[]" id="product_reward_id" value="' + product_reward_id + 
                                                        '"></td><td><h6>'+product_reward+'</h6><input type="hidden" style="outline:none;border:0;" name="product_reward[]" id="product_reward" value="' + product_reward + 
                                                        '"></td><td><h6>'+product_reward_qty+'</h6><input type="hidden" style="outline:none;border:0;" name="product_reward_qty[]" id="product_reward_qty" value="' + product_reward_qty + 
                                                        '"></td><td><h6>'+product_satuan+'</h6><input type="hidden" style="outline:none;border:0;" name="satuan_qty[]" id="satuan_qty" value="'+product_satuan+'"></td><td><button type="button" id="' + j + '" class="btn btn-danger btn-small remove_row_reward">&times;</button></td></tr>');
        };

        $(document).on('click', '.remove_row_reward', function() {
            var row_id2 = $(this).attr("id");
            $('#rowreward' + row_id2 + '').remove();
        });
    </script>
    <style>
        .text-small{
            font-size: 8pt;
            color: red;
        }
    </style>
@endsection
