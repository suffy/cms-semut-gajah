@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Mapping Site
    </header>
    <div class="card-body">
        <div class="content-body">
            <div class="row">
                <div class="col-md-6">
                    {{-- <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newMappingSite" ><span class="fa fa-plus"></span> Create New</a> --}}
                </div>
                <div class="col-md-6">
                    <form method="get" action=" @if(auth()->user()->account_role == 'manager')
                                {{url('/manager/mapping-site')}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('/superadmin/mapping-site')}}
                            @endif
                            ">
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search...">
                            <button class="btn btn-secondary">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <br>
        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-outer">
                    <div class="table-inner">
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    {{-- <td>No</td> --}}
                                    <td>Code</td>
                                    <td>Branch Name</td>
                                    <td>Company Name</td>
                                    <td>Company Code</td>
                                    <td>Sub</td>
                                    <td>Status HO</td>
                                    <td>Nomor Telepon</td>
                                    <td>Min Transaction</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mappingSites as $key=>$mappingSite)
                                <tr>
                                    <td>{{ $mappingSite->kode }}</td>
                                    <td>{{ $mappingSite->branch_name }}</td>
                                    <td>{{ $mappingSite->nama_comp }}</td>
                                    <td>{{ $mappingSite->kode_comp }}</td>
                                    <td>{{ $mappingSite->sub }}</td>
                                    <td>{{ $mappingSite->status_ho }}</td>
                                    <td>{{ $mappingSite->telp_wa }}</td>
                                    <td>Rp. {{ number_format($mappingSite->min_transaction, 2, ',', '.') }}</td>
                                    <td><a href="javascript:void(0)"
                                            class="btn btn-green btn-sm btn-edit-mapping-site mx-1" data-toggle="modal"
                                            data-target="#editMappingSite" data-id="{{ $mappingSite->id }}"
                                            data-site-id="{{ $mappingSite->site_id }}"
                                            data-site-name="{{ $mappingSite->site_name }}"
                                            data-name="{{ $mappingSite->name }}"
                                            data-action="{{url('manager/mapping-site/'). '/' .$mappingSite->id}}"
                                            data-icon="{{asset($mappingSite->icon)}}">Edit</a></td>
                                    {{-- <td class="text-center">
                                            <div style="display: inline-flex;">
                                                <a href="javascript:void(0)" 
                                                    class="btn btn-green btn-sm btn-edit-mapping-site mx-1" data-toggle="modal" 
                                                    data-target="#editMappingSite"
                                                    data-id="{{ $mappingSite->id }}"
                                    data-site-id="{{ $mappingSite->site_id }}"
                                    data-site-name="{{ $mappingSite->site_name }}"
                                    data-name="{{ $mappingSite->name }}"
                                    data-action="{{url('manager/mapping-site/'). '/' .$mappingSite->id}}"
                                    data-icon="{{asset($mappingSite->icon)}}">Edit</a>
                                    <form method="post" enctype="multipart/form-data"
                                        action="{{url('manager/mapping-site/'.$mappingSite->id)}}">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name="id" value="{{$mappingSite->id}}">
                                        <input type="hidden" name="url" value="{{Request::url()}}">
                                        <input type="hidden" name="status" value="1">
                                        <button type="submit" class="btn btn-sm btn-red mx-1"
                                            onclick="return confirm('Are you sure?')">Hapus</button>
                                    </form>
                    </div>
                    </td> --}}
                    </tr>
                    @endforeach
                    </tbody>
                    </table>
                    {!! $mappingSites->links() !!}
                    <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

{{-- MODAL START --}}
<div id="newMappingSite" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-name">New Mapping Site</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{url('manager/mapping-site')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Site ID</label>
                        <div class="col-sm-12">
                            <input type="text" name="site_id" class="form-control" placeholder="Site ID" value=""
                                required>
                            <input type="hidden" name="url" value="{{Request::url()}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Site Name</label>
                        <div class="col-sm-12">
                            <input type="text" name="site_name" class="form-control" placeholder="Site Name" value=""
                                required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Company Name</label>
                        <div class="col-sm-12">
                            <input type="text" name="name" class="form-control" placeholder="Company Name" value=""
                                required>
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

<div id="editMappingSite" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-name">Edit Mapping Site</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-mapping-site" action="{{url('/mapping-site/min-update')}}" method="post"
                    enctype="multipart/form-data">
                    @method('put')
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Site Code</label>
                        <div class="col-sm-12">
                            <input id="edit-site-code" type="text" name="site_id" class="form-control"
                                placeholder="Site ID" readonly value="" required>
                            <input id="edit-id" hidden name="id" value="" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Site Name</label>
                        <div class="col-sm-12">
                            <input id="edit-branch-name" readonly type="text" name="site_name" class="form-control"
                                placeholder="Site Name" value="" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Minimal Transaction</label>
                        <div class="col-sm-12">
                            <input id="edit-site-min" type="text" name="min_transaction" class="form-control"
                                placeholder="Minimal Transaksi" value="" required
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">
                        </div>
                    </div>
                    <hr>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-blue">Update</button> &nbsp &nbsp
                            <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
{{-- MODAL END --}}


<script>
    $('.btn-edit-mapping-site').on('click', function () {
        var id = $(this).attr('data-id');

        $.ajax({
            url: "/mapping-site/fetchAjax",
            type: "GET",
            data: {
                id: id
            },
            success: function (data) {
                console.log(data);
                $('#edit-id').val(data.id);
                $('#edit-site-code').val(data.kode);
                $('#edit-branch-name').val(data.branch_name);
                if (data.min_transaction == null) {
                    $('#edit-site-min').val('0');
                } else {
                    $('#edit-site-min').val(data.min_transaction);
                }
            }
        });
    })

    // function fetch_data(page, query) {
    //     $.ajax({
    //         url: @if(auth() -> user() -> account_role == 'manager')
    //         "{{url('manager/mapping-site/fetch_data')}}?page=" + page + "&search=" + query
    //         @else "{{url('superadmin/mapping-site/fetch_data')}}?page=" + page + "&search=" + query
    //         @endif,
    //         success: function (data) {
    //             $('tbody').html('');
    //             $('tbody').html(data);
    //         }
    //     })
    // }

    // $('#search').on('keyup', function () {
    //     var query = $('#search').val();
    //     var page = $('#hidden_page').val();
    //     fetch_data(page, query);
    // })

    // $(document).on('click', '.pagination a', function (event) {
    //     event.preventDefault();
    //     var page = $(this).attr('href').split('page=')[1];
    //     $('#hidden_page').val(page);

    //     var query = $('#search').val();

    //     $('li').removeClass('active');
    //     $(this).parent().addClass('active');
    //     fetch_data(page, query);
    // });

    $(function () {
        $("#edit-site-min").keyup(function (e) {
            $(this).val(format($(this).val()));
        });
    });
    var format = function (num) {
        var str = num.toString().replace("", ""),
            parts = false,
            output = [],
            i = 1,
            formatted = null;
        if (str.indexOf(".") > 0) {
            parts = str.split(".");
            str = parts[0];
        }
        str = str.split("").reverse();
        for (var j = 0, len = str.length; j < len; j++) {
            if (str[j] != ",") {
                output.push(str[j]);
                if (i % 3 == 0 && j < (len - 1)) {
                    output.push(",");
                }
                i++;
            }
        }
        formatted = output.reverse().join("");
        return ("" + formatted + ((parts) ? "." + parts[1].substr(0, 2) : ""));
    };
</script>

@stop