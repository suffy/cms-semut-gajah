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

            <div class="row">
                <div class="col-md-3">
                    <form id="form-search" method="get" action="{{url('product-category-filter')}}">
                        <div class="filter-product bg-grey p-3">
                            <div class="category">
                                <p class="title">Product Categories</p>
                                <div class="list">
                                    @foreach ($category_list as $item)
                                        
                                        <a href="{{ url('products/category', $item->slug) }}" class="input-category">
                                            {{ ucwords($item->name) }}
                                            <span class="pull-right">
                                                {{count($item->product)}}
                                            </span>
                                        </a>
                                        <br>
                                    @endforeach
                                </div>
                            </div>
                            <br>
                            <div class="price">
                                <p class="title">Filter By Price</p>
                                <input type="text" class="form-control input-amount" name="start-price" style="margin-bottom: 10px" placeholder="0">
                                <input type="text" class="form-control input-amount" name="end-price" style="margin-bottom: 10px" placeholder="10.000.000">
                                <button id="btn-price-filter" class="btn button-blue pull-right" style="padding: 5px 15px;">FILTER</button>
                            </div>
                            <br><br>
                            <div class="vendor">
                                <p class="title">Vendors</p>
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
                                <p class="title">Top rated Products</p>
                                @foreach ($top_rated as $item)
                                <a href="{{ url('products', $item->slug) }}">
                                    <div class="product mb-2">
                                        <div class="row">
                                            <div class="col-4">
                                                <img src="{{ asset($item->image) }}" alt="" class="w-100 p-1" style="border: solid 1px lightgray;">
                                            </div>
                                            <div class="col-8 fs-8">
                                                {{ $item->name }} <br>
                                                Rp. {{ number_format($item->price, 0, ".", ".") }},-
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>

                            <br>

                            <div class="tags">
                                <p class="title">Tags</p>
                                <div class="content">
                                @foreach($productTags as $tags)
                                    @foreach(explode(',', $tags) as $tag)   
                                        <a href="{{url('products/category/'. $slug_category. '?tags='. $tag)}}" class='btn btn-cat' style='margin-bottom: 3px;'>{{($tag)}}</a>&nbsp
                                    @endforeach
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-9">
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
    </section>
    <br>

<!-- BEST SELLER  -->
    <section class="books">
        <div class="container">
            <div class="books-box-no-shadow">
                <div class="books-box-inside">
                    <div class="section-title">
                        <h1 class="pull-left"><span class="col-white bg-dark-blue" style="width:50px">BEST</span> SELLER</h1>
                        <a href="{{url('products')}}" class="pull-right hide-tablet" style="color:black">
                        MORE PRODUCTS
                        <span class="fa fa-caret-right fa-lg"></span>
                        </a>
                    </div>
                    <div class="clearfix"></div>
                    <div class="best-seller owl-carousel owl-theme">
                        @foreach ($best_seller as $item)
                        @php
                            if(Auth::user()){
                                $user_id = Auth::user()->id;
                                $wishlists = App\Wishlist::where('product_id',$item->id)
                                        ->where('user_id',$user_id)
                                        ->first();
                                $status = 0;
                                if($wishlists){
                                    $status = 1;
                                }else{
                                    $status=0;
                                }
                            }

                            if($item->price != 0){
                                $discount = (((int)$item->price_promo-(int)$item->price)/(int)$item->price*100);
                            }else{
                                $item->price = 0;
                            }
                        @endphp
                        <div class="item">
                            <div class="book-content">
                                @if($item->price_promo!="")
                                <div class="discount-badge">{{number_format($discount,1)}}%</div>
                                @endif
                                <div class="box-description">
                                <a href="{{ url('products', $item->slug) }}">
                                <img src="{{ asset($item->image) }}" alt="" class="w-100 book-img">
                                <div class="book-title-box">
                                    <div class="book-title">
                                        {{ $item->name }}
                                    </div>
                                </div>
                                <div class="book-price-box">
                                    @if ($item->price_promo != null)
                                        <span class="strike-through col-red">Rp. {{ number_format($item->price_promo, 0, ".", ".") }}</span>
                                        <br>
                                        <span>Rp. {{ number_format($item->price, 0, ".", ".") }}</span>
                                    @else
                                        <span>Rp. {{ number_format($item->price, 0, ".", ".") }}</span>
                                    @endif
                                </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div style="margin-top: 10px;">
                        <a href="{{url('products')}}" class="text-center button-blue shown-tablet">More Product </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('js')

<script>
  
    var input_category = "{{$slug_category}}";
    var input_start_price = "{{\Illuminate\Support\Facades\Request::get('start_price')}}";
    var input_end_price = "{{\Illuminate\Support\Facades\Request::get('end_price')}}";
    var input_vendor = "{{\Illuminate\Support\Facades\Request::get('vendor')}}";
    var input_tags = "{{\Illuminate\Support\Facades\Request::get('tags')}}";
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

    $(".input-category").on('click', function() {
        var value = $(this).attr('data-id');
        console.log(value);
        input_category = value;
        filterUrl();
    })

    $('#btn-price-filter').on('click', function(e) {
        submitForm();
    })

    $('.button-search').on('click', function(e) {
        submitForm();
    })

    $("#form-search").on('submit', function(){
        submitForm();
    })

    $(".input_tags").on('click', function() {
        var value = $(this).val();        
        console.log(value);
        input_tags = value;
        filterUrl();
    })


    function submitForm() {
        $("#form-search").submit(function(e) {
            //some stuff...
            //get form action:
            search_url = $(this)[0].form;
            input_start_price = $('input[name="start-price"]').val();
            input_end_price = $('input[name="end-price"]').val();
            input_tags = $('input[name="tags"]').val();
            input_vendor = $('.form-input-vendor:checked').val() ?? "";

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

        var url = new URL("{{url('product-category-filter')}}");
        
        url.searchParams.set("category", input_category); // setting your param
        url.searchParams.set("start_price", input_start_price); // setting your param
        url.searchParams.set("end_price", input_end_price); // setting your param
        url.searchParams.set("vendor", input_vendor); // setting your param
        url.searchParams.set("tags", input_tags); // setting your param
        url.searchParams.set("view", view); // setting your param
        var newUrl = url.href;
        
        // window.location = newUrl;
        //window.history.pushState('products', 'products', newUrlPage);

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
    
    $(document).ready(function(){
            $('.best-seller').owlCarousel({
                items:1,
                loop:true,
                margin:30,
                autoHeight:true,
                nav:true,
                navText : ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
                responsive:{
                    600:{
                        items:4
                    }
                }
            })
        });

</script>
@endsection