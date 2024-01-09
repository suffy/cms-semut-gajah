@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<a 
    href="
        @if(auth()->user()->account_role == 'manager')
            {{ url('manager/product/availability') }}
        @elseif(auth()->user()->account_role == 'superadmin')
            {{ url('superadmin/product/availability') }}
        @elseif(auth()->user()->account_role == 'admin')
            {{ url('admin/product/availability') }}
        @elseif(auth()->user()->account_role == 'distributor')
            {{ url('distributor/product/availability') }}
        @endif
    " 
    class="btn btn-primary"
><i class="fa fa-arrow-left mr-2"></i>Kembali</a>
<br><br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Products Availability | {{$site_code}}
    </header>
    <div class="card-body">
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-9"></div>
                <div class="col-md-3">
                <form method="get" action="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('manager/product/availability')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('superadmin/product/availability')}}
                    @elseif(auth()->user()->account_role == 'admin')
                        {{url('admin/product/availability')}}
                    @elseif(auth()->user()->account_role == 'distributor')
                        {{url('distributor/product/availability')}}
                    @endif
                ">
                    <div class="input-group mt-4">
                        <input type="text" class="form-control" name="search" placeholder="Search...">
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

                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Image</td>
                                    <td>Name</td>
                                    <td>Status</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)

                                    <tr align="center">
                                        <td class="align-middle">{{$products->firstItem() + $loop->index}}</td>
                                        <td class="align-middle">
                                            @if (!file_exists( public_path() . $product->product->image))
                                                {{-- <iframe src="{{ $row->image }}" width="100" allow="autoplay"></iframe> --}}
                                                <img src="{{ $product->product->image }}" width="75px">
                                            @elseif (file_exists( public_path() . $product->product->image))
                                                <img src="{{asset($product->product->image)}}" width="75px">
                                            @endif
                                        </td>
                                        <td class="align-middle">{{$product->product->name}}</td>
                                        <td class="align-middle" width="40px">
                                            @if(auth()->user()->account_role == 'distributor') 
                                                @if($product->status == 1) 
                                                <span class="status status-success">Aktif</span>
                                                @else
                                                <span class="status status-danger">NonAktif</span>
                                                @endif
                                            @else 
                                                <label class="switch">
                                                    <input data-id="{{$product->id}}" class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $product->status ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            @endif
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $products->appends(Request::all())->links() }}
                </div>
            </div>
    </div>
</section>

<script>

    $(function(){
        $('.toggle-class').change(function(){
            var status = $(this).prop('checked') == true ? 1 : 0;
            var product_id = $(this).data('id');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/product-avail-status')}}/" + product_id
                        @elseif(auth()->user()->account_role == 'superadmin')
                            "{{url('superadmin/product-avail-status')}}/" + product_id
                        @elseif(auth()->user()->account_role == 'admin')
                            "{{url('admin/product-avail-status')}}/" + product_id
                        @elseif(auth()->user()->account_role == 'distributor')
                            "{{url('distributor/product-avail-status')}}/" + product_id
                    @endif,
                data: {'status': status, 'product_id': product_id},
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