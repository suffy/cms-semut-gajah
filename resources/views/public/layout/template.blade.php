<!DOCTYPE html>
<html lang="en">

<head>

     <!-- ##### head Area Start ##### -->
     @include('public.includes.head')
     <!-- ##### head Area End ##### -->

</head>

<body>



    <!-- ##### Header Area Start ##### -->
    @include('public.includes.header')
    <!-- ##### Header Area End ##### -->

    @yield('content')

    <!-- ##### Footer Area Start ##### -->
    @include('public.includes.footer')
    <!-- ##### Footer Area End ##### -->

    <!-- ##### All Javascript Script ##### -->
    <!-- Bootstrap core JavaScript -->

    <script src="{{ asset('html/assets/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('html/assets/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="{{ asset('html/assets/OwlCarousel2/dist/owl.carousel.min.js') }}"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    @include('sweetalert::alert', ['cdn' => "https://cdn.jsdelivr.net/npm/sweetalert2@9"])
    @include('sweetalert::alert')

    <!-- ##### JS custom Area Start ##### -->
    @yield('js')
    <!-- ##### JS custom Area End ##### -->


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
            position: fixed;
            left: 0; right: 0;
            box-shadow: 0px 0px 50px rgba(0,0,0,0.7);
            border-top: 5px solid #05ab08;
            text-align: center;
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
            position: fixed;
            left: 0; right: 0;
            box-shadow: 0px 0px 50px rgba(0,0,0,0.7);
            border-top: 5px solid #e3342f;
            text-align: center;
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



@if(!empty(Session::get('status')) && Session::get('status') == 1)
    <script>
        showNotif("{{Session::get('message')}}");
    </script>
@endif

@if(!empty(Session::get('status')) && Session::get('status') == 2)
    <script>
        showAlert("{{Session::get('message')}}");
    </script>
@endif


@php 
    $user = Auth::user();
@endphp


@if(isset($user) && $user->account_type=="4")
    <script>

        var url = "{{url('member/shopping-cart')}}";
        var url_count = "{{url('member/count-shopping-cart')}}";

        $(document).ready(function(){
            getShoppingCart();
            getShoppingCartCount();
        })
        

        function getShoppingCart(){
            $.ajax({
                type:"GET",
                url: url,
                beforeSend:function(){
                    $('#cart-display-loading').show();

                },
                success:function(response){
                    setTimeout(function(){
                        $('#header-sakura-cart').html(response);
                        $('#cart-display-loading').hide();
                        
                        if (window.location.href == '{{url("cart")}}') {
                            $('#page-shopping-cart').html(response);
                            
                            setTimeout(function(){
                                $('#page-cart-display-loading').hide();
                            },1000)
                        }
                        
                    }, 1000)

                }
            })
        }

        function getShoppingCartCount(){
            $.ajax({
                type:"GET",
                url: url_count,
                beforeSend:function(){
                    $('#cart-display-loading').show();
                },
                success:function(response){
                    setTimeout(function(){
                        $('.cart-badge').html(response.data);
                        $('#cart-display-loading').hide();
                    }, 1000)
                }
            })
        }
        
    </script>

    @endif

    
    <script>
        $(document).ready(function(){
            var url = "{{ url('stat-counter') }}";
            $.ajax({
                url: url,
                method: "GET",
                success:function(response){
                    console.log(response);
                }
            });
        });
    </script>
    
    <script>
        $("img").on("error", function () {
            $(this).attr("src", "{{asset('/')}}no-images.png");
        });

        function formatAmountNoDecimals( number ) {
            var rgx = /(\d+)(\d{3})/;
            while( rgx.test( number ) ) {
                number = number.replace( rgx, '$1' + '.' + '$2' );
            }
            return number;
        }

        function formatAmount( number ) {

            // remove all the characters except the numeric values
            number = number.replace( /[^0-9]/g, '' );
            number.substring( number.length - 2, number.length );

            // set the precision
            number = new Number( number );
            number = number.toFixed( 2 );    // only works with the "."

            // change the splitter to ","
            number = number.replace( /\./g, ',' );

            // format the amount
            x = number.split( ',' );
            x1 = x[0];
            x2 = x.length > 1 ? ',' + x[1] : '';

            return formatAmountNoDecimals( x1 );
        }
        
    </script>

</body>

</html>
