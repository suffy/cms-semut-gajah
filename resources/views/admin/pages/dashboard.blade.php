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

    <section class="panel col-md-6">
        <div class="card-body">
            <div class="heading-section">
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-4 col-lg-4">
                        <h4>Dashboard</h4>
                        <h5>Management system</h5>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-8 col-lg-4">
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

            {{-- <form method="get">
                <div class="row d-flex my-5">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 col-xl-3 my-1 align-self-end">
                        <a href="javascript:void(0)" class="btn btn-blue" onclick="return print()">Export PDF</a>
                        <a href="{{ url('manager/logs?start_date=') }}{{ Request::get('start_date') }}&end_date={{Request::get('end_date')}}" class="btn btn-blue" style="@if(auth()->user()->account_role != 'manager' && auth()->user()->account_role != 'superadmin') display:none; @endif">Export Log Activity</a>
                        <!-- @if (auth()->user()->account_role != 'distributor')
                            <select name="site_id" id="mapping-site" class="form-control mt-2">
                                <option value="" @if(\Illuminate\Support\Facades\Request::get('category_id')=="") selected @endif>Site Name</option>
                                @foreach ($mappingSites as $mappingSite)
                                    <option value="{{ $mappingSite->kode }}" @if(\Illuminate\Support\Facades\Request::get('site_id')==$mappingSite->id) selected @endif>{{ $mappingSite->kode }}</option>
                                @endforeach
                            </select>
                        @endif -->
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-1 col-xl-2 my-2 text-center align-self-end" style="@if(auth()->user()->account_role == 'distributor') visibility:hidden; @endif">
                        <select name="site_id" id="mapping_site" class="form-control mt-2"></select>
                    </div>
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-3 col-xl-3 my-1">
                        <label for="start-data">Start Date</label>
                        <input type="date" class="form-control mt-2" name="start_date" id="start-date" value="{{$start_date}}">
                    </div>
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-3 col-xl-3 my-1">
                        <label for="end-data">End Date</label>
                        <input type="date" class="form-control mt-2" name="end_date" id="end-date" value="{{$end_date}}">
                    </div>
                    <div class="col-xs-12 col-sm-2 col-md-2 col-lg-1 col-xl-1 my-1 align-self-end">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-blue mt-2"><span class="fa fa-search"></span> Filter</button>
                        <br>
                    </div>
                </div>
            </form> --}}
            <form method="get">
                <div class="col-md-12">
                    <div class="row d-flex">
                        <div class="col-md-4 align-self-end">
                            <label for="mapping_site" class="mb-4">Mapping Site</label>
                            <select name="site_id" id="mapping_site" class="form-control"></select>
                        </div>
                        <div class="col-md-4">
                            <label for="start-data">Start Date</label>
                            <input type="date" class="form-control mt-2" name="start_date" id="start-date" value="{{$start_date}}">
                        </div>
                        <div class="col-md-4">
                            <label for="end-data">End Date</label>
                            <input type="date" class="form-control mt-2" name="end_date" id="end-date" value="{{$end_date}}">
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12 d-flex flex-row-reverse">
                            <button type="submit" class="btn btn-blue ml-2"><span class="fa fa-search"></span> Filter</button>
                            <a href="{{ url('manager/logs?start_date=') }}{{ Request::get('start_date') }}&end_date={{Request::get('end_date')}}" class="btn btn-secondary ml-2" style="@if(auth()->user()->account_role != 'manager' && auth()->user()->account_role != 'superadmin') display:none; @endif">Export Log Activity</a>
                            <a href="javascript:void(0)" class="btn btn-secondary" onclick="return print()">Export PDF</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
            
    <section class="panel">
        <div class="card-body">
            @if(auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                <div class="row mt-5" id="print">
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Customer
                                <h3>{{ number_format($customers, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Salesman
                                <h3>{{ number_format($salesmen, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Product Category
                                <h3>{{ number_format(count($productCategories), 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Product
                                <h3>{{ number_format($products, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Transactions
                                <h3>{{ number_format($orders, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Available Promos
                                <h3>{{ number_format($promos, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Subscribe Orders
                                <h3>{{ number_format($subscribes, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                User Registered
                                <h3>{{ number_format($customer_registered, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-border">
                            <div class="card-body">
                                Total Point
                                <h3>{{ number_format($point, 0, '.', ',') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <h3 class="mt-5">Transactions</h3>
            <div class="row mt-5" id="print">
                <div class="row col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div class="col-md-6">
                        <div class="box-border">
                            <div class="card-body">
                                New Order
                                <h4>{{ number_format($newOrders, 0, '.', ',') }} <p style="display: inline;">(Rp {{ number_format($newOrdersTotal, 0, '.', ',') }})</p></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box-border">
                            <div class="card-body">
                                Order Confirmed
                                <h4>{{ number_format($confirmedOrders, 0, '.', ',') }} <p style="display: inline;">(Rp {{ number_format($confirmedOrdersTotal, 0, '.', ',') }})</p></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box-border">
                            <div class="card-body">
                                Delivery
                                <h4>{{ number_format($deliveryOrders, 0, '.', ',') }} <p style="display: inline;">(Rp {{ number_format($deliveryOrdersTotal, 0, '.', ',') }})</p></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box-border">
                            <div class="card-body">
                                Complete Order
                                <h4>{{ number_format($completeOrders, 0, '.', ',') }} <p style="display: inline;">(Rp {{ number_format($completeOrdersTotal, 0, '.', ',') }})</p></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box-border">
                            <div class="card-body">
                                Cancel Order
                                <h4>{{ number_format($cancelOrders, 0, '.', ',') }} <p style="display: inline;">(Rp {{ number_format($cancelOrdersTotal, 0, '.', ',') }})</p></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box-border">
                            <div class="card-body">
                                Total Order
                                <h4>{{ number_format($totalOrders, 0, '.', ',') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <canvas id="order-chart"></canvas>
                </div>
            </div>

            @isset($userApps)
            <h3 class="mt-5">Top 10 User Apps</h3>
            <div class="row top-graph">
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 my-4" style="margin:0 auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Site Code</th>
                                <th>User ERP</th>
                                <th>User Apps</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userApps as $rowUserApps)
                            @php
                                $percen = round((($rowUserApps->total / $rowUserApps->userErp) * 100), 3);
                                if($percen == 0) {
                                    $percen = 'Below 0.001';
                                }
                            @endphp
                            <tr style="text-align: center;">
                                <td>{{$rowUserApps->site_code}}</td>
                                <td>{{number_format($rowUserApps->userErp, 0, '.', ',')}}</td>
                                <td>{{number_format($rowUserApps->total, 0, '.', ',')}}</td>
                                <td>{{ $percen }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 my-4" style="margin:0 auto;">
                    <canvas id="bar-chart"></canvas>
                </div>
            </div>
            @endisset

            <h3 class="mt-5">Leaderboards</h3>
            <a href="javascript:void(0)" class="btn btn-secondary btn-chart">View Chart</a>
            <div class="row top-table mt-4">
                <div class="col-md-6">
                    <table class="table table-striped">
                        <thead>
                            <tr style="text-align:center;">
                                <th colspan="2">Rekap Top Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $topProduct)
                                <tr>
                                    <td width="600">{{ $topProduct->product }}</td>
                                    <td>{{ number_format($topProduct->total, 0, '.', ',') }}</td>
                                </tr>
                            @empty
                                <tr style="text-align:center;">
                                    <td colspan="2">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 ">
                    <table class="table table-striped">
                        <thead>
                            <tr style="text-align:center;">
                                <th colspan="2">Rekap Top Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topRatings as $topRating)
                                <tr>
                                    <td width="600">{{ $topRating->product }}</td>
                                    <td>{{ $topRating->star_review }}</td>
                                </tr>
                            @empty
                                <tr style="text-align:center;">
                                    <td colspan="2">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @isset($topDistributor)
                    <div class="col-md-6 ">
                        <table class="table table-striped">
                            <thead>
                                <tr style="text-align:center;">
                                    <th colspan="3">Rekap Top Distributor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topDistributor as $top_dist)
                                    <tr>
                                        <td>{{ $top_dist->kode }}</td>
                                        <td>{{ $top_dist->branch }} | {{ $top_dist->nama }}</td>
                                        <td>Rp {{ number_format($top_dist->total, 0, '.', ',') }}</td>
                                    </tr>
                                @empty
                                    <tr style="text-align:center;">
                                        <td colspan="3">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endisset
                @isset($topUser)
                    <div class="col-md-6 ">
                        <table class="table table-striped">
                            <thead>
                                <tr style="text-align:center;">
                                    <th colspan="3">Rekap Top User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topUser as $top_user)
                                    <tr>
                                        <td>{{ $top_user->customer_code }}</td>
                                        <td>{{ $top_user->name }}</td>
                                        <td>Rp {{ number_format($top_user->total, 0, '.', ',') }}</td>
                                    </tr>
                                @empty
                                    <tr style="text-align:center;">
                                        <td colspan="3">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endisset
            </div>
            <div class="row top-chart" style="display:none;">
                @php
                    $no = 1;
                @endphp
                @foreach ($productTop as $item)
                    @if(($no % 2) != 0)
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6 my-4" style="margin:0 auto;">
                            <canvas id="top-order-{{$no}}" width="700" height="350"></canvas>
                        </div>
                        {{-- <div class="col-md-6 my-4" style="margin:0 auto;">
                            <figure class="highcharts-figure">
                                <div id="top-order-{{$no}}"></div>
                            </figure>
                        </div> --}}
                    @elseif(($no % 2) == 0)
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-6 my-4" style="margin:0 auto;">
                            <canvas id="top-rating-{{$no}}" width="700" height="350"></canvas>
                        </div>
                        {{-- <div class="col-md-6 my-4" style="margin:0 auto;">
                            <figure class="highcharts-figure">
                                <div id="top-rating-{{$no}}"></div>
                            </figure>
                        </div> --}}
                    @endif
                    @php
                        $no++;
                    @endphp
                @endforeach
            </div>
        </div>
    </section>

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
            $('#mapping_site').select2({
                placeholder: "Pilih Mapping Site",
                ajax: {
                    url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/all-mapping-site')}}"
                        @elseif(auth()->user()->account_role == 'superadmin')
                            "{{url('superadmin/all-mapping-site')}}"
                        @elseif(auth()->user()->account_role == 'admin')
                            "{{url('admin/all-mapping-site')}}"
                        @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
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
        });

        function generateTopOrder(data, no) {
            //get the pie chart canvas
            var topProductsChart = JSON.parse(data);
            var ctxProductChart = $("#top-order-" + no);
            if(no == 1) {
                var label = "";
            } else {
                var label = topProductsChart.category;
            }
            
            if(topProductsChart) {
                if(topProductsChart.product) {
                    //pie chart data
                    var dataTopProducts = {
                    labels: topProductsChart.product,
                    data_id: topProductsChart.id,
                    datasets: [
                        {
                        label: "Top Order " + label,
                        data: topProductsChart.total,
                        backgroundColor: [
                            "#54478c",
                            "#2c699a",
                            "#048ba8",
                            "#0db39e",
                            "#16db93",
                            "#83e377",
                            "#b9e769",
                            "#efea5a",
                            "#f1c453",
                            "#f29e4c",
                        ],
                        borderColor: [
                            "#54478c",
                            "#2c699a",
                            "#048ba8",
                            "#0db39e",
                            "#16db93",
                            "#83e377",
                            "#b9e769",
                            "#efea5a",
                            "#f1c453",
                            "#f29e4c",
                        ],
                        borderWidth: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1]
                        }
                    ]
                    };
            
                    var theHelp = Chart.helpers;
                    //options
                    var optionsTopProducts = {
                        responsive: false,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            position: "top",
                            text: "Rekap Top Order " + label,
                            fontSize: 18,
                            fontColor: "#111"
                        },
                        legend: {
                            display: true,
                            position: "left",
                            labels: {
                                fontColor: "#333",
                                fontSize: 16,
                                // generateLabels(chart) {
                                //     const data = chart.data;
                                //     if (data.labels.length && data.datasets.length) {
                                //         const {labels: {pointStyle}} = chart.legend.options;

                                //         return data.data_id.map((label, i) => {
                                //         var meta = chart.getDatasetMeta(0);
                                //         var ds = data.datasets[0];
                                //         var arc = meta.data[i];
                                //         var custom = arc && arc.custom || {};
                                //         var getValueAtIndexOrDefault = theHelp.getValueAtIndexOrDefault;
                                //         var arcOpts = chart.options.elements.arc;
                                //         var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                //         var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                //         var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                                //         return {
                                //             text: data.data_id[i],
                                //             fillStyle: fill,
                                //             strokeStyle: stroke,
                                //             lineWidth: bw,
                                //             hidden: isNaN(ds.data[i]) || meta.data[i].hidden,

                                //             // Extra data used for toggling the correct item
                                //             index: i
                                //         };
                                //         });
                                //     }
                                //     return [];
                                // }
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                        return previousValue + currentValue;
                                    });
                                    var currentValue = dataset.data[tooltipItem.index];
                                    var percentage = Math.round((currentValue/total) * 100)+"%";
                                    return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                                }
                            }
                        }
                    };
                } else {
                    //pie chart data
                    var dataTopProducts = {
                    labels: ['Data Kosong'],
                    datasets: [
                        {
                        label: "Top Order " + label,
                        data: [1],
                        }
                    ]
                    };
            
                    //options
                    var optionsTopProducts = {
                        responsive: false,
                        title: {
                            display: true,
                            position: "top",
                            text: "Rekap Top Order " + label,
                            fontSize: 18,
                            fontColor: "#111"
                        },
                        legend: {
                            display: true,
                            position: "left",
                            labels: {
                                fontColor: "#333",
                                fontSize: 16
                            }
                        }, 
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                        return previousValue + currentValue;
                                    });
                                    var currentValue = dataset.data[tooltipItem.index];
                                    var percentage = Math.round((currentValue/total) * 100)+"%";
                                    return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                                }
                            }
                        }
                    };
                }
            } else {
                //pie chart data
                var dataTopProducts = {
                labels: ['Data Kosong'],
                datasets: [
                    {
                    label: "Top Order " + label,
                    data: [1],
                    }
                ]
                };
            
                //options
                var optionsTopProducts = {
                    responsive: false,
                    title: {
                        display: true,
                        position: "top",
                        text: "Rekap Top Order " + label,
                        fontSize: 18,
                        fontColor: "#111"
                    },
                    legend: {
                        display: true,
                        position: "left",
                        labels: {
                            fontColor: "#333",
                            fontSize: 16
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = Math.round((currentValue/total) * 100)+"%";
                                return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                            }
                        }
                    }
                };
            }
       
            //create Pie Chart class object
            var chart1 = new Chart(ctxProductChart, {
                type: "pie",
                data: dataTopProducts,
                options: optionsTopProducts
            });
        }

        function generateTopRating(data, no) {
            //get the pie chart canvas
            var topRatingsChart = JSON.parse(data);
            var ctxRatingChart = $("#top-rating-" + no);
            if(no == 2) {
                var label = "";
            } else {
                var label = topRatingsChart.category;
            }

            if(topRatingsChart) {
                if(topRatingsChart.product) {
                    var dataTopRatings = {
                    labels: topRatingsChart.product,
                    data_id: topRatingsChart.id,
                    datasets: [
                        {
                        label: "Top Order " + label,
                        data: topRatingsChart.star_review,
                        backgroundColor: [
                            "#54478c",
                            "#2c699a",
                            "#048ba8",
                            "#0db39e",
                            "#16db93",
                            "#83e377",
                            "#b9e769",
                            "#efea5a",
                            "#f1c453",
                            "#f29e4c",
                        ],
                        borderColor: [
                            "#54478c",
                            "#2c699a",
                            "#048ba8",
                            "#0db39e",
                            "#16db93",
                            "#83e377",
                            "#b9e769",
                            "#efea5a",
                            "#f1c453",
                            "#f29e4c",
                        ],
                        borderWidth: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1]
                        }
                    ]
                    };
            
                    var theHelp = Chart.helpers;
                    //options
                    var optionsTopRatings = {
                        responsive: false,
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            position: "top",
                            text: "Rekap Top Rating " + label,
                            fontSize: 18,
                            fontColor: "#111"
                        },
                        legend: {
                            display: true,
                            position: "left",
                            labels: {
                            fontColor: "#333",
                            fontSize: 16,
                                // generateLabels(chart) {
                                //     const data = chart.data;
                                //     if (data.labels.length && data.datasets.length) {
                                //         const {labels: {pointStyle}} = chart.legend.options;

                                //         return data.data_id.map((label, i) => {
                                //         var meta = chart.getDatasetMeta(0);
                                //         var ds = data.datasets[0];
                                //         var arc = meta.data[i];
                                //         var custom = arc && arc.custom || {};
                                //         var getValueAtIndexOrDefault = theHelp.getValueAtIndexOrDefault;
                                //         var arcOpts = chart.options.elements.arc;
                                //         var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                                //         var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                                //         var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

                                //         return {
                                //             text: data.data_id[i],
                                //             fillStyle: fill,
                                //             strokeStyle: stroke,
                                //             lineWidth: bw,
                                //             hidden: isNaN(ds.data[i]) || meta.data[i].hidden,

                                //             // Extra data used for toggling the correct item
                                //             index: i
                                //         };
                                //         });
                                //     }
                                //     return [];
                                // }
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                        return previousValue + currentValue;
                                    });
                                    var currentValue = dataset.data[tooltipItem.index];
                                    var percentage = Math.round((currentValue/total) * 100)+"%";
                                    return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                                }
                            }
                        }
                    };
                } else {
                    var dataTopRatings = {
                    labels: ['Data Kosong'],
                    datasets: [
                        {
                        label: "Top Rating " + label,
                        data: [1],
                        }
                    ]
                    };
            
                    //options
                    var optionsTopRatings = {
                        responsive: false,
                        title: {
                            display: true,
                            position: "top",
                            text: "Rekap Top Rating " + label,
                            fontSize: 18,
                            fontColor: "#111"
                        },
                        legend: {
                            display: true,
                            position: "left",
                            labels: {
                            fontColor: "#333",
                            fontSize: 16
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                        return previousValue + currentValue;
                                    });
                                    var currentValue = dataset.data[tooltipItem.index];
                                    var percentage = Math.round((currentValue/total) * 100)+"%";
                                    return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                                }
                            }
                        }
                    };
                }
            } else {
                var dataTopRatings = {
                labels: ['Data Kosong'],
                datasets: [
                    {
                    label: "Top Rating " + label,
                    data: [1],
                    }
                ]
                };
            
                //options
                var optionsTopRatings = {
                    responsive: false,
                    title: {
                        display: true,
                        position: "top",
                        text: "Rekap Top Rating " + label,
                        fontSize: 18,
                        fontColor: "#111"
                    },
                    legend: {
                        display: true,
                        position: "left",
                        labels: {
                        fontColor: "#333",
                        fontSize: 16
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = Math.round((currentValue/total) * 100)+"%";
                                return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                            }
                        }
                    }
                };
            }
       
            //create Pie Chart class object
            var chart2 = new Chart(ctxRatingChart, {
                type: "pie",
                data: dataTopRatings,
                options: optionsTopRatings
            });
        }

        function topOrder(data, no) {
            var topProductsChart = JSON.parse(data);
            if(no == 1) {
                var title = 'Top Order';
                var label = 'Rekap Order';
            } else {
                var title = 'Top Order ' + topProductsChart.category;
                var label = 'Rekap Order ' + topProductsChart.category;
            }

            if(topProductsChart) {
                if(topProductsChart.product) {
                    var data = topProductsChart;
                    var check_data = topProductsChart.product;
                    var dataTopProducts = new Array(check_data.length);
                    Object.keys(check_data).forEach(function(key) {    
                        dataTopProducts[key] = {
                            name: data.product[key],
                            y: data.total[key]
                        }
                    });

                    var topProductsSeries = dataTopProducts;
                } else {
                    var topProductsSeries = {
                        name: 'Data Kosong',
                        y: 1
                    };
                }
            } else {
                var topProductsSeries = {
                    name: 'Data Kosong',
                    y: 1
                };
            }

            Highcharts.chart('top-order-' + no, {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: title,
                    style: {"font-weight": "bold"}
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.percentage:.1f} %'
                        }
                    },
                    series: {
                        dataLabels: {
                            style: {
                                fontSize: '13px',
                                fontWeight: 'normal'
                            }
                        }
                    }
                }, 
                series: [{
                    name: label,
                    colorByPoint: true,
                    data: topProductsSeries    
                }]});
        }

        function topRating(data, no) {
            var topRatingsChart = JSON.parse(data);
            if(no == 2) {
                var title = 'Top Rating';
                var label = 'Rekap Rating';
            } else {
                var title = 'Top Rating ' + topRatingsChart.category;
                var label = 'Rekap Order ' + topRatingsChart.category;
            }

            if(topRatingsChart) {
                if(topRatingsChart.product) {
                    var data = topRatingsChart;
                    var check_data = topRatingsChart.product;
                    var dataTopRatings = new Array(check_data.length);
                    Object.keys(check_data).forEach(function(key) {    
                        dataTopRatings[key] = {
                            name: data.product[key],
                            y: data.star_review[key]
                        }
                    });

                    var topRatingsSeries = dataTopRatings;
                } else {
                    var topRatingsSeries = {
                        name: 'Data Kosong',
                        y: 1
                    };
                }
            } else {
                var topRatingsSeries = {
                    name: 'Data Kosong',
                    y: 1
                };
            }

            Highcharts.chart('top-rating-' + no, {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: title,
                    style: {"font-weight": "bold"}
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.percentage:.1f} %'
                        }
                    },
                    series: {
                        dataLabels: {
                            style: {
                                fontSize: '13px',
                                fontWeight: 'normal'
                            }
                        }
                    }
                }, 
                series: [{
                    name: label,
                    colorByPoint: true,
                    data: topRatingsSeries    
                }]});
        }

        function generateBarChart(data_user) {
            var user_apps_label = [];
            var user_apps_datasets = [];
            var user_apps_datasets_erp = [];
            data_user.map(function(item) {
                user_apps_label.push(item.site_code);
                user_apps_datasets.push(item.total);
                user_apps_datasets_erp.push(item.userErp);
            });

            const labels = user_apps_label;
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'User ERP',
                        data: user_apps_datasets_erp,
                        borderColor: 'rgb(54, 162, 235, 0.5)',
                        backgroundColor: 'rgb(54, 162, 235, 0.5)',
                    },
                    {
                        label: 'User Apps',
                        data: user_apps_datasets,
                        borderColor: 'rgb(255, 99, 132, 0.5)',
                        backgroundColor: 'rgb(255, 99, 132, 0.5)',
                    }
                ]
            }
            const config = {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Chart.js Bar Chart'
                        }
                    }
                }
            }

            var barChart = new Chart($("#bar-chart"), config);
        }

        $(document).ready(function(){
            var top = {!! json_encode($productTop) !!};

            Object.keys(top).forEach(function(key) {    
                if(((parseInt(key) + 1) % 2) != 0) {
                    generateTopOrder(top[key], (parseInt(key) + 1));
                    // topOrder(top[key], (parseInt(key) + 1));
                } else if (((parseInt(key) + 1) % 2) == 0) {
                    generateTopRating(top[key], (parseInt(key) + 1));
                    // topRating(top[key], (parseInt(key) + 1));
                }
            });

            @if(isset($userApps))
                var user_apps = {!! json_encode($userApps) !!};
                generateBarChart(user_apps);
            @endif

            const labels = ['New', 'Confirm', 'Delivery', 'Complete', 'Cancel'];
            const labels_data = [{!! json_encode($newOrders) !!}, {!! json_encode($confirmedOrders) !!}, {!! json_encode($deliveryOrders) !!}, {!! json_encode($completeOrders) !!}, {!! json_encode($cancelOrders) !!}];
            const labels_color = ['rgb(54, 162, 235, 0.5)', 'rgb(255, 205, 86, 0.5)', 'rgb(255, 159, 64, 0.5)', 'rgb(75, 192, 192, 0.5)', 'rgb(255, 99, 132, 0.5)'];
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Order',
                        data: labels_data,
                        borderColor: labels_color,
                        backgroundColor: labels_color,
                    }
                ]
            }
            const config = {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        position: "top",
                        text: "Order",
                        fontSize: 18,
                        fontColor: "#111"
                    },
                    legend: {
                        display: true,
                        position: "top",
                        labels: {
                        fontColor: "#333",
                        fontSize: 16
                        }
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex];
                                var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.index];
                                var percentage = Math.round((currentValue/total) * 100)+"%";
                                return data.labels[tooltipItem.index] + ": " + currentValue + " (" + percentage + ")";
                            }
                        }
                    }
                }
            }

            var orderChart = new Chart($("#order-chart"), config);
        });
        
        $('.btn-chart').on('click', function(){
            if($('.btn-chart').attr("class") == "btn btn-secondary btn-chart" || $('.btn-chart').attr("class") == "btn btn-chart btn-secondary") {
                $('.btn-chart').removeClass("btn-secondary");
                $('.btn-chart').addClass("btn-blue");
                $('.top-table').css('display', 'none');
                $('.top-chart').show();
            } else if($('.btn-chart').attr("class") == "btn btn-blue btn-chart" || $('.btn-chart').attr("class") == "btn btn-chart btn-blue") {
                $('.btn-chart').removeClass("btn-blue");
                $('.btn-chart').addClass("btn-secondary");
                $('.top-table').show();
                $('.top-chart').css('display', 'none');
            }
        })
    </script>
@endsection
