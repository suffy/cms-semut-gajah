@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Customers
    </header>

    <div class="card-body">

        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Today +2
                        <h3>{{ $usersToday }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Last Week
                        <h3>{{ $usersLastWeek }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Last Month
                        <h3>{{ $usersLastMonth }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        This Year
                        <h3>{{ $usersThisYear }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newUser"><span
                        class="fa fa-plus"></span> Create New</a>
                <a href="
                            @if(auth()->user()->account_role == 'manager')
                                {{url('manager/customers-import')}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('superadmin/customers-import')}}
                            @elseif(auth()->user()->account_role == 'admin')
                                {{url('admin/customers-import')}}
                            @elseif(auth()->user()->account_role == 'distributor')
                                {{url('distributor/customers-import')}}
                            @endif
                        " class="btn btn-blue">Import User</a>
                <a href="
                        @if(auth()->user()->account_role == 'manager')
                            {{url('manager/customers-export')}}
                        @elseif(auth()->user()->account_role == 'superadmin')
                            {{url('superadmin/customers-export')}}
                        @elseif(auth()->user()->account_role == 'admin')
                            {{url('admin/customers-export')}}
                        @elseif(auth()->user()->account_role == 'distributor')
                            {{url('distributor/customers-export')}}
                        @endif
                    " class="btn btn-blue">Export User</a>
                <br>
            </div>
            <div class="col-md-6">
                <form method="get"
                    action="@if(auth()->user()->account_role == 'manager'){{url('manager/customers')}}@elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/customers')}}@else{{url('distributor/customers')}}@endif">
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

        <div class="form-group row">
            <div class="col-sm-4 col-md-4 col-lg-2">
                <label for="site_id" class="col-sm-1 col-form-label">Site ID</label>
                <select class="form-control" name="site_id" onChange="location = this.value;">
                    <option value="customers" @if(!\Illuminate\Support\Facades\Request::get('site_id')) selected @endif>
                        Ada</option>
                    <option value="?site_id=false" @if(\Illuminate\Support\Facades\Request::get('site_id')=='false' )
                        selected @endif>Tidak</option>
                </select>
            </div>
            <div class="col-sm-4 col-md-4 col-lg-2">
                <label for="platform" class="col-sm-1 col-form-label">Platform</label>
                <select class="form-control" name="platform" onChange="location = this.value;">
                    <option value="?platform=app" @if(!\Illuminate\Support\Facades\Request::get('platform')=='app' )
                        selected @endif>App</option>
                    <option value="?platform=erp" @if(\Illuminate\Support\Facades\Request::get('platform')=='erp' )
                        selected @endif>Erp</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-outer table-responsive">
                    <div class="table-inner">
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>Name</td>
                                    <td>Contact</td>
                                    <td>Point</td>
                                    <td>Site Name</td>
                                    <td>Salesman</td>
                                    <td>Code Approval</td>
                                    {{-- <td>Credit Limit</td> --}}
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td class="text-center">{{ $user->name }}</td>
                                    <td>
                                        <i class="fa fa-phone"> {{ $user->phone }}</i><br>
                                        <i class="fa fa-envelope"> {{ $user->email }}</i><br>
                                        <i class="fa fa-home">{{ $user->address }}</i>
                                    </td>
                                    <td class="align-middle">
                                        @if($user->point)
                                        {{ $user->point }} Point
                                        <a href="javascript:void(0)" class="pull-right point-history"
                                            data-id="{{$user->id}}"" data-point=" {{$user->point}}">Detail <span
                                                class="fa fa-file-text-o"></span></a>
                                        @else
                                        0 Point
                                        @endif
                                    </td>
                                    <td class="align-middle"><span id="label-mapping-site-{{$user->id}}">
                                            @if (count($user->user_default_address) != 0)
                                            @if ($user->user_default_address[0]->site_name == null)
                                            {{ $user->user_default_address[0]->site_name }}
                                            @else
                                            {{ $user->user_default_address[0]->site_name->branch_name }}
                                            @endif
                                            @endif</span>
                                        {{-- <a href="javascript:void(0)" class="pull-right" data-id="{{$user->id}}"
                                        data-toggle="modal" data-target="#mappingSiteModal"
                                        onclick="setIdMappingSite('{{$user->id}}')">Edit <span
                                            class="fa fa-edit"></span></a> --}}
                                        <!-- <select name="mapping_site" id="mapping-site" class="form-control" required></select> -->
                                    </td>
                                    {{-- for user app --}}
                                    @if ($user->platform == 'app')
                                    <td class="align-middle"><span id="label-salesman-{{$user->id}}">
                                            @if ($user->salesman_code != null)
                                            {{ $user->salesman->namasales }}
                                            @endif</span>
                                        {{-- <a href="javascript:void(0)" class="pull-right" data-id="{{$user->id}}"
                                        data-toggle="modal" data-target="#salesmanModal" onclick="setId('{{$user->id}}'
                                        @if($user->site_code!=null) , '{{ $user->site_code }}' @endif)">Edit <span
                                            class="fa fa-edit"></span></a> --}}
                                    </td>
                                    @elseif($user->platform == 'erp')
                                    <td class="align-middle"><span id="label-salesman-erp-{{$user->customer_code}}">
                                            @if (count($user->meta_user) > 0)
                                            {{ $user->meta_user[0]->salesman->namasales }}
                                            @endif</span>
                                        {{-- <a href="javascript:void(0)" class="pull-right" data-customer-code="{{$user->customer_code}}"
                                        data-toggle="modal" data-target="#salesmanModalErp"
                                        onclick="setCustomerCode('{{$user->customer_code}}' @if($user->site_code!=null)
                                        , '{{ $user->site_code }}' @endif)">Edit <span class="fa fa-edit"></span></a>
                                        --}}
                                    </td>
                                    @else
                                    <td></td>
                                    @endif

                                    {{-- <td class="align-middle">Rp <span id="label-credit-limit-{{$user->id}}">{{number_format($user->credit_limit)}}</span><a
                                        href="javascript:void(0)" class="pull-right button-edit-credit-limit"
                                        data-id="{{$user->id}}"><span class="fa fa-edit"></span></a>
                                    <form id="form-credit-limit-{{$user->id}}" class="form-price" style="display:none">
                                        @csrf
                                        <input type="number" step="any" name="credit_limit"
                                            value="{{$user->credit_limit}}" class="form-control">
                                        <button type="submit" class="btn btn-default btn-sm  btn-update-credit-limit">
                                            Update</button>
                                    </form>
                                    </td> --}}
                                    <td class="align-middle" id="code-approval-{{$user->id}}">
                                        <span id="span-code-{{$user->id}}">
                                            @if($user->code_approval != null)
                                            {{ $user->code_approval }}
                                            @endif
                                        </span>
                                        {{-- <a href="javascript:void(0)" class="pull-right code-generate" data-id="{{$user->id}}">Generate
                                        <span class="fa fa-refresh"></span></a> --}}
                                    </td>
                                    <td class="align-middle text-center">
                                        {{-- @if($user->account_status==1)
                                            <div class="status status-success"><i class="fa fa-check"></i> Aktif</div>
                                        @elseif($user->account_status==0)
                                            <div class="status status-info"><i class="fa fa-clock-o"></i> Baru</div>
                                        @elseif($user->account_status==3)
                                            <div class="status status-danger"><i class="fa fa-close red"></i> Banned</div>
                                        @endif --}}
                                        @if($user->otp_verified_at != null && $user->otp_verified_at < $today) <div
                                            class="status status-success"><i class="fa fa-check"></i> Aktif
                    </div>
                    @elseif($user->otp_verified_at != null && $user->otp_verified_at > $today)
                    <div class="status status-success"><i class="fa fa-check"></i> Aktif (Baru)</div>
                    @else
                    <div class="status status-info"><i class="fa fa-clock-o"></i> Belum Aktif</div>
                    @endif

                    </td>
                    <td width="50px">
                        <a href="
                                                    @if(auth()->user()->account_role == 'manager')
                                                        {{url('manager/customer-detail/' . $user->id)}}
                                                    @elseif(auth()->user()->account_role == 'superadmin')
                                                        {{url('superadmin/customer-detail/' . $user->id)}}
                                                    @elseif(auth()->user()->account_role == 'admin')
                                                        {{url('admin/customer-detail/' . $user->id)}}
                                                    @elseif(auth()->user()->account_role == 'distributor')
                                                        {{url('distributor/customer-detail/' . $user->id)}}
                                                    @endif
                                                " data-id="" data-name="" data-email=""
                            class="btn btn-green btn-sm button-edit-user">Detail</a>
                    </td>
                    </tr>
                    @endforeach
                    </tbody>
                    </table>

                    {{$users->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}
                </div>
            </div>
        </div>

    </div>

    </div>
</section>


<!-- Modal -->
<div id="newUser" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">New User</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form
                    action="@if(auth()->user()->account_role == 'manager'){{url('manager/customer')}}@else{{url('superadmin/customer')}}@endif"
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row has-error">
                        <label class="col-sm-12 col-form-label">Name</label>
                        <div class="col-sm-12">
                            <input type="text" name="name"
                                class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Name"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="col-sm-12">
                            <span class="text-small"> {{ $errors->first('name') }} </span>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('phone') ? 'has-error' : '' }}">
                        <label class="col-sm-12 col-form-label">Phone</label>
                        <div class="col-sm-12">
                            <input type="text" name="phone"
                                class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                placeholder="+628111111111" value="{{ old('phone') }}" required>
                        </div>
                        <div class="col-sm-12">
                            <span class="text-small"> {{ $errors->first('phone') }} </span>
                        </div>
                    </div>
                    <div class="form-group row {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label class="col-sm-12 col-form-label">Email</label>
                        <div class="col-sm-12">
                            <input type="email" name="email"
                                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Email"
                                value="{{ old('email') }}" required>
                        </div>
                        <div class="col-sm-12">
                            <span class="text-small"> {{ $errors->first('email') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Site ID</label>
                        <div class="col-sm-12">
                            <select name="site_id" id="site_id" class="form-control" required>
                                <option value="">Site ID</option>
                                @foreach ($mappingSites as $mappingSite)
                                <option value="{{ $mappingSite->id }}"
                                    {{ old('site_id') == $mappingSite->id ? 'selected' : '' }}>{{ $mappingSite->kode }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Address</label>
                        <div class="col-sm-12">
                            <textarea name="address" id="address" cols="30" rows="10" class="form-control"
                                required>{{old('address')}}</textarea>
                        </div>
                        <div class="col-sm-12">
                            <span class="text-small"> {{ $errors->first('address') }} </span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Other Address</label>
                        <div class="col-sm-12">
                            <textarea name="other_address" id="other_address" cols="30" rows="10"
                                class="form-control">{{old('other_address')}}</textarea>
                        </div>
                        <div class="invalid-feedback">
                            {{ $errors->first('other_address') }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Password</label>
                        <div class="col-sm-12">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Credit Limit</label>
                        <div class="col-sm-12">
                            <input type="number" step="any" name="credit_limit" class="form-control"
                                placeholder="Credit Limit" value="{{old('credit_limit')}}" required>
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

<!-- Modal -->
<div id="salesmanModalErp" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Salesman</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="customer-code">
                <input type="hidden" id="site-code-salesman-erp">
                <select name="salesman" id="salesman-erp" class="form-control" required></select>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-blue btn-update-salesman-erp">Save</button> &nbsp &nbsp
                        <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div id="salesmanModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Salesman</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user-id">
                <input type="hidden" id="site-code-salesman">
                <select name="salesman" id="salesman" class="form-control" required></select>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-blue btn-update-salesman">Save</button> &nbsp &nbsp
                        <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div id="mappingSiteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Mapping Site</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user-id-mapping-site">
                <select name="mapping_site" id="mapping-site" class="form-control" required></select>
                <hr>
                <div class="form-group row">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-blue btn-update-mapping-site">Save</button> &nbsp &nbsp
                        <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div id="pointHistoryModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Point History</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table align="right">
                    <thead>
                        <tr>
                            <td colspan="3">
                                <h6 id="point-total"></h6>
                            </td>
                        </tr>
                        <tr></tr>
                    </thead>
                </table>
                <table class="table default-table" id="tabelPoint">
                    <thead>
                        <tr align="center">
                            <td>Invoice</td>
                            <td>Total Transaksi</td>
                            <td>Point</td>
                        </tr>
                    </thead>
                    <tbody id="tabelPointBody">
                    </tbody>
                </table>
                <hr>
            </div>
        </div>

    </div>
</div>

<script>
    function setCustomerCode(customer_code, site_code) {
        $('#customer-code').val(customer_code);
        $('#site-code-salesman-erp').val(site_code);
    }

    function setId(user_id, site_code) {
        $('#user-id').val(user_id);
        $('#site-code-salesman').val(site_code);
    }

    function setIdMappingSite(user_id) {
        $('#user-id-mapping-site').val(user_id);
    }

    @if(count($errors) > 0)
    $('#newUser').modal('show');
    @endif

    $(document).ready(function () {
        var site_code_salesman = $('#site-code-salesman').val()
        var site_code_salesman_erp = $('#site-code-salesman-erp').val()
        $('#salesman-erp').select2({
            placeholder: "Pilih Salesman",
            ajax: {
                url: function (data) {
                    return 'customers/all-salesman/' + $('#site-code-salesman-erp').val() + '';
                },
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.namasales,
                                id: item.kodesales
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('.code-generate').on('click', function () {
            var id = $(this).attr('data-id');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: 'customers/code-approval/' + id,
                data: {
                    'id': id
                },
                success: function (data) {
                    $('#span-code-' + id).html(data.code_approval)
                    showNotif("Berhasil generate code approval")
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    showAlert(thrownError);
                }
            });
        })

        $('.point-history').on('click', function () {
            var id = $(this).attr('data-id');
            var point = $(this).attr('data-point');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: 'customers/point-history/' + id,
                success: function (data) {
                    $('#pointHistoryModal').modal('show');
                    $('#tabelPointBody').empty();
                    $('#point-total').html('Total Point : ' + point);
                    Object.keys(data).forEach(function (key) {
                        $('#tabelPointBody').append('<tr><td align="center"><h6>' +
                            data[key].order.invoice + '</h6></td>' +
                            '<td align="center"><h6>' + new Intl.NumberFormat(
                                "id-ID", {
                                    style: "currency",
                                    currency: "IDR"
                                }).format(data[key].order.payment_total) +
                            '</h6></td>' +
                            '<td align="center"><h6>' + data[key].order.point +
                            '</h6></td></tr>');
                    });
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    showAlert(thrownError);
                }
            });
        })

        $('#salesman').select2({
            placeholder: "Pilih Salesman",
            ajax: {
                url: function (data) {
                    return 'customers/all-salesman/' + $('#site-code-salesman').val() + '';
                },
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.namasales,
                                id: item.kodesales
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#mapping-site').select2({
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: 'customers/all-mapping-site',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.branch_name + ' - ' + item.nama_comp,
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    });

    // mapping site
    $('.btn-update-mapping-site').on('click', function () {
        var user_id = $('#user-id-mapping-site').val();
        var mapping_site = $('#mapping-site').val()

        $.ajax({
            url: @if(auth() -> user() -> account_role == 'manager')
            "{{url('manager/update-mapping-site')}}"
            @else "{{url('superadmin/update-mapping-site')}}"
            @endif,
            mimeType: "multipart/form-data",
            type: "POST",
            data: {
                mapping_site: mapping_site,
                user_id: user_id,
                _token: '{{csrf_token()}}'
            },
            dataType: "json",
            success: function (data) {
                $('#label-mapping-site-' + user_id).html(data.data.site_name.branch_name);
                showNotif("Mapping Site updated");
                // $('#form-salesman-erp-'+customerCode).toggle();
                $('#mappingSiteModal').modal('hide');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });

    })

    // customer app
    $('.btn-update-salesman').on('click', function () {
        var user_id = $('#user-id').val();
        var salesman_code = $('#salesman').val()

        $.ajax({
            url: @if(auth() -> user() -> account_role == 'manager')
            "{{url('manager/update-salesman')}}"
            @else "{{url('superadmin/update-salesman')}}"
            @endif,
            mimeType: "multipart/form-data",
            type: "POST",
            data: {
                salesman: salesman_code,
                user_id: user_id,
                _token: '{{csrf_token()}}'
            },
            dataType: "json",
            success: function (data) {
                $('#label-salesman-' + user_id).html(data.data.salesman.namasales);
                showNotif("Salesman updated");
                // $('#form-salesman-erp-'+customerCode).toggle();
                $('#salesmanModal').modal('hide');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });

    })

    // customer erp
    $('.btn-update-salesman-erp').on('click', function () {
        var customer_code = $('#customer-code').val();
        var salesman_code = $('#salesman-erp').val()

        $.ajax({
            url: @if(auth() -> user() -> account_role == 'manager')
            "{{url('manager/update-salesman-erp')}}"
            @else "{{url('superadmin/update-salesman-erp')}}"
            @endif,
            mimeType: "multipart/form-data",
            type: "POST",
            data: {
                salesman: salesman_code,
                customer: customer_code,
                _token: '{{csrf_token()}}'
            },
            dataType: "json",
            success: function (data) {
                $('#label-salesman-erp-' + customer_code).html(data.data.salesman.namasales);
                showNotif("Salesman updated");
                // $('#form-salesman-erp-'+customerCode).toggle();
                $('#salesmanModalErp').modal('hide');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                showAlert(thrownError);
            }
        });

    })

    // credit limit
    $('.button-edit-credit-limit').on('click', function () {
        var id = $(this).attr('data-id');
        $('#form-credit-limit-' + id).toggle();


        $('#form-credit-limit-' + id).submit(function (e) {

            var form_data = new FormData($('#form-credit-limit-' + id)[0]);
            @if(auth() -> user() -> account_role == 'manager')
            var url = "{{url('/manager/update-credit-limit')}}" + "/" + id
            @else
            var url = "{{url('/superadmin/update-credit-limit')}}" + "/" + id
            @endif

            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                data: form_data,
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    $('#label-credit-limit-' + id).html(data.data.credit_limit);
                    showNotif("Credit limit updated");
                    $('#form-credit-limit-' + id).toggle();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    showAlert(thrownError);
                }
            });

            e.preventDefault();

        })
    })
</script>

<style>
    .text-small {
        font-size: 8pt;
        color: red;
    }
</style>
@stop