<div class="clearfix"></div>

<section id="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <p class="footer-title">INFORMATION</p>

                @php 
                $page = App\Page::where('position','footer-1')->get();
                @endphp
                @foreach($page as $row)
                <a href="{{url('page/'.$row->slug)}}">{{$row->title}}</a>
                @endforeach
                
                <!-- <a href="">Contact Us</a>
                <a href="">Payment</a>
                <a href="">Shipping</a>
                <a href="">Returns</a>
                <a href="">FAQs</a>
                <a href="">Live Chat</a> -->
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="footer-title">OUR COMPANY</p> 
                @php 
                $page = App\Page::where('position','footer-2')->get();
                @endphp
                @foreach($page as $row)
                <a href="{{url('page/'.$row->slug)}}">{{$row->title}}</a>
                @endforeach
                <!-- <a href="">About Company</a>
                <a href="">Careers</a>
                <a href="">Corporate Responsibilityn</a>
                <a href="">Site Map</a> -->
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="footer-title">HELP</p> 
                @php 
                $page = App\Page::where('position','footer-3')->get();
                @endphp
                @foreach($page as $row)
                <a href="{{url('page/'.$row->slug)}}">{{$row->title}}</a>
                @endforeach
                <!-- <a href="">Your Order</a>
                <a href="">Shopping Guide</a>
                <a href="">Product Care</a>
                <a href="">General Information</a> -->
            </div>
            <div class="col-md-3 col-sm-6">
                <p class="footer-title">STORE LOCATION</p> 
                @php 
                $page = App\Page::where('position','footer-4')->get();
                @endphp
                @foreach($page as $row)
                <a href="{{url('page/'.$row->slug)}}">{{$row->title}}</a>
                @endforeach
                <!-- <a href="">Bandung Plaza</a>
                <a href="">BTS</a>
                <a href="">Paris Van Java</a>
                <a href="">Bandung Mall</a> -->
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <p class="footer-title">CONTACT US</p> 
                <div class="row">
                    <div class="col-sm-12">

                    <!-- @php
                    $opt = App\DataOption::where('slug','logo_bottom')->first();
                    @endphp
                    @if(isset($opt))
                    <img src="{{asset($opt->icon)}}" class="img-fluid" style="width: 300px; margin-bottom: 30px">
                    @endif -->
                    
                        @php $email = App\DataOption::where('slug', 'email')->first(); @endphp
                        @if($email)
                        <a href="#footer"><b><span class="fa fa-envelope"></span> {{$email->option_value}}</b></a>
                        
                        @endif

                        @php $address = App\DataOption::where('slug', 'address')->first(); @endphp
                        @if($address)
                        <a href="#footer"><b><span class="fa fa-map-marker"></span> {{$address->option_value}}</b></a>
                        
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12" style="margin-top: 30px">
                <div class="row">
                    <div class="col-sm-12">                    
                        @php $phone = App\DataOption::where('slug', 'phone')->first(); @endphp
                        @if($phone)
                        <a href="#footer"><b><span class="fa fa-phone"></span> {{$phone->option_value}}</b></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 d-flex justify-content-center" style="margin-top: 30px">

                @php
                $socmed = App\SocialMedia::where('status', 1)->get();
                if($socmed){
                  foreach ($socmed as $key => $value) {
                    echo '<a href="'.$value->url.'" class="socmed-icon" target="_blank">
                      <img src="'.asset($value->icon).'" alt="">
                    </a>';
                  }
                }
              @endphp
              
            </div>
            <!-- <div class="col-md-3 col-sm-6 col-xs-12">
            <p class="footer-title">SECURE SHOPPING</p>
              <img src="{{ asset('assets/img/credit/visa.png') }}" alt="" class="img-fluid">
              <img src="{{ asset('assets/img/credit/american-express.png') }}" alt="" class="img-fluid">
              <img src="{{ asset('assets/img/credit/mastercard.png') }}" alt="" class="img-fluid">
              <img src="{{ asset('assets/img/credit/paypal2.png') }}" alt="" class="img-fluid">
            </div> -->
        </div>
    </div>
</section>

<div class="copyright">
    <div class="container">
        <div class="d-flex">
            <div class="p-2">SAKURA</div>
            <div class="p-2">Copyright <i class="fa fa-copyright"></i> {{date('Y')}}. Sakura - All rights reserved</div>
            <div class="ml-auto p-2">
                @php 
                    $page = App\Page::where('slug','panduan-keamanan')->get();
                @endphp
                @foreach($page as $row)
                    <a href="{{url('page/'.$row->slug)}}" class="col-white">{{$row->title}}</a>
                @endforeach
                | 
                @php 
                    $page = App\Page::where('slug','kebijakan-privasi')->get();
                @endphp
                @foreach($page as $row)
                    <a href="{{url('page/'.$row->slug)}}" class="col-white">{{$row->title}}</a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@php 
$opt = App\DataOption::where('slug','tawk')->get();
@endphp
@foreach($opt as $row)
{!!$row->option_value!!}
@endforeach
