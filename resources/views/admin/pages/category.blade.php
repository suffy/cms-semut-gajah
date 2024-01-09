@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<section class="panel">
    <header class="panel-heading">
        Category
    </header>
    <div class="card-body">
        <div class="content-body">
        <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newCategory" ><span class="fa fa-plus"></span> Create New</a>
        <br>
        <br>
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            <table class="table default-table dataTable">
                                <thead>
                                    <tr align="center">
                                        <td>No</td>
                                        <td>Icon</td>
                                        <td>Name</td>
                                        <td>Description</td>
                                        <td>Status</td>
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no=0;
                                    @endphp

                                    @foreach($category as $doc)
                                    <tr>
                                        <td width="10px">{{$category->firstItem()+$no++}}</td>
                                        <td width="60px">
                                            @if ($doc->icon == null)
                                                <div>
                                                    <a class="example-image-link" href="{{asset('images/no-images.png')}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset('images/no-images.png')}}" alt="" style="width: 50px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px 2px #838383;"/>
                                                    </a>
                                                </div>
                                            @else
                                                <div>
                                                    <a class="example-image-link" href="{{asset($doc->icon)}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset($doc->icon)}}" alt="" style="width: 50px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px 2px #838383;"/>
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle"><b>{{$doc->name}}</b></td>
                                        <td>
                                            {!! $doc->description !!}
                                        </td>
                                        <td width="40px">
                                            
                                                <label class="switch">
                                                    <input data-id="{{$doc->id}}" data-user_role="{{$account_role}}" class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $doc->status ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                              </label>
                                        </td>

                                        <td class="text-center align-middle" width="60px">
                                            <a href="javascript:void(0)" 
                                                class="btn btn-green btn-sm btn-edit-category" data-toggle="modal" 
                                                data-target="#editCategory"
                                                data-id="{{$doc->id}}"
                                                data-name="{{$doc->name}}"
                                                data-status="{{$doc->status}}"
                                                data-description="{{$doc->description}}"
                                                data-action="{{url($account_role . '/categories/').'/'.$doc->id}}"
                                                data-icon="{{asset($doc->icon)}}">Edit</a>
                                            <form method="post" enctype="multipart/form-data" action="{{url('admin/categories/'.$doc->id)}}">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="id" value="{{$doc->id}}">
                                                <input type="hidden" name="url" value="{{Request::url()}}">
                                                <input type="hidden" name="status" value="1">
                                                <button type="submit" class="btn btn-sm btn-red" onclick="return confirm('Are you sure?')">Hapus</button>
                                            </form>
                                           
                                        </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                            </div>
                            {{ $category->appends(Request::all())->links() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
</section>
    {{-- MODAL START --}}
    <div id="newCategory" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">New Category</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url($account_role . '/categories')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Category Name</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                <textarea class="form-control" name="description" style="height: 300px;"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <input type="file" name="icon" value="">
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
                    <h4 class="modal-name">Edit Category</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url($account_role . '/categories/')}}" id="edit-category"  method="post" enctype="multipart/form-data">
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
    {{-- MODAL END --}}

    <script>
        $(function(){
          $('.toggle-class').change(function(){
              var status = $(this).prop('checked') == true ? 1 : 0;
              var category_id = $(this).data('id');
              var role = $(this).data('user_role');

              console.log(role);

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/'+ role +'/category-status/'+category_id,
                  data: {'status': status, 'category_id': category_id},
                  success: function(data){
                    console.log(data)
                    showNotif("Perubahan status sukses")
                  },
                  error: function (xhr, ajaxOptions, thrownError) {
                    showAlert(thrownError);
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

    @stop
