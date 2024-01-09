@extends('admin.layout.template')

@section('content')
@php
    $account_role = auth()->user()->account_role;
@endphp
  <div class="page-member-title">
    <h3>Orders</h3>
  </div>

<br>

<section class="panel">
    {{-- <header class="panel-heading">
        <b>Filter</b>
    </header> --}}
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Today
                        <h3>{{ $ordersToday }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Last Week
                        <h3>{{ $ordersLastWeek }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Last Month
                        <h3>{{ $ordersLastMonth }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Last Year
                        <h3>{{ $ordersLastYear }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <form method="get" action="{{url('admin/orders')}}">
            <div class="form-group row">
                <label for="staticEmail" class="col-xs-2 col-sm-2 col-md-1 ol-form-label">Filter</label>
                <div class="col-xs-4 col-sm-4 col-md-2 my-1">
                    <input type="text" class="form-control datepicker" name="start_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-xs-4 col-sm-4 col-md-2 my-1">
                    <input type="text" class="form-control datepicker"  name="end_date" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-xs-2 col-sm-2 col-md-1 my-1">
                    <button type="submit" class="btn btn-success">Search</button>
                </div>
            </div>
        </form>

        <div class="form-group row">
            <label for="staticEmail" class="col-4 col-sm-4 col-md-1 col-form-label">Category</label>
            <div class="col-8 col-sm-8 col-md-2 my-1">
                <select class="form-control" name="category_id" onChange="location = this.value;">
                    <option value="orders" @if(\Illuminate\Support\Facades\Request::get('category_id')=="") selected @endif>Semua</option>
                    @foreach ($categories as $category)
                        <option value="?category_id={{$category->id}}" @if(\Illuminate\Support\Facades\Request::get('category_id') == $category->id) selected @endif>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <label for="staticEmail" class="col-4 col-sm-4 col-md-1 col-form-label">Quantity</label>
            <div class="col-8 col-sm-8 col-md-2 my-1">
                <select class="form-control" name="qty" onChange="location = this.value;">
                    <option value="orders" @if(\Illuminate\Support\Facades\Request::get('max') == "") selected @endif>Semua</option>
                    <option value="?qty=1&max=50" @if(\Illuminate\Support\Facades\Request::get('max') == '50') selected @endif>1 - 50</option>
                    <option value="?qty=50&max=100" @if(\Illuminate\Support\Facades\Request::get('max') == '100') selected @endif>50 - 100</option>
                </select>
            </div>

            
            <div class="col-md-6 my-1">
                <form method="get" action="{{url('admin/orders')}}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search...">
                        <div class="input-group-append">
                        <button class="btn btn-secondary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</section>
<br>

  <div class="card border-light">
    <div class="table-responsive">
                        
        <a 
            href="
                @if($account_role == "manager")
                    {{url('manager/orders')}}
                @elseif($account_role == "superadmin")
                    {{url('superadmin/orders')}}
                @elseif($account_role == "admin")
                    {{url('admin/orders')}}
                @elseif($account_role == "distributor")
                    {{url('distributor/orders')}}
                @endif
            "
            class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status_faktur')=="") active @endif"
        >Semua</a>
        <a 
            href="
                @if($account_role == "manager")
                    {{url('manager/orders?status_faktur=F')}}
                @elseif($account_role == "superadmin")
                    {{url('superadmin/orders?status_faktur=F')}}
                @elseif($account_role == "admin")
                    {{url('admin/orders?status_faktur=F')}}
                @elseif($account_role == "distributor")
                    {{url('distributor/orders?status_faktur=F')}}
                @endif
            " 
            class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status_faktur')=="F") active @endif"
        >Faktur</a>
        <a 
            href="
                @if($account_role == "manager")
                    {{url('manager/orders?status_faktur=R')}}
                @elseif($account_role == "superadmin")
                    {{url('superadmin/orders?status_faktur=R')}}
                @elseif($account_role == "admin")
                    {{url('admin/orders?status_faktur=R')}}
                @elseif($account_role == "distributor")
                    {{url('distributor/orders?status_faktur=R')}}
                @endif
            " 
            class="btn btn-tab @if(\Illuminate\Support\Facades\Request::get('status_faktur')=="R") active @endif"
        >Retur</a>
      <table class="table default-table orderTable">
        <thead>
            <tr>
                <th style="display: none;">Tanggal</th>
                <th>Tanggal</th>
                <th>No. Order</th>
                <th>Customer</th>
                @if (auth()->user()->account_role != 'distributor')
                    <th>Site Name</th>
                @endif
                <th>Nominal</th>
                <th>Pembayaran</th>
                <th>Reward Point</th>
                <th>Payment Point</th>
                <th>Status</th>
                <th>Status Faktur</th>
                <th>Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td style="display: none;">{{ $order->order_time }}</td>
                    <td>{{ date('d M Y', strtotime($order->order_time)) }}</td>
                    <td>{{ $order->invoice }}</td>
                    <td>{{ $order->name }}</td>
                    @if (auth()->user()->account_role != 'distributor')
                        <td>{{ $order->branch_name }}</td>
                    @endif
                    <td>Rp. {{ number_format($order->payment_total, 2, ',', '.') }}</td>
                    <td>Rp. {{ number_format($order->payment_final, 2, ',', '.') }}</td>
                    <td>
                        @if($order->status == '4')
                            @if($order->point) 
                                {{ $order->point }} Point
                            @else
                                0 Point
                            @endif
                        @else
                            0 point
                        @endif
                    </td>
                    <td>
                        @if($order->payment_point) 
                            {{ $order->payment_point }} Point
                        @else
                            0 Point
                        @endif
                    </td>
                    <td>
                        @if ($order->status == '1')
                            <span class="status status-primary">New Order</span>
                        @elseif($order->status == '2')
                            <span class="status status-warning">Payment</span>
                        @elseif($order->status == '2')
                                <span class="status status-warning">Payment Confirmed</span>
                        @elseif($order->status == '3')
                            <span class="status status-info">Delivery</span>
                        @elseif($order->status == '4')
                            <span class="status status-success">Complete</span>
                        @elseif($order->status == '10')
                            <span class="status status-danger">Cancel</span>
                        @endif
                    </td>
                    <td>@if($order->status_faktur == 'F') Faktur @elseif($order->status_faktur == 'R') Retur @endif</td>
                    <td>{{ date('H:i:s', strtotime($order->order_time)) }}</td>
                    <td  width="30px">
                    <a href="order-detail/{{ $order->id }}" class="btn btn-blue btn-sm"><span class="fa fa-eye"></span> Detail</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
      </table>

      {{ $orders->appends($_GET)->links() }}
    </div>
  </div>
</div>
<br>

<script>
        $('.orderTable').DataTable().order([ 0, "asc" ])
    });
</script>

@endsection
