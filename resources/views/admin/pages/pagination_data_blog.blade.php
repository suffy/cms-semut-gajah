@forelse($blogs as $row) 
    <tr align="center">
        <td>{{$loop->iteration}}</td>
        <td>{{$row->title}}</td>
        {{-- <td>{{$row->image}}</td> --}}
        <td>
            <img class="img img-responsive" id="icon" src="{{asset($row->image)}}" width="100px">
        </td>
        <td>{!! $row->description !!}</td>
        <td>
            <label class="switch">
                <input data-id="{{$row->id}}" class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $row->status_highlight ? 'checked' : '' }}>
                <span class="slider round"></span>
            </label>
        </td>
        <td>            
            <a href="javascript:void(0)" 
            class="btn btn-green btn-sm btn-edit-blog mx-1" data-toggle="modal" 
            data-target="#editBlog"
            data-id="{{ $row->id }}"
            data-title="{{ $row->title }}"
            data-desc="{{ $row->description }}"
            data-image="{{ $row->image }}">Edit</a>

            <a href="javascript:void(0)" 
            class="btn btn btn-sm btn-red btn-sm btn-delete-blog mx-1"
            data-id="{{ $row->id }}" onclick="return confirm('Are you sure?')">
                Hapus
            </a>
        </td>
    </tr>
@empty
<tr>
    <td colspan="6" align="center"> Data Kosong !! </td>
</tr>
@endforelse
<tr>
    <td colspan="6" align="center">{!! $blogs->links() !!}</td>
</tr>