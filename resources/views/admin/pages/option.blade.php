@extends('admin.layout.template')
@section('content')

<section class="panel">
  <header class="panel-heading">
    Options
  </header>

  <div class="card-body">
    <div class="panel">
      <a href="#" data-toggle="modal" data-target="#newOption" class="btn btn-blue"><span class="fa fa-plus"></span> New Option</a><br><br>
      <table class="table default-table dataTable">
        <thead class="text-center">
          <tr>
            <th>No</th>
            <th>Key</th>
            <th>Name</th>
            <th>Value</th>
            <th>Menu</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($option as $key => $opsi)
          <tr>
            <td class="text-center">{{ $key+1 }}</td>

            <td>{{$opsi->slug}}</td>
            <td>
              {{$opsi->option_name}}<br>
              {{$opsi->option_name_en}}

            </td>
            <td>

              @if($opsi->icon)
              <div style="width: 100%; text-align: center">
              <img src="{{asset($opsi->icon)}}" class="img-fluid" style="max-width: 100px; max-height: 100px; object-fit: cover; margin: auto">
              </div>
              <br>
            @endif

            <div style="max-width: 400px; max-height: 150px; overflow:auto">
              {{$opsi->option_value}}<br>
              {{$opsi->option_value_en}}
            </div>
            </td>
            <td>{{$opsi->position}}</td>
            <td>

              <div class="btn-group">
                <a href="javascript:void(0)" class="btn btn-green btn-sm button-edit-options" data-id="{{ $opsi->id }}" data-option_type="{{ $opsi->option_type }}" data-slug="{{ $opsi->slug }}" data-option_name="{{ $opsi->option_name }}" data-option_name_en="{{ $opsi->option_name_en }}" data-option_value="{{ $opsi->option_value }}" data-option_value_en="{{ $opsi->option_value_en }}" data-editable="{{ $opsi->editable }}" data-icon="{{ $opsi->icon }}" data-status="{{ $opsi->status }}" data-update="{{ url('admin/options',$opsi->id) }}" data-position="{{ $opsi->position }}" data-toggle="modal" data-target="#editOption" style="display: inline-block;">
                  <i class="fa fa-edit"></i>
                </a>

                
                <form action="{{url('admin/options/'.$opsi->id)}}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
                  @method('delete')
                  @csrf
                  <button type="submit" class="btn btn-red btn-sm" value="Delete" onclick="return confirm('Delete Data?')"><i class="fa fa-trash"></i></button>
                </form>
               
              </div>
    </div>

    </td>
    </tr>
    @endforeach
    </tbody>
    </table>
  </div>
  </div>
</section>
<!-- Modal New -->
<div class="modal fade" id="newOption" tabindex="-1" role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Add Option</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{url('admin/options')}}" method="post" enctype="multipart/form-data">
          @csrf

          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Key</label>
            <div class="col-sm-12">
              <input type="text" name="slug" id="" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Image </label>
            <div class="col-sm-12">
              <input type="file" class="file" name="icon" value="">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Page</label>
            <div class="col-sm-12">
              <select class="form-control" name="position">
                <option value="none">- Select Page -</option>
                <option value="general">General</option>
                @foreach($menu as $row)
                <option value="{{$row->slug}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
            <div class="form-group row">
            <label class="col-sm-12 col-form-label">Name</label>
            <div class="col-sm-12">
              <input type="text" name="option_name" id="" class="form-control" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Value</label>
            <div class="col-sm-12">
              <textarea name="option_value" id="" class="form-control"></textarea>
            </div>
          </div>
            </div>
            <div class="col-md-6">
            <div class="form-group row">
            <label class="col-sm-12 col-form-label">Name En</label>
            <div class="col-sm-12">
              <input type="text" name="option_name_en" id="" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Value En</label>
            <div class="col-sm-12">
              <textarea name="option_value_en" id="" class="form-control"></textarea>
            </div>
          </div>
            </div>
          </div>
          
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- EndModalNew -->
<!-- Modal Edit -->
<div class="modal fade" id="editOption" tabindex="-1" role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title">Edit Option</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="updateOption" action="#" method="post" enctype="multipart/form-data">
          @method('put')
          @csrf
          <input type="hidden" name="url" value="{{Request::url()}}" required>
          <input type="hidden" id="input-id-edit" name="id" value="">
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Key</label>
            <div class="col-sm-12">
              <input type="text" id="input-slug-edit" name="slug" class="form-control" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Image </label>
            <div class="col-sm-12">
              <input type="file" class="file" name="icon" value="">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Page</label>
            <div class="col-sm-12">
              <select id="edit-page" class="form-control" name="position">
                <option value="0">- Select Page -</option>
                <option value="general">General</option>
                @foreach($menu as $row)
                <option value="{{$row->slug}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-sm-12 col-form-label">Name</label>
                <div class="col-sm-12">
                  <input type="text" id="input-option_name-edit" name="option_name" class="form-control">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-12 col-form-label">Value</label>
                <div class="col-sm-12">
                  <textarea id="input-option_value-edit" name="option_value" class="form-control" style="height: 200px"></textarea>
                  <span style="font-size: 8pt">* Tambahkan tag {{"<br>"}} untuk new line paragraph</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
            <div class="form-group row">
              <label class="col-sm-12 col-form-label">Name En</label>
              <div class="col-sm-12">
                <input type="text" id="input-option_name-edit_en" name="option_name_en" class="form-control">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-12 col-form-label">Value En</label>
              <div class="col-sm-12">
                <textarea id="input-option_value-edit_en" name="option_value_en" class="form-control" style="height: 200px"></textarea>
              </div>
            </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- EndModal Edit -->

<script type="text/javascript">
  $('.button-edit-options').on('click', function(e) {
    var url = $(this).data('update');
    var id = $(this).attr('data-id');
    var option_type = $(this).attr('data-option_type');
    var slug = $(this).attr('data-slug');
    var option_name = $(this).attr('data-option_name');
    var option_name_en = $(this).attr('data-option_name_en');
    var option_value = $(this).attr('data-option_value');
    var option_value_en = $(this).attr('data-option_value_en');
    var position = $(this).attr('data-position');
    var icon = $(this).attr('data-icon');
    var status = $(this).attr('data-status');

    $('#updateOption').attr('action', url);
    $('#input-id-edit').val(id);
    $('#input-option_type-edit').val(option_type);
    $('#input-option_name-edit').val(option_name);
    $('#input-option_name-edit_en').val(option_name_en);
    $('#input-option_value-edit').val(option_value);
    $('#input-option_value-edit_en').val(option_value_en);
    $('#input-slug-edit').val(slug);
    $('#edit-page').val(position);
    $('#input-icon-edit').val(icon);
    $('#input-status-edit').val(status);
  });
  $('.file').bind('change', function() {
    //this.files[0].size gets the size of your file.
    if (this.files[0].size > 2000000) {
      alert("File size is too big, please select another file (max 2MB)");
      $(this).val('');
    }
  });
</script>
@stop