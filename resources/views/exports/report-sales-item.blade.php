{{-- <table>
    <thead>
        <tr>
        <th>No</th>
        <th>Invoice</th>
        <th>Nama</th>
        <th>Alamat</th>
        <th>Item</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Pembayaran</th>
        <th>Tgl. Order</th>
        </tr>
    </thead>
    <tbody>

    @foreach($orders as $order)
        @foreach($order->data_item as $item)
            @if($item->product_id)
                <tr>
                    <td scope="row">{{$loop->iteration}}</td>
                    <td>{{ $order->invoice }}</td>
                    <td>@foreach($order->data_user->user_address as $user){{ $order->data_user->name }}<br>{{ $user->shop_name }}@endforeach</td>
                    <td>{{ ucwords($order->address) }}, {{ ucwords($order->kelurahan) }}, {{ ucwords($order->kecamatan) }}, {{ ucwords($order->kota) }}, {{ ucwords($order->provinsi) }} {{ $order->kode_pos }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>Rp. {{ number_format($item->total_price, 2) }}</td>
                    <td>{{ strtoupper($order->payment_method) }}</td>
                    <td>{{ date('l, d M Y H:m', strtotime($order->order_time)) }}</td>
                </tr>
            @endif
        @endforeach
    @endforeach
    </tbody>
</table> --}}
<table>
    <thead>
      <tr>
        <th>No</th>
        <th>Invoice</th>
        <th>Nama</th>
        <th>Alamat</th>
        <th>Item</th>
        <th>Pembayaran</th>
        <th>Tgl. Order</th>
        <th>Qty</th>
        <th>Promo</th>
        <th>Total Sebelum Promo</th>
        <th>Sub Total</th>
      </tr>
    </thead>
    <tbody>
    @php($total = 0)
    @foreach($orders as $order)
        @foreach($order->order_details as $item)
            @if($item->product_id)
                <tr>
                    <td scope="row">{{$loop->iteration}}</td>
                    <td>{{ $order->invoice }}</td>
                    <td>@foreach($order->data_user->user_address as $user){{ $order->data_user->name }}<br>{{ $user->shop_name }}@endforeach</td>
                    <td>{{ ucwords($order->address) }}, {{ ucwords($order->kelurahan) }}, {{ ucwords($order->kecamatan) }}, {{ ucwords($order->kota) }}, {{ ucwords($order->provinsi) }} {{ $order->kode_pos }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ strtoupper($order->payment_method) }}</td>
                    <td>{{ date('l, d M Y H:m', strtotime($order->order_time)) }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>
                        @if($item->promo)
                            @foreach($item->promo->reward as $reward)
                                @if($reward->reward_disc)
                                    {{$reward->reward_disc}}%
                                @elseif($reward->reward_nominal)
                                    Rp. {{number_format($reward->reward_nominal,2)}}
                                @elseif($reward->reward_point)
                                    {{$reward->reward_point}} Point
                                @elseif($reward->bonus)
                                    {{$reward->bonus_name}} - ({{$reward->reward_qty}})
                                @else
                                @endif
                            @endforeach
                        @else
                            Tidak Dapat Promo
                        @endif
                    </td>
                    <td>Rp. {{ number_format($item->total_price, 2) }}</td>
                    <td></td>
                </tr>
            @endif
        @endforeach
        <tr>
            <th colspan="10">Total Per Order</th>
            <th style="text-align:center">Rp. {{ number_format($order->payment_final, 2) }}</th>
            @php($total += $order->payment_final)
        </tr>
    @endforeach
    <tr>
        <th colspan="10">Total Order</th>
        <th style="text-align:center">Rp. {{ number_format($total, 2) }}</th>
    </tr>
    </tbody>
  </table>