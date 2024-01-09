@extends('admin.layout.template')

@section('content')

    <section class="panel">
        <header class="panel-heading">
            Category
        </header>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            <a href="#" data-toggle="modal" data-target="#newCategory" class="btn btn-blue">New Category</a>
                            <a href="{{url('admin/posts')}}" class="btn btn-blue">Back</a>
                            <br><br>
                            <table class="table default-table dataTable">
                                <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Icon</td>
                                    <td>Name</td>
                                    <td>Slug</td>
                                    <td>Parent</td>
                                    <td>Type</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>
                                @php $no=1;

                                @endphp
                                @foreach($category as $s)
                                    <tr>
                                        <td>
                                            {{$no}}
                                        </td>
                                        <td>
                                        @if($s->icon)
                                        <div style="width: 100%; text-align: center">
                                        <img src="{{asset($s->icon)}}" class="img-fluid" style="max-width: 100px; max-height: 100px; object-fit: cover; margin: auto">
                                        </div>
                                        <br>
                                        @endif
                                        </td>
                                        <td>{{$s->name}}</td>
                                        <td>{{$s->slug}}</td>
                                        <td>{{$s->category_parent}}</td>
                                        <td>{{$s->type}}</td>
                                        <td>@if($s->status==1) <span class="status status-success">Active</span> @else <span class="status status-warning">Inactive</span> @endif</td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-green btn-sm btn-edit-category" data-toggle="modal" data-target="#editCategory"
                                               data-id="{{$s->id}}"
                                               data-name="{{$s->name}}"
                                               data-parent="{{$s->category_parent}}"
                                               data-status="{{$s->status}}"
                                               data-update="{{ route('admin.post-categories.update',$s->id) }}"
                                               data-icon="{{asset($s->icon)}}">Edit</a>
                                            <form action="{{ url('admin/post-categories/'.$s->id) }}" method="post" style="display: inline-block;" onsubmit="return confirm('Do you really delete Data?');">
                                                @method('delete')
                                                @csrf
                                                <input type="submit" class="btn btn-red btn-sm" value="Delete" onclick="return confirm('Delete Data?')"/>
                                            </form>
                                        </td>
                                    </tr>
                                    @php $no=$no+1;@endphp
                                @endforeach
                                </tbody>
                            </table>

                            {{$category->appends($_GET)->links()}}
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <div id="newCategory" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Category</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url('admin/post-categories')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Parent ID *kosongkan jika tidak ada</label>
                            <div class="col-sm-12">
                                @csrf

                                @php
                                    $parent = $category;
                                @endphp
                                <select name="category_parent" class="form-control">
                                    <option value="0">- Select Parent -</option>
                                    @foreach($parent as $serv)
                                        <option value="{{$serv->id}}">{{$serv->name}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control" placeholder="Name" value="" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Image </label>
                            <div class="col-sm-12">
                            <input type="file" class="file" name="icon" value="">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Status </label>
                            <div class="col-sm-12">
                            <select name="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inaction</option>
                            </select>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save Category</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div id="editCategory" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Category</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" action="#" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Parent ID *kosongkan jika tidak ada</label>
                            <div class="col-sm-12">
                                @method('put')
                                @csrf

                                @php
                                    $parent = $category;
                                @endphp
                                <select id="edit-parent" name="category_parent" class="form-control">
                                    <option value="0">- Select Parent -</option>
                                    @foreach($parent as $serv)
                                        <option value="{{$serv->id}}">{{$serv->name}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                <input type="hidden" name="id" id="edit-id"  value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" id="edit-name" name="name" class="form-control" placeholder="Name" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Image </label>
                            <div class="col-sm-12">
                            <input type="file" id="edit-icon" class="file" name="icon" value="">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Status </label>
                            <div class="col-sm-12">
                            <select name="status" id="edit-status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inaction</option>
                            </select>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Update Category</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>



    <script>
        $('.btn-edit-category').on('click', function(){
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var parent = $(this).attr('data-parent');
            var icon = $(this).attr('data-icon');
            var url = $(this).data('update');
            var status = $(this).data('status');

            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-parent').val(parent);
            $('#edit-status').val(status);
            $('#updateForm').attr('action', url);

            if(parent==0)
                $('#edit-icon').attr('src', icon);
        })
    </script>

    @if(!empty(Session::get('status')) && Session::get('status') == 1)
        <script>
            showNotif("{{Session::get('message')}}");
        </script>
    @endif

@stop
