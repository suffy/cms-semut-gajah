<table>
    <thead>
        <tr>
        <th>No</th>
        <th>Invoice</th>
        <th>Nama</th>
        <th>Alamat</th>
        <th>Item</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Voucher</th>
        <th>Ongkir</th>
        <th>Total</th>
        <th>Pembayaran</th>
        <th>Tgl. Order</th>
        </tr>
    </thead>
    <tbody>
    @foreach($orders as $order)
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
                <td>{{ $item }}</td>
                <td>{{ $qty }}</td>
                <td>{{ $harga }}</td>
                <td></td>
                <td>Bebas Ongkir</td>
                <td>Rp. {{ number_format($order->payment_final, 2) }}</td>
                    <td>{{ strtoupper($order->payment_method) }}</td>
                <td>{{ date('l, d M Y H:m', strtotime($order->order_time)) }}</td>
            </tr>
    @endforeach
    </tbody>
</table>