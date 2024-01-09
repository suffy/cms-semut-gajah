@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<a 
    href="
        @if($account_role == 'manager')
            {{url('manager/top-spender/create')}}
        @elseif($account_role == 'superadmin')
            {{url('superadmin/top-spender/create')}}
        @elseif($account_role == 'admin')
            {{url('admin/top-spender/create')}}
        @endif
    " 
    class="btn btn-blue" 
    onclick="return togglePage()"
>Tambah Top Spender</a>
<br>
<br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Top Spender
    </header>
    <div class="card-body">
        
        <div class="card-body">
            <form method="get" action="
                @if($account_role == 'manager')
                    {{url('manager/top-spender')}}
                @elseif($account_role == 'superadmin')
                    {{url('superadmin/top-spender')}}
                @elseif($account_role == 'admin')
                    {{url('admin/top-spender')}}
                @endif
            ">
                <div class="row">
                    <div class="col-sm-4 col-md-4 col-lg-2">
                        <input type="text" name="start" placeholder="Date Start..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-2">
                        <input type="text" name="end" placeholder="Date End..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="col-1">
                        <button type="submit" class="btn btn-blue">Filter</button>
                    </div>
                </form>
                    <div class="col-md-12 col-lg-6 ml-auto">
                        <form method="get" action="
                            @if($account_role == 'manager')
                                {{url('manager/top-spender')}}
                            @elseif($account_role == 'superadmin')
                                {{url('superadmin/top-spender')}}
                            @elseif($account_role == 'admin')
                                {{url('admin/top-spender')}}
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
                    
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <th>No</th>
                                    <th>Banner</th>
                                    <th>Judul</th>
                                    <th>Deskripsi</th>
                                    <th>Periode</th>
                                    <th>Filter By</th>
                                    <th>Reward</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topSpender as $row)
                                    <tr align="center">
                                        <td>{{$loop->iteration}}</td>
                                        <td>
                                            @if (!file_exists( public_path() . $row->banner))
                                                <img src="/no-images.png" width="75px">
                                            @elseif (file_exists( public_path() . $row->banner))
                                                <img src="{{ $row->banner }}" width="75px">
                                            @endif
                                        </td>
                                        <td>{{$row->title}}</td>
                                        <td>{!! $row->description !!}</td>
                                        <td>{{$row->start}} - {{$row->end}}</td>
                                        <td>
                                            @if($row->filter != 'Tidak ada filter')
                                                <h6>
                                                    @php
                                                        $filter = explode('|', $row->filter);
                                                    @endphp
                                                    @foreach($filter as $data)
                                                        <span class="badge badge-success">{{$data}}</span>
                                                    @endforeach
                                                </h6>
                                            @else
                                                Tidak ada filter
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($row->rank_reward as $reward)
                                                @if($row->reward == 'cash')
                                                    Rank {{$reward->pos}} : Rp{{$reward->nominal}}
                                                @elseif($row->reward == 'point')
                                                    Rank {{$reward->pos}} : {{$reward->nominal}} Point
                                                @endif
                                                <br>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-info" data-toggle="modal" onclick="showList('{{$row->id}}', '{{$row->title}}')">List Customer</a>
                                            <a href="
                                                @if($account_role == 'manager')
                                                    {{url('manager/top-spender/edit/'.$row->id)}}
                                                @elseif($account_role == 'superadmin')
                                                    {{url('superadmin/top-spender/edit/'.$row->id)}}
                                                @elseif($account_role == 'admin')
                                                    {{url('admin/top-spender/edit/'.$row->id)}}
                                                @endif
                                            " 
                                            class="btn btn-blue">Edit</a>
                                            <a href="javascript:void(0)" class="btn btn-red" onclick="hapus('{{$row->id}}')">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $topSpender->appends(Request::all())->links() }}
                </div>
            </div>
     
    </div>
</section>

{{-- modal create --}}
<div class="modal fade" id="topSpenderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Top Spender</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="
                    @if($account_role == 'manager')
                        {{url('manager/top-spender/store')}}
                    @elseif($account_role == 'superadmin')
                        {{url('superadmin/top-spender/store')}}
                    @elseif($account_role == 'admin')
                        {{url('admin/top-spender/store')}}
                    @endif
                ">
                    @csrf
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Judul...">
                    </div>
                    <div class="form-group">
                        <label for="start">Periode Awal</label>
                        <input type="text" class="form-control" id="start" name="start" placeholder="Periode Awal..." onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="form-group">
                        <label for="end">Periode Akhir</label>
                        <input type="text" class="form-control" id="end" name="end" placeholder="Periode Akhir..." onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="filter">Filter By</label>
                    </div>
                    <div class="form-group">
                        <label for="site_code">Site Code <span class="text-small">* boleh dikosongkan</span></label>
                        <select class="form-control {{ $errors->has('site_code') ? 'is-invalid' : '' }}" aria-label="Default select example" name="site_code" id="site_code"></select>
                    </div>
                    <div class="form-group">
                        <label for="site_code">Brand <span class="text-small">* boleh dikosongkan</span></label>
                        <select class="form-control" id="brand_id" name="brand_id">
                            <option selected disabled>Pilih Brand</option>
                            @foreach($brand as $row => $value)
                                <option value="{{$value}}">{{ucwords($row)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="site_code">Product <span class="text-small">* boleh dikosongkan</span></label>
                        <select class="form-control" aria-label="Default select example" name="product_id" id="product_id"></select>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="reward">Reward</label>
                        <select class="form-control" id="reward" name="reward">
                            <option disabled selected>Pilih Reward</option>
                            <option value="cash">Cash</option>
                            <option value="point">Point</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nominal">Nominal</label>
                        <input type="text" class="form-control" id="nominal" name="nominal" placeholder="Nominal...">
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Simpan</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Tutup</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- end modal create --}}

{{-- modal edit --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Top Spender</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="
                    @if($account_role == 'manager')
                        {{url('manager/top-spender/update')}}
                    @elseif($account_role == 'superadmin')
                        {{url('superadmin/top-spender/update')}}
                    @elseif($account_role == 'admin')
                        {{url('admin/top-spender/update')}}
                    @endif
                ">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="form-group">
                        <label for="title">Judul</label>
                        <input type="text" class="form-control" id="edit_title" name="title" placeholder="Judul...">
                    </div>
                    <div class="form-group">
                        <label for="start">Periode Awal</label>
                        <input type="text" class="form-control" id="edit_start" name="start" placeholder="Periode Awal..." onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="form-group">
                        <label for="end">Periode Akhir</label>
                        <input type="text" class="form-control" id="edit_end" name="end" placeholder="Periode Akhir..." onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="filter">Filter By</label>
                    </div>
                    <div class="form-group">
                        <label for="site_code">Site Code <span class="text-small">* boleh dikosongkan</span></label>
                        <select class="form-control {{ $errors->has('site_code') ? 'is-invalid' : '' }}" aria-label="Default select example" name="site_code" id="edit_site_code"></select>
                    </div>
                    <div class="form-group">
                        <label for="site_code">Brand <span class="text-small">* boleh dikosongkan</span></label>
                        <input type="text" class="form-control" id="edit_brand_id" name="brand_id" placeholder="Brand...">
                    </div>
                    <div class="form-group">
                        <label for="site_code">Product <span class="text-small">* boleh dikosongkan</span></label>
                        <select class="form-control" aria-label="Default select example" name="product_id" id="edit_product_id"></select>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="reward">Reward</label>
                        <select class="form-control" id="edit_reward" name="reward">
                            <option disabled selected>Pilih Reward</option>
                            <option value="cash">Cash</option>
                            <option value="point">Point</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nominal">Nominal</label>
                        <input type="text" class="form-control" id="edit_nominal" name="nominal" placeholder="Nominal...">
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Simpan</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Tutup</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- end modal edit --}}

{{-- modal list --}}
<div id="listModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Top Spender</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table default-table" id="tabelList">
                    <thead>
                        <tr align="center">
                            <td>No</td>
                            <td>Nama</td>
                            <td>Customer Code</td>
                            <td>Total Transaksi</td>
                        </tr>
                    </thead>
                    <tbody id="tabelListBody">
                    </tbody>
                </table>
                <hr>
            </div>
        </div>

    </div>
</div>

<script>
    function editTopSpender(id, title, start, end, site_code, brand_id, product_id, reward, nominal) {
        $('#id').val(id);
        $('#edit_title').val(title);
        $('#edit_start').val(start);
        $('#edit_end').val(end);
        var $newOption = $("<option selected='selected'></option>").val(site_code).text(site_code);
        $('#edit_site_code').append($newOption).trigger('change');
        $('#edit_brand_id').val(brand_id);
        var $newOption = $("<option selected='selected'></option>").val(product_id).text(product_id);
        $('#edit_product_id').append($newOption).trigger('change');
        $('#edit_reward').val(reward).change();
        $('#edit_nominal').val(nominal);
    }

    $(document).ready(function () {
        $('#site_code').select2({
            dropdownParent: $('#topSpenderModal'),
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: @if(auth()->user()->account_role == 'manager')
                        "{{url('manager/all-mapping-site')}}"
                    @elseif(auth()->user()->account_role == 'superadmin')
                        "{{url('superadmin/all-mapping-site')}}"
                    @elseif(auth()->user()->account_role == 'admin')
                        "{{url('admin/all-mapping-site')}}"
                @endif,
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.kode,
                                id: item.kode
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#edit_site_code').select2({
            dropdownParent: $('#editModal'),
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: @if(auth()->user()->account_role == 'manager')
                        "{{url('manager/all-mapping-site')}}"
                    @elseif(auth()->user()->account_role == 'superadmin')
                        "{{url('superadmin/all-mapping-site')}}"
                    @elseif(auth()->user()->account_role == 'admin')
                        "{{url('admin/all-mapping-site')}}"
                @endif,
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.kode,
                                id: item.kode
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#product_id').select2({
            dropdownParent: $('#topSpenderModal'),
            placeholder: "Pilih Product",
            ajax: {
                url: 'all-product',
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.name,
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

        $('#edit_product_id').select2({
            dropdownParent: $('#editModal'),
            placeholder: "Pilih Product",
            ajax: {
                url: 'all-product',
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.name,
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

    function showList(id, title) {
        $.ajax({
            url: 'top-spender/list/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#listModal').modal('show');
                // title modal
                $('#listModal').find('.modal-title').text(title);
                $('#tabelListBody').empty();
                const list_data = data.data;
                if(list_data.length > 0) {
                    $.each(list_data, function(index, value) {
                        const total = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(value.total);

                        $('#tabelListBody').append(
                            '<tr align="center">' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + value.name + '</td>' +
                            '<td>' + value.customer_code + '</td>' +
                            '<td>' + total + '</td>' +
                            '</tr>'
                        );
                    });
                } else {
                    $('#tabelListBody').append(
                        '<tr align="center">' +
                        '<td colspan="3">Tidak ada data</td>' +
                        '</tr>'
                    );
                }
            }
        });
    }

    function hapus(id) {
        if(confirm('Yakin Akan Menghapus Data ?')) {
            var formData = {
                _token: "{{ csrf_token() }}",
                id: id,
                url: "{{ Request::url() }}",
            };

            var id = id;

            $.ajax({
                type: 'POST',
                url: @if(auth()->user()->account_role == 'manager')
                        '/manager/top-spender/delete'
                        @elseif(auth()->user()->account_role == 'superadmin')
                            '/superadmin/top-spender/delete'
                        @elseif(auth()->user()->account_role == 'admin')
                            '/admin/top-spender/delete'
                        @endif,
                data: formData,
                dataType: "json",
                encode: true,
            }).done(function (data) {
                location.reload();
                showNotif("Data Berhasil Dihapus!");
            });
        } else {
            return false;
        }
    }
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
    
    .text-small{
        font-size: 8pt;
        color: red;
    }
</style>
@stop