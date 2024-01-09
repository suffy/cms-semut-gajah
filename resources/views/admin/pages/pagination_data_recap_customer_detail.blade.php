@forelse($products as $row)
    <tr align="center">
        <td>{{$row->product->kodeprod}}</td>
        <td>{{$row->product->name}}</td>
        <td>{{$row->product->brand_id}}</td>
        <td>{{$row->qty_total}}</td>
        <td>Rp. {{ number_format($row->price_total, 0, '.', ',') }}</td>
    </tr>
    @empty
        <tr>
            <td colspan="5" style="text-align:center">Data Tidak Ditemukan</td>
        </tr>
@endforelse