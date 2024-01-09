@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp
<a 
    href="
        @if(auth()->user()->account_role == 'manager')
            {{url('manager/products/create')}}
        @elseif(auth()->user()->account_role == 'superadmin')
            {{url('superadmin/products/create')}}
        @elseif(auth()->user()->account_role == 'admin')
            {{url('admin/products/create')}}
        @elseif(auth()->user()->account_role == 'distributor')
            {{url('distributor/products/create')}}
        @endif
    " 
    class="btn btn-blue" 
    onclick="return togglePage()"
>Tambah Produk</a> &nbsp
<a 
    href="
        @if(auth()->user()->account_role == 'manager')
            {{url('manager/product-import')}}
        @elseif(auth()->user()->account_role == 'superadmin')
            {{url('superadmin/product-import')}}
        @elseif(auth()->user()->account_role == 'admin')
            {{url('admin/product-import')}}
        @elseif(auth()->user()->account_role == 'distributor')
            {{url('distributor/product-import')}}
        @endif
    " 
    class="btn btn-blue"
>Import Produk</a>
<br>
<br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Products
    </header>
    <div class="card-body">
        
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4 col-md-4 col-lg-3">
                    <label>Category</label>
                    <select class="form-control" name="category_id" onChange="location = this.value;">
                        <option value="products" @if(\Illuminate\Support\Facades\Request::get('category_id')=="") selected @endif>Semua</option>
                        @foreach ($category as $categories)
                            <option value="products?category_id={{$categories->id}}" @if(\Illuminate\Support\Facades\Request::get('category_id')==$categories->id) selected @endif>
                                {{$categories->name}}
                            </option>
                        @endforeach
                    </select>
                    <br>
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-6">
                <form method="get" action="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('manager/products')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('superadmin/products')}}
                    @elseif(auth()->user()->account_role == 'admin')
                        {{url('admin/products')}}
                    @elseif(auth()->user()->account_role == 'distributor')
                        {{url('distributor/products')}}
                    @endif
                ">
                    <div class="input-group mt-4">
                        <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ Request::get('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
            <div class="table-responsive">
                <div class="scroll-table-outer">
                    <div class="scroll-table-inner card-body">
                        
                    <a 
                        href="
                            @if($account_role == "manager")
                                {{url('manager/products?status=')}}
                            @elseif($account_role == "superadmin")
                                {{url('superadmin/products?status=')}}
                            @elseif($account_role == "admin")
                                {{url('admin/products?status=')}}
                            @elseif($account_role == "distributor")
                                {{url('distributor/products?status=')}}
                            @endif
                        "
                        class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status')=="") active @endif"
                    >Semua</a>
                    <a 
                        href="
                            @if($account_role == "manager")
                                {{url('manager/products?status=1')}}
                            @elseif($account_role == "superadmin")
                                {{url('superadmin/products?status=1')}}
                            @elseif($account_role == "admin")
                                {{url('admin/products?status=1')}}
                            @elseif($account_role == "distributor")
                                {{url('distributor/products?status=1')}}
                            @endif
                        " 
                        class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status')=="1") active @endif"
                    >Aktif</a>
                    <a 
                        href="
                            @if($account_role == "manager")
                                {{url('manager/products?status=0')}}
                            @elseif($account_role == "superadmin")
                                {{url('superadmin/products?status=0')}}
                            @elseif($account_role == "admin")
                                {{url('admin/products?status=0')}}
                            @elseif($account_role == "distributor")
                                {{url('distributor/products?status=0')}}
                            @endif
                        " 
                        class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status')=="0") active @endif"
                    >NonAktif</a>
                    
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Image</td>
                                    <td>Kode Product</td>
                                    <td>Name</td>
                                    <td>Category</td>
                                    {{-- <td>Weight</td> --}}
                                    <td>Unit</td>
                                    <td>Description</td>
                                    <td width="130px">Price Retail</td>
                                    <td width="130px">Price Grosir</td>
                                    <td width="130px">Price Semi Grosir</td>
                                    @if (auth()->user()->account_role == 'distributor')
                                        {{-- <td>Stock</td> --}}
                                    @endif
                                    {{-- <td>Status</td> --}}
                                    <td width="75px">Action</td>
                                </tr>
                            </thead>
                            <tbody>

                            @foreach($product as $row)
                                <tr>
                                    <td class="align-middle">{{$loop->iteration-1+$product->firstItem() }}</td>
                                    <td class="align-middle">
                                        @if (!file_exists( public_path() . $row->image))
                                            {{-- <iframe src="{{ $row->image }}" width="100" allow="autoplay"></iframe> --}}
                                            <img src="{{ $row->image }}" width="75px">
                                        @elseif (file_exists( public_path() . $row->image))
                                            <img src="{{asset($row->image)}}" width="75px">
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">{{$row->kodeprod}}</td>
                                    <td class="align-middle text-center">{{ $row->name }}</td>
                                    <td class="align-middle text-center">@if($row->category){{$row->category->name}}@endif</td>
                                    {{-- <td class="align-middle">{{ $row->weight }}</td> --}}
                                    <td class="align-middle text-center">
                                        {{-- Large Unit : {{ $row->large_unit }} <br>
                                        Medium Unit : {{ $row->medium_unit }} <br>
                                        Small Unit : {{ $row->small_unit }}  --}}
                                        {{ $row->satuan_online }}
                                    </td>
                                    <td class="align-middle text-center">{!! Str::limit($row->description, 50) !!}</td>
                                    <td class="align-middle text-center">
                                        Rp <span>{{$row->price->harga_ritel_gt ?? 0}}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        Rp <span>{{$row->price->harga_grosir_mt ?? 0}}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        Rp <span>{{$row->price->harga_semi_grosir ?? 0}}</span>
                                    </td>
                                    {{-- <td>
                                        @if ($row->product_stock)
                                            {{ $row->product_stock->stock }}
                                        @endif
                                    </td> --}}
                                    {{-- <td class="align-middle" width="40px">
                                        @if(auth()->user()->account_role == 'distributor') 
                                            @if($row->status == 1) 
                                            <span class="status status-success">Aktif</span>
                                            @else
                                            <span class="status status-danger">NonAktif</span>
                                            @endif
                                        @else 
                                            <label class="switch">
                                                <input data-id="{{$row->id}}" class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $row->status ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                        @endif
                                    </td> --}}
                                    <td class="align-middle">
                                        <div style="display: inline-flex;">
                                            <a href="
                                                    @if($account_role == "manager")
                                                        {{url('manager/products/'.$row->id.'?slug='.$row->slug)}}
                                                    @elseif($account_role == "superadmin")
                                                        {{url('superadmin/products/'.$row->id.'?slug='.$row->slug)}}
                                                    @elseif($account_role == "admin")
                                                        {{url('admin/products/'.$row->id.'?slug='.$row->slug)}}
                                                    @elseif($account_role == "distributor")
                                                        {{url('distributor/products/'.$row->id.'?slug='.$row->slug)}}
                                                    @endif
                                                " 
                                                class="btn btn-blue btn-sm"
                                            ><i class="fa fa-eye"></i></a>
                                            @if(!file_exists(public_path($row->image))) 
                                                {{-- File Gambar Kosong --}}
                                                <button class="btn btn-green btn-sm modal-image" data-id="{{$row->id}}" data-toggle="modal" data-target="#upload-image" data-keyboard="false"> 
                                                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                                                </button>
                                            @elseif(is_null($row->image) && $row->image_backup)
                                                {{-- Data Gambar Kosong --}}
                                                <button class="btn btn-neutral btn-sm btn-get-image-name" data-id="{{$row->id}}"> 
                                                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                    {{-- <form 
                                        action="
                                            @if($account_role == "manager")
                                                {{ url('manager/duplicate-product/'.$row->id) }}
                                            @elseif($account_role == "superadmin")
                                                {{ url('superadmin/duplicate-product/'.$row->id) }}
                                            @elseif($account_role == "admin")
                                                {{ url('admin/duplicate-product/'.$row->id) }}
                                            @elseif($account_role == "distributor")
                                                {{ url('distributor/duplicate-product/'.$row->id) }}
                                            @endif
                                        " 
                                        method="POST" 
                                        style="display: inline-block;" 
                                        onsubmit="return confirm('Duplikat produk?')"
                                    >
                                        @method('post')
                                        @csrf
                                        <button type="submit" class="btn btn-green btn-sm" value="duplicate" >
                                            <i class=" fa fa-copy" title="Duplicate"></i>
                                        </button>
                                    </form> --}}
                                    {{-- <form 
                                        action="
                                            @if($account_role == "manager")
                                                {{ url('manager/products/'.$row->id) }}
                                            @elseif($account_role == "superadmin")
                                                {{ url('superadmin/products/'.$row->id) }}
                                            @elseif($account_role == "admin")
                                                {{ url('admin/products/'.$row->id) }}
                                            @elseif($account_role == "distributor")
                                                {{ url('distributor/products/'.$row->id) }}
                                            @endif
                                        " 
                                        method="POST" 
                                        style="display: inline-block;" 
                                        onsubmit="return confirm('Hapus produk?')"
                                    >
                                        @method('delete')
                                        @csrf
                                        <button type="submit" class="btn btn-red btn-sm button-delete" value="Delete" >
                                            <i class=" fa fa-trash" title="Delete"></i>
                                        </button>
                                    </form> --}}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $product->appends(Request::all())->links() }}
                </div>
            </div>
    </div>

    {{-- MODAL START --}}
    <div id="upload-image" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Upload Image</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="insert">
                        <div class="row px-3 pb-3">
                            <div class="col-12 py-2">
                                <input type="hidden" id="product-id">
                                <input type="file" id="input-image" name="image">
                            </div>
                            <div class="col-12">
                                <img id="image"  width="150" height="150"/>
                            </div>
                        </div>
                        <button class="btn btn-blue btn-sm ml-3">Simpan</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    // var loadFile = function(event) {
    //     var output = document.getElementById('output');
    //     output.src = URL.createObjectURL(event.target.files[0]);
    //     output.onload = function() {
    //         URL.revokeObjectURL(output.src) // free memory
    //     }
    // };

    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // $(previewId).css('src', e.target.result );
                $(previewId).attr('src', e.target.result);
                $(previewId).hide();
                $(previewId).fadeIn(850);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#input-image").change(function() {
        readURL(this, '#image');
    }); 

                // save
    $('#insert').on('submit', function(e) {
        e.preventDefault();
        let id = $('#product-id').val();
        var fd          = new FormData(this);
        var image       = $('#input-image')[0].files;
        var token       = "{{csrf_token()}}";
        fd.append('_token', token);
        fd.append('image', image);

        $.ajax({ 
            url: "/manager/products/image/" + id,
            type: 'POST',
            data: fd,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) { 
                $('#upload-image').modal('hide');
                showNotif("Upload image sukses");
                setTimeout(location.reload(true), 30000);
            }
        });
    });

    $('.modal-image').on('click', function(e) {
        var id = $(this).data("id");
        $('#product-id').val(id);
    });

    $('.btn-get-image-name').on('click', function(e) {
        var id = $(this).data("id");
        e.preventDefault();
        console.log(id);
        $.ajax({
                type: "GET",
                dataType: "json",
                url: '/admin/products/get-image-name/'+id,
                success: function(data){
                    showNotif("Get Image Name")
                    setTimeout(location.reload(true), 30000);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    // console.log(error);
                    showAlert(thrownError);
                }
            });
    });

    $('.button-edit-price-buy').on('click', function(){
        var id = $(this).attr('data-id');
        $('#form-price-buy-'+id).toggle();

        
            
            $('#form-price-buy-'+id).submit(function(e){
                
                var form_data = new FormData($('#form-price-buy-'+id)[0]);
                var url = "{{url('/admin/update-product-price-buy')}}"+"/"+id

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: url,
                    data: form_data,
                    mimeType: "multipart/form-data",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data){
                        console.log(data)
                        $('#label-price-buy-'+id).html(data.data.price);
                        showNotif("Perubahan harga sukses");
                        $('#form-price-buy-'+id).toggle();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        showAlert(thrownError);
                    }
                });

                e.preventDefault();
                
            })


    })

    $('.button-edit-price-sell').on('click', function(){
        var id = $(this).attr('data-id');
        $('#form-price-sell-'+id).toggle();

        
            
            $('#form-price-sell-'+id).submit(function(e){
                
                var form_data = new FormData($('#form-price-sell-'+id)[0]);
                var url = "{{url('/admin/update-product-price-sell')}}"+"/"+id

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: url,
                    data: form_data,
                    mimeType: "multipart/form-data",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data){
                        console.log(data)
                        $('#label-price-sell-'+id).html(data.data.price);
                        showNotif("Perubahan harga sukses");
                        $('#form-price-sell-'+id).toggle();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        showAlert(thrownError);
                    }
                });

                e.preventDefault();
                
            })


    })


    $('.button-edit-stock').on('click', function(){
        var id = $(this).attr('data-id');
        $('#form-stock-'+id).toggle();

        $('#form-stock-'+id).submit(function(e){
                
                var form_data = new FormData($('#form-stock-'+id)[0]);
                var url = "{{url('/admin/update-product-stock')}}"+"/"+id

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: url,
                    data: form_data,
                    mimeType: "multipart/form-data",
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data){
                        console.log(data)
                        $('#label-stock-'+id).html(data.data.stock);
                        showNotif("Perubahan harga sukses");
                        $('#form-stock-'+id).toggle();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        showAlert(thrownError);
                    }
                });

                e.preventDefault();
                
            })

    });
</script>

<script>
        $(function(){
          $('.toggle-class').change(function(){
              var status = $(this).prop('checked') == true ? 1 : 0;
              var category_id = $(this).data('id');

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/admin/product-status/'+category_id,
                  data: {'status': status, 'category_id': category_id},
                  success: function(data){
                    console.log(data)
                    showNotif("Perubahan status sukses")
                  },
                  error: function (xhr, ajaxOptions, thrownError) {
                      console.log(error);
                    showAlert(thrownError);
                  }
              });
            })
        })
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
</style>
@stop