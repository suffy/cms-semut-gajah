@extends('admin.layout.template')

@section('content')

    <section class="panel col-md-6">
        <header class="panel-heading" style="padding-bottom: 30px;">
            Send Notif Product Price
            <div class="pull-right">
                    <div class="col-sm-12">
                        <select class="form-control" id="type">
                            <option value="product">Product</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
            </div>
        </header>

        <div class="card-body">            
            <div class="form-group row" id="row-product">
                <div class="col-sm-4 col-md-4 col-lg-6">
                    <label for="site_id" class="col-form-label">
                        Products
                        <span id="alert" class="text-small"> * Pilih Product Dulu </span>
                    </label>
                    <span class="text-smalls" id="product_name"></span>
                    <select name="product_id" id="products" data-name="" class="form-control"></select>
                </div>
            </div>
            <div class="form-group row" id="row-group" style="display:none;">
                <div class="col-sm-4 col-md-4 col-lg-6">
                    <label for="site_id" class="col-form-label">
                        Group
                        <span id="alert" class="text-small"> * Pilih Group Dulu </span>
                    </label>
                    <span class="text-smalls" id="group_name"></span>
                    <select name="groups" id="groups" data-name="" class="form-control"></select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-4 col-md-4 col-lg-12">
                    <label class=" col-form-label">
                        Message
                        <span class="text-small"> * Gunakan kata {item} untuk nama product / group sementara </span>
                    </label>
                    <div class="input-group">
                        <textarea class="form-control" id="message" name="message" readonly></textarea>
                    </div>
                </div>
            </div>
            {{-- Halo Pengguna Semut Gajah, Untuk {item} bulan depan akan mengalami kenaikan harga segera beli yaa --}}
            <div class="form-group row">
                <div class="col-sm-4 col-md-4 col-lg-12">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12">
                    <button class="float-right btn btn-blue col-lg-4 btn-send" disabled>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="panel">

        {{-- <div class="card-body">            
            <div class="row">
                <div class="col-11 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer table-responsive">
                        <div class="table-inner">
                            <table class="table default-table dataTable">
                                <thead>
                                <tr  align="center">
                                    <td>Name</td>
                                    <td>Customer Code</td>
                                    <td>Site Code</td>
                                    <td>Total Order</td>
                                    <td>Total Nominal</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody id="data-recap">
                                @if(isset($testimonials))
                                    @include('admin.pages.pagination_data_recap_customer')
                                @else
                                    <tr>
                                        <td colspan="6" align="center"> Data Kosong !! </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div> --}}
    </section>

    <style>
        .text-smalls{
            font-size: 8pt;
            color: cadetblue;
        }
    </style>

    <script>
    $(document).ready(function () {
        $(document).on('change', '#type', function(event){
            var type = $('#type').val();
            if(type === 'product') { 
                $('#groups').empty('');
                $('#row-product').show();
                $('#row-group').hide();
            } else if (type === 'group') { 
                $('#products').empty('');
                $('#row-product').hide();
                $('#row-group').show();
            }
        });

        $('#products').select2({
            placeholder: "Pilih Products",
            ajax: {
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/customers/all-products')}}"
                        @elseif(auth()->user()->account_role == 'superadmin') 
                            "{{url('superadmin/customers/all-products')}}",
                    @endif,
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
                cache: true
            }
        }).on('change', function(e) {
            $("#message").attr("readonly", false); 
        });

        $('#groups').select2({
            placeholder: "Pilih Groups",
            ajax: {
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/customers/all-groups')}}"
                        @elseif(auth()->user()->account_role == 'superadmin') 
                            "{{url('superadmin/customers/all-groups')}}",
                    @endif,
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.nama_group,
                                id: item.nama_group,
                            }
                        })
                    };
                },
                cache: true
            }
        }).on('change', function(e) {
            $("#message").attr("readonly", false); 
        });

        $(document).on('change', '#message', function(event){
            var message     = $('#message').val();
            var product     = $("#products option:selected").text();
            var id_product  = $("#products").val() ?? null;
            var groups      = $("#groups option:selected").text();
            if(product) {
                let result      = message.replace("{item}", product);
                console.log(id_product);
                $(".btn-send").attr("disabled", false); 
                window.result       = result;
                window.id_product   = id_product;
            } else if (groups) {
                let result      = message.replace("{item}", groups);
                console.log(id_product);
                $(".btn-send").attr("disabled", false); 
                window.result       = result;
                window.id_product   = null
            }

            console.log(result);
        });

        $(document).on('click', '.btn-send', function () {
            var token       = "{{csrf_token()}}";
            var message     = window.result;
            var id          = window.id_product;

            $.ajax({ 
                url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/product/notif')}}"
                        @elseif(auth()->user()->account_role == 'superadmin') 
                            "{{url('superadmin/product/notif')}}",
                    @endif,
                dataType: 'json',
                type: 'POST',
                data: {
                            "_token": token,
                            "message": message,
                            "id_product": id
                        },
                success: function(response) { 
                    // location.reload();
                    showNotif("Sukses mengirim notifikasi")
                    setTimeout(location.reload(true), 30000);
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        });
    });
    </script>

    <style>
        .text-small{
            font-size: 8pt;
            color: red;
        }
    </style>
@stop
