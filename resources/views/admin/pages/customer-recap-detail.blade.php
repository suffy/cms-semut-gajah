@extends('admin.layout.template')

@section('content')

    <section class="panel">
        <header class="panel-heading">
            Recaps Customers
        </header>

        <div class="card-body">   
            
            <div class="row">
                <div class="col-11 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer table-responsive">
                        <div class="table-inner">
                            <table class="table default-table">
                                <thead>
                                <tr  align="center">
                                    <td>Kodeprod</td>
                                    <td>Product</td>
                                    <td>Brand ID</td>
                                    <td>Qty</td>
                                    <td>Total Nominal</td>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($products as $row)
                                    <tr align="center">
                                        <td>{{$row->product->kodeprod}}</td>
                                        <td>{{$row->product->name}}</td>
                                        <td>{{$row->product->brand_id}}</td>
                                        <td>{{$row->qty_total}}</td>
                                        <td>{{$row->price_total}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{-- {{$customers->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}} --}}
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <script>
    </script>

    <style>
        .text-small{
            font-size: 8pt;
            color: red;
        }
    </style>
@stop
