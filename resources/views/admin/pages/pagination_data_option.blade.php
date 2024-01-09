@forelse($options as $row) 
    <tr>
        <td align="center">{{$options->firstItem() + $loop->index}}.</td>
        <td align="center">{{$row->option_name}}</td>
        <td align="center">{{$row->option_value}}</td>
        <td align="center">
            <a href="javascript:void(0)" 
            class="btn btn-green btn-sm btn-edit-option mx-1" data-toggle="modal" 
            data-target="#editOption"
            data-id="{{ $row->id }}"
            data-slug="{{ $row->slug }}"
            data-name="{{ $row->option_name }}"
            data-value="{{ $row->option_value }}">Edit</a>

            {{-- <a href="javascript:void(0)" 
            class="btn btn btn-sm btn-red btn-sm btn-delete-testi mx-1"
            data-id="{{ $row->id }}" onclick="return confirm('Are you sure?')">Delete</a> --}}
        </td>
    </tr>
@empty
<tr>
    {{-- <td colspan="6" align="center"> Data Kosong !! </td> --}}
</tr>
@endforelse
<tr>
    <td colspan="6" align="center">{!! $options->links() !!}</td>
</tr>