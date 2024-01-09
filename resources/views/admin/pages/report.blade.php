@extends('admin.layout.template')

@section('content')
<div class="page-member-title">
    <h3>Laporan</h3>
</div>

<br>

<section class="panel">
    {{-- <header class="panel-heading">
        <b>Filter</b>
    </header> --}}

    <div class="card-body">

        <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Total Product
                        <h3>{{ $totalProducts }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Total Order
                        <h3>{{ $totalOrders }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Customer
                        <h3>{{ $totalUsers }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <div class="box-border">
                    <div class="card-body">
                        Salesman
                        <h3>{{ $totalSalesman }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 my-5">
                <a href="javascript:void(0)" class="btn btn-blue" onclick="return print()">Export PDF</a>
            </div>
        </div>

        <div class="row" id="print">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Rekap Top Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topProducts as $topProduct)
                            <tr>
                                <td width="600">{{ $topProduct->product }}</td>
                                <td>{{ $topProduct->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 ">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2">Rekap Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="600">Jumlah Order</td>
                            <td>{{ $totalOrders }}</td>
                        </tr>
                        <tr>
                            <td width="600">Order Pending</td>
                            <td>{{ $totalPendingOrders }}</td>
                        </tr>
                        <tr>
                            <td width="600">Order Terkonfirmasi</td>
                            <td>{{ $totalConfirmationOrders }}</td>
                        </tr>
                        <tr>
                            <td width="600">Pengiriman</td>
                            <td>{{ $totalDeliveryOrders }}</td>
                        </tr>
                        <tr>
                            <td width="600">Order Selesai</td>
                            <td>{{ $totalSuccessOrders }}</td>
                        </tr>
                        <tr>
                            <td width="600">Order Batal</td>
                            <td>{{ $totalFailedOrders }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
</script>

@endsection
