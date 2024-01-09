@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<section class="panel" id="listPage">
    <header class="panel-heading">
        Products Availability
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
                                    <td>Code</td>
                                    <td>Branch Name</td>
                                    <td>City</td>
                                    <td width="75px">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mappingSites as $site)

                                    <tr align="center">
                                        <td>{{$mappingSites->firstItem() + $loop->index}}</td>
                                        <td>{{$site->kode}}</td>
                                        <td>{{$site->branch_name}}</td>
                                        <td>{{$site->nama_comp}}</td>
                                        <td>
                                            <a href="{{url('manager/product/availability/'.$site->kode)}}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $mappingSites->appends(Request::all())->links() }}
                </div>
            </div>
    </div>
</section>

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