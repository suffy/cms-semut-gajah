@extends('admin.layout.template')

@section('content')
  <div class="page-member-title">
    <h3>Laporan</h3>
  </div>

  

<!-- <div class="input-group">
    <button type="button" class="btn btn-default pull-right" id="daterange-btn">
        <span>
            <i class="fa fa-calendar"></i> Filter : {{date('d F Y', strtotime($mulai))}} - {{date('d F Y', strtotime($sampai))}}
        </span>
        <i class="fa fa-caret-down"></i>
    </button>
</div> -->

<br>

<section class="panel">
    <header class="panel-heading">
        <b>Filter</b>
    </header>
    <div class="card-body">
        <form method="get" action="{{url('admin/report-statistik')}}">
        <div class="row">
            <div class="col-md-4 col-lg-3 col-xl-3">
                <label>Mulai</label>
                <input type="text" class="form-control datepicker" name="mulai" value="{{$mulai}}">
            </div>
            <div class="col-md-3 col-lg-3 col-xl-3">
                <label>Sampai</label>
                <input type="text" class="form-control datepicker"  name="sampai" value="{{$sampai}}">
            </div>
            <div class="col-md-3 col-lg-3 col-xl-3">
                <label>Status</label>
                <select class="form-control" name="status">
                    <option value="" @if($status=="") selected @endif>Semua</option>
                    <option value="1" @if($status=="1") selected @endif>Baru</option>
                    <option value="2" @if($status=="2") selected @endif>Approve</option>
                    <option value="7" @if($status=="3") selected @endif>Pengiriman</option>
                    <option value="3" @if($status=="4") selected @endif>Selesai</option>
                    <option value="5" @if($status=="10") selected @endif>Batal</option>
                </select>
                <br>
            </div>
            <div class="col-md-2 text-center">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-blue form-control"><span class="fa fa-search"></span> Filter</button>
                <br>
            </div>
            </div>
        </form>
    </div>
</section>

@php 
    $total_trans = 0;
    $nominal_trans = 0;
    $total_item = 0;
    // $total_large_qty = 0;
    // $total_medium_qty = 0;
    // $total_small_qty = 0;
    $total_trans = 0;
    // $total_discount = 0;

    foreach($orders as $o):
        $total_trans = $total_trans+1;
        $nominal_trans = $nominal_trans+$o->payment_final;
        // $total_discount = $total_discount+$o->payment_discount;
        $total_item = $total_item+count($o->data_item);
        // foreach($o->data_item as $i):
        //     $total_large_qty = $total_large_qty+$i->large_qty;
        //     $total_medium_qty = $total_medium_qty+$i->medium_qty;
        //     $total_small_qty = $total_small_qty+$i->small_qty;
        // endforeach;
    endforeach;
@endphp

<div class="row">
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Total Transaksi<br>
                <h3>{{ ($total_trans) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Total Pembayaran<br>
                <h3>Rp {{ (number_format($nominal_trans)) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Total Item<br>
                <h3>{{ ($total_item) }}</h3>
            </div>
        </div>
    </div>
    {{-- <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Total Discount<br>
                <h3>Rp {{ (number_format($total_discount)) }}</h3>
            </div>
        </div>
    </div> --}}
</div>
<hr>
  <div class="row">
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Today<br>
                <h3>{{ ($today) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                This Weeks<br>
                <h3>{{ ($this_weeks) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                This Month<br>
                <h3>{{ ($this_month) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                This Year<br>
                <h3>{{ ($this_year) }}</h3>
            </div>
        </div>
    </div>
</div>
<br>
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
    </script>


@endsection
