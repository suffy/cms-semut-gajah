@extends('admin.layout.template')

@section('content')

<div id="addStock" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Stok Gudang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{url('admin/store-product-stock')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                    <input type="hidden" name="product_id"  value="{{ $product->id }}" required>
                    <input type="hidden" name="price_sell"  value="{{ $product->price }}" required>
                    <input type="hidden" name="price_promo"  value="{{ $product->price_promo }}" required>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Gudang</label>
                        <div class="col-sm-12">
                            <select name="location_id" class="form-control">
                                @if( count($location) != 0)
                                    @foreach ($location as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Stock</label>
                        <div class="col-sm-12">
                            <input type="text" name="stock" class="form-control input-amount" placeholder="Stock" value="0">
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

<div id="editProductStock" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Stok Gudang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="" id="updateProductStock" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                    <input type="hidden" name="product_id"  value="{{ $product->id }}" required>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Gudang</label>
                        <div class="col-sm-12">
                            <select name="" id="input-location-id-edit" class="form-control" disabled>
                                @if( count($location) != 0)
                                    @foreach ($location as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <input type="hidden" name="location_id" value="{{ $item->id ?? '' }}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Stock</label>
                        <div class="col-sm-12">
                            <input type="text" name="stock" id="input-stock-edit" class="form-control input-amount" placeholder="Stock" value="0">
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

<a 
    href="
        @if(auth()->user()->account_role == 'manager')
            {{ url('manager/products') }}
        @elseif(auth()->user()->account_role == 'superadmin')
            {{ url('superadmin/products') }}
        @elseif(auth()->user()->account_role == 'admin')
            {{ url('admin/products') }}
        @endif
    " 
    class="btn btn-primary"
><i class="fa fa-arrow-left mr-2"></i>Kembali ke Products</a>
<a href="javascript:void(0)" class="btn btn-green" onclick="return editProduct()">Edit Product</a><br><br>

<div class="row">
    <div class="col-md-3">
        <div class="box-border">
            <div class="card-body">
                Penjualan Hari Ini
                <h3>{{ $salesToday }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box-border">
            <div class="card-body">
                Penjualan Bulan Ini
                <h3>{{ $salesLastMonth }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box-border">
            <div class="card-body">
                Penjualan Tahun Ini
                <h3>{{ $salesLastYear }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box-border">
            <div class="card-body">
                Total Penjualan
                <h3>{{ $salesTotal }}</h3>
            </div>
        </div>
    </div>
</div>

<div id="product-information">
    <div class="row" id="product-detail">
        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
            <img src="{{asset($product->image)}}" class="img-fluid">
            <hr>
            <div class="row">
            @foreach($product->product_image as $gal)
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="text-center">
                        <div class="image-outer">
                            @if($gal->path==null)
                                <img class="card-img-top img-fluid" src="{{asset('themes/assets/images/icon-folder.png')}}">
                            @else
                                <img class="card-img-top img-fluid" src="{{asset($gal->path)}}">
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
            <hr><br>
        
            @if($product->status==1)
            Status : Aktif
            <form method="post" enctype="multipart/form-data" action="{{url('admin/update-product-status')}}">
                @csrf
                <input type="hidden" name="id" value="{{$product->id}}">
                <input type="hidden" name="url" value="{{Request::url()}}">
                <input type="hidden" name="status" value="0">
                <button type="submit" class="btn btn-xs btn-red pull-right" style="margin-top: -25px" onclick="return confirm('Are you sure?')">Non Aktifkan</button>
            </form>
            @else
            Status : Tidak aktif
            <form method="post" enctype="multipart/form-data" action="{{url('admin/update-product-status')}}">
                @csrf
                <input type="hidden" name="id" value="{{$product->id}}">
                <input type="hidden" name="url" value="{{Request::url()}}">
                <input type="hidden" name="status" value="1">
                <button type="submit" class="btn btn-xs btn-green pull-right" style="margin-top: -25px" onclick="return confirm('Are you sure?')">Aktifkan</button>
            </form>
            @endif

            <hr>
            @if($product->featured==1)
                Produk : Recomended
                <form method="post" enctype="multipart/form-data" action="{{url('admin/update-product-recommendation')}}">
                    @csrf
                    <input type="hidden" name="id" value="{{$product->id}}">
                    <input type="hidden" name="url" value="{{Request::url()}}">
                    <input type="hidden" name="status" value="0">
                    <button type="submit" class="btn btn-xs btn-red pull-right" style="margin-top: -25px" onclick="return confirm('Are you sure?')">Non aktifkan</button>
                </form>
            @else
                Produk : Regular
                <form method="post" enctype="multipart/form-data" action="{{url('admin/update-product-recommendation')}}">
                    @csrf
                    <input type="hidden" name="id" value="{{$product->id}}">
                    <input type="hidden" name="url" value="{{Request::url()}}">
                    <input type="hidden" name="status" value="1">
                    <button type="submit" class="btn btn-xs btn-green pull-right" style="margin-top: -25px" onclick="return confirm('Are you sure?')">Aktifkan</button>
                </form>
            @endif
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <td colspan="2"><h4>Informasi Produk</h4></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>Kode Product</b></td>
                        <td>: {{ $product->kodeprod }}</td>
                    </tr>
                    <tr>
                        <td><b>Nama</b></td>
                        <td>: {{ $product->name }}</td>
                    </tr>
                    <tr>
                        <td><b>Kategori</b></td>
                        @if ($product->category_id != null)
                            <td>: {{ $product->category->name }}</td>
                        @else
                            <td>: -</td>
                        @endif
                    </tr>
                    <tr>
                        <td><b>Jenis</b></td>
                        <td>: {{ $product->type }}</td>
                    </tr>
                    <tr>
                        <td><b>Berat</b></td>
                        <td>: {{ $product->weight }} gr</td>
                    </tr>
                    <tr>
                        <td><b>Satuan</b></td>
                        <td>: {{ $product->satuan_online }}</td>
                    </tr>
                    <!-- <tr>
                        <td><b>Harga Beli</b></td>
                        <td>: Rp. {{ number_format($product->price_buy, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><b>Harga Jual</b></td>
                        <td>: Rp. {{ number_format($product->price_sell, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><b>Harga Normal</b></td>
                        <td>: Rp. {{ number_format($product->price_promo, 2, ',', '.') }}</td>
                    </tr> -->
                    <tr>
                        <td><b>Harga Retail</b></td>
                        <td>: Rp <span>{{$product->price->harga_ritel_gt ?? 0}}</span></td>
                    </tr>
                    <tr>
                        <td><b>Harga Grosir</b></td>
                        <td>: Rp <span>{{$product->price->harga_grosir_mt ?? 0}}</span>
                    </tr>
                    <tr>
                        <td><b>Harga Semi Grosir</b></td>
                        <td>: Rp <span>{{$product->price->harga_semi_grosir ?? 0}}</span>
                    </tr>
                </tbody>
            </table>
            <hr>

            {{-- <h4>Stok Gudang <a href="" class="btn btn-default btn-sm button-new-product-locations pull-right"
                data-product    ="{{ $product->id}}"
                data-toggle     ="modal"
                data-target     ="#addStock"
                style         ="display: inline-block;">Tambah Lokasi</a>
            </h4>
                <br>

                <div style="border: 1px solid #f1f1f1; padding: 15px">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gudang</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product_loc as $product_loc)
                            <tr>
                                <td>{{ $product_loc->location->name }}</td>
                                <td class="text-center">{{ $product_loc->stock }}</td>
                                <td class="text-center">
                                    <a href="javascript:void(0)" class="btn btn-green btn-sm button button-edit-stock"
                                    data-id ="{{ $product_loc->id }}"
                                    data-location    ="{{ $product_loc->location_id }}"
                                    data-stock       ="{{ $product_loc->stock}}"
                                    data-url      ="{{ url('admin/update-product-stock/'.$product_loc->id)}}"
                                    data-toggle      ="modal"
                                    data-target      ="#editProductStock"
                                    style            ="margin-right: 3px"><i class="fa fa-pencil"></i></a>

                                    <a href="{{url('admin/delete-product-stock/'.$product_loc->id)}}" class="btn btn-red btn-sm" onclick="return confirm('Delete Data?')"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div><br> --}}

        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
            <h4>Deskripsi Produk</h4>
            <br>
            <p>{!!$product->description!!}</p>
            <hr>

        </div>

    </div>
</div>
<div id="product-edit" style="display:none">
<form action="{{url('admin/products/'.$product->id)}}" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-xl-9 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <section id="newPost" class="panel">
                <header class="panel-heading">
                    PRODUK DETAIL
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Nama</label>
                                <div class="col-sm-12">
                                    @csrf
                                    @method('put')
                                    <input type="text" name="name" class="form-control" placeholder="Title" value="{{$product->name}}" required>
                                    <input type="hidden" name="url" value="{{Request::url()}}" required>
                                </div>
                            </div>
                            <!-- <div class="form-group row">
                                <label class="col-sm-12 col-form-label">SKU</label>
                                <div class="col-sm-12">
                                    <input type="text" name="sku" class="form-control" placeholder="SKU" value="{{$product->sku}}">
                                </div>
                            </div> -->
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Retail</label>
                                <div class="col-sm-12">
                                    <input type="text" name="harga_ritel_gt" class="form-control input-amount" placeholder="Harga Beli" value="{{$product->price->harga_ritel_gt ?? 0}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Grosir</label>
                                <div class="col-sm-12">
                                    <input type="text" name="harga_grosir_mt" class="form-control input-amount" placeholder="Harga Jual" value="{{$product->price->harga_grosir_mt ?? 0}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Semi Grosir</label>
                                <div class="col-sm-12">
                                    <input type="text" name="harga_semi_grosir" class="form-control input-amount" placeholder="Harga Jual" value="{{$product->price->harga_semi_grosir ?? 0}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Sebelum promo (Dicoret) <span class="text-small">* kosongkan jika tidak ada</span></label>
                                <div class="col-sm-12">
                                    <input type="text" name="price_promo" class="form-control input-amount" placeholder="Harga Promo" value="{{$product->price_promo}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Berat <span class="text-small">* contoh 0.85</span></label>
                                <div class="col-sm-12">
                                    <input type="text" name="weight" class="form-control" placeholder="Berat" value="{{$product->weight}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Stok</label>
                                <div class="col-sm-12">
                                    <input type="number" name="stock" class="form-control" placeholder="Stock" value="{{$product->stock}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Type</label>
                                <div class="col-sm-12">
                                    <input type="text" name="type" class="form-control" placeholder="Type" value="{{$product->type}}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Satuan Online</label>
                                <div class="col-sm-12">
                                    <input type="text" name="satuan_online" class="form-control input-amount" placeholder="Satuan Besar" value="{{ $product->satuan_online }}">
                                </div>
                            </div>
                            <!-- <div class="form-group row">
                                {{-- <label class="col-sm-12 col-form-label">Brand</label>
                                <div class="col-sm-8">
                                    <select name="brand" class="form-control">
                                        @if( count($brand) != 0)
                                            @foreach ($brand as $item)
                                                <option value="{{ $item->name }}" @if($product->brand==$item->name) selected @endif>{{ $item->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div> --}}
                                {{-- <div class="col-sm-4">
                                    <a class="btn btn-blue" href="{{url('admin/partner-logo?type=brand')}}">Tambah Brand</a>
                                </div> --}}
                            </div> -->
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Deskripsi</label>
                                <div class="col-sm-12">
                                    <textarea class="editor" name="description">{{$product->description}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
        <div class="col-xl-3 col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <section id="newPost" class="panel">
                <header class="panel-heading">
                    Publish
                </header>
                <div class="card-body">

                    <label><input type="radio" name="status" value="1" @if($product->status==1) checked @endif> Publish</label> &nbsp
                    <label><input type="radio" name="status" value="0" @if($product->status==0) checked @endif> Draft</label>
                    <hr>

                    <button type="submit" class="btn btn-blue">Update</button>
                    <a href="javascript:void(0)" class="btn btn-danger" onclick="return editProduct()">Cancel</a>

                </div>
            </section>
            <section id="newPost" class="panel">
                <header class="panel-heading">
                    Cover
                </header>
                <div class="card-body">

                    <img id="blah" src="{{asset($product->image)}}" alt="your image" class="img-fluid" /><br><br>
                    <input type='file' id="imgInp" class="file" name="image" />

                    <script>
                        function readURL(input) {
                            if (input.files && input.files[0]) {
                                var reader = new FileReader();

                                reader.onload = function(e) {
                                    $('#blah').attr('src', e.target.result);
                                }

                                reader.readAsDataURL(input.files[0]); // convert to base64 string
                            }
                        }

                        $("#imgInp").change(function() {
                            readURL(this);
                        });
                    </script>

                </div>


                <hr>

                <header class="panel-heading">
                    Category
                </header>
                <div class="card-body" style="height: 300px; overflow-x: scroll;">
                    @foreach($category as $s)
                    @if($s->category_parent==0)
                    <label><input type="radio" name="category_id" value="{{$s->id}}" required @if($product->category_id==$s->id) checked @endif> &nbsp {{$s->name}}</label><br>
                   
                    <hr style="margin-top: 5px">
                    @endif
                    @endforeach
                </div>
                <header class="panel-heading">
                    Tags
                </header>
                <div class="card-body">
                    <textarea type="text" class="form-control" id="input-tags" name="tags">{{$product->tags}}</textarea>
                    *pisahkan dengan comma ( , )
                    <div id="tag-places" style="margin-top: 10px; margin-bottom: 10px;"></div>
                </div>
            </section>
            <script>
                $('#input-tags').on('keyup', function() {
                    var result = $(this).val();
                    result = result.split(",");
                    var html = "";
                    for (var i = 0; i < result.length; i++) {
                        html += "<a href='#input-tags' class='btn status status-info' style='margin-bottom: 3px;'>" + result[i] + "</a>&nbsp";
                    }

                    $('#tag-places').html(html);

                    if (result == "") {
                        $('#tag-places').html("");
                    }
                })
            </script>

        </div>
    </div>
</form>


<div class="panel">

    <h5 class="page-heading">Upload your Images <span id="counter"></span></h5><br>
    <form method="post" action="{{ url('admin/product-multiple-upload') }}" enctype="multipart/form-data" class="dropzone" id="my-dropzone">
        @csrf
        <input type="hidden" name="product_id" value="{{$product->id}}">
        <div class="dz-message">
            <div class="col-xs-12">
                <div class="message">
                    <p>Drop files here or Click to Upload</p>
                </div>
            </div>
        </div>
        <div class="fallback">

            <input type="file" name="file" multiple>
        </div>
    </form>

    {{--Dropzone Preview Template--}}
    <div id="preview" style="display: none;">

        <div class="dz-preview dz-file-preview">
            <div class="dz-image"><img data-dz-thumbnail /></div>

            <div class="dz-details">
                <div class="dz-size"><span data-dz-size></span></div>
                <div class="dz-filename"><span data-dz-name></span></div>
            </div>
            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
            <div class="dz-error-message"><span data-dz-errormessage></span></div>
            <div class="dz-success-mark">

                <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                    <!-- Generator: Sketch 3.2.1 (9971) - http://www.bohemiancoding.com/sketch -->
                    <title>Check</title>
                    <desc>Created with Sketch.</desc>
                    <defs></defs>
                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                        <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>
                    </g>
                </svg>

            </div>
            <div class="dz-error-mark">

                <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                    <!-- Generator: Sketch 3.2.1 (9971) - http://www.bohemiancoding.com/sketch -->
                    <title>error</title>
                    <desc>Created with Sketch.</desc>
                    <defs></defs>
                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                        <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475">
                            <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup"></path>
                        </g>
                    </g>
                </svg>
            </div>
        </div>
    </div>
    <br><br><br>


    <div class="row">

        @foreach($product->product_image as $gal)
        <div class="col-md-4 col-sm-4 col-xs-12">

        <div class="card gallery-card text-center">

        <div class="image-outer">



            @if($gal->path==null)
                <img class="card-img-top img-fluid" src="{{asset('themes/assets/images/icon-folder.png')}}">
            @else
                <img class="card-img-top img-fluid" src="{{asset($gal->path)}}">
            @endif


        </div>

        <div class="">
        <form action="{{ url('admin/delete-product-image/'.$gal->id) }}" method="post" style="display: inline-block;">
                        @method('delete')
            @csrf
            <input type="hidden" name="url"  value="{{Request::url()}}" required>
                        <button type="submit" class="btn btn-danger btn-sm" value="Delete" onclick="return confirm('Delete Data?')">
                            <i class="glyphicon glyphicon-trash"></i> Delete
                        </button>
                    </form>
        </div>
        </div>

        </div>
    @endforeach
</div>

</div>

<script src="{{url('https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.js')}}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/dropzone.css">

<script>

function editProduct(){
    $('#product-information').toggle();
    $('#product-edit').toggle();
}

var total_photos_counter = 0;
Dropzone.options.myDropzone = {
    uploadMultiple: true,
    parallelUploads: 2,
    maxFilesize: 16,
    maxFiles: 5,
    previewTemplate: document.querySelector('#preview').innerHTML,
    addRemoveLinks: true,
    dictRemoveFile: 'Remove file',
    dictFileTooBig: 'Image is larger than 16MB',
    timeout: 500000,

    init: function () {
        this.on("removedfile", function (file) {
            $.post({
                url: '/images-delete',
                data: {id: file.name, _token: $('[name="_token"]').val()},
                dataType: 'json',
                headers: {
                  'X-CSRF-TOKEN':  '{{ csrf_token() }}'
         },
                success: function (data) {
                    total_photos_counter--;
                    $("#counter").text("# " + total_photos_counter);
                }
            });
        });
    },
    success: function (file, done) {
        total_photos_counter++;
        $("#counter").text("# " + total_photos_counter);
    }
};
</script>

<script>
    $('.button-edit-stock').on('click', function(e){
        var url = $(this).data('url');
        var id = $(this).attr('data-id');
        var stock = $(this).attr('data-stock');
        var location = $(this).attr('data-location');

        $('#updateProductStock').attr('action', url);
        $('#input-id-edit').val(id);
        $('#input-stock-edit').val(stock);
        $('#input-location-id-edit').val(location);
    });
</script>


<style>
.gallery-card{
    height: 250px;
    margin-bottom: 30px;
}

.image-outer{

    height: 150px;

    overflow: hidden;
    position: relative;
    border: 1px solid #f1f1f1;
    padding: 5px;
}

.image-outer img{
    position: absolute;

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

@if(!empty(Session::get('status')))
    <script>
        editProduct();
    </script>
@endif

@stop