@extends('public.layout.template')

@section('content')
    <section id="product-category">
        <div class="container">
            <div class="text-center mt-5 mb-5">
                <h1>Products</h1>
                @if ($category != null)
                <h5>{{ $category->name }}</h5>
                @endif
            </div>

            <style>

                .btn-filter-mobile{
                    display: none;
                }
                
                @media screen and (max-width: 768px){
                    .filter-mobile{
                        display: none;
                    }

                    .btn-filter-mobile{
                        display: block;
                    }
                }
            </style>

            <div class="row">
                <div class="col-md-3 col-sm-12 col-xs-12">
                    <a href="javascript:void(0)" class="btn button-rectangle btn-filter-mobile" onclick="return showFilter()">Filter</a>
                    <br><br>
                    <div class="filter-mobile">
                    <form id="form-search" method="get" action="{{url('product-filter')}}">
                        <div class="filter-product bg-grey p-3">
                            <div class="price">
                                <p class="title">Search</p>
                                <input type="text" id="form-input-search" class="form-control" name="input-search" style="margin-bottom: 10px" placeholder="Search">
                                <button class="btn button-blue pull-right button-search" style="padding: 5px 15px;">Search</button>
                            </div>
                            <br><br>
                            <div class="category">
                                <p class="title">Product Categories</p>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input form-input-category" value=""  id="category-" name="category" checked>
                                    <label class="custom-control-label w-100" for="category-">Semua Kategori<span class="pull-right">
                                    {{ $allproducts }}
                                    </span></label>
                                </div>
                                @foreach($category_list as $row)
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input form-input-category" value="{{$row->id}}"  id="category-{{$row->id}}" name="category">
                                    <label class="custom-control-label w-100" for="category-{{$row->id}}">{{$row->name}}<span class="pull-right">
                                    {{count($row->product)}}
                                    </span></label>
                                </div>
                                @endforeach
                            </div>
                            <br>
                            <div class="price">
                                <p class="title">Filter By Price</p>
                                <input type="text" id="form-input-start-price" class="form-control input-amount" name="start-price" style="margin-bottom: 10px" placeholder="0">
                                <input type="text" id="form-input-end-price" class="form-control input-amount" name="end-price" style="margin-bottom: 10px" placeholder="10.000.000">
                                <button class="btn button-blue pull-right button-price-filter" style="padding: 5px 15px;">FILTER</button>
                            </div>
                            <br><br>
                            <div class="vendor">
                                <p class="title">Vendors</p>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input form-input-vendor" value="0"  id="vendor-" name="vendor" checked>
                                    <label class="custom-control-label w-100" for="vendor-">Semua Vendor<span class="pull-right">
                                    </span></label>
                                </div>
                                @foreach($partnerlogo as $row)
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" class="custom-control-input form-input-vendor" value="{{$row->name}}"  id="vendor-{{$row->name}}" name="vendor">
                                    <label class="custom-control-label w-100" for="vendor-{{$row->name}}">{{$row->name}}<span class="pull-right">
                                    </span></label>
                                </div>
                                @endforeach
                            </div>

                            <br>

                            <div class="top-rated">
                                <p class="title">Newest Product</p>
                                @foreach ($newest_product as $item)
                                <a href="{{ url('products', $item->slug) }}">
                                    <div class="product mb-2">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <img src="{{ asset($item->image) }}" alt="" class="w-100 p-1" style="border: solid 1px lightgray;">
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <h5 style="color: #000000; font-size: 11pt; margin-top: 10px">{{ $item->name }}<h5>
                                                <h6  style="color: #000000; font-size: 10pt">Rp. {{ number_format($item->price, 0, ".", ".") }},-</h6>
                                                <hr>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>

                        </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-9 col-sm-12 col-xs-12">
                    <div class="item-product">
                    
                    <!-- sorting -->

                    
                    <div class="row" id="display-loading" style="display: none">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="box-border">
                                <div class="text-center">
                                    <img src="{{asset('loading.gif')}}" width="100px" style="margin:auto"><br>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="product">

                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        a.input-category{
            text-decoration: none;
            color: #333;
        }
    </style>

@endsection

@section('js')
    

<script>
  
    var input_category = "{{\Illuminate\Support\Facades\Request::get('category')}}";
    var input_start_price = "{{\Illuminate\Support\Facades\Request::get('start_price')}}";
    var input_end_price = "{{\Illuminate\Support\Facades\Request::get('end_price')}}";
    var input_vendor = "{{\Illuminate\Support\Facades\Request::get('vendor')}}";
    var input_tags = "{{\Illuminate\Support\Facades\Request::get('tags')}}";
    var input_search = "{{\Illuminate\Support\Facades\Request::get('search')}}";
    var search_url = "";
    var view = "grid";

    filterUrl();

    function showFilter(){
        $('.filter-mobile').toggle();
    }

    function listView() {
        view = "list";
        filterUrl();
    }

    function gridView() {
        view = "grid";
        filterUrl();
    }

    function toggleFilter() {
        $('.filter-result').fadeToggle();
    }


    $(".form-input-vendor").on('change', function() {
        var value = $(this).val();
        console.log(value);
        input_vendor = value;
        filterUrl();
    })

    $(".form-input-category").on('change', function() {
        var value = $(this).val();
        console.log(value);
        input_category = value;
        filterUrl();
    })

    // $(".input-category").on('click', function() {
    //     var value = $(this).attr('data-id');
    //     console.log(value);
    //     input_category = value;
    //     filterUrl();
    // })

    $('.button-price-filter').on('click', function(e) {
        submitForm();
    })

    $('.button-search').on('click', function(e) {
        submitForm();
    })

    $("#form-search").on('submit', function(){
        submitForm();
    })


    function submitForm() {
        $("#form-search").submit(function(e) {
            //some stuff...
            //get form action:
            search_url = $(this)[0].form;
            input_start_price = $('#form-input-start-price').val();
            input_end_price = $('#form-input-end-price').val();
            input_vendor = $('.form-input-vendor').val();
            input_category = $('.form-input-category').val();
            input_search = $('#form-input-search').val();

            filterUrl();
            e.preventDefault();
            //some other stuff...
        });
    }

    function filterUrl() {

        var windowWidth = window.screen.width < window.outerWidth ?
        window.screen.width : window.outerWidth;
        var mobile = windowWidth < 768;

        if(mobile){
            $('.filter-mobile').css("display","none");
        }

        var url = new URL("{{url('product-filter')}}");

        url.searchParams.set("category", input_category); // setting your param
        url.searchParams.set("start_price", input_start_price); // setting your param
        url.searchParams.set("end_price", input_end_price); // setting your param
        url.searchParams.set("vendor", input_vendor); // setting your param
        url.searchParams.set("search", input_search); // setting your param
        url.searchParams.set("view", view); // setting your param
        var newUrl = url.href;
        console.log(newUrl);

        var url_page = new URL("{{url('products')}}");
        url_page.searchParams.set("category", input_category); // setting your param
        url_page.searchParams.set("start_price", input_start_price); // setting your param
        url_page.searchParams.set("end_price", input_end_price); // setting your param
        url_page.searchParams.set("vendor", input_vendor); // setting your param
        url_page.searchParams.set("search", input_search); // setting your param
        url_page.searchParams.set("view", view); // setting your param
        var newUrlPage = url_page.href;
        console.log(newUrlPage);

        // window.location = newUrl;
        window.history.pushState('Products', 'Products', newUrlPage);

        $('#form-input-search').val(input_search)


        $.ajax({
            type: "GET",
            url: newUrl,
            beforeSend: function() {
                $('#display-loading').show();
                $('#product').css({
                    opacity: 0.4
                });
            },
            success: function(response) {

                setTimeout(function() {

                    $('#product').html(response);
                    $('#display-loading').hide();
                    $('#product').css({
                        opacity: 1
                    });

                }, 1000)

            }
        })
    }
    

</script>

@endsection