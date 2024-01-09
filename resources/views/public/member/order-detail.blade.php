@extends('public.layout.member-layout')

@section('member-content')

@php
$user = Auth::user();
@endphp

<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <a href="{{url('member/order')}}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Kembali</a>
            </header>

            <div class="panel-body">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">

                            <div class="box-order">
                                <div class="order-header">
                                    Transaction Detail
                                </div>
                                <div class="order-body">
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Nama</b></label>
                                        <div class="col-sm-8">
                                            <p> : {!! $order->name !!}</p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Telp</b></label>
                                        <div class="col-sm-8">
                                            <p> : {!! $order->phone !!}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Total
                                                Belanja</b></label>
                                        <div class="col-sm-8">
                                            <p> : Rp {!! number_format($order->payment_total) !!}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Kurir</b></label>
                                        <div class="col-sm-8">
                                        <p> : {{$order->courier}}</p>
                                        <p> : {{$order->delivery_service}}</p>
                                        <p> : Rp {{number_format($order->delivery_fee)}}</p>
                                        <p> : Resi {{$order->delivery_track}}</p>
                                        @if($order->photo)
                                            <img src="{{asset('images/'.$order->photo)}}" width="100px">
                                        @endif
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Discount</b></label>
                                        <div class="col-sm-8">
                                        : Rp {!! number_format($order->payment_discount) !!} @if($order->payment_discount_code!="") <span class="status status-danger">{{$order->payment_discount_code}} </span>@endif
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Total
                                                Pembayaran</b></label>
                                        <div class="col-sm-8">
                                            <p> : Rp {!! number_format($order->payment_final) !!}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b>Alamat Kirim</b></label>
                                        <div class="col-sm-8">
                                            <p> : {{$order->address}}</p>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail3" class="col-sm-4 col-form-label"><b> Status
                                            </b></label>
                                        <div class="col-sm-8">
                                            <p> :
                                                @if($order->status==1)
                                                <span class="status status-success">Transaksi Baru</span>
                                                @elseif($order->status==2)
                                                <span class="status status-success">Pembayaran Pending</span>
                                                @elseif($order->status==7)
                                                <span class="status status-success">Transaksi sudah dibayar </span>
                                                @elseif($order->status==3)
                                                <span class="status status-warning">Pengiriman</span>
                                                @elseif($order->status==4)
                                                <span class="status status-danger">Selesai</span>
                                                @elseif($order->status==5)
                                                <span class="status status-danger">Batal</span>
                                                @endif
                                            </p>
                                        </div>
                                        <br>
                                    </div>

                                    <style>
                                        .rating-header {
                                            margin-top: -10px;
                                            margin-bottom: 10px;
                                        }
                                    </style>

                                </div>
                            </div>

                        </div>

                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="box-order">
                                <div class="order-header">
                                    Order Detail
                                </div>
                                <div class="order-body">

                                    <div class="product mb-2">
                                        <!-- looping -->
                                        <form action="{{url('member/submit-review/'.$order->id)}}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @php 
                                            $total = 0;
                                            $weight = 0;
                                        @endphp
                                        @foreach($order->data_item as $row)
        
                                        @if($row->product)
                                        <div class="row">
                                            <div class="col-2">
                                                <img src="{{ asset($row->product->image) }}" alt=""  style="border: solid 1px #ddd;" class="img-fluid">
                                            </div>
                                            <div class="col-8">
                                                <p>{{$row->product->name}}<br>
                                                <span style="color: red">Rp. {{number_format($row->price)}} | {{$row->qty}} Item </span></p>
                                            </div>

                                            @php 
                                                $val = 0;
                                                $review = "";
                                                $prod_review = \App\ProductReview::where('order_id', $order->id)->where('product_id', $row->product->id)->first();
                                                if($prod_review){
                                                    $val = $prod_review->star_review;
                                                    $review = $prod_review->detail_review;
                                                }
                                            @endphp

                                            @if($order->status=='4')
                                            <div class="card-body">
                                                <div class="form-group" id="rating-ability-wrapper{{$row->product->id}}">
                                                    <label class="control-label" for="rating{{$row->product->id}}">
                                                    <span class="field-label-header">Rate produk ini?</span><br>
                                                    <span class="field-label-info"></span>
                                                    <input type="hidden" id="reviewid{{$row->product->id}}" name="review_id[]" value="" required="required">
                                                    <input type="hidden" id="id{{$row->product->id}}" name="id[]" value="{{$row->product->id}}" required="required">
                                                    <input type="hidden" id="selected_rating{{$row->product->id}}" name="selected_rating[]" value="" required="required">
                                                    </label>
                                                    <h4 class="bold rating-header" style="">
                                                    <span class="selected-rating{{$row->product->id}}">{{$val}}</span><small> / 5</small>
                                                    </h4>
                                                    <button type="button" class="btnrating btn btn-default brn-sm @if($val>=1 && $val!=0) btn-warning @endif" data-id="{{$row->product->id}}" data-attr="1" id="rating-star-{{$row->product->id}}-1">
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="btnrating btn btn-default brn-sm @if($val>=2 && $val!=0) btn-warning @endif" data-id="{{$row->product->id}}" data-attr="2" id="rating-star-{{$row->product->id}}-2">
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="btnrating btn btn-default brn-sm @if($val>=3 && $val!=0) btn-warning @endif" data-id="{{$row->product->id}}" data-attr="3" id="rating-star-{{$row->product->id}}-3">
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="btnrating btn btn-default brn-sm @if($val>=4 && $val!=0) btn-warning @endif" data-id="{{$row->product->id}}" data-attr="4" id="rating-star-{{$row->product->id}}-4">
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" class="btnrating btn btn-default brn-sm @if($val>=5 && $val!=0) btn-warning @endif" data-id="{{$row->product->id}}" data-attr="5" id="rating-star-{{$row->product->id}}-5">
                                                        <i class="fa fa-star" aria-hidden="true"></i>
                                                    </button>
                                                    <br>
                                                    <br>
                                                    <!-- <input type="file" name="image[]" multiple> -->
                                                    <br>
                                                    <textarea name="review[]" class="form-control" style="margin-top: 15px" placeholder="Komentar">{{$review}}</textarea>
                                                </div>
                                            </div>

                                            @endif
                                            
                                        </div>
                                        @endif
                                        @php 
                                            $total = $total + ((float)$row->price*(float)$row->qty);
                                            $weight = $weight + ((float)$row->weight*(float)$row->qty);
                                        @endphp
                                        @endforeach
                                            @if($order->status=='4')
                                            <button type="submit" class="btn button-blue">Simpan Review</button> 
                                            @endif
                                        </form>
                                    </div>
                                    <hr>
                                        @php 
                                            $weight = (float)$weight * 1000;
                                        @endphp
                                    <div class="text-right">Total Belanja : Rp. {{number_format($total)}}</div>
                                    <div class="text-right">Berat : {{number_format($order->order_weight, 2)}} gram</div>
                                    <div class="clearfix"></div>

                                </div>
                            </div>

                        </div>

                        <div class="col-12 col-md-12 col-sm-12 col-xs-12">

                            <div class="box-order">
                                <div class="order-header">
                                    Pembayaran dan Bukti Transfer
                                </div>
                                <div class="order-body">

                                    <div class="col-md-12">
                                        <div class="payment-notif">
                                            <!-- Countdown dashboard start -->
                                                <div id="countdown_dashboard">
                                                    @if($order->status==1)
                                                        <h4>Akan dikonfirmasi sebelum : </h4>
                                                    @endif

                                                    @if($order->status==2 && $order->payment_link=="")
                                                        <h4>Upload Bukti transfer sebelum : </h4>
                                                    @endif

                                                    @if($order->status==2 && $order->payment_link!="")
                                                        <h4>Menunggu Konfirmasi Bukti transfer sebelum : </h4>
                                                    @endif

                                                    @if($order->status==1)
                                                    <div class="dash days_dash">
                                                        <span class="dash_title">days</span>
                                                        <div class="digit">0</div>
                                                        <div class="digit">6</div>
                                                    </div>

                                                    <div class="dash hours_dash">
                                                        <span class="dash_title">hours</span>
                                                        <div class="digit">1</div>
                                                        <div class="digit">5</div>
                                                    </div>
                                            
                                                    <div class="dash minutes_dash">
                                                        <span class="dash_title">minutes</span>
                                                        <div class="digit">0</div>
                                                        <div class="digit">4</div>
                                                    </div>
                                            
                                                    <div class="dash seconds_dash">
                                                        <span class="dash_title">seconds</span>
                                                        <div class="digit">3</div>
                                                        <div class="digit">3</div>
                                                    </div>
                                                    @endif

                                                    @if($order->status=="2")
                                                        <div class="dash days_dash">
                                                            <span class="dash_title">days</span>
                                                            <div class="digit">0</div>
                                                            <div class="digit">6</div>
                                                        </div>
                                            
                                                        <div class="dash hours_dash">
                                                            <span class="dash_title">hours</span>
                                                            <div class="digit">1</div>
                                                            <div class="digit">5</div>
                                                        </div>
                                            
                                                        <div class="dash minutes_dash">
                                                            <span class="dash_title">minutes</span>
                                                            <div class="digit">0</div>
                                                            <div class="digit">4</div>
                                                        </div>
                                            
                                                        <div class="dash seconds_dash">
                                                            <span class="dash_title">seconds</span>
                                                            <div class="digit">3</div>
                                                            <div class="digit">3</div>
                                                        </div>
                                                    @endif                                                    
                                                </div>
                                                <div class="clearfix"></div>
                                                <!-- Countdown dashboard end -->

                                                    @if($order->status==2 && $order->payment_link!="")
                                                        <img src="{{asset($order->payment_link)}}" class="img-fluid" style="max-width: 200px; margin-bottom: 10px">
                                                        <br>
                                                        <h6><span class="status status-success">Transaksi sudah dibayar </span></h6>
                                                        <a href="{{asset($order->payment_link)}}" target="_blank">Lihat detail</a>
                                                    @endif
                                                    @if($order->status==7)
                                                        <img src="{{asset($order->payment_link)}}" class="img-fluid" style="max-width: 200px; margin-bottom: 10px">
                                                        <h6><span class="status status-success">Transaksi sudah dibayar </span></h6>
                                                        <a href="{{asset($order->payment_link)}}" target="_blank">Lihat detail</a>
                                                    @endif
                                        </div>

                                        <a href="{{ url('member/invoice', $order->id) }}" target="_blank" class="btn button-blue" style="margin-bottom: 5px">Cetak Invoice</a>
                                        @if($order->status==2)
                                        <a href="javascript:void(0)" class="btn button-blue" data-toggle="modal" style="margin-bottom: 5px"
                                            data-target="#proof_payment">
                                            Upload Bukti Transfer
                                        </a>

                                        <a href="{{ url('member/payment', $order->id) }}" target="_blank" class="btn button-blue" style="margin-bottom: 5px">Informasi Pembayaran</a>

                                        @endif

                                    </div>
                                    <!-- Modal -->
                                    <div class="modal fade" id="proof_payment" data-backdrop="static" tabindex="-1"
                                        role="dialog" aria-labelledby="proof_paymentLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <b>Bukti Transaksi</b>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                <form method="post" action="{{url('member/payment-upload')}}" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{$order->id}}">
                                                        <input type="file" name="image">
                                                        <button type="submit" class="btn btn-primary">Upload</button>
                                                    </form>
                                                </div>
                                        </div>
                                    </div>
                                </div>

                            </div>


                        </div>


                    </div>
                </div>

            </div>
    </div>
    </section>
</div>
</div>

<style>
    .order-header{
        background: rgba(33, 46, 116, 0.1);
        padding: 5px 15px 5px 15px;
        margin-bottom: 10px;
    }


#countdown_dashboard {
  margin: 0 auto;
  padding: 15px 0px 15px 0px;
  border-radius: 3px;
  /*border:1px solid #eee;*/
  background: rgba(255,255,255,0.1);
  box-shadow: 5px;
  
}

.dash {
	width: 65px;
	height: 85px;
	float: left;
	margin-left: 0px;
	position: relative;
  text-align: center;
}

/*.digit {
  border-top:5px solid #000;
}
*/
.dash .digit {
  background: #333;
  color: #fff;
	font-size: 15pt;
	font-weight: 800;
	float: left;
	width: 24px;
  padding: 5px 0;
	text-align: center;
	font-family: inherit;
	position: relative;
   margin: 1px;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
}

.dash_title {
	
	display: block;
	font-size: 7.5pt;
  margin-bottom: 5px;
  text-align: left;
	color: #777;
	text-transform: uppercase;
	letter-spacing: 2px;
  font-weight:100;
}

hr {margin: 30px 0;}

hr.dark {
  height:1px;
  background: #111;
  border: 0;
  border-bottom:1px solid #222;
}
hr.light {
  height:1px;
  background: #ccc;
  border: 0;
  border-bottom:1px solid #fff;
}

.clearfix {
  clear:both;
}

</style>

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

@if($order->status==1)
    @php 
        $date = date("Y-m-d H:i:s", strtotime('+24 hours', strtotime($order->created_at)));
    @endphp
@endif

@if($order->status==2)
    @php 
        $date = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($order->updated_at)));
    @endphp
@endif

<script src="{{asset('js/countdown-jquery.min.js')}}"></script>

<script>
    /* jQuery Countdown plugin v0.9.5  */

(function($){

$.fn.countDown = function (options) {

    config = {};

    $.extend(config, options);

    diffSecs = this.setCountDown(config);

    $('#' + $(this).attr('id') + ' .digit').html('<div class="top"></div><div class="bottom"></div>');
    $(this).doCountDown($(this).attr('id'), diffSecs, 500);

    if (config.onComplete)
    {
        $.data($(this)[0], 'callback', config.onComplete);
    }
    if (config.omitWeeks)
    {
        $.data($(this)[0], 'omitWeeks', config.omitWeeks);
    }
    return this;

};

$.fn.stopCountDown = function () {
    clearTimeout($.data(this[0], 'timer'));
};

$.fn.startCountDown = function () {
    this.doCountDown($(this).attr('id'),$.data(this[0], 'diffSecs'), 500);
};

$.fn.setCountDown = function (options) {
    var targetTime = new Date();

    if (options.targetDate)
    {
        targetTime.setDate(options.targetDate.day);
        targetTime.setMonth(options.targetDate.month-1);
        targetTime.setFullYear(options.targetDate.year);
        targetTime.setHours(options.targetDate.hour);
        targetTime.setMinutes(options.targetDate.min);
        targetTime.setSeconds(options.targetDate.sec);
    }
    else if (options.targetOffset)
    {
        targetTime.setDate(options.targetOffset.day + targetTime.getDate());
        targetTime.setMonth(options.targetOffset.month + targetTime.getMonth());
        targetTime.setFullYear(options.targetOffset.year + targetTime.getFullYear());
        targetTime.setHours(options.targetOffset.hour + targetTime.getHours());
        targetTime.setMinutes(options.targetOffset.min + targetTime.getMinutes());
        targetTime.setSeconds(options.targetOffset.sec + targetTime.getSeconds());
    }

    var nowTime = new Date();

    diffSecs = Math.floor((targetTime.valueOf()-nowTime.valueOf())/1000);

    $.data(this[0], 'diffSecs', diffSecs);

    return diffSecs;
};

$.fn.doCountDown = function (id, diffSecs, duration) {
    $this = $('#' + id);
    if (diffSecs <= 0)
    {
        diffSecs = 0;
        if ($.data($this[0], 'timer'))
        {
            clearTimeout($.data($this[0], 'timer'));
        }
    }

    secs = diffSecs % 60;
    mins = Math.floor(diffSecs/60)%60;
    hours = Math.floor(diffSecs/60/60)%24;
    if ($.data($this[0], 'omitWeeks') == true)
    {
        days = Math.floor(diffSecs/60/60/24);
        weeks = Math.floor(diffSecs/60/60/24/7);
    }
    else 
    {
        days = Math.floor(diffSecs/60/60/24)%7;
        weeks = Math.floor(diffSecs/60/60/24/7);
    }

    $this.dashChangeTo(id, 'seconds_dash', secs, duration ? duration : 800);
    $this.dashChangeTo(id, 'minutes_dash', mins, duration ? duration : 1200);
    $this.dashChangeTo(id, 'hours_dash', hours, duration ? duration : 1200);
    $this.dashChangeTo(id, 'days_dash', days, duration ? duration : 1200);
    $this.dashChangeTo(id, 'weeks_dash', weeks, duration ? duration : 1200);

    $.data($this[0], 'diffSecs', diffSecs);
    if (diffSecs > 0)
    {
        e = $this;
        t = setTimeout(function() { e.doCountDown(id, diffSecs-1) } , 1000);
        $.data(e[0], 'timer', t);
    } 
    else if (cb = $.data($this[0], 'callback')) 
    {
        $.data($this[0], 'callback')();
    }

};

$.fn.dashChangeTo = function(id, dash, n, duration) {
    $this = $('#' + id);
    d2 = n%10;
    d1 = (n - n%10) / 10

    if ($('#' + $this.attr('id') + ' .' + dash))
    {
        $this.digitChangeTo('#' + $this.attr('id') + ' .' + dash + ' .digit:first', d1, duration);
        $this.digitChangeTo('#' + $this.attr('id') + ' .' + dash + ' .digit:last', d2, duration);
    }
};

$.fn.digitChangeTo = function (digit, n, duration) {
    if (!duration)
    {
        duration = 800;
    }
    if ($(digit + ' div.top').html() != n + '')
    {

        $(digit + ' div.top').css({'display': 'none'});
        $(digit + ' div.top').html((n ? n : '0')).slideDown(duration);

        $(digit + ' div.bottom').animate({'height': ''}, duration, function() {
            $(digit + ' div.bottom').html($(digit + ' div.top').html());
            $(digit + ' div.bottom').css({'display': 'block', 'height': ''});
            $(digit + ' div.top').hide().slideUp(10);

        
        });
    }
    }
;
$(document).ready(function() {

    @if($order->status==1)
    var s = ("{{$date}}");
    var a = s.split(/[^0-9]/);
    var ts=new Date (a[0],a[1]-1,a[2],a[3],a[4],a[5] );
    console.log(ts.getDate()+"-"+(ts.getMonth()+1)+"-"+ts.getFullYear()+"-"+ts.getHours()+"-"+ts.getMinutes()+"-"+ts.getSeconds())
    @endif

    @if($order->status==2)
    var s = ("{{$date}}");
    var a = s.split(/[^0-9]/);
    var ts=new Date (a[0],a[1]-1,a[2],a[3],a[4],a[5] );
    console.log(ts.getDate()+"-"+(ts.getMonth()+1)+"-"+ts.getFullYear()+"-"+ts.getHours()+"-"+ts.getMinutes()+"-"+ts.getSeconds())
    @endif
    
    $('#countdown_dashboard').countDown({
        targetDate: {
            'day': 		ts.getDate(),
            'month': 	(ts.getMonth()+1),
            'year': 	ts.getFullYear(),
            'hour': 	ts.getHours(),
            'min': 		ts.getMinutes(),
            'sec': 		ts.getSeconds(),
            'utc':    true
        }, omitWeeks: true
      
    });
});
})(jQuery);


$(".btnrating").on('click',(function(e) {
                                    
    var id = $(this).attr('data-id');

    var previous_value = $("#selected_rating"+id).val();

    var selected_value = $(this).attr("data-attr");
    $("#selected_rating"+id).val(selected_value);
    
    $(".selected-rating"+id).empty();
    $(".selected-rating"+id).html(selected_value);

    for (i = 1; i <= selected_value; ++i) {
        $("#rating-star-"+id+"-"+i).toggleClass('btn-warning');
        $("#rating-star-"+id+"-"+i).toggleClass('btn-default');
    }
    
    for (ix = 1; ix <= previous_value; ++ix) {
        $("#rating-star-"+id+"-"+ix).toggleClass('btn-warning');
        $("#rating-star-"+id+"-"+ix).toggleClass('btn-default');
    }

}));

</script>

<!-- countdown times out -->
<script>
    @if($order->status==1)
    CountDownTimer('{{$order->created_at}}', 'countdown_dashboard');
        function CountDownTimer(dt, id){
            var s = ("{{$date}}");
            var a = s.split(/[^0-9]/);
            var countDownDate=new Date (a[0],a[1]-1,a[2],a[3],a[4],a[5] ).getTime();
			var _second = 1000;
			var _minute = _second * 60;
			var _hour = _minute * 60;
			var _day = _hour * 24;
			var timer;
            
            function showRemaining() {
                var now = new Date();
                var distance = countDownDate - now;
				if (distance < 0) {
					clearInterval(timer);
					document.getElementById(id).innerHTML = '<p style="color:red">Waktu konfirmasi telah berakhir</p>';
					return;
					}
				}
		    timer = setInterval(showRemaining, 1000);
		}
    @endif
    @if($order->status==2)
    CountDownTimer('{{$order->updated_at}}', 'countdown_dashboard');
        function CountDownTimer(dt, id){
            var s = ("{{$date}}");
            var a = s.split(/[^0-9]/);
            var countDownDate=new Date (a[0],a[1]-1,a[2],a[3],a[4],a[5] ).getTime();
			var _second = 1000;
			var _minute = _second * 60;
			var _hour = _minute * 60;
			var _day = _hour * 24;
			var timer;
            
            function showRemaining() {
                var now = new Date();
                var distance = countDownDate - now;
				if (distance < 0) {
					clearInterval(timer);
					document.getElementById(id).innerHTML = '<p style="color:red">Waktu upload pembayaran telah berakhir</p>';
					return;
					}
				}
		    timer = setInterval(showRemaining, 1000);
		}
    @endif
</script>
@endsection
