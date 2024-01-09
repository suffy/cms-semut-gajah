@extends('admin.layout.template')

@section('content')

<form action="@if(auth()->user()->account_role == 'manager'){{url('manager/products')}}@else{{url('superadmin/products')}}@endif" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-9 col-sm-12 col-xs-12">
            <section id="newPost" class="panel">
                <header class="panel-heading">
                    Create New Product
                </header>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Nama</label>
                                <div class="col-sm-12">
                                    @csrf
                                    @method('put')
                                    <input type="text" name="name" class="form-control" placeholder="Title" required>
                                    <input type="hidden" name="url" value="{{Request::url()}}" required>
                                </div>
                            </div>
                            <!-- <div class="form-group row">
                                <label class="col-sm-12 col-form-label">SKU</label>
                                <div class="col-sm-12">
                                    <input type="text" name="sku" class="form-control" placeholder="SKU">
                                </div>
                            </div> -->
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Retail</label>
                                <div class="col-sm-12">
                                    <input type="text" name="harga_ritel_gt" class="form-control input-amount" placeholder="Harga Beli">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Grosir</label>
                                <div class="col-sm-12">
                                    <input type="text" name="harga_grosir_mt" class="form-control input-amount" placeholder="Harga Jual">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Semi Grosir</label>
                                <div class="col-sm-12">
                                    <input type="text" name="harga_semi_grosir" class="form-control input-amount" placeholder="Harga Jual">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Harga Sebelum promo (Dicoret) <span class="text-small">* kosongkan jika tidak ada</span></label>
                                <div class="col-sm-12">
                                    <input type="text" name="price_promo" class="form-control input-amount" placeholder="Harga Promo">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Berat <span class="text-small">* contoh 0.85</span></label>
                                <div class="col-sm-12">
                                    <input type="text" name="weight" class="form-control" placeholder="Berat">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Stok</label>
                                <div class="col-sm-12">
                                    <input type="number" name="stock" class="form-control" placeholder="Stock">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Type</label>
                                <div class="col-sm-12">
                                    <input type="text" name="type" class="form-control" placeholder="Type">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Satuan Online</label>
                                <div class="col-sm-12">
                                    <input type="text" name="satuan_online" class="form-control input-amount" placeholder="Satuan Besar">
                                </div>
                            </div>
                            <!-- {{-- <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Brand</label>
                                <div class="col-sm-8">
                                    <select name="brand" class="form-control">
                                        @if( count($brand) != 0)
                                            @foreach ($brand as $item)
                                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <a class="btn btn-blue" href="{{url('admin/partner-logo?type=brand')}}">Tambah Brand</a>
                                </div>
                            </div> --}} -->
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Deskripsi</label>
                                <div class="col-sm-12">
                                    <textarea class="editor" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
        <div class="col-md-3 col-sm-12 col-xs-12">
            <section id="newPost" class="panel">
                <header class="panel-heading">
                    Publish
                </header>
                <div class="card-body">

                    <label><input type="radio" name="status" value="1" checked> Publish</label> &nbsp
                    <label><input type="radio" name="status" value="0"> Draft</label>
                    <hr>

                    <button type="submit" class="btn btn-blue">Save</button>
                    <button type="button" class="btn btn-danger" onclick="return togglePost()">Cancel</button>

                </div>
            </section>
            <section id="newPost" class="panel">
                <header class="panel-heading">
                    Cover
                </header>
                <div class="card-body">

                    <img id="blah" src="{{asset('images/no-images.png')}}" alt="your image" class="img-fluid" /><br><br>
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

                <header class="panel-heading">
                    File Images
                </header>
                <div class="card-body">

                    <input type='file' id="multi-image" class="file" name="image_multi[]" multiple/>
                    <span style="font-size: 8pt; color: red">*Bisa upload lebih dari 1 gambar (ctrl + select file) di browse files</span>

                </div>


                <hr>

                <header class="panel-heading">
                    Category
                </header>
                <div class="card-body" style="height: 300px; overflow-x: scroll;">
                    @foreach($category as $s)
                    @if($s->category_parent==0)
                    <label><input type="radio" name="category_id" value="{{$s->id}}" required> &nbsp {{$s->name}}</label><br>
                   
                    <hr style="margin-top: 5px">
                    @endif
                    @endforeach
                </div>
                <header class="panel-heading">
                    Tags
                </header>
                <div class="card-body">
                    <textarea type="text" class="form-control" id="input-tags" name="tags"></textarea>
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

<style>
    .text-small{
        font-size: 8pt;
        color: red;
    }
</style>

@stop