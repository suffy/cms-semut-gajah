@forelse($customers as $row) 
    <tr align="center">
        <td>{{$row->user->name}}</td>
        <td>{{$row->user->customer_code}}</td>
        <td>{{$row->user->site_code}}</td>
        <td>{{$row->order_count}}</td>
        <td>Rp. {{ number_format($row->payment_final, 0, '.', ',') }}</td>
        <td>
            {{-- <a href="{{url('/manager/customers/recaps/detail/' . $row->id)}}" class="btn btn-green btn-sm">Detail</a> --}}
            <a href="javascript:void(0)" class="btn btn-green btn-recap-detail" id="btn-detail" data-id="{{$row->customer_id}}" data-toggle="modal" data-target="#detail" data-backdrop="static" data-keyboard="false">Detail</a>
        </td>
    </tr>
@empty
<tr>
    <td colspan="6" align="center"> Data Tidak Ditemukan </td>
</tr>
@endforelse
<tr>
    <td colspan="6" align="center">{!! $customers->links() !!}</td>
</tr>