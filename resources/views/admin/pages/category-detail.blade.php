@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Category
    </header>
    
    <div class="card-body">
        <div class="content-body">
            <div class="row">
                <div class="col-md-2">
                    <img src="{{asset($category->icon)}}" class="img-fluid">
                </div>
                <div class="col-md-10">
                    <h3>{{$category->name}}</h3>
                    <p>{{$category->description}}</p>
                    <p>@if($category->status==1) <span class="status status-success">Active</span> @else <span class="status status-warning">Inactive</span> @endif</p>
                    <a href="javascript:void(0)" class="btn btn-green btn-sm btn-edit-category" data-toggle="modal" data-target="#editCategory"
                    data-id="{{$category->id}}"
                    data-name="{{$category->name}}"
                    data-status="{{$category->status}}"
                    data-description="{{$category->description}}"
                    data-action="{{url('admin/categories/').'/'.$category->id}}"
                    data-icon="{{asset($category->icon)}}">Edit</a>    
                    <a href="{{url('category/'.$category->slug)}}" class="btn btn-blue btn-sm" target="_blank">Preview</a>         
                </div>
            </div>
        </div>
    </div>
</section>



<section class="panel">
    <header class="panel-heading">
        Feature
    </header>
    <div class="card-body">
        <div class="content-body">
        <div class="table-outer">
                    <div class="table-inner">
                        <a href="{{url('admin/features/create')}}" class="btn btn-blue" onclick="return toggleFeature()">New Feature</a> &nbsp
                        <br><br>
                        <table class="table default-table dataTable">
                            <thead>
                                <tr>
                                    <td>Icon</td>
                                    <td>Name</td>
                                    <td>Category</td>
                                    <td>Content</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                $features = $category->feature;
                                @endphp

                                @foreach($features as $no => $row)

                                <tr>
                                    <td>
                                    @if($row->icon!==null)
                                        <img src="{{asset($row->icon)}}" class="img-fluid" style="max-width: 70px">
                                    @endif
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->category->name}}</td>
                                    <td>{!! $row->content !!}</td>
                                    <td>

                                    <button type="button" class="btn btn-blue btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                    <div class="dropdown-menu">
                                    <a href="{{url('admin/features/'.$row->id)}}" class="dropdown-item btn-edit-features">Show</a>
                                    <a href="{{url('admin/features/'.$row->id.'/edit')}}" class="dropdown-item btn-edit-features">Edit</a>
                                    <form action="{{ url('admin/features/'.$row->id) }}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
                                        @method('delete')
                                        @csrf
                                        <a href="javascript:void(0)" class="dropdown-item " onclick="$(this).closest('form').submit()">Delete</a>
                                    </form>
                                    </div>
                     
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
        </div>
    </div>
</section>


    <div id="editCategory" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Edit Category</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="edit-category" action="{{url('admin/categories/')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Category Name</label>
                            <div class="col-sm-12">
                                @method('put')
                                @csrf
                                <input id="edit-name" type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                <input id="edit-id" type="hidden" name="id"  value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                <textarea class="form-control" id="edit-description" name="description" style="height: 300px;"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <img class="img img-responsive" id="edit-icon" src="{{asset('images/no-images.png')}}" width="100px"><br><br>
                                <input type="file" name="icon" value="">
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
        $(function(){
          $('.toggle-class').change(function(){
              var status = $(this).prop('checked') == true ? 1 : 0;
              var category_id = $(this).data('id');

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/admin/change-status-category',
                  data: {'status': status, 'category_id': category_id},
                  success: function(data){
                    console.log(data.success)
                  }
              });
            })
        })
</script>

    <script>
        $('.btn-edit-category').on('click', function(){
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var description = $(this).attr('data-description');
            var icon = $(this).attr('data-icon');
            var action = $(this).attr('data-action');
            var status = $(this).attr('data-status');

            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-description').val(description);
            $('#edit-icon').attr('src', icon);
            $('#edit-category').attr('action', action);
            $("input[name=status][value="+status+"]").attr('checked', true);


        })
    </script>


    @if(!empty(Session::get('status')) && Session::get('status') == 1)
    <script>
        showNotif("{{Session::get('message')}}");
    </script>
    @endif

    @stop
