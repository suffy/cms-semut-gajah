@foreach($salesmen as $salesman)
<tr>
    <td>{{ $salesman->kodesales }}</td>
    <td>{{ $salesman->kodesales_erp }}</td>
    <td>{{ $salesman->namasales }}</td>
    {{-- <td class="text-center align-middle" width="60px">
        <a href="{{url('admin/categories/')}}" class="btn btn-blue btn-sm">Detail</a>
        <form method="post" enctype="multipart/form-data" action="{{url('admin/categories/')}}">
            @csrf
            @method('delete')
            <input type="hidden" name="id" value="">
            <input type="hidden" name="url" value="{{Request::url()}}">
            <input type="hidden" name="status" value="1">
            <button type="submit" class="btn btn-sm btn-red" onclick="return confirm('Are you sure?')">Hapus</button>
        </form>
        
    </td> --}}
</tr>
@endforeach
<tr>
    <td colspan="3" align="center">{!! $salesmen->links() !!}</td>
</tr>