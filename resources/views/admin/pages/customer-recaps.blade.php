@extends('admin.layout.template')

@section('content')

    <section class="panel col-md-6">
        <header class="panel-heading">
            Recaps Customers
        </header>

        <div class="card-body">            
            <div class="form-group row">
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <label for="site_id" class="col-form-label">Products</label>
                    <span class="text-smalls" id="product_name"></span>
                    <select name="product_id" id="products" data-name="" class="form-control"></select>
                </div>
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <label for="platform" class="col-form-label">Brand</label>
                    <span class="text-smalls" id="brand_name"></span>
                    <select name="brand_id" id="brands" class="form-control"></select>
                </div>
                @if(auth()->user()->account_role != 'distributor')
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <label for="site_id" class="col-form-label">Site</label>
                    <span class="text-smalls" id="site_name"></span>
                    <select name="site_code" id="site_code" class="form-control"></select>
                </div>
                @endif
            </div>
            <div class="form-group row">
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <label class=" col-form-label">
                        Date
                    </label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                    {{-- <input type="date" class="form-control" id="start_date" name="start_date" value="{{ \Carbon\Carbon::now()->firstOfMonth()->format('Y-m-d') }}"> --}}
                </div>
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <input style="margin-top:35px;" type="date" class="form-control" id="end_date"  name="end_date">
                    {{-- <input style="margin-top:35px;" type="date" class="form-control" id="end_date"  name="end_date" value="{{ date('Y-m-d') }}"> --}}
                </div>
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <label class=" col-form-label">
                        Search
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search...">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12">
                    <button class="float-right btn btn-blue col-lg-4 btn-filter">
                        Filter
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="panel">

        <div class="card-body">            
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

        </div>
    </section>

    {{-- MODAL START --}}
    <div id="detail" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Detail Recaps Order</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table default-table">
                        <thead>
                        <tr  align="center">
                            <td>Kodeprod</td>
                            <td>Product</td>
                            <td>Brand ID</td>
                            <td>Qty</td>
                            <td>Total Nominal</td>
                        </tr>
                        </thead>
                        <tbody id="detail-recap">
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <style>
        .text-smalls{
            font-size: 8pt;
            color: cadetblue;
        }
    </style>

    <script>
    $(document).ready(function () {
        // page, product_id, brand_id, start, end, search
        fetch_data(1, "", "", "", "", "", "");

        $('#products').select2({
            placeholder: "Pilih Products",
            ajax: {
                url: 'all-products',
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
        })
        // .on('change', function(e) {
            // $("#test").val(data);
            // var product_id = $(this).val();
            // fetch_data(1, product_id, "", "", "", "", "")
            // console.log(product_id);
        // });

        $('#brands').select2({
            placeholder: "Pilih Brands",
            ajax: {
                url: 'all-brands',
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.brand,
                                id: item.brand_id
                            }
                        })
                    };
                },
                cache: true
            }
        })
        // .on('change', function(e) {
        //     var brand_id = $(this).val();
        //     fetch_data(1, "", brand_id, "", "", "", "")
        // });

        $('#site_code').select2({
            placeholder: "Pilih Mapping Site",
            ajax: {
                url: 'all-mapping-site',
                dataType: 'json',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.branch_name,
                                id: item.kode
                            }
                        })
                    };
                },
                cache: true
            }
        })
        // .on('change', function(e) {
        //     var site_code = $(this).val();
        //     // alert(id);
        //     fetch_data(1, "", "", "", "", "", site_code)
        // });

        $(document).on('click', '.btn-filter', function(event){
            var start       = $('#start_date').val() ?? "";
            var end         = $('#end_date').val() ?? "";
            var products    = $('#products').val() ?? "";
            var brands      = $('#brands').val() ?? "";
            var site_code   = $('#site_code').val() ?? "";
            var product_name= $("#products option:selected").text();
            var brands_name= $("#brands option:selected").text();
            var site_code_name= $("#site_code option:selected").text();

            console.log(products + ' ' + brands  + ' ' + start + ' ' +end + ' ' + site_code) 

            fetch_data(1, products, brands, start, end, "", site_code)
            // if(products) {
                $("#products").val(null).trigger("change");
                $("#product_name").html('*' + product_name);
                // $('#products').select2({
                //     placeholder: product_name
                // });
            // }

            // if(brands) {
                $("#brands").val(null).trigger("change");
                $("#brand_name").html('*' + brands_name);
            //     $('#brands').select2({
            //         placeholder: brands_name
            //     });
            // }

            // if(site_code) {
                $("#site_code").val(null).trigger("change");
                $("#site_name").html('*' + site_code_name);
            //     $('#site_code').select2({
            //         placeholder: site_code_name
            //     });
            // }
        });

        // $(document).on('change', '#end_date', function(event){
        //     var start   = $('#start_date').val();
        //     var end     = $('#end_date').val();

        //     fetch_data(1, "", "", start, end, "")
        // });

        $('#search').keypress(function (e) {
            var key     = e.which;
            var search  = $('#search').val();
            if(key == 13)  // the enter key code
            {
                // fetch_data(1, "", "", "", "", search)
                // fetchData(page, search);
                // $('#search').val("")
            }
        });

        function fetch_data(page, product_id, brand_id, start, end, search, site_code) {
            console.log(search);
            $.ajax({
                url:@if(auth()->user()->account_role == 'manager')
                        "{{url('/manager/customers/recaps/fetch_data')}}?page="+page+"&product_id="+product_id+"&brand_id="+brand_id+"&start_date="+start+"&end_date="+end+"&search="+search+"&site_code="+site_code
                    @elseif(auth()->user()->account_role == 'distributor')
                        "{{url('/distributor/customers/recaps/fetch_data')}}?page="+page+"&product_id="+product_id+"&brand_id="+brand_id+"&start_date="+start+"&end_date="+end+"&search="+search+"&site_code="+site_code
                    @else 
                        "{{url('/superadmin/customers/recaps/fetch_data')}}?page="+page+"&product_id="+product_id+"&brand_id="+brand_id+"&start_date="+start+"&end_date="+end+"&search="+search+"&site_code="+site_code
                    @endif,
                success: function(data) {
                    $('#data-recap').html('');
                    $('#data-recap').html(data);
                }
            })
        }

        $(document).on('click', '#btn-detail', function(event){
            var id = $(this).data("id");

            var start       = $('#start_date').val() ?? "";
            var end         = $('#end_date').val() ?? "";
            // var start       = "";
            // var end         = "";
            var products    = $('#products').val() ?? "";
            var brands      = $('#brands').val() ?? "";
            var product_name= $("#products option:selected").text();
            var brands_name= $("#brands option:selected").text();

            console.log(products + ' ' + brands  + ' ' + start + ' ' +end) 

            fetch_data_detail(id, products, brands, start, end);

            $("#products").val(null).trigger("change");
            $("#product_name").html('*' + product_name);

            $("#brands").val(null).trigger("change");
            $("#brand_name").html('*' + brands_name);
        });

        function fetch_data_detail(id, product_id, brand_id, start, end) {
            $.ajax({
                url:@if(auth()->user()->account_role == 'manager')
                        "/manager/customers/recaps/detail/" + id + "?product_id="+product_id+"&brand_id="+brand_id+"&start_date="+start+"&end_date="+end
                    @elseif(auth()->user()->account_role == 'distributor')
                        "/distributor/customers/recaps/detail/" + id + "?product_id="+product_id+"&brand_id="+brand_id+"&start_date="+start+"&end_date="+end
                    @else 
                        "/superadmin/customers/recaps/detail/" + id + "?product_id="+product_id+"&brand_id="+brand_id+"&start_date="+start+"&end_date="+end
                    @endif,
                success: function(data) {
                    $('#detail-recap').html('');
                    $('#detail-recap').html(data);
                }
            })
        }

        $(document).on('click', '.pagination a', function(event){
            event.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            $('#hidden_page').val(page);

            var query = $('#search').val();

            $('li').removeClass('active');
            $(this).parent().addClass('active');
            
            fetch_data(page, "", "");
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
