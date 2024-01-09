@extends('public.layout.template')
@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/kenwheeler/slick@1.8.1/slick/slick-theme.css"/>
@endsection

@section('content')
    <div class="bread-crumb bg-grey-desc">
        <div class="container">
            <span>Products</span>&nbsp;/&nbsp;
            <a href="{{ url('products/category', $product->category->slug) }}" class="col-blue">{{ ucwords($product->category->name) }}</a>&nbsp;/&nbsp;
            <a href="#" class="col-blue">{{ ucwords($product->brand) }}</a>&nbsp;/&nbsp;
            <span>{{ ucwords($product->name) }}</span>
        </div>
    </div>

    <section id="product-detail">
        <div class="container">
            <div class="detail mb-5">
                <div class="row">
                    <style>
                        .slider-nav {
                            margin: 20px auto;
                        }
                        .slider-nav img {
                            width: 100px;
                            padding: 10px 0;
                            margin: 0 auto;
                        }
                        .slider-for img {
                            width: 400px;
                            margin: 0 auto
                        }
                    </style>

                    @php 
                        $product_image = \App\ProductImage::where('product_id', $product->id)->get();
                    @endphp
                    <div class="col-lg-6">
                        <div class="slider-for">
                            <div class="item">
                                <img src="{{ asset($product->image) }}" alt="" class="img-fluid " alt="image"  draggable="false">
                            </div>
                            @foreach($product_image as $row)
                            <div class="item">
                                <img src="{{ asset($row->path) }}" alt="" class="img-fluid " alt="image"  draggable="false">
                            </div>
                            @endforeach
                        </div>

                        <div class="slider-nav" style="overflow: hidden;">
                            <div class="item">
                                <img src="{{ asset($product->image) }}" alt="" class="img-fluid " alt="image"  draggable="false">
                            </div>
                            @foreach($product_image as $row)
                            <div class="item">
                                <img src="{{ asset($row->path) }}" alt="" class="img-fluid " alt="image"  draggable="false">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h3 class="mb-3">{{ ucwords($product->name) }}</h3>
                        <h5 class="mb-4">
                            

                            @if ($product->price_promo != null)
                                <span class="col-red mr-3">Rp. {{ number_format($product->price_promo, 0, ".", ".") }}</span>
                                <span class="strike-through font-weight-light">Rp {{ number_format($product->price, 0, ".", ".") }}</span>
                            @else
                                <span class="font-weight-light">Rp {{ number_format($product->price, 0, ".", ".") }}</span>
                            @endif

                            @php 
                            if($product->price != 0){
                                $discount = (((int)$product->price_promo-(int)$product->price)/(int)$product->price*100);
                            }else{
                                $product->price = 0;
                            }
                            @endphp
                            @if($product->price_promo!="")
                            <div class="discount-badge" style="margin-left: -50px">{{number_format($discount,1)}}%</div>
                            @endif
                        </h5>

                        <div class="mt-5 mb-5 product-amount-box">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="product-amount item-amount">
                                        <i class="fa fa-minus-circle"></i>
                                        <span class="">1</span>
                                        <i class="fa fa-plus-circle"></i>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @if($product->stock==null || $product->stock=="0")
                                    <a href="#" class="btn button-blue w-100" style="opacity: 0.5" onclick="return showAlert('Maaf Stock Habis')">Add to cart</a>
                                    @else
                                    <a href="#" class="btn button-blue w-100 addToCart">Add to cart</a>
                                    @endif
                                </div>
                            </div>

                            <!-- <div class="row mt-2">
                                <div class="col-md-8">
                                    <a href="{{ url('cart') }}" class="btn button-white w-100">BUY NOW</a>
                                </div>
                            </div> -->
                        </div>

                        <hr>

                        <div class="">
                            <div class="row">
                                <div class="col-3">
                                    SKU
                                </div>
                                <div class="col-8">
                                    : {{ $product->sku }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    Availability
                                </div>
                                <div class="col-8">
                                    @if ($product->stock == null)
                                        : <span class="red">Not Available</span>
                                    @else
                                        : <span class="col-green">{{$product->stock}} - In Stock</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-3">
                                    Category
                                </div>
                                <div class="col-8">
                                    : <a href="{{ url('products/category', $product->category->slug) }}">{{ ucwords($product->category->name) }}</a>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
            @if ($product->description != null)
               

                <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#home" class="btn btn-default title">DESKRIPSI</a></li>
                <li><a data-toggle="tab" href="#menu1"  class="btn btn-default title">REVIEW</a></li>
                </ul>

                <div class="tab-content">
                <div id="home" class="tab-pane fade in active show">
                    <br>
                    {!! $product->description !!}
                </div>
                <div id="menu1" class="tab-pane fade">
                    <br>
                    
                    @php 
                        $review = \App\ProductReview::where('product_id', $product->id)->orderBy('id', 'desc')->whereNotNull('star_review')->limit(15)->get();
                    @endphp
                    <div class="review-block">
                        <div class="row">
                            <div class="col-sm-3" style="text-align:center">
                                <h3>{{ $ratingsRound }}</h3>
                                <div class="review-block-rate">
                                        <button type="button" class="btn-review @if($ratingsRound>=1 && $ratingsRound!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                        <span class="fa fa-star" aria-hidden="true"></span>
                                        </button>
                                        <button type="button" class="btn-review @if($ratingsRound>=2 && $ratingsRound!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                        <span class="fa fa-star" aria-hidden="true"></span>
                                        </button>
                                        <button type="button" class="btn-review @if($ratingsRound>=3 && $ratingsRound!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                        <span class="fa fa-star" aria-hidden="true"></span>
                                        </button>
                                        <button type="button" class="btn-review @if($ratingsRound>=4 && $ratingsRound!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                        <span class="fa fa-star" aria-hidden="true"></span>
                                        </button>
                                        <button type="button" class="btn-review @if($ratingsRound>=5 && $ratingsRound!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                        <span class="fa fa-star" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <span class="fa fa-user" aria-hidden="true"> {{ $sumRatings }} total</span>
                            </div>
                            <div class="col-sm-9 d-flex align-content-between flex-wrap">
                                @foreach($countRatings as $i => $row)
                                <div class="col-sm-1">
                                    <span class="fa fa-star reviewstar" aria-hidden="true"></span>
                                    {{ $row->star_review }}
                                </div>
                                <div class="col-sm-11 align-self-center">
                                    <div class="progress progress-striped active">             
                                        <div id="pg-{{$i}}" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">             
                                            <span class="sr-only">100%</span>   
                                        </div>
                                    </div>    
                                </div>
                                @endforeach
                            </div>
                        </div><br>
                        @foreach($review as $row)
                        <div class="row">
                            <div class="col-sm-3">
                                <!-- <img src="http://dummyimage.com/60x60/666/ffffff&text=No+Image" class="img-rounded"> -->
                                <div class="review-block-name"><a href="#">Customers</a></div>
                                <div class="review-block-date">{{date('d/m/Y', strtotime($row->created_at))}}</div>
                            </div>
                            <div class="col-sm-9">
                                <div class="review-block-rate">
                                    <button type="button" class="btn-review @if($row->star_review>=1 && $row->star_review!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                    <span class="fa fa-star" aria-hidden="true"></span>
                                    </button>
                                    <button type="button" class="btn-review @if($row->star_review>=2 && $row->star_review!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                    <span class="fa fa-star" aria-hidden="true"></span>
                                    </button>
                                    <button type="button" class="btn-review @if($row->star_review>=3 && $row->star_review!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                    <span class="fa fa-star" aria-hidden="true"></span>
                                    </button>
                                    <button type="button" class="btn-review @if($row->star_review>=4 && $row->star_review!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                    <span class="fa fa-star" aria-hidden="true"></span>
                                    </button>
                                    <button type="button" class="btn-review @if($row->star_review>=5 && $row->star_review!=0) reviewstar @endif btn-xs" aria-label="Left Align">
                                    <span class="fa fa-star" aria-hidden="true"></span>
                                    </button>
                                </div>
                                <div class="review-block-description">{{$row->detail_review}}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                </div>
                </div>

            @endif
        </div>
    </div>

    <br>
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
                                <img src="{{ asset($item->image) }}" alt="" class="book-img">
                                <div class="book-title-box">
                                    <a href="{{ url('products', $item->slug) }}" class="book-title">
                                        {{ $item->name }}
                                    </a>
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

    <div class="modal" id="productAdded">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h5 class="text-center">Berhasil Ditambahkan </h5>
                    {{-- @php Alert::success('Success Title', 'Success Message'); @endphp --}}
                    {{-- <div class="item-detail">
                        <img src="{{ asset('html/assets/images/core/product-1.png') }}" class="img-fluid">
                        <div>
                            <h5>Mechanics of Fluids (5e) SI Edition</h5>
                            <p>
                                <span class="col-blue"> Rp. 314.800,-</span>
                            </p>
                        </div>

                    </div> --}}
                    <div style="text-align: center; margin: 20px 0;">
                        <a href="shopping-cart.html" class="btn button-blue" style="font-size: .8rem;">LIHAT KERANJANG BELANJA</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            var action = false, clicked = false;
            var Owl = {

                init: function() {
                Owl.carousel();
                },

                carousel: function() {
                    var owl;
                    $(document).ready(function() {

                        owl = $('.product-display').owlCarousel({
                            items 	   : 1,
                            center	   : true,
                            loop       : true,
                            nav        : true,
                            margin     : 10,
                            dotsContainer: '.test1',
                            navText: ["<i class='fa fa-chevron-left'></i>","<i class='fa fa-chevron-right'></i>"],
                            responsive:{
                                768:{
                                    nav: false
                                }
                            }
                        });

                        $('.owl-next').on('click',function(){
                            action = 'next';
                        });

                        $('.owl-prev').on('click',function(){
                            action = 'prev';
                        });

                        $('.product-thumbnail').on('click', 'li', function(e) {
                            owl.trigger('to.owl.carousel', [$(this).index(), 300]);
                        });
                    });
                }
            };

            $(document).ready(function() {
                Owl.init();
            });
        })
    </script>

    <script>
        $(document).ready(function(){

            $( ".fa-minus-circle" ).click(function() {
                var btn = $(this).parent().find("span")
                var a = parseInt(btn.text()) - 1
                btn.html(a)
            });

            $( ".fa-plus-circle" ).click(function() {
                var btn = $(this).parent().find("span")
                btn.html(parseInt(btn.text()) + 1)
            });

            $( ".addToCart" ).click(function() {

                    var amount = $(".product-amount").text();

                    var formData = {
                        "_token": "{{ csrf_token() }}",
                        "product_id": "{{$product->id}}", //for get email 
                        "qty": amount, //for get email 
                    };

                    var data = formData;

                $.ajax({
                        type: "POST",
                        url: "{{url('member/product-add-to-cart')}}",
                        data: data,
                        headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                        
                        beforeSend: function () {

                        },
                        success: function (response) {

                            if(response.status=="0"){
                                $("#cartList").modal('show');
                            }else{
                                showNotif("Cart berhasil ditambahkan");

                                setTimeout(function () {

                                    getShoppingCart();
                                    getShoppingCartCount();
                                    $("#cartList").modal('show');

                                }, 1000);
                            }


                        },
                        error: function (xhr, status, error) {
                            setTimeout(function () {

                                console.log(xhr.responseText)

                            }, 2000);
                        }
                    });
                
            })
        });
    </script>

    <!-- SLICK -->
    <script type="text/javascript">
        $(document).ready(function(){
            $('.slider-for').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                fade: true,
                asNavFor: '.slider-nav'
            });
            $('.slider-nav').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                asNavFor: '.slider-for',
                dots: false,
                centerMode: true,
                focusOnSelect: true
            });
        });

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

    <!-- Progress Bar -->
    <script type="text/javascript">
        $(document).ready(function() {
            @foreach($countRatings as $i => $val)
                var ProgressBar = document.getElementById('pg-{{$i}}');
                ProgressBar.style.width = {{$val->persen}}+'%';
            @endforeach
        })
    </script>
@endsection