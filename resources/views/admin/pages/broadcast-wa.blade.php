@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<div class="row">

    <div class="col-md-12 col-sm-12 col-xs-12">
        
        <a 
        href="
            @if($account_role == 'manager')
                {{url('manager/broadcast/create')}}
            @elseif($account_role == 'superadmin')
                {{url('superadmin/broadcast/create')}}
            @endif
        " 
        class="btn btn-blue" 
        onclick="return togglePage()"
        >Tambah Jadwal</a>
        <br><br>

        <section class="panel">
            <header class="panel-heading">
                Broadcast Message
            </header>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="table-outer">
                            <div class="table-inner">
                
                                <div class="table-responsive">
                                    <table class="table default-table dataTable">
                                        <thead>
                                        <tr align="center">
                                            <td>No</td>
                                            <td>Judul</td>
                                            <td>Jadwal</td>
                                            <td>Klasifikasi</td>
                                            <td>Tipe</td>
                                            <td>Template</td>
                                            <td>Detail</td>
                                            <td>Aksi</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($broadcastWA as $row)
                                            <tr align="center">
                                                <td class="align-middle">{{$loop->iteration}}</td>
                                                <td class="align-middle">{{$row->title}}</td>
                                                <td class="align-middle">{{$row->schedule}}</td>
                                                <td class="align-middle">{{ucfirst($row->classification)}}</td>
                                                <td class="align-middle">{{$row->type_name}}</td>
                                                <td class="align-middle text-left">
                                                    {{ $row->message }}
                                                </td>
                                                <td class="align-middle">
                                                    <a href="javascript:void(0)" onclick="setModalData('{{$row->id}}')">Lihat Detail</a></a>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="broadcast/edit/{{$row->id}}" class="btn btn-primary btn-xs">Edit</a>
                                                    <a href="broadcast/delete/{{$row->id}}"  class="btn btn-danger btn-xs" onclick="return confirm('Yakin Akan Menghapus Data ?');">Delete</a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                            {{ $broadcastWA->appends(Request::all())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        

    <!-- Modal -->
    <div id="detailModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Detail Broadcast</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table default-table" id="tableDistributor" style="display:none;">
                        <thead>
                            <tr align="center">
                                <td>Site Code</td>
                                <td>Nama</td>
                            </tr>
                        </thead>
                        <tbody id="tableDistributorBody">
                        </tbody>
                    </table>
                    <table class="table default-table" id="tableUser" style="display:none;">
                        <thead>
                            <tr align="center">
                                <td>Customer Code</td>
                                <td>Nama</td>
                            </tr>
                        </thead>
                        <tbody id="tableUserBody">
                        </tbody>
                    </table>
                    <hr>
                </div>
            </div>

        </div>
    </div>

    </div>

</div>

<script>
    function setModalData(id) {
        $.ajax({
            url: @if(auth()->user()->account_role == 'manager')
                    "{{url('manager/broadcast/detail')}}/"+id
                @elseif(auth()->user()->account_role == 'superadmin')
                    "{{url('superadmin/broadcast/detail')}}/"+id
            @endif,
            type: "GET",
            dataType: "JSON",
            success: function(data) {
                $('#detailModal').modal('show');
                
                var distributor = data.broadcast_wa_detail[0].distributor;
                var user        = data.broadcast_wa_detail[0].user;
                var detail      = data.broadcast_wa_detail;
                console.log(detail);
                if(distributor != null) {
                    $('#tableUser').hide();
                    $('#tableUserBody').empty();
                    $('#tableDistributor').show();

                    $.each(detail, function(key, value) {
                        $('#tableDistributorBody').append(
                            '<tr align="center">'+
                                '<td>'+value.distributor.kode+'</td>'+
                                '<td>'+value.distributor.branch_name+'</td>'+
                            '</tr>'
                        );
                    });
                } else if(user != null) {
                    $('#tableDistributor').hide();
                    $('#tableDistributorBody').empty();
                    $('#tableUser').show();

                    $.each(detail, function(key, value) {
                        $('#tableUserBody').append(
                            '<tr align="center">'+
                                '<td>'+value.user.customer_code+'</td>'+
                                '<td>'+value.user.name+'</td>'+
                            '</tr>'
                        );
                    });
                }
            }
        });
    }
</script>

@stop