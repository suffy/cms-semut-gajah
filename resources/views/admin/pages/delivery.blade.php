<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    
        <title>Semut Gajah</title>

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
            background: #f1f1f1;
            color: #000000;
            border: 1px solid #f1f1f1;
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
</head>

<body>
    <div class="invoice-box" style="max-width: 100vw;margin: auto;padding: .3vw;border: 1px solid #eee;box-shadow: 0 0 10px rgba(0, 0, 0, .15);font-size: 16px;line-height: 24px;font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;color: #555;">
        <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left">
            

            <tr>
                <th colspan="3" style="text-align: center">INFORMASI PENGIRIMAN</th>
            </tr>

            <tr class="information">

                <td colspan="3" style="padding: 5px;vertical-align: top;">
                    <p>Kepada : </p>
                    <b>Nama : {{$order['name']}}</b><br>
                    <b>No Telp : {{$order['phone']}}</b><br>
                    <b>Alamat : {{ucwords($order['address'])}}, {{ ucwords($order['kelurahan']) }}, {{ ucwords($order['kecamatan']) }}, {{ ucwords($order['kota']) }}, {{ ucwords($order['provinsi']) }} {{ $order['kode_pos'] }}</b><br>
                </td>
            </tr>

            <tr class="information">

                <td colspan="3" style="padding: 5px;vertical-align: top;">
                @php 
                    $title = App\DataOption::where('slug', 'title')->first();
                    $email = App\DataOption::where('slug', 'email')->first(); 
                @endphp
                    <p>Dari : </p>
                    @if($title)
                        <b>{{$title->option_value}}</b><br>
                    @endif
                    @if($email)
                        <b>{{$email->option_value}}</b><br>
                    @endif
                </td>
            </tr>

            <tr>
                <td colspan="2">Nomor Order</td>
                <td  colspan="1">#{{$order['id']}}</td>
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
                <th style="text-align: center" width="50px">No.</th>
                <th width="100%">Nama Item</th>
                <th style="text-align: center">Julmah</th>
            </tr>

            @foreach($order_details[0] as $key => $value)
                <tr>
                    <td  style="text-align: center">{{$key+1}}</td>
                    <td style="text-align: left">{{$value->product_name}}</td>
                    <td  style="text-align: center">
                        {{$value->qty}} Pcs
                    </td>
                </tr>
            @endforeach
            

        </table>

    </div>

</body>
</html>