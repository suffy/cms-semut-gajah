@extends('admin.layout.template')

@section('content')
  <div class="page-member-title">
    <h3>Transactions</h3>
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
     
               <form method="get" action="{{url('admin/orders')}}">
               <div class="row">
                    <div class="col-md-2">
                      <label>Mulai</label>
                      <input type="text" class="form-control datepicker" name="mulai" value="{{$mulai}}">
                    </div>
                    <div class="col-md-2">
                      <label>Sampai</label>
                      <input type="text" class="form-control datepicker"  name="sampai" value="{{$sampai}}">
                    </div>
                    <div class="col-md-2">
                    <label>Parameter</label>
                        <select class="form-control" name="params">
                            <option value="id" @if(\Illuminate\Support\Facades\Request::get('params')=="id") selected @endif>Default</option>
                            <option value="name" @if(\Illuminate\Support\Facades\Request::get('params')=="name") selected @endif>Nama</option>
                            <option value="payment_total" @if(\Illuminate\Support\Facades\Request::get('params')=="payment_total") selected @endif>Harga</option>
                        </select>
                        <br>
                    </div>
                    <div class="col-md-2">
                        <label>Urutan</label>
                        <select class="form-control" name="ordering">
                            <option value="asc" @if(\Illuminate\Support\Facades\Request::get('ordering')=="asc") selected @endif>Depan</option>
                            <option value="desc" @if(\Illuminate\Support\Facades\Request::get('ordering')=="desc") selected @endif>Belakang</option>
                        </select>
                        <br>
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select class="form-control" name="status">
                            <option value="" @if($status=="") selected @endif>Semua</option>
                            <option value="1" @if($status=="1") selected @endif>Baru</option>
                            <option value="2" @if($status=="2") selected @endif>Approve</option>
                            <option value="7" @if($status=="7") selected @endif>Approve Payment</option>
                            <option value="3" @if($status=="3") selected @endif>Pengiriman</option>
                            <option value="4" @if($status=="4") selected @endif>Selesai</option>
                            <option value="5" @if($status=="5") selected @endif>Batal</option>
                        </select>
                        <br>
                    </div>
                    <div class="col-md-1">
                        <label>Kata</label>
                        <input type="text" class="form-control" name="search" value="{{$search}}" placeholder="Kata">
                        <br>
                    </div>
                    <div class="col-md-1 text-center">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-blue form-control"><span class="fa fa-search"></span> Filter</button>
                        <br>
                    </div>
                    </div>
                </form>
            </div>
    
</section>
<br>

  <div class="card border-light">
    <div class="table-responsive">

    

      <table class="table default-table dataTable">
        <thead>
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama</th>
            <th>Alamat</th>
            <th>Pengiriman</th>
            <th>Item</th>
            <th>Qty</th>
            <th class="text-right">Biaya</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>

          @foreach($orders as $trans)
          <tr>
            <td scope="row">{{$loop->iteration}}</td>
            <td>
                @if($trans->order_time!==null)
                    {{date('d F Y - H:i:s', strtotime($trans->order_time))}}
                @endif
            </td>
            <td>
                {{$trans->name}}<br>
                <a href="https://api.whatsapp.com/send/?phone=+62{{substr($trans->phone, 1,15)}}&text=Konfirmasi Order #{{$trans->id}} an {{$trans->name}}" target="_blank"><span class="fa fa-whatsapp"></span> chat</a>
            </td>
            <td>
                {{$trans->address}}
            </td>
            <td>
                Pengiriman : {{$trans->courier}}<br>
                Ongkir : Rp {{number_format($trans->delivery_fee)}}<br>

            </td>
            <td>
                {{count($trans->data_item)}}
            </td>
            <td>
                @php
                $qty = 0;
                foreach($trans->data_item as $i):
                    $qty = $qty+$i->qty;
                endforeach;
                
                @endphp
                {{$qty}}
            </td>
            <td  class="text-right">
                Total Rp {{number_format($trans->payment_total)}}<br>
                Discount Rp {{number_format($trans->payment_discount)}}<br>
                Bayar Rp {{number_format($trans->payment_final)}}
            </td>
            <td>
                @if($trans->status==1)
                <span class="status status-info">New Transaction</span>
                @elseif($trans->status==2)
                <span class="status status-success">Approve</span>
                @elseif($trans->status==3)
                <span class="status status-success">Proses</span>
                @elseif($trans->status==4)
                <span class="status status-warning">Completed</span>
                @elseif($trans->status==5)
                <span class="status status-danger">Cancel</span>
                @elseif($trans->status==6)
                <span class="status status-info">Retur</span>
                @elseif($trans->status==7)
                <span class="status status-info">Approve Payment</span>
                @endif
            </td>
            <td  width="30px">
              <a href="order-detail/{{ $trans->id }}" class="btn btn-blue btn-sm"><span class="fa fa-eye"></span> Detail</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      {{ $orders->appends($_GET)->links() }}
    </div>
  </div>

  <br>

  <div class="row">
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
    </div>
    <div class="col-md-4 col-sm-3 col-xs-6">
        <div class="box-border">
            <div class="card-body">
                Transaksi Baru<br>
                <h3>{{ ($transaction_new) }}</h3>
            </div>
        </div>
    </div>

</div>
<br>


  <script>

var date = new Date();

var firstDay;
var lastDay;
var status = "{{\Illuminate\Support\Facades\Request::get('status')}}";
var searchQuery = "{{\Illuminate\Support\Facades\Request::get('search')}}";

var tgl_awal = "{{\Illuminate\Support\Facades\Request::get('mulai')}}";
var tgl_akhir = "{{\Illuminate\Support\Facades\Request::get('sampai')}}";

if(tgl_awal==""){
    firstDay = moment().startOf('month').format('YYYY-MM-DD')
}else{
    firstDay = {{$mulai}};
}


if(tgl_akhir==""){
    lastDay = moment()
}else{
    lastDay = {{$sampai}};
}

if(tgl_awal==""&&tgl_akhir==""){
    var q_s = " ";

    if(searchQuery!==""){
        q_s = "&search="+searchQuery;
    }

    var url_page = "{{url('admin/orders?status=')}}"+status + "&mulai=" + formatDateToISO(firstDay) + "&sampai=" + formatDateToISO(lastDay)+q_s;
    window.history.pushState('Order', 'Order', url_page);
}


$('#daterange-btn').daterangepicker(
    {
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: {{$mulai}},
        endDate: {{$sampai}}
    },
    function (start, end) {

        firstDay = start;
        lastDay = end;
        getOrder(firstDay, lastDay);

    }
);

function getOrder(start, end) {

    $('#daterange-btn').html("<i class='fa fa-calendar'></i> " + formatDate(formatDateToISO(start)) + " - " + formatDate(formatDateToISO(end))+" &nbsp <span class='caret'></span>");
    getJsonOrder(status, start, end);

}

function getJsonOrder(status, start, end){

    var q_s = " ";

    if(searchQuery!==""){
        q_s = "&search="+searchQuery;
    }

    var url_page = "{{url('admin/orders?status=')}}"+status + "&mulai=" + formatDateToISO(start) + "&sampai=" + formatDateToISO(end)+q_s;
    window.location.href = url_page;
}

</script>

@endsection
