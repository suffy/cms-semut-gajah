@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Menu
    </header>
    <div class="card-body">
        <div class="content-body">
        <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newMenu" ><span class="fa fa-plus"></span> Create New</a>
        <br>
        <br>
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            <table class="table default-table dataTable">
                                <thead>
                                    <tr align="center">
                                        <td width="70px">No</td>
                                        <td>Name ID</td>
                                        <td>Name En</td>
                                        <td>Status</td>
                                        <td width="150px">Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no=1;
                                    @endphp

                                    @foreach($menu as $row)
                                    <tr class="text-center">
                                        <td>{{$no++}}</td>
                                            <td><b>{{$row->name}}</b></td>
                                            <td><b>{{$row->name_en}}</b></td>
                                            <td>@if($row->status==1) <span class="status status-success">Active</span> @else <span class="status status-warning">Inactive</span> @endif</td>

                                            <td>

                                                <form action="{{ url('admin/menu-status/'.$row->id) }}" method="POST" onsubmit="return confirm('Confirmation?');">
                                                    @method('post')
                                                    @csrf
                                                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                                        @if($row->status==1)
                                                        <input type="hidden" value="0" name="status">
                                                        <a href="#" class="btn btn-sm btn-neutral" onclick="$(this).closest('form').submit()">Deactivate</a>
                                                        @else
                                                        <input type="hidden" value="1" name="status">
                                                        <a href="#" class="btn btn-sm btn-green" onclick="$(this).closest('form').submit()">Activate</a>
                                                        @endif
                                                    </form>

                                                @if($row->editable=='1')
                                                <a href="javascript:void(0)" class="btn btn-green btn-xs btn-edit-menu" data-toggle="modal" data-target="#editMenu"
                                                data-id="{{$row->id}}"
                                                data-name="{{$row->name}}"
                                                data-status="{{$row->status}}"
                                                data-action="{{url('admin/menus').'/'.$row->id}}"><i class="fa fa-edit"></i></a>
                                                @endif
                                                @if($row->deletable=='1')
                                                <form action="{{ url('admin/menus/'.$row->id) }}" method="post" style="display: inline-block;" onsubmit="return confirm('Do you really delete Data?');">
                                                    @method('delete')
                                                    @csrf
                                                    <button type="submit" class="btn btn-red btn-xs" value="Delete" onclick="return confirm('Delete Data?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>


    


    <div id="newMenu" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">New Menu</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url('admin/menus')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Menu Name</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save Menu</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>


    <div id="editMenu" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Edit Menu</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="edit-menu" action="{{url('admin/menus/')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Menu Name</label>
                            <div class="col-sm-12">
                                @method('put')
                                @csrf
                                <input id="edit-name" type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                <input id="edit-id" type="hidden" name="id"  value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Status</label>
                            <div class="col-sm-12">
                                <label><input type="radio" name="status" value="1"> Aktif </label> &nbsp
                                <label><input type="radio" name="status" value="0"> Non Aktif </label>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Update Menu</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        $(function(){
          $('.toggle-class').change(function(){
              var status = $(this).prop('checked') == true ? 1 : 0;
              var menu_id = $(this).data('id');

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/admin/change-status-menu',
                  data: {'status': status, 'menu_id': menu_id},
                  success: function(data){
                    console.log(data.success)
                  }
              });
            })
        })
</script>

    <script>
        $('.btn-edit-menu').on('click', function(){
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var action = $(this).attr('data-action');
            var status = $(this).attr('data-status');

            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-menu').attr('action', action);
            $("input[name=status][value="+status+"]").attr('checked', true);

        })
    </script>


    @if(!empty(Session::get('status')) && Session::get('status') == 1)
    <script>
        showNotif("{{Session::get('message')}}");
    </script>
    @endif

    @stop
