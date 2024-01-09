@forelse($testimonials as $row) 
    <tr>
        <td align="center">{{$loop->iteration}}.</td>
        <td align="center">{{$row->name}}</td>
        <td align="center">{{$row->description}}</td>
        <td align="center">{{$row->shop_name}}</td>
        <td align="center">{{$row->city}}</td>
        <td align="center">
            {{-- <a href="javascript:void(0)" 
            class="btn btn-green btn-sm btn-edit-testi mx-1" data-toggle="modal" 
            data-target="#editTesti"
            data-id="{{ $row->id }}"
            data-name="{{ $row->name }}"
            data-desc="{{ $row->description }}"
            data-shop-name="{{ $row->shop_name }}"
            data-city="{{ $row->city }}"
            data-action="{{url('manager/testimonials/update'). '/' .$row->id}}">Edit</a> --}}
            @if(!$row->accept)
                <a href="javascript:void(0)" class="btn btn-green btn-sm btn-accept-testi mx-1" data-id="{{ $row->id }}" onclick="return confirm('Are you sure?')">Accept</a>
                @else

            @endif

            <a href="javascript:void(0)" 
            class="btn btn btn-sm btn-red btn-sm btn-delete-testi mx-1"
            data-id="{{ $row->id }}" onclick="return confirm('Are you sure?')">
            @if($row->accept)
                Hapus
            @else
                Tolak
            @endif
            </a>
        </td>
    </tr>
@empty
<tr>
    <td colspan="6" align="center"> Data Kosong !! </td>
</tr>
@endforelse
<tr>
    <td colspan="6" align="center">{!! $testimonials->links() !!}</td>
</tr>