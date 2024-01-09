@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<a 
    href="
        @if($account_role == 'manager')
            {{url('manager/promo/create')}}
        @elseif($account_role == 'superadmin')
            {{url('superadmin/promo/create')}}
        @elseif($account_role == 'admin')
            {{url('admin/promo/create')}}
        @elseif($account_role == 'distributor')
            {{url('distributor/promo/create')}}
        @endif
    " 
    class="btn btn-blue" 
    onclick="return togglePage()"
>Tambah Promo</a>
<a href="
        @if($account_role == 'manager')
            {{url('manager/promo/priority/bottom')}}
        @elseif($account_role == 'superadmin')
            {{url('superadmin/promo/priority/bottom')}}
            @elseif($account_role == 'admin')
                {{url('admin/promo/priority/bottom')}}
        @endif
        " class="btn btn-blue float-right">Prioritas Banner Bawah</a>
<a href="
        @if($account_role == 'manager')
            {{url('manager/promo/priority/top')}}
        @elseif($account_role == 'superadmin')
            {{url('superadmin/promo/priority/top')}}
            @elseif($account_role == 'admin')
                {{url('admin/promo/priority/top')}}
        @endif
        " class="btn btn-blue float-right mr-4">Prioritas Banner Atas</a>
<br>
<br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Promo
    </header>
    <div class="card-body">
        
        <div class="card-body">
            <form method="get" action="
                @if($account_role == 'manager')
                    {{url('manager/promo')}}
                @elseif($account_role == 'superadmin')
                    {{url('superadmin/promo')}}
                @elseif($account_role == 'admin')
                    {{url('admin/promo')}}
                @endif
            ">
                <div class="row">
                    <div class="col-sm-4 col-md-4 col-lg-2">
                        {{-- <input type="date" name="search" class="search-input form-control"
                        placeholder="Date start..." autocomplete="off"> --}}
                        <input type="text" name="start" placeholder="Date Start..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-2">
                        {{-- <input type="date" name="search" class="search-input form-control"
                        placeholder="Date end..." autocomplete="off"> --}}
                        <input type="text" name="end" placeholder="Date End..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="col-1">
                        <button type="submit" class="btn btn-blue">Filter</button>
                    </div>
                </form>
                    {{-- <div class="col-md-1"></div> --}}
                    <div class="col-md-12 col-lg-6 ml-auto">
                        <form method="get" action="
                            @if($account_role == 'manager')
                                {{url('manager/promo')}}
                            @elseif($account_role == 'superadmin')
                                {{url('superadmin/promo')}}
                            @elseif($account_role == 'admin')
                                {{url('admin/promo')}}
                            @endif
                        ">
                            <div class="input-group">
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
                        
                    <a 
                        href="
                            @if($account_role == "manager")
                                {{url('manager/promo')}}
                            @elseif($account_role == "superadmin")
                                {{url('superadmin/promo')}}
                            @elseif($account_role == 'admin')
                                {{url('admin/promo')}}
                            @endif
                        "
                        class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status')=="") active @endif"
                    >Semua</a>
                    <a 
                        href="
                            @if($account_role == "manager")
                                {{url('manager/promo?status=1')}}
                            @elseif($account_role == "superadmin")
                                {{url('superadmin/promo?status=1')}}
                            @elseif($account_role == "admin")
                                {{url('admin/promo?status=1')}}
                            @endif
                        " 
                        class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status')=="1") active @endif"
                    >Aktif</a>
                    <a 
                        href="
                            @if($account_role == "manager")
                                {{url('manager/promo?status=0')}}
                            @elseif($account_role == "superadmin")
                                {{url('superadmin/promo?status=0')}}
                            @elseif($account_role == "admin")
                                {{url('admin/promo?status=0')}}
                            @endif
                        " 
                        class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status')=="0") active @endif"
                    >NonAktif</a>
                    
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Image</td>
                                    <td>Name</td>
                                    <td>Description</td>
                                    {{-- <td>Name</td> --}}
                                    <td>Term & Condition</td>
                                    <td>Category</td>
                                    <td>Start</td>
                                    <td>End</td>
                                    <td>Reward</td>
                                    <td>Status</td>
                                    <td width="75px">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($promos as $row)
                                <tr align="center">
                                    <td>{{$promos->firstItem() + $loop->index}}.</td>
                                    <td class="align-middle">
                                        @if (!file_exists( public_path() . $row->banner))
                                            <img src="/no-images.png" width="75px">
                                        @elseif (file_exists( public_path() . $row->banner))
                                            <img src="{{ $row->banner }}" width="75px">
                                        @endif
                                    </td>
                                    <td class="align-middle">{{$row->title}}</td>
                                    <td class="align-middle">{!! $row->description !!}</td>
                                    <td class="align-middle">
                                        @if($row->termcondition == 1)
                                            @if($row->detail_termcondition == 1)
                                                min : {{$row->min_qty}} item
                                                <br>
                                            @else
                                                min : 
                                                @foreach($row->sku as $row_sku)
                                                    {{$row_sku->product->name}} ( {{$row_sku->min_qty}} {{$row_sku->satuan}} )
                                                    <br>
                                                @endforeach
                                            @endif
                                        @elseif($row->termcondition == 2)
                                            min : Rp. {{number_format($row->min_transaction)}}
                                        @else
                                            @if($row->detail_termcondition == 1)
                                                min : {{$row->min_qty}} item
                                                <br>
                                            @else
                                                min : 
                                                @foreach($row->sku as $row_sku)
                                                    {{$row_sku->product->name}} ( {{$row_sku->min_qty}} {{$row_sku->satuan}} )
                                                    <br>
                                                @endforeach
                                            @endif
                                            min : Rp. {{number_format($row->min_transaction)}}
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($row->category == 1)
                                            Potongan Harga
                                        @elseif($row->category == 2)
                                            Bonus Product
                                        @else
                                            Potongan Harga & Bonus Product
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{ Carbon\Carbon::parse($row->start)->formatLocalized('%A, %d %B %Y')}}
                                    </td>
                                    <td class="align-middle">
                                        {{ Carbon\Carbon::parse($row->end)->formatLocalized('%A, %d %B %Y')}}
                                    </td>
                                    <td class="align-middle">
                                        @if($row->category == 1) 
                                            @foreach ($row->reward as $reward_data)
                                                @if($reward_data->reward_disc != null)
                                                    Diskon {{$reward_data->reward_disc}}%
                                                @elseif($reward_data->reward_nominal != null)
                                                    Potongan Rp. {{number_format($reward_data->reward_nominal)}}
                                                @elseif($reward_data->reward_point != null)
                                                    {{$reward_data->reward_point}} Point
                                                @endif
                                                <br>
                                            @endforeach
                                        @elseif($row->category == 2)
                                            @foreach ($row->reward as $reward_data)
                                                - {{$reward_data->product->name}} ({{$reward_data->reward_qty}} {{$reward_data->satuan}})                                                
                                                <br>
                                            @endforeach
                                        @else
                                            @foreach ($row->reward as $reward_data)
                                                @if($reward_data->reward_disc != null)
                                                    Diskon {{$reward_data->reward_disc}}%
                                                @elseif($reward_data->reward_nominal != null)
                                                    Potongan Rp. {{number_format($reward_data->reward_nominal)}}
                                                @elseif($reward_data->reward_point != null)
                                                    {{$reward_data->reward_point}} Point
                                                @elseif($reward_data->reward_product_id != null)
                                                    {{$reward_data->product->name}} ({{$reward_data->reward_qty}} {{$reward_data->satuan}})                                                
                                                @endif
                                                <br>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="align-middle" width="40px">
                                        <label class="switch">
                                            <input data-id="{{$row->id}}" data-user_role={{$account_role}} class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $row->status ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-86px, 34px, 0px);">
                                            <li><a href="promo/detail/{{$row->id}}" class="dropdown-item">Detail</a></li>
                                            <li>
                                                <a href="javascript:void(0)" class="dropdown-item" data-toggle="modal" data-target="#editBanner" onclick="setEditBannerData('{{$row->id}}', '{{$row->banner}}')">
                                                    Edit Banner
                                                </a>
                                            </li>
                                            <li>
                                                <a href="promo/edit/{{$row->id}}" class="dropdown-item btn-edit-offers">
                                                    Edit
                                                </a>
                                            </li>
                                            <li><a href="promo/{{$row->id}}" class="dropdown-item" onclick="return confirm('Yakin Akan Menghapus Data ?');">Delete</a></li>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $promos->appends(Request::all())->links() }}
                </div>
            </div>
     
    </div>
</section>

<div id="editBanner" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Banner Promo</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-banner" action="@if(auth()->user()->account_role == 'manager'){{url('manager/promo/banner')}}@else{{url('superadmin/promo/banner')}}@endif" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Banner</label>
                        <input id="edit-id" type="hidden" name="id" value="" required>
                        <div class="col-sm-12">
                            <img class="img img-responsive" id="edit-image" width="250px"><br><br>
                            <input type="file" name="banner" value="">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Update Banner</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    function setEditBannerData(id, banner) {
        $('#edit-id').val(id);
        $('#edit-image').attr('src', banner);
    }
    $(function(){
        $('.toggle-class').change(function(){
            var status = $(this).prop('checked') == true ? 1 : 0;
            var promo_id = $(this).data('id');
            var role = $(this).data('user_role');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: '/'+ role +'/promo-status/'+promo_id,
                data: {'status': status, 'promo_id': promo_id},
                success: function(data){
                    console.log(data)
                    showNotif("Perubahan status sukses")
                },
                error: function (xhr, ajaxOptions, thrownError) {
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