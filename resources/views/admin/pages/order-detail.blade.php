@extends('admin.layout.template')

@section('content')

<div class="row">
    <div class="col-md-12">
        <section class="panel">
            <header class="panel-heading">
                <a href="{{url('admin/orders')}}" class="btn btn-blue">
                    <i class="fa fa-arrow-left"></i></a>&nbsp Kembali
            </header>

            <div class="card-body form-activity">
            <a href="{{url('admin/delivery/'.$order->id)}}" class="btn btn-neutral" target="_blank"><i class="fa fa-print"></i> Cetak Alamat</a> &nbsp
            <a href="{{url('admin/invoice/'.$order->id)}}" class="btn btn-neutral" target="_blank"><i class="fa fa-print"></i> Cetak Invoice</a> &nbsp

            @if($order->status==1)
            <form id="finish-order" method="post" action="{{url('admin/update-detail-order')}}">
                @csrf
                <input name="order-id" type="hidden" value="{{$order->id}}">
                <input name="type" type="hidden" value="update-status">
                <input name="status" type="hidden" value="2">
                <button type="submit" class="btn btn-green" onclick="return confirm('Proses?')">Konfirmasi</button>
            </form>
            &nbsp
            @endif
            {{-- @if($order->status==2 && $order->payment_link!="")
            <form id="finish-order" method="post" action="{{url('admin/update-detail-order')}}">
                @csrf
                <input name="order-id" type="hidden" value="{{$order->id}}">
                <input name="type" type="hidden" value="update-status">
                <input name="status" type="hidden" value="7">
                <button type="submit" class="btn btn-green" onclick="return confirm('Proses?')">Konfirmasi Pembayaran</button>
            </form>
            &nbsp
            @endif --}}
            @if($order->status==2)
            <form id="finish-order" method="post" action="{{url('admin/update-detail-order')}}">
                @csrf
                <input name="order-id" type="hidden" value="{{$order->id}}">
                <input name="type" type="hidden" value="update-status">
                <input name="status" type="hidden" value="3">
                <button type="submit" class="btn btn-warning"  onclick="return confirm('Proses?')">Pengiriman</button>
            </form>
            &nbsp
            @endif
            @if($order->status==3)
            <form id="finish-order" method="post" action="{{url('admin/update-detail-order')}}">
                @csrf
                <input name="order-id" type="hidden" value="{{$order->id}}">
                <input name="type" type="hidden" value="update-status">
                <input name="status" type="hidden" value="4">
                <button type="submit" class="btn btn-blue" C>Selesaikan</button>
            </form>
            &nbsp
            @endif
            @if($order->status != 4 && $order->status != 10)
            <form id="finish-order" method="post" action="{{url('admin/update-detail-order')}}">
                @csrf
                <input name="order-id" type="hidden" value="{{$order->id}}">
                <input name="type" type="hidden" value="update-status">
                <input name="status" type="hidden" value="10">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Proses?')">Batalkan</button>
            </form>
            &nbsp
            @endif
            
            </div>

            <div class="card-body">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 col-sm-5 col-xs-12">

                            <div class="box-order">
                                <div class="order-header">
                                    Order Detail
                                </div>
                                <div class="order-body">
                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Invoice ID</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $order->invoice }}
                                        </div>
                                    </label>
                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Nama Penerima</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $order->name }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>No. Telp</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $order->phone }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Alamat</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ ucwords($order->address) }}, {{ ucwords($order->kelurahan) }}, {{ ucwords($order->kecamatan) }}, {{ ucwords($order->kota) }}, {{ ucwords($order->provinsi) }} {{ $order->kode_pos }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Catatan</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ isset($order->notes) ? $order->notes : "Tidak ada" }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>App Version</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ isset($order->app_version) ? $order->app_version : "Belum ada"  }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Sales Code</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : Belum ada
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Tanggal Order</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ date('l, d M Y H:i:s', strtotime($order->order_time)) }}
                                        </div>
                                    </label>

                                    {{-- <label class="row">
                                        <div class="col-sm-4">
                                            <b>Dikirim Pada</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $order->delivery_time }}
                                        </div>
                                    </label> --}}

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Partner Assignment</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : Belum ada
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Pengorder</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $order->data_user->name }}
                                        </div>
                                    </label>

                                    {{-- <label class="row">
                                        <div class="col-sm-4">
                                            <b>Status COD</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ $order->delivery_status }}
                                        </div>
                                    </label> --}}

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Payment Type</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : {{ strtoupper($order->payment_method) }}
                                        </div>
                                    </label>

                                    <label class="row">
                                        <div class="col-sm-4">
                                            <b>Status Order</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : 
                                            @if ($order->status == '1')
                                                Menunggu konfirmasi
                                            @elseif($order->status == '2')
                                                Konfirmasi pembayaran
                                            @elseif($order->status == '7')
                                                Persiapan pengiriman
                                            @elseif($order->status == '3')
                                                Pengiriman
                                            @elseif($order->status == '4')
                                                Konfirmasi
                                            @elseif($order->status == '10')
                                                Order Batal
                                            @endif
                                        </div>
                                    </label>

                                    {{-- <label class="row">
                                        <div class="col-sm-4">
                                            <b>Feedback</b>
                                        </div>
                                        <div class="col-sm-8">
                                            : Belum ada
                                        </div>
                                    </label> --}}
                                </div>
                            </div>

                        </div>

                        <div class="col-md-7 col-sm-7 col-xs-12">
                            <div class="box-order-items">
                                <div class="order-header">
                                    Order Items
                                </div>
                                <div class="order-items">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    {{-- <th scope="col">No</th> --}}
                                                    <th scope="col">Product Name</th>
                                                    <th scope="col">QTY</th>
                                                    <th scope="col">Price</th>
                                                    <th scope="col">Outlet</th>
                                                    <th scope="col">Diskon Outlet</th>
                                                    <th scope="col">Sub Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $order_disc   = 0;
                                                $total_price  = 0;
                                                @endphp
                                                @foreach ($orderDetails as $key => $orderDetail)
                                                @php
                                                $order_disc +=  $orderDetail->rp_cabang;
                                                $total_price += ($orderDetail->qty * $orderDetail->price_apps);
                                                @endphp
                                                    <tr>
                                                        {{-- <th scope="row">{{ $key+1 }}</th> --}}
                                                        <td>{{ $orderDetail->product->name }}</td>
                                                        <td>{{ $orderDetail->qty }}</td>
                                                        <td>Rp. {{ number_format($orderDetail->price_apps, 2, ',', '.') }}</td>
                                                        <td>{{ucfirst(strtolower($orderDetail->order->user->class))}}</td>
                                                        {{-- <td>{{$orderDetail->product->brand_id}}</td> --}}
                                                        <td>
                                                            {{-- @if($orderDetail->order->user->salur_code == 'RT') Tidak ada diskon 
                                                            @elseif($orderDetail->order->user->salur_code == 'SW') 3% 
                                                            @elseif($orderDetail->order->user->salur_code == 'WS' || $orderDetail->order->user->salur_code == 'SO') 4.5% 
                                                            @endif --}}
                                                            Rp. {{number_format($orderDetail->rp_cabang, 2, ',', '.')}} ({{$orderDetail->disc_cabang}} %)
                                                        </td>
                                                        <td>Rp. {{ number_format($orderDetail->total_price, 2, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                  <th scope="row">Items</th>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td>{{ count($orderDetails) }}</td>
                                                </tr>
                                                {{-- <tr>
                                                  <th scope="row">Berat</th>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                </tr> --}}
                                                <tr>
                                                  <th scope="row">Sub Total</th>
                                                  <td></td>
                                                  <td>Rp. {{ number_format($total_price, 2, ',', '.') }}</td>
                                                  <td></td>
                                                  <td>Rp. {{number_format($order_disc, 2, ',', '.')}}</td>
                                                  <td>Rp. {{ number_format($order->payment_total, 2, ',', '.') }}</td>
                                                </tr>
                                                <tr>
                                                  <th scope="row">Ongkir</th>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td>Bebas Ongkir</td>
                                                </tr>
                                                <!-- <tr>
                                                  <th scope="row">Discount</th>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td>Rp. {{ number_format($order->payment_discount, 1, ',', '.') }}</td>
                                                </tr> -->
                                                <!-- <tr>
                                                  <th scope="row">Kode Unik</th>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td>{{ $orderDetail->payment_discount_code }}</td>
                                                </tr> -->
                                                @if($orderPromos)
                                                    @foreach($orderPromos as $row) 
                                                    <tr>
                                                        <th scope="row">{{ $row->promo->title }}</th>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>
                                                            @php
                                                            $total_point    = null;
                                                            $total          = null;
                                                            @endphp
                                                            @if ($row->disc_principal)
                                                                {{ $row->disc_principal }}% (Rp. {{ isset($row->disc_nominal) ? $row->disc_nominal : 0 }})
                                                            @elseif ($row->rp_principal)
                                                                Rp {{ number_format($row->rp_principal)}}
                                                            @elseif ($row->point_principal)
                                                                {{ $row->point_principal }} Point
                                                            @elseif($row->bonus)
                                                            @php
                                                            $reward = $row->promo->reward
                                                                                    ->where('promo_id', $row->promo_id)
                                                                                    ->where('reward_product_id', $row->bonus)
                                                                                    ->first();
                                                            @endphp
                                                                {{ $row->bonus_name }} - ( {{ $row->bonus_qty }} {{ $reward->satuan }} )
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                                <tr>
                                                  <th scope="row">Total Pembayaran</th>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td></td>
                                                  <td>Rp. {{ number_format($order->payment_final, 2, ',', '.') }}</td>
                                                  @php 
                                                    $total_price = 0;
                                                  @endphp
                                                  @foreach($orderDetails as $row)
                                                    @php
                                                        $total_price += $row->total_price;
                                                    @endphp
                                                  @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="box-order-status">
                                <div class="order-header">
                                    Order Status
                                </div>
                                <div class="order-status">
                                    @if ($order->status != 10)
                                        <div class="row pgb p-0 mt-3">
                                            <div class="col step complete p-0"><p>1</p>
                                                <span class="img-circle"></span>
                                            </div>
                                            <div class="col step @if($order->status >= 2) complete @endif p-0"><p>2</p>
                                                <span class="img-circle"></span>
                                            </div>
                                            <div class="col step @if($order->status >= 3) complete @endif p-0"><p>3</p>
                                                <span class="img-circle"></span>
                                            </div>
                                            <div class="col step @if($order->status >= 4) complete @endif p-0"><p>4</p>
                                                <span class="img-circle"></span>
                                            </div>
                                        </div>
                                        <div class="row pgb p-0">
                                            <div class="col text-center"><p>Pemesanan</p></div>
                                            <div class="col text-center"><p>Konfirmasi</p></div>
                                            <div class="col text-center"><p>Pengiriman</p></div>
                                            <div class="col text-center"><p>Selesai</p></div>
                                        </div>
                                    @elseif($order->status == '10')     
                                    <div class="row pgb p-0 mt-3">
                                        <div class="col step active p-0"><p></p>
                                            <span class="img-circle"></span>
                                        </div>                
                                    </div>
                                    <div class="row pgb p-0">
                                        <div class="col text-center"><p>Batal</p></div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="box-order-tracking">
                                <div class="order-header">
                                    Order Tracking
                                </div>
                                <div class="order-tracking">
                                    @php
                                        $created = \Carbon\Carbon::parse($order->created_at);
                                        $payment_date = \Carbon\Carbon::parse($order->payment_date);
                                        $payment_confirm_date = \Carbon\Carbon::parse($order->confirmation_time);
                                        $delivery_time = \Carbon\Carbon::parse($order->delivery_time);
                                        $final_time = \Carbon\Carbon::parse($order->complete_time);
                                        $now = \Carbon\Carbon::now();
                                    @endphp
                                    <div class="alert alert-primary" role="alert">
                                        <span>{{$created->diffForHumans($now)}}</span><br>
                                        <strong>New Order</strong>
                                    </div>
                                    
                                    {{-- @if ($order->status >= 2 && $payment_date != null)
                                        <div class="alert alert-primary" role="alert">
                                            <span>{{$payment_date->diffForHumans($now)}}</span><br>
                                            <strong>Payment</strong>
                                        </div>
                                    @endif --}}

                                    {{-- @if ($order->status == 7 && $payment_confirm_date != null)
                                        <div class="alert alert-primary" role="alert">
                                            <span>{{$payment_confirm_date->diffForHumans($now)}}</span><br>
                                            <strong>Payment Confirmation</strong>
                                        </div>
                                    @endif --}}

                                    @if ($order->status >= 2)
                                        <div class="alert alert-primary" role="alert">
                                            <span>{{$payment_confirm_date->diffForHumans($now)}}</span><br>
                                            <strong>Order Confirmed</strong>
                                        </div>
                                    @endif

                                    @if ($order->status >= 3 && $delivery_time != null && $order->status != 7)
                                        <div class="alert alert-primary" role="alert">
                                            <span>{{$delivery_time->diffForHumans($now)}}</span><br>
                                            <strong>Delivery Process</strong>
                                        </div>
                                    @endif

                                    @if ($order->status == 4 && $final_time != null)
                                        <div class="alert alert-success" role="alert">
                                            <span>{{$final_time->diffForHumans($now)}}</span><br>
                                            <strong>Confirmation</strong>
                                        </div>
                                    @endif

                                    @if ($order->status == 10 && $final_time != null)
                                        <div class="alert alert-danger" role="alert">
                                            <span>{{$final_time->diffForHumans($now)}}</span><br>
                                            <strong>Cancel Order</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12">
                            {{-- <div class="box-order">
                                <div class="order-header">
                                    Pembayaran dan Bukti Transfer
                                </div>
                                <div class="order-body">

                                    <div class="col-md-12">
                                        <div class="payment-notif">                                            
                                            <!-- Countdown dashboard start -->
                                                <div id="countdown_dashboard">
                                                    @if($order->status==2 && $order->payment_link=="")
                                                        <h4>Menunggu upload bukti transfer sebelum : </h4>
                                                    @endif

                                                    @if($order->status==2 && $order->payment_link!="")
                                                        <h4>Konfirmasi Bukti transfer sebelum : </h4>
                                                    @endif

                                                    @if($order->status=="1")
                                                        <h4>Konfirmasi sebelum : </h4>

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

                            </div> --}}
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

.form-activity{
    overflow: hidden;
}
.form-activity a,
.form-activity form,
.form-activity button{
    float: left;
    margin-right: 5px;
    margin-bottom: 5px;
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

.form-activity{
    overflow: hidden;
}
.form-activity a,
.form-activity form,
.form-activity button{
    float: left;
    margin-right: 5px;
    margin-bottom: 5px;
}

.pgb .step {
    text-align: center;
    position:relative;
}
.pgb h2 {
    font-size:1.3rem;
}
.pgb .step p {
    position:absolute;
    height:60px;
    width:100%;
    text-align:center;
    display:block;
    z-index:3;
    color:#fff;
    font-size:160%;
    line-height:55px;
    opacity:.7;
}
.pgb .active.step p {
    opacity:1;
    font-weight:600;
}
.pgb .img-circle {
    display: inline-block;
    width: 60px;
    height: 60px;
    border-radius:50%;
    background-color:#9E9E9E;
    border:4px solid #fff;
}
.pgb .complete .img-circle {
    background-color:#4CAF50;
}
.pgb .active .img-circle {
    background-color:#FF9800;
}
.pgb .step .img-circle:before {
    content: "";
    display: block;
    background: #9E9E9E;
    height: 4px;
    width: 50%;
    position: absolute;
    bottom: 50%;
    left: 0;
    z-index: -1;
    margin-right:24px;
}
.pgb .step .img-circle:after {
    content: "";
    display: block;
    background: #9E9E9E;
    height: 4px;
    width: 50%;
    position: absolute;
    bottom: 50%;
    left: 50%;
    z-index: -1;
}
.pgb .step.active .img-circle:after {
    background: #9E9E9E;
}

.pgb .step.complete .img-circle:after, .pgb .step.complete .img-circle:before {
    background: #4CAF50 !important;
}

.pgb .step:last-of-type .img-circle:after, .pgb .step:first-of-type .img-circle:before{
    display: none;
}
</style>

@if($order->status==1)
    @php 
        $date = date("Y-m-d H:i:s", strtotime('+24 hours', strtotime($order->created_at)));
    @endphp
@endif

@if($order->status==2)
    @php 
        $date = date("Y-m-d H:i:s", strtotime('+24 hours', strtotime($order->updated_at)));
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
