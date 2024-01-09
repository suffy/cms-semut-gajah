@extends('public.layout.member-layout')

@section('member-content')

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table th{
            padding: 5px;
            vertical-align: top;
            background: #212e74;
            color: #ffffff;
            border: 1px solid #212e74;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
            border: 1px solid #f1f1f1;
            border-collapse: collapse;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td{
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }


    </style>

<a href="{{url('member/order-detail/'.$order->id)}}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i>
                    Kembali
                </a>

<div class="invoice-box" style="max-width: 100vw;margin: auto;padding: .3vw;border: 1px solid #eee;box-shadow: 0 0 10px rgba(0, 0, 0, .15);font-size: 16px;line-height: 24px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #555;">
    <div class="card-body">
        <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left">
           

            <tr>
                <th colspan="5" style="text-align: center">ORDER INFORMATION #{{$order['id']}}</th>
            </tr>

            <tr class="information">

                <td colspan="5" style="padding: 5px;vertical-align: top;">
                    <b>Nama : {{$order['name']}}</b><br>
                    <b>No Telp : {{$order['phone']}}</b><br>
                    <b>Tanggal Order : {{$order['order_time']}}</b>
                </td>
            </tr>

            <tr>
                <td colspan="2">Invoice ID</td>
                <td  colspan="3">#{{$order['id']}}</td>
            </tr>
            <tr>
                <td colspan="2">Alamat Pengiriman</td>
                <td  colspan="3">{{$order['address']}}</td>
            </tr>
                @php

                $order_detail = \App\OrderDetail::join('products', 'products.id','=','order_detail.product_id')
                ->where('order_id', $order->id)
                ->select('order_detail.*', 'name as product_name')
                ->get();


                $item = 0;
                $total = 0;

                foreach ($order_detail as $key => $value) {
                    $item = $item + $value->qty;
                    $total = $total + $value->total_price;
                }

                $order_details = [$order_detail, $item, $total];

                @endphp



            <tr>
                <th style="text-align: center">No.</th>
                <th>Product Name</th>
                <th style="text-align: center">Qty</th>
                <th style="text-align: right">Price</th>
                <th style="text-align: right">Sub Total</th>
            </tr>

            @foreach($order_details[0] as $key => $value)
                <tr>
                    <td class="invert" style="text-align: center">{{$key+1}}</td>
                    <td class="invert">{{$value->product_name}}</td>
                    <td class="invert" style="text-align: center">
                        {{$value->qty}}
                    </td>
                    <td class="invert" style="text-align: right">Rp {{number_format($value->price)}}</td>
                    <td class="invert" style="text-align: right">RP {{number_format($value->total_price)}}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4">Items</td>
                <td colspan="1" style="text-align: right">{{$order_details[1]}}</td>
            </tr>
            <tr>
                <td colspan="4">Berat</td>
                <td style="text-align: right">{{$order->order_weight}} gr</td>
            </tr>
            <tr>
                <td colspan="4">Sub Total</td>
                <td colspan="1" style="text-align: right">Rp {{number_format($order_details[2])}}</td>
            </tr>
            <tr>
                <td colspan="4">Ongkir</td>
                <td style="text-align: right">Rp {{number_format($order->delivery_fee)}}</td>
            </tr>

            <tr>
                <td colspan="4">Discount</td>
                <td style="text-align: right">Rp {{number_format($order->payment_discount)}} <br> {{$order->payment_discount_code}}</td>
            </tr>

            <tr>
                <th colspan="4">Total Pembayaran</th>
                <th style="text-align: right">Rp {{number_format($order->payment_final)}}</th>
            </tr>


            <tr>
                <td colspan="5">
                    <p style="text-align: center">
                        
                    @php 
                        $page = \App\Page::where('slug', 'cara-pembayaran')->first();
                    @endphp

                    @if($page)

                    {!!$page->content!!}

                    @endif
                    </p>
                </td>
            </tr>

        </table>

    </div>
    </div>

    @endsection