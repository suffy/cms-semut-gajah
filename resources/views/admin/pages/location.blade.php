@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Options
    </header>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12  ">
                <div class="table-outer">
                    <div class="table-inner">
                        <a href="#" data-toggle="modal" data-target="#newOption" class="btn btn-blue">Tambah Option</a><br><br>
                        <table class="table default-table">
                            <thead>
                            <tr align="center">
                                <th scope="col">No</th>
                                <th scope="col">Name</th>
                                <th scope="col">Longitude</th>
                                <th scope="col">Latitude</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($location as $key => $row)
                                <tr>
                                  <td>{{ $location->firstItem() + $key }}</td>
                                  <td>{{$row->name}}</td>
                                  <td>{{$row->longitude}}</td>
                                  <td>{{$row->latitude}}</td>
                                  <td>@if($row->status==1) active @else non aktif @endif</td>
                                  <td>

                                    <a href="javascript:void(0)" class="btn btn-green btn-sm button-edit-locations"
                                    data-id                ="{{ $row->id }}"
                                    data-longitude       ="{{ $row->longitude }}"
                                    data-ordering              ="{{ $row->data }}"
                                    data-name       ="{{ $row->name }}"
                                    data-latitude      ="{{ $row->latitude }}"
                                    data-parent_id          ="{{ $row->parent_id }}"
                                    data-icon              ="{{ $row->icon }}"
                                    data-status            ="{{ $row->status }}"
                                    data-update            ="{{ url('admin/locations',$row->id) }}"
                                    data-toggle            ="modal"
                                    data-target            ="#editOption"
                                    style                  ="display: inline-block;">
                                   Detail Edit
                                  </a>

                                  @if ( $row->deletable == 1)
                                    <form action="{{url('admin/locations/'.$row->id)}}" method="POST" style="display: inline-block;">
                                        @method('delete')
                                        @csrf
                                        <button type="submit" class="btn btn-red btn-sm" value="Delete" onclick="return confirm('Delete Data?')">
                                       Delete
                                      </button>
                                    </form>
                                  @endif
                                  </td>
                                </tr>
                              @endforeach
                            </tbody>
                        </table>
                        <div class="text-center">
                            {{ $location->links() }}
                          </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal New -->
<div class="modal fade" id="newOption" tabindex="-1" role="dialog" aria-labelledby="option" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="title">Tambah</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{url('admin/locations')}}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Name</label>
            <div class="col-sm-12">
              <input type="text" name="name" id="" class="form-control" required>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Latitude</label>
            <div class="col-sm-12">
              <input type="text" name="latitude" id="" class="form-control">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-sm-12 col-form-label">Longitude</label>
            <div class="col-sm-12">
              <input type="text" name="longitude" id="" class="form-control">
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
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="title">Edit Option</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       <form id="updateLocation" action="#" method="post" enctype="multipart/form-data">
        @method('put')
        @csrf
        <input type="hidden" name="url" value="{{Request::url()}}" required>
        <input type="hidden" id="input-id-edit" name="id" value="" >
        <div class="form-group row">
            <label class="col-sm-12 col-form-label">Name</label>
            <div class="col-sm-12">
              <input type="text" id="input-name-edit" name="name" class="form-control">
            </div>
          </div>
        
        <div class="form-group row">
            <label class="col-sm-12 col-form-label">Status</label>
            <div class="col-sm-12">
              <select id="input-status-edit" name="status" class="form-control">
                  <option value="1">Active</option>
                  <option value="0">Non Active</option>
              </select>
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
    $('.button-edit-locations').on('click', function (e) {
        var url              = $(this).data('update');
        var id               = $(this).attr('data-id');
        var name      = $(this).attr('data-name');
        var status           = $(this).attr('data-status');
       
        $('#updateLocation').attr('action', url);
        $('#input-id-edit').val(id);
        $('#input-name-edit').val(name);
        $('#input-status-edit').val(status);
    });
</script>


  @if(!empty(Session::get('status')) && Session::get('status') == 1)
  <script>
    showNotif("{{Session::get('message')}}");
  </script>
  @endif

  @if(!empty(Session::get('status')) && Session::get('status') == 2)
  <script>
    showAlert("{{Session::get('message')}}");
  </script>
  @endif
  @stop
