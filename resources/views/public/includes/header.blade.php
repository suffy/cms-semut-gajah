@php 
    $user = Auth::user();
@endphp

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target=".dual-collapse2">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse w-100 order-2 order-lg-1 dual-collapse2">
            <a class="nav-link hide-tablet" href="{{ url('page/lokasi-toko') }}" style="position: relative;" >
                <img src="{{ asset('html/assets/images/icons/icon-pin.png') }}" alt="" class="img-fluid">
            </a>
            <ul class="navbar-nav mr-auto">
                @php $menus = App\Menu::where('status', '1')->get(); @endphp

                @foreach($menus as $menu)
                <li class="nav-item  @if ( Request::segment(1)==$menu->slug ) {{'active'}} @endif">
                <a class="nav-link header-menu" href="{{url($menu->slug)}}">
                    {!! $menu->name !!}
                </a>
                </li>
                @endforeach

            </ul>
        </div>
        <div class="mx-auto order-1 order-lg-2">
            <a class="navbar-brand mx-auto col-blue" href="{{ url('/') }}">
            @php
            $opt = App\DataOption::where('slug','logo')->first();
            @endphp
            @if(isset($opt))
            <img id="main-logo" src="{{asset($opt->icon)}}" class="img-fluid">
            @endif
            </a>
        </div>
        <div class="navbar-collapse collapse w-100 order-3 dual-collapse2">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link header-menu" href="{{url('contact')}}">Hubungi Kami</a>
                </li>
                @if($user) 
                <li class="nav-item">
                    @if($user->account_type=="4")
                    <a class="nav-link header-menu" href="{{url('member/dashboard')}}">{{$user->name}}</a>
                    @elseif($user->account_type=="1")
                    <a class="nav-link header-menu" href="{{url('admin/dashboard')}}">{{$user->name}}</a>
                    @endif
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link header-menu" href="{{url('member/login')}}">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link header-menu" href="{{url('member/register')}}">Register</a>
                </li>
                @endif
                <li class="nav-item">

                    @php 
                        if($user){
                            $wishlists = App\Wishlist::where('user_id', Auth::user()->id)
                            ->orderBy('id','asc')->get();
                        }
                    @endphp
                    
                    <a class="nav-link" href="{{ url('member/wishlist') }}" style="position: relative;" >
                        @if($user)
                            <div class="cart-wishlist">{{count($wishlists)}}</div>
                        @endif
                        <img src="{{ asset('html/assets/images/icons/icon-love.png') }}" alt="" class="img-fluid">
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{url('cart')}}" style="position: relative;" data-toggle="modal" data-target="#cartList">
                        <div class="cart-badge">0</div>
                        <img src="{{ asset('html/assets/images/icons/icon-shop.png') }}" alt="" class="img-fluid">
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<nav class="navbar navbar-expand-lg bg-dark-blue">
    <div class="container">
        <button class="navbar-toggler col-white" type="button" data-toggle="collapse" data-target=".dual-collapse3">
            KATEGORI PRODUK
        </button>
        <div class="navbar-collapse collapse dual-collapse3">
            <ul class="navbar-nav">
                @php $category = App\Category::where('status', 1)->get(); @endphp
                @foreach ($category as $item)
                <li class="nav-item">
                    <a href="{{ url('products?category='. $item->id) }}" class="nav-link @if( Request::segment(1)=='products' && Request::get('category')==$item->id))  {{'active'}}@endif">
                        {{ $item->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>

<div id="cartList" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">My Cart</h4>
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">

                    <div class="row" id="cart-display-loading" style="display: none">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="box-border">
                                <div class="text-center">
                                    <img src="{{asset('loading.gif')}}" width="100px" style="margin:auto"><br>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(isset($user) && $user->account_type=="4")

                        <section id="header-sakura-cart"></section>

                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <a href="{{url('cart')}}" class="btn button-blue w-100">Show Cart</a>
                            </div>
                        </div>

                    @else

                        <form method="POST" action="{{url('member/auth')}}" enctype="multipart/form-data">
                            @csrf
                            <input id="fcm_token" type="hidden" name="token" value="">
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-6 offset-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Login') }}
                                    </button>

                                    @if (Route::has('password.request'))
                                        <a class="btn btn-link" href="{{ route('password.request') }}">
                                            {{ __('Forgot Your Password?') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                    @endif

                </div>
            </div>

        </div>
    </div>


    <script>
            function showNotif(text) {

                $('#text-notif').html(text);
                $('#topbar-notification').fadeIn();

                setTimeout(function () {
                    $('#topbar-notification').fadeOut();
                }, 2000)
            }

            function showAlert(text) {

                $('#alert-notif').html(text);
                $('#alert-notification').fadeIn();

                setTimeout(function () {
                    $('#alert-notification').fadeOut();
                }, 2000)
            }
        </script>
        <script>
            //Format ke ISO Standard
            function formatDateToISO(date) {
                var d = new Date(date),
                    month = '' + (d.getMonth() + 1),
                    day = '' + d.getDate(),
                    year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('-');
            }

            // Indonesia Format
            function formatDate(d) {

                var date = new Date(d);

                if (isNaN(date.getTime())) {
                    return d;
                } else {

                    var weekday = new Array(7);
                    weekday[0] = "Minggu";
                    weekday[1] = "Senin";
                    weekday[2] = "Selasa";
                    weekday[3] = "Rabu";
                    weekday[4] = "Kamis";
                    weekday[5] = "Jumat";
                    weekday[6] = "Sabtu";

                    var month = new Array();
                    month[0] = "Januari";
                    month[1] = "Februari";
                    month[2] = "Maret";
                    month[3] = "April";
                    month[4] = "Mei";
                    month[5] = "Juni";
                    month[6] = "Juli";
                    month[7] = "Agustus";
                    month[8] = "September";
                    month[9] = "October";
                    month[10] = "November";
                    month[11] = "Desember";

                    day = date.getDate();

                    if (day < 10) {
                        day = "0" + day;
                    }

                    var hour;
                    var minutes;
                    var second;

                    if (date.getHours() == 0) {
                        hour = ""
                    } else {
                        hour = " | " + date.getHours() + ":";
                    }

                    if (date.getMinutes() == 0) {
                        minutes = ""
                    } else {
                        minutes = date.getMinutes() + ":";
                    }

                    if (date.getSeconds() == 0) {
                        second = ""
                    } else {
                        second = date.getSeconds();
                    }

                    // return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear() + "  " + hour + minutes + second;
                    return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear();

                }

            }

            function formatDateTime(d) {

                var date = new Date(d);

                if (isNaN(date.getTime())) {
                    return d;
                } else {

                    var weekday = new Array(7);
                    weekday[0] = "Minggu";
                    weekday[1] = "Senin";
                    weekday[2] = "Selasa";
                    weekday[3] = "Rabu";
                    weekday[4] = "Kamis";
                    weekday[5] = "Jumat";
                    weekday[6] = "Sabtu";

                    var month = new Array();
                    month[0] = "Januari";
                    month[1] = "Februari";
                    month[2] = "Maret";
                    month[3] = "April";
                    month[4] = "Mei";
                    month[5] = "Juni";
                    month[6] = "Juli";
                    month[7] = "Agustus";
                    month[8] = "September";
                    month[9] = "October";
                    month[10] = "November";
                    month[11] = "Desember";

                    day = date.getDate();

                    if (day < 10) {
                        day = "0" + day;
                    }

                    var hour;
                    var minutes;
                    var second;

                    if (date.getHours() == 0) {
                        hour = ""
                    } else {
                        hour = " | " + date.getHours() + ":";
                    }

                    if (date.getMinutes() == 0) {
                        minutes = ""
                    } else {
                        minutes = date.getMinutes() + ":";
                    }

                    if (date.getSeconds() == 0) {
                        second = ""
                    } else {
                        second = date.getSeconds();
                    }

                    return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear() + "  " + hour + minutes + second;

                }

            }

            function nominalToCurrency(number)
            {
                number = number.toFixed(2) + '';
                x = number.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            }
        </script>

        </head>

    <body>

    <style>
            #topbar-notification {
                display: none;
                z-index: 99999;
                background: #ffffff;
                color: #05ab08;
                width: 300px;
                margin: auto;
                margin-top: 10%;
                overflow: auto;
                position: absolute;
                left: 0; right: 0;
                box-shadow: 0px 0px 50px rgba(0,0,0,0.7);
                border-top: 5px solid #05ab08;
                text-align: center;
                border-radius: 30px;
                padding: 30px;
                animation-name: fadeIn;
            }

            #topbar-notification i{
                font-size: 44pt;
                margin-bottom: 15px;
            }

            #alert-notification {
                display: none;
                z-index: 99999;
                background: #ffffff;
                color: #e3342f;
                width: 300px;
                margin: auto;
                margin-top: 10%;
                overflow: auto;
                position: absolute;
                left: 0; right: 0;
                box-shadow: 0px 0px 50px rgba(0,0,0,0.7);
                border-top: 5px solid #e3342f;
                text-align: center;
                border-radius: 30px;
                padding: 30px;
                animation-name: fadeIn;
            }

            #alert-notification i{
                font-size: 44pt;
                margin-bottom: 15px;
            }
            
        </style>

        <div id="topbar-notification">

            <div class="container">
                <i class="fa fa-check-circle-o"></i>
                <div id="text-notif">
                    My awesome top bar
                </div>
            </div>

        </div>

        <div id="alert-notification">

            <div class="container">
            <i class="fa fa-times-circle-o"></i>
                <div id="alert-notif">
                    My awesome top bar
                </div>
            </div>

        </div>
        