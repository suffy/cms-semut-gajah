
@php
$no=0;
@endphp
@foreach($mappingSites as $mappingSite)
<tr>
    <td width="10px">{{$mappingSites->firstItem()+$no++}}</td>
    <td>{{ $mappingSite->kode }}</td>
    <td>{{ $mappingSite->branch_name }}</td>
    <td>{{ $mappingSite->nama_comp }}</td>
    <td>{{ $mappingSite->kode_comp }}</td>
    <td>{{ $mappingSite->sub }}</td>
    <td>{{ $mappingSite->status_ho }}</td>
    <td>{{ $mappingSite->telp_wa }}</td>
    <td>Rp. {{ number_format($mappingSite->min_transaction, 2, ',', '.') }}</td>
    <td><a href="javascript:void(0)" 
        class="btn btn-green btn-sm btn-edit-mapping-site mx-1" data-toggle="modal" 
        data-target="#editMappingSite"
        data-id="{{ $mappingSite->id }}"
        data-site-id="{{ $mappingSite->site_id }}"
        data-site-name="{{ $mappingSite->site_name }}"
        data-name="{{ $mappingSite->name }}"
        data-action="{{url('manager/mapping-site/'). '/' .$mappingSite->id}}"
        data-icon="{{asset($mappingSite->icon)}}">Edit</a></td>
    {{-- <td class="text-center">
        <div style="display: inline-flex;">
            <a href="javascript:void(0)" 
                class="btn btn-green btn-sm btn-edit-mapping-site mx-1" data-toggle="modal" 
                data-target="#editMappingSite"
                data-id="{{ $mappingSite->id }}"
                data-site-id="{{ $mappingSite->site_id }}"
                data-site-name="{{ $mappingSite->site_name }}"
                data-name="{{ $mappingSite->name }}"
                data-action="{{url('manager/mapping-site/'). '/' .$mappingSite->id}}"
                data-icon="{{asset($mappingSite->icon)}}">Edit</a>
            <form method="post" enctype="multipart/form-data" action="{{url('manager/mapping-site/'.$mappingSite->id)}}">
                @csrf
                @method('delete')
                <input type="hidden" name="id" value="{{$mappingSite->id}}">
                <input type="hidden" name="url" value="{{Request::url()}}">
                <input type="hidden" name="status" value="1">
                <button type="submit" class="btn btn-sm btn-red mx-1" onclick="return confirm('Are you sure?')">Hapus</button>
            </form>
        </div>
    </td> --}}
</tr>
@endforeach
<tr>
    <td colspan="6" align="center">{!! $mappingSites->links() !!}</td>
</tr>