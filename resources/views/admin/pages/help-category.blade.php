@extends('admin.layout.template')

@section('content')

@php
    $account_role = Auth()->user()->account_role;
@endphp

<section class="panel">
    <header class="panel-heading">
        Help Category
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
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no=0;
                                    @endphp

                                    @foreach($helpCategories as $helpCategory)
                                    <tr>
                                        <td width="10px">{{$helpCategories->firstItem()+$no++}}</td>
                                        <td width="60px">
                                            @if ($helpCategory->icon == null)
                                                <div>
                                                    <a class="example-image-link" href="{{asset('images/no-images.png')}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset('images/no-images.png')}}" alt="" style="width: 50px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px 2px #838383;"/>
                                                    </a>
                                                </div>
                                            @else
                                                <div>
                                                    <a class="example-image-link" href="{{asset($helpCategory->icon)}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset($helpCategory->icon)}}" alt="" style="width: 50px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px 2px #838383;"/>
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle"><b>{{$helpCategory->name}}</b></td>
                                        <td>
                                            {!! $helpCategory->description !!}
                                        </td>
                                        <td class="text-center align-middle" width="60px">
                                            <a href="javascript:void(0)" 
                                                class="btn btn-green btn-sm btn-edit-category" data-toggle="modal" 
                                                data-target="#editCategory"
                                                data-id="{{$helpCategory->id}}"
                                                data-name="{{$helpCategory->name}}"
                                                data-status="{{$helpCategory->status}}"
                                                data-description="{{$helpCategory->description}}"
                                                data-action="{{url($account_role . '/help-categories/').'/'.$helpCategory->id}}"
                                                data-icon="{{asset($helpCategory->icon)}}">Edit</a>
                                            <form method="post" enctype="multipart/form-data" action="{{url($account_role . '/help-categories/'.$helpCategory->id)}}">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="id" value="{{$helpCategory->id}}">
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
                            {{ $helpCategories->appends(Request::all())->links() }}
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
                    <form action="{{url($account_role . '/help-categories')}}" method="post" enctype="multipart/form-data">
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
                                <textarea name="description" id="description"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <input type="file" name="icon" value="" required>
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
                    <form id="edit-category" action="{{url($account_role . '/help-categories/')}}" method="post" enctype="multipart/form-data">
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
                                <textarea name="description" id="edit-description"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <img class="img img-responsive" id="edit-icon" src="{{asset('images/no-images.png')}}" width="100px"><br><br>
                                <input type="file" name="icon" value="">
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

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/manager/category-status/'+category_id,
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
            tinymce.get('edit-description').setContent(description);
            $('#edit-icon').attr('src', icon);
            $('#edit-category').attr('action', action);
            $("input[name=status][value="+status+"]").attr('checked', true);
        })
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.0.6/jquery.tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#mytextarea'
        });

        tinymce.init({
            selector: '#description'
        });

        tinymce.init({
            selector: '#edit-description'
        });
    </script>

    @stop
