@extends('admin.layout.template')
@section('content')

<section class="panel">
  <header class="panel-heading">
    Options For Landing Page
  </header>

  <div class="card-body">
    <div class="panel">
      {{-- <a href="#" data-toggle="modal" data-target="#newOption" class="btn btn-blue"><span class="fa fa-plus"></span> New Option</a><br><br> --}}
      <table class="table default-table">
        <thead class="text-center">
          <tr>
            <th>No</th>
            {{-- <th>Key</th> --}}
            <th>Name</th>
            <th>Value</th>
            {{-- <th>Menu</th> --}}
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        {{-- @foreach($option as $key => $opsi)
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
        @endforeach --}}

        @if(isset($options))
            @include('admin.pages.pagination_data_option')
        @else
            <tr>
                <td colspan="6" align="center"> Data Kosong !! </td>
            </tr>
        @endif
    </tbody>

    </table>
  </div>
  </div>
</section>

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
          {{-- @method('put') --}}
          @csrf
          <input type="hidden" name="url" value="{{Request::url()}}" required>
          <input type="hidden" id="edit-id" name="id" value="">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group row">
                <label class="col-sm-12 col-form-label">Name</label>
                <div class="col-sm-12">
                  <input type="text" id="edit-name" name="option_name" readonly class="form-control">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-12 col-form-label">Value</label>
                <div class="col-sm-12">
                  {{-- <textarea id="input-option_value-edit" name="option_value" class="form-control" style="height: 200px"></textarea>
                  <span style="font-size: 8pt">* Tambahkan tag {{"<br>"}} untuk new line paragraph</span> --}}
                  <input type="text" id="edit-value" name="option_value" class="form-control">
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

<script>
    $(document).ready(function() {

        $(document).on('click', '.btn-edit-option', function () {
        // $('.btn-edit-testi').on('click', function(){
            var id          = $(this).attr('data-id');
            var name        = $(this).attr('data-name');
            var value        = $(this).attr('data-value');

            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-value').val(value);
            // $('#edit-testi').attr('action', action);
        })

        fetch_data(1);  

        $('#updateOption').on('submit', function(e) {
            e.preventDefault();
            var fd  = new FormData(this);
            var id = $('#edit-id').val();
            $.ajax({ 
                url: "/manager/options/update/" + id,
                type: 'POST',
                data: fd,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) { 
                    fetch_data(1);
                    $('#editOption').modal('hide');
                    showNotif("Simpan data sukses")
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        });

        function fetch_data(page) {
            $.ajax({
                url:@if(auth()->user()->account_role == 'manager')
                        "{{url('/manager/options/fetch_data')}}?page="+page
                    @else 
                        "{{url('/superadmin/options/fetch_data')}}?page="+page
                    @endif,
                success: function(data) {
                    $('tbody').html('');
                    $('tbody').html(data);
                }
            })
        }

        $(document).on('click', '.pagination a', function(event){
            event.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            $('#hidden_page').val(page);

            $('li').removeClass('active');
            $(this).parent().addClass('active');
            fetch_data(page);
        });
    });
</script>
@stop