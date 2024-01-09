<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- The above 4 meta tags *must* come first in the head; any other head content must come *after* these tags -->

<!-- Title -->
@php 
$opt = App\DataOption::where('slug','title')->get();
@endphp
@foreach($opt as $row)
<title>{{$row->option_value}}</title>
@endforeach



@php 
$opt = App\DataOption::where('slug','meta')->get();
@endphp
@foreach($opt as $row)
<meta property="{{$row->option_name}}" content="{{$row->option_value}}">
@endforeach

<!-- Favicon -->
<link rel="icon" href="img/core-img/favicon.ico">

<!-- Bootstrap 4 core CSS -->
<link rel="stylesheet" href="{{ asset('html/assets/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('html/assets/css/font-awesome.min.css') }}">

<!-- Custom styles for this template -->
<link rel="stylesheet" href="{{ asset('html/assets/OwlCarousel2/dist/assets/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('html/assets/OwlCarousel2/dist/assets/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="{{ asset('html/assets/style.css') }}" >
<link rel="stylesheet" href="{{ asset('html/assets/sakura-style.css') }}">
<link rel="stylesheet" href="{{asset('html/assets/stylesheet.css')}}">

@yield('css')