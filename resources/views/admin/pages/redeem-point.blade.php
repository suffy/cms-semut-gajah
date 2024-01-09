@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp
<a 
    href="
        @if(auth()->user()->account_role == 'manager')
            {{url('manager/redeem-point/create')}}
        @elseif(auth()->user()->account_role == 'superadmin')
            {{url('superadmin/redeem-point/create')}}
        @endif
    " 
    class="btn btn-blue" 
>Tambah Produk Redeem</a>
<br>
<br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Products
    </header>
    <div class="card-body">
            <div class="table-responsive">
                <div class="scroll-table-outer">
                    <div class="scroll-table-inner card-body">
                    
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Image</td>
                                    <td>Kode Product</td>
                                    <td>Name</td>
                                    <td>Point Redeem</td>
                                    <td>Deskripsi</td>
                                    <td>Syarat dan Ketentuan</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>

                            @foreach($redeem_products as $row)
                                <tr>
                                    <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                    <td class="align-middle text-center">
                                        @if (!file_exists( public_path() . $row->image))
                                            {{-- <iframe src="{{ $row->image }}" width="100" allow="autoplay"></iframe> --}}
                                            <img src="{{ $row->image }}" width="75px">
                                        @elseif (file_exists( public_path() . $row->image))
                                            <img src="{{asset($row->image)}}" width="75px">
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">{{$row->kodeprod}}</td>
                                    <td class="align-middle text-center">{{ $row->name }}</td>
                                    <td class="align-middle text-center">{{ $row->redeem_point }}</td>
                                    <td class="align-middle text-center">{{ $row->redeem_desc }}</td>
                                    <td class="align-middle text-center">{{ $row->redeem_snk }}</td>
                                    <td class="align-middle text-center">
                                        <a href="redeem-point/edit/{{$row->id}}" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="redeem-point/delete/{{$row->id}}" class="btn btn-danger btn-xs" onclick="return confirm('Yakin Akan Menghapus Data ?');">Delete</a>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $redeem_products->appends(Request::all())->links() }}
                </div>
            </div>
    </div>
</section>

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