@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Blog For Landing Page
    </header>
    <div class="card-body">
        <div class="content-body">
            <div class="row">
                <div class="col-md-6">
                    <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newBlog" ><span class="fa fa-plus"></span> Create New</a>
                </div>
                <div class="col-md-6">
                    {{-- <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search...">
                        <button class="btn btn-secondary" type="button">
                            <i class="fa fa-search"></i>
                        </button>
                    </div> --}}
                </div>
            </div>
        <br>
        <br>
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            <table class="table default-table">
                                <thead>
                                    <tr align="center">
                                        <td>No</td>
                                        <td>Image</td>
                                        <td>Title</td>
                                        <td>Description</td>
                                        <td>Status Highlight</td>
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- pagination_data_blog --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- MODAL START --}}
    <div id="newBlog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">New Blogs</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="insert" action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Title</label>
                            <div class="col-sm-12">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                {{-- <input type="text" name="description" id="description" class="form-control" required> --}}
                                <textarea class="form-control" name="description" id="description" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Image</label>
                            <div class="col-sm-12">
                                <img class="img img-responsive" id="frame" src="{{asset('images/no-images.png')}}" width="100px"><br><br>
                                <input type="file" id="image" name="image" value="">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div id="editBlog" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Edit Blogs</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="update" action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Title</label>
                            <div class="col-sm-12">
                                <input type="hidden" id="edit-id" class="form-control">
                                <input type="text" name="title" id="edit-title" class="form-control" placeholder="Title" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                {{-- <input type="text" name="description" id="edit-description" class="form-control" required> --}}
                                <textarea class="form-control" name="description" id="edit-desc" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Image</label>
                            <div class="col-sm-12">
                                <img class="img img-responsive" id="edit-frame" src="{{asset('images/no-images.png')}}" width="100px"><br><br>
                                <input type="file" id="edit-image" name="image" value="">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Update</button> &nbsp &nbsp
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
        $(document).ready(function() {

            // tinymce.init({
            //     selector: '#mytextarea'
            // });

            // tinymce.init({
            //     selector: '#description'
            // });

            // tinymce.init({
            //     selector: '#edit-description'
            // });

            function readURL(input, previewId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        // $(previewId).css('src', e.target.result );
                        $(previewId).attr('src', e.target.result);
                        $(previewId).hide();
                        $(previewId).fadeIn(850);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#image").change(function() {
                readURL(this, '#frame');
            });  

            $("#edit-image").change(function() {
                readURL(this, '#edit-frame');
            });  

            $('#newBlog').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
            })

            $('#editBlog').on('hidden.bs.modal', function () {
                $(this).find('form').trigger('reset');
            })

            // edit
            $(document).on('click', '.btn-edit-blog', function () {
                var id          = $(this).attr('data-id');
                var title       = $(this).attr('data-title');
                var desc        = $(this).attr('data-desc');
                var image       = $(this).attr('data-image');
                $('#edit-frame').attr('src', '/' + image);
                $('#edit-title').val(title);
                $('#edit-id').val(id);
                $('#edit-desc').val(desc);
                // tinyMCE.get('edit-description').setContent(desc);
            });

            $(document).on('click', '.btn-delete-blog', function () {
                var id          = $(this).attr('data-id');
                // var token       = $(this).attr('data-token');
                var token       = "{{csrf_token()}}";
                $.ajax({ 
                    url: "/manager/blogs/delete/" + id,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                                "_token": token
                            },
                    success: function(response) { 
                        fetch_data(1);
                        // $('#editTesti').modal('hide');
                        showNotif("Hapus data sukses")
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr);
                        console.log(status);
                        console.log(error);
                    }
                });
            });

            // $('.toggle-class').change(function(){
            $(document).on('change', '.toggle-class', function () {
                var status  = $(this).prop('checked') == true ? 1 : 0;
                var id      = $(this).data('id');

                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: '/manager/blogs/status/'+id,
                    data: {'status': status, 'category_id': id},
                    success: function(data){
                        console.log(data)
                        showNotif("Perubahan status sukses")
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        console.log(error);
                        showAlert(thrownError);
                    }
                });
            })

            fetch_data(1);  

            $('#update').on('submit', function(e) {
                e.preventDefault();
                var fd  = new FormData(this);
                var id = $('#edit-id').val();
                var image       = $('#image')[0].files;
                var token       = "{{csrf_token()}}";
                fd.append('_token', token);
                fd.append('image', image);

                $.ajax({ 
                    url: "/manager/blogs/update/" + id,
                    type: 'POST',
                    data: fd,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) { 
                        fetch_data(1);
                        $('#editBlog').modal('hide');
                        showNotif("Simpan data sukses")
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr);
                        console.log(status);
                        console.log(error);
                    }
                });
            });

            // save
            $('#insert').on('submit', function(e) {
                e.preventDefault();
                var fd          = new FormData(this);
                var image       = $('#image')[0].files;
                var token       = "{{csrf_token()}}";
                fd.append('_token', token);
                fd.append('image', image);

                $.ajax({ 
                    url: "{{url('/manager/blogs/store')}}",
                    type: 'POST',
                    data: fd,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) { 
                        // fetchData(endpoint, page);
                        fetch_data(1);
                        $('#newBlog').modal('hide');
                        showNotif("Simpan data sukses")
                    }
                });
            });

            function fetch_data(page) {
                $.ajax({
                    url:@if(auth()->user()->account_role == 'manager')
                            "{{url('/manager/blogs/fetch_data')}}?page="+page
                        @else 
                            "{{url('/superadmin/blogs/fetch_data')}}?page="+page
                        @endif,
                    success: function(data) {
                        console.log(data);
                        $('tbody').html('');
                        $('tbody').html(data);
                    }
                })
            }

            $(document).on('click', '.pagination a', function(event){
                event.preventDefault();
                var page = $(this).attr('href').split('page=')[1];
                $('#hidden_page').val(page);

                var query = $('#search').val();

                $('li').removeClass('active');
                $(this).parent().addClass('active');
                fetch_data(page);
            });
        });
    </script>

    @stop
