@extends('admin.layout.template')

@section('content')
  <div class="page-member-title">
    <h3>Laporan</h3>
  </div>
<br>

<section class="panel">
    <header class="panel-heading">
        <b>Filter</b>
    </header>
    <div class="card-body">
                <div class="alert alert-primary" role="alert">
                    Laporan transaksi dengan status telah diproses
                </div>
               <form method="get" action="@if(auth()->user()->account_role == 'manager'){{url('manager/report-sales/transaksi')}}@else{{ url('superadmin/report-sales/transaksi') }}@endif">
               <div class="row d-flex">
                    <div class="col-sm-3 col-md-2 col-lg-1 my-1 align-self-end">
                        <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/report-sales/item')}}@else{{ url('superadmin/report-sales/item') }}@endif" class="btn btn-neutral" id="item" data-name="item" onclick="return laporan(this)">Laporan Items</a>
                    </div>
                    <div class="col-sm-9 col-md-2 col-lg-2 my-1 align-self-end">
                        <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/report-sales/transaksi')}}@else{{ url('superadmin/report-sales/transaksi') }}@endif"  class="btn btn-neutral" id="transaksi" data-name="transaksi" onclick="return laporan(this)">Laporan Per Transaksi</a>
                    </div>
                    <div class="col-sm-5 col-md-3 col-lg-3 my-1">
                        <label for="start-data">Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start-date">
                    </div>
                    <div class="col-sm-5 col-md-3 col-lg-3 my-1">
                        <label for="end-data">End Date</label>
                        <input type="date" class="form-control" name="end_date" id="end-date">
                    </div>
                    <div class="col-sm-2 col-md-2 col-lg-1 my-1 text-center align-self-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-blue"><span class="fa fa-search"></span> Filter</button>
                        <br>
                    </div>
                    <div class="col-sm-3 col-md-2 col-lg-1 my-1 align-self-end">
                        <a href="javascript:void(0)" class="btn btn-blue" onclick="return print()">Export PDF</a>
                    </div>
                    <div class="col-sm-9 col-md-2 col-lg-1 my-1 align-self-end">
                        <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/report-sales/transaksi/excel')}}@else{{ url('superadmin/report-sales/transaksi/excel') }}@endif" class="btn btn-blue">Export Excel</a>
                    </div>
                </div>
                </form>
                <div class="row d-flex" style="margin-top:10px;">
                    <div class="col-sm-3 col-md-2 col-lg-1 my-1 align-self-end">
                        <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/report-sales/transaksi?status_faktur=F')}}@else{{ url('superadmin/report-sales/transaksi?status_faktur=F') }}@endif" class="btn @if(\Illuminate\Support\Facades\Request::get('status_faktur')=="F") btn-blue @elseif(\Illuminate\Support\Facades\Request::get('status_faktur')=="R") btn-neutral @else btn-blue @endif" data-name="item">Faktur</a>
                    </div>
                    <div class="col-sm-9 col-md-2 col-lg-1 my-1 align-self-end">
                        <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/report-sales/transaksi?status_faktur=R')}}@else{{ url('superadmin/report-sales/transaksi?status_faktur=R') }}@endif"  class="btn @if(\Illuminate\Support\Facades\Request::get('status_faktur')=="F") btn-neutral @elseif(\Illuminate\Support\Facades\Request::get('status_faktur')=="R") btn-blue @else btn-neutral @endif" data-name="transaksi">Retur</a>
                    </div>
                </div>
            </div>
    
</section>
<br>

  <div class="card border-light">
    <div class="table-responsive">

                    <div class="outer-print" style="border: 1px solid #f1f1f1; box-shadow: 4px 4px 4px #dddddd; margin-bottom: 50px;">
                        <div id="print">

                            <style>
                                #print{
                                    padding: 50px;
                                }

                                .export-table th{
                                    border: 2px solid #dddddd;
                                    padding: 5px;
                                }

                                .export-table td{
                                    border: 1px solid #dddddd;
                                    padding: 5px;
                                }

                                #table-report{
                                    font-size: 9pt;
                                    width: 100%;
                                }
                            </style>
                            <br>

                            <div class="header-export" style="text-align: center">
                            @php $title = App\DataOption::where('slug', 'title')->first(); @endphp
                            @if($title)
                            <h3><b>{{$title->option_value}} </b></h3>
                            @endif
                            @php 
                                $phone = App\DataOption::where('slug', 'phone')->first();
                                $url = App\DataOption::where('option_name', 'og:url')->first();
                                $email = App\DataOption::where('slug', 'email')->first();
                            @endphp
                            @if($phone && $url && $phone)
                            <b>{{ $url->option_value }} | {{ $email->option_value }}</b>
                            @endif
                            <br>
                            <br>
                            </div>
                            <div style="margin-bottom: 10px; border-top: 3px solid #000000"></div>
                            <div style="margin-bottom: 35px; border-top: 1px solid #000000"></div>
                            {{-- <h6 class="show-print-variable">Laporan Penjualan : <span id="tanggalawal">{{$mulai}}</span> - <span id="tanggalakhir">{{$sampai}}</span></h6> --}}

                            <div class="row" id="display-loading" style="display: none">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="box-border">
                                        <div class="text-center">
                                            <img src="{{asset('loading.gif')}}" width="100px" style="margin:auto"><br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="display-report">
                            <table id="table-report" class="table default-table export-table">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Invoice</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>Pembayaran</th>
                                    <th>Tgl. Order</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    {{-- <th>Voucher</th> --}}
                                    <th>Ongkir</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        @php
                                            $item = 0;
                                            $qty = 0;
                                            $harga = 0;
                                        @endphp
                                        @foreach($order->data_item as $data)
                                            @if($data->product_id)
                                                @php
                                                    $item++;
                                                    $qty += $data->qty;
                                                    $harga += $data->total_price;
                                                @endphp
                                            @endif
                                        @endforeach
                                            <tr>
                                                <td scope="row">{{$loop->iteration}}</td>
                                                <td>{{ $order->invoice }}</td>
                                                <td>@foreach($order->data_user->user_address as $user){{ $order->data_user->name }}<br>{{ $user->shop_name }}@endforeach</td>
                                                <td>{{ ucwords($order->address) }}, {{ ucwords($order->kelurahan) }}, {{ ucwords($order->kecamatan) }}, {{ ucwords($order->kota) }}, {{ ucwords($order->provinsi) }} {{ $order->kode_pos }}</td>
                                                <td>{{ strtoupper($order->payment_method) }}</td>
                                                <td>{{ date('l, d M Y H:m', strtotime($order->order_time)) }}</td>
                                                <td>{{ $item }}</td>
                                                <td>{{ $qty }}</td>
                                                <td>Rp. {{ number_format($harga, 2) }}</td>
                                                {{-- <td></td> --}}
                                                <td>Bebas Ongkir</td>
                                                <td>Rp. {{ number_format($order->payment_final, 2) }}</td>
                                            </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" style="text-align:center">Data Kosong</td>
                                    </tr>
                                    @endforelse
                                    <tr>
                                        <th colspan="10">Total</th>
                                        <th>Rp. {{ number_format($total, 2) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            <br>
                            <br>

                            <div class="show-print-variable" style="text-align: right; width: 100%">
                                <p>{{$title->option_value}}, {{Date('d M Y')}}</p>
                                <p>Disiapkan Oleh,</p>
                                <br><br>
                                <p>{{\Illuminate\Support\Facades\Auth::user()->name}}</p>
                            </div>
                        </div>
                    </div>

    
    </div>
  </div>

  

  {{-- <div class="row">
    <div class="col-md-2 col-sm-3 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Today<br>
                <h3>{{ ($today) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-3 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                This Weeks<br>
                <h3>{{ ($this_weeks) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-3 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                This Month<br>
                <h3>{{ ($this_month) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-3 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                This Year<br>
                <h3>{{ ($this_year) }}</h3>
            </div>
        </div>
    </div> --}}

</div>
<br>

<script>
    function print(){

        // $('.show-print-variable').show();

        var divContents = $("#print").html();
        var printWindow = window.open('', '', 'height=auto,width=800');
        printWindow.document.write('<div>'+divContents+'</div>');
        printWindow.document.write(
            '<style> td{border:1px solid #dddddd; padding: 3px; margin: 0}' +
            ' table{border-collapse: collapse; align: center;}</style>'
            );
        printWindow.document.close();
        printWindow.print();

        // $('.show-print-variable').hide();

    }

    $(document).ready(function () {
        @if(Request::segment(3) == 'transaksi')
            $('#transaksi').removeClass('btn-neutral');
            $('#transaksi').addClass('btn-blue');
        @endif
    });
</script>
@endsection