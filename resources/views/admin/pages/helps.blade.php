@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
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
                                        <!-- <td>Icon</td> -->
                                        <td>Name</td>
                                        <td>Description</td>
                                        <td>Category</td>
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no=0;
                                    @endphp

                                    @foreach($helps as $help)
                                    <tr>
                                        <td width="10px">{{$helps->firstItem()+$no++}}</td>
                                        <td style="display:none;">{{ $loop->iteration }}</td>
                                        <!-- <td width="60px">
                                            @if ($help->icon == null)
                                                <div>
                                                    <a class="example-image-link" href="{{asset('images/no-images.png')}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset('images/no-images.png')}}" alt="" style="width: 50px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px 2px #838383;"/>
                                                    </a>
                                                </div>
                                            @else
                                                <div>
                                                    <a class="example-image-link" href="{{asset($help->icon)}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset($help->icon)}}" alt="" style="width: 50px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px 2px #838383;"/>
                                                    </a>
                                                </div>
                                            @endif
                                        </td> -->
                                        <td class="align-middle"><b>{{$help->name}}</b></td>
                                        <td>
                                            {!! $help->description !!}
                                        </td>
                                        <td>{{ $help->help_category->name }}</td>
                                        <td class="text-center align-middle" width="60px">
                                            <a href="javascript:void(0)" 
                                                class="btn btn-green btn-sm btn-edit-help" data-toggle="modal" 
                                                data-target="#editCategory"
                                                data-id="{{$help->id}}"
                                                data-name="{{$help->name}}"
                                                data-status="{{$help->status}}"
                                                data-description="{{$help->description}}"
                                                data-help-category-id="{{$help->help_category_id}}"
                                                data-action="{{url($account_role . '/helps/').'/'.$help->id}}"
                                                data-icon="{{asset($help->icon)}}">Edit</a>
                                            <form method="post" enctype="multipart/form-data" action="{{url($account_role . '/helps/'.$help->id)}}">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="id" value="{{$help->id}}">
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
                            {{ $helps->appends(Request::all())->links() }}
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
                    <form action="{{url('manager/helps')}}" method="post" enctype="multipart/form-data">
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
                                <textarea name="description" id="mytextarea"></textarea>
                            </div>
                        </div>
                        <!-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <input type="file" name="icon" value="">
                            </div>
                        </div> -->
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Category</label>
                            <div class="col-sm-12">
                                <select class="form-control" name="category" id="category">
                                    @foreach ($helpCategories as $helpCategory)
                                        <option value="{{ $helpCategory->id }}">{{ $helpCategory->name }}</option>
                                    @endforeach
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
                    <h4 class="modal-name">Edit Category</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="edit-category" action="{{url('manager/helps/')}}" method="post" enctype="multipart/form-data">
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
                        <!-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <img class="img img-responsive" id="edit-icon" src="{{asset('images/no-images.png')}}" width="100px"><br><br>
                                <input type="file" name="icon" value="">
                            </div>
                        </div> -->
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Category</label>
                            <div class="col-sm-12">
                                <select class="form-control" name="category" id="edit-category">
                                    @foreach ($helpCategories as $helpCategory)
                                        <option value="{{ $helpCategory->id }}">{{ $helpCategory->name }}</option>
                                    @endforeach
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
        $('.btn-edit-help').on('click', function(){
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var description = $(this).attr('data-description');
            // var icon = $(this).attr('data-icon');
            var help_category_id = $(this).attr('data-help-category-id');
            var action = $(this).attr('data-action');
            var status = $(this).attr('data-status');

            $('#edit-id').val(id);
            $('#edit-name').val(name);
            $('#edit-description').val(description);
            // $('#edit-icon').attr('src', icon);
            $('#edit-category').attr('action', action);
            tinyMCE.get('edit-description').setContent(description);
            $("option[value="+help_category_id+"]").attr('selected', true);
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
            selector: '#edit-description',
            plugins: 'image code',
            toolbar: 'undo redo | link image | code',
            /* enable title field in the Image dialog*/
            image_title: true,
            /* enable automatic uploads of images represented by blob or data URIs*/
            automatic_uploads: true,
            /*
                here we add custom filepicker only to Image dialog
            */
            file_picker_types: 'image',
            /* and here's our custom image picker*/
            file_picker_callback: function (cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function () {
                var file = this.files[0];

                var reader = new FileReader();
                reader.onload = function () {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);

                    /* call the callback and populate the Title field with the file name */
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
                };

                input.click();
            },
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
            });
    </script>

    @stop
