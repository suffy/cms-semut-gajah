@extends('admin.layout.template')

@section('content')

                @php

                $stat_all = \App\StatCounter::all();
                $stat_today = \App\StatCounter::where('date', 'like' ,'%'.date('Y-m-d').'%')
                            ->first();
            
                $total_visitor = 0;
                $total_view = 0;
                foreach ($stat_all as $stat){
                    $total_visitor = $total_visitor+$stat->visitors;
                    $total_view = $total_view+$stat->views;
                }
            
                @endphp

    <section class="panel">
        <div class="card-body">
            <div class="heading-section">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Dashboard</h4>
                        <h5>Management system</h5>
                    </div>
                    <div class="col-md-8">
                        <div class="panel-menu-content">
                            {{-- <form class="custom-form-search">
                                <input class="form-control" name="search" placeholder="search...">
                                <button type="submit" class="btn"><span class="fa fa-search"></span></button>
                            </form> --}}

                        </div>
                    </div>
                </div>
                <hr>
            </div>

            {{-- <div class="row">
                <div class="col-md-3">
                    <div class="box-border">
                        <div class="card-body">
                            Visitor Today
                            <img src="{{asset('images/core/icon-user-one.svg')}}" class="img-responsive icon-dashboard">
                            <h3>@if(isset($stat_today))  {{$stat_today->visitors}}@endif</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="box-border">
                        <div class="card-body">
                            Total Visitor
                            <img src="{{asset('images/core/icon-group.svg')}}" class="img-responsive icon-dashboard">
                            <h3>{{$total_visitor}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="box-border">
                        <div class="card-body">
                            Views Today
                            <img src="{{asset('images/core/icon-eye.svg')}}" class="img-responsive icon-dashboard">
                            <h3>@if(isset($stat_today)) {{$stat_today->views}} @endif</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="box-border">
                        <div class="card-body">
                            Total Views
                            <img src="{{asset('images/core/icon-eye-text.svg')}}" class="img-responsive icon-dashboard">
                            <h3>{{$total_view}}</h3>
                        </div>
                    </div>
                </div>
            </div> --}}

            <form method="get" action="@if(auth()->user()->account_role == 'manager'){{url('manager')}}@elseif(auth()->user()->account_role == 'superadmin'){{ url('superadmin') }}@elseif(auth()->user()->account_role == 'admin'){{ url('admin') }}@endif">
                <div class="row d-flex my-5">
                    <div class="col-xs-12 col-md-12 col-lg-3 my-1 align-self-end">
                        <a href="javascript:void(0)" class="btn btn-blue" onclick="return print()">Export PDF</a>
                        <a href="{{ url('manager/logs?start_date=') }}{{ Request::get('start_date') }}&end_date={{Request::get('end_date')}}" class="btn btn-blue" style="@if(auth()->user()->account_role != 'manager' && auth()->user()->account_role != 'superadmin') display:none; @endif">Export Log Activity</a>
                        <a href="@if(auth()->user()->account_role == 'manager'){{url('manager?chart=true')}}@elseif(auth()->user()->account_role == 'superadmin'){{ url('superadmin?chart=true') }}@elseif(auth()->user()->account_role == 'admin'){{ url('admin?chart=true') }}@elseif(auth()->user()->account_role == 'distributor'){{ url('distributor?chart=true') }}@endif" class="btn btn-blue">View Chart</a>
                        <!-- @if (auth()->user()->account_role != 'distributor')
                            <select name="site_id" id="mapping-site" class="form-control mt-2">
                                <option value="" @if(\Illuminate\Support\Facades\Request::get('category_id')=="") selected @endif>Site Name</option>
                                @foreach ($mappingSites as $mappingSite)
                                    <option value="{{ $mappingSite->kode }}" @if(\Illuminate\Support\Facades\Request::get('site_id')==$mappingSite->id) selected @endif>{{ $mappingSite->kode }}</option>
                                @endforeach
                            </select>
                        @endif -->
                    </div>
                    <div class="col-xs-12 col-md-12 col-lg-2 my-1 text-center align-self-end" style="@if(auth()->user()->account_role == 'distributor') visibility:hidden; @endif">
                        <select name="site_id" id="mapping_site" class="form-control mt-2"></select>
                    </div>
                    <div class="col-xs-12 col-md-5 col-lg-3 my-1">
                        <label for="start-data">Start Date</label>
                        <input type="date" class="form-control mt-2" name="start_date" id="start-date">
                    </div>
                    <div class="col-xs-12 col-md-5 col-lg-3 my-1">
                        <label for="end-data">End Date</label>
                        <input type="date" class="form-control mt-2" name="end_date" id="end-date">
                    </div>
                    <div class="col-xs-12 col-md-2 col-lg-1 my-1 align-self-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-blue mt-2"><span class="fa fa-search"></span> Filter</button>
                        <br>
                    </div>
                </div>
            </form>
            @if(auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                <div class="row mt-5" id="print">
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Customer
                                <h3>{{ count($customers) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Salesman
                                <h3>{{ count($salesmen) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Product Category
                                <h3>{{ count($productCategories) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Product
                                <h3>{{ count($products) }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Transactions
                                <h3>{{ count($orders) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Promo
                                <h3>{{ count($promos) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Subscribe Orders
                                <h3>{{ count($subscribes) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <h3 class="mt-5">Transactions</h3>
            <div class="row mt-5" id="print">
                <div class="col-md-4">
                    <div class="box-border">
                        <div class="card-body">
                            New Order
                            <h3>{{ count($newOrders) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box-border">
                        <div class="card-body">
                            Order Confirmed
                            <h3>{{ count($confirmedOrders) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box-border">
                        <div class="card-body">
                            Delivery
                            <h3>{{ count($deliveryOrders) }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="box-border">
                        <div class="card-body">
                            Complete Order
                            <h3>{{ count($completeOrders) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="box-border">
                        <div class="card-body">
                            Cancel Order
                            <h3>{{ count($cancelOrders) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mt-5">Leaderboards</h3>
            <div class="row">
                <div class="col-md-6">
                    <canvas id="top-order"></canvas>
                </div>
                <div class="col-md-6 ">
                    <canvas id="top-rating"></canvas>
                </div>
            </div>
        </div>
    </section>

    <script>
        $(function(){
            //get the pie chart canvas
            var topProductsChart = JSON.parse(`<?php echo $topProductsChart; ?>`);
            var ctxProductChart = $("#top-order");
            var topRatingsChart = JSON.parse(`<?php echo $topRatingsChart; ?>`);
            var ctxRatingChart = $("#top-rating");
            console.log(topRatingsChart);
       
            //pie chart data
            var dataTopProducts = {
              labels: topProductsChart.product,
              datasets: [
                {
                  label: "Top Order",
                  data: topProductsChart.total,
                  backgroundColor: [
                    "#DEB887",
                    "#A9A9A9",
                    "#DC143C",
                    "#F4A460",
                    "#2E8B57",
                    "#1D7A46",
                    "#CDA776",
                  ],
                  borderColor: [
                    "#CDA776",
                    "#989898",
                    "#CB252B",
                    "#E39371",
                    "#1D7A46",
                    "#F4A460",
                    "#CDA776",
                  ],
                  borderWidth: [1, 1, 1, 1, 1,1,1]
                }
              ]
            };
            var dataTopRatings = {
              labels: topRatingsChart.product,
              datasets: [
                {
                  label: "Top Order",
                  data: topRatingsChart.star_review,
                  backgroundColor: [
                    "#DEB887",
                    "#A9A9A9",
                    "#DC143C",
                    "#F4A460",
                    "#2E8B57",
                    "#1D7A46",
                    "#CDA776",
                  ],
                  borderColor: [
                    "#CDA776",
                    "#989898",
                    "#CB252B",
                    "#E39371",
                    "#1D7A46",
                    "#F4A460",
                    "#CDA776",
                  ],
                  borderWidth: [1, 1, 1, 1, 1,1,1]
                }
              ]
            };
       
            //options
            var optionsTopProducts = {
              responsive: true,
              title: {
                display: true,
                position: "top",
                text: "Rekap Top Order",
                fontSize: 18,
                fontColor: "#111"
              },
              legend: {
                display: true,
                position: "bottom",
                labels: {
                  fontColor: "#333",
                  fontSize: 16
                }
              }
            };
            var optionsTopRatings = {
              responsive: true,
              title: {
                display: true,
                position: "top",
                text: "Rekap Top Rating",
                fontSize: 18,
                fontColor: "#111"
              },
              legend: {
                display: true,
                position: "bottom",
                labels: {
                  fontColor: "#333",
                  fontSize: 16
                }
              }
            };
       
            //create Pie Chart class object
            var chart1 = new Chart(ctxProductChart, {
              type: "pie",
              data: dataTopProducts,
              options: optionsTopProducts
            });
            var chart2 = new Chart(ctxRatingChart, {
              type: "pie",
              data: dataTopRatings,
              options: optionsTopRatings
            });
        });

        $(document).ready(function () {
            $('#mapping_site').select2({
                placeholder: "Pilih Mapping Site",
                ajax: {
                    url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/all-mapping-site')}}"
                        @elseif(auth()->user()->account_role == 'superadmin')
                            "{{url('superadmin/all-mapping-site')}}"
                        @elseif(auth()->user()->account_role == 'admin')
                            "{{url('admin/all-mapping-site')}}"
                        @elseif(auth()->user()->account_role == 'distributor')
                            "{{url('distributor/all-mapping-site')}}"
                    @endif,
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data){
                        return {
                            results: $.map(data, function(item){
                                return {
                                    text: item.kode,
                                    id: item.kode
                                }
                            })
                        };
                    },
                    cache: true
                }
            });
        })
    </script>
@endsection
