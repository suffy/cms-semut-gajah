@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Testimonial For Landing Page
    </header>
    <div class="card-body">
        <div class="content-body">
            <div class="row">
                <div class="col-md-6">
                    {{-- <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newTesti" ><span class="fa fa-plus"></span> Create New</a> --}}
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
                                        <td>Name</td>
                                        <td>Description</td>
                                        <td>Shop Name</td>
                                        <td>City</td>
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($testimonials))
                                        @include('admin.pages.pagination_data_testi')
                                    @else
                                        <tr>
                                            <td colspan="6" align="center"> Data Kosong !! </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- MODAL START --}}
    {{-- <div id="newTesti" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">New Testimonials</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="insert" action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control" placeholder="Person Name" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                <input type="text" name="description" class="form-control" placeholder="Description" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Shop Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="shop_name" class="form-control" placeholder="Shop Name" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">City</label>
                            <div class="col-sm-12">
                                <input type="text" name="city" class="form-control" placeholder="City" value="" required>
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

    <div id="editTesti" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-name">Edit Mapping Site</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="update" action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input id="edit-name" type="text" name="name" class="form-control" placeholder="Person Name" required>
                                <input id="edit-id" type="hidden" name="id"  value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Description</label>
                            <div class="col-sm-12">
                                <input type="text" id="edit-desc" name="description" class="form-control" placeholder="Description" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Shop Name</label>
                            <div class="col-sm-12">
                                <input type="text" id="edit-shop" name="shop_name" class="form-control" placeholder="Shop Name" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">City</label>
                            <div class="col-sm-12">
                                <input type="text" id="edit-city" name="city" class="form-control" placeholder="City" value="" required>
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
    </div> --}}
    {{-- MODAL END --}}

    <script>
        $(document).ready(function() {

            $(document).on('click', '.btn-delete-testi', function () {
                var id          = $(this).attr('data-id');
                // var token       = $(this).attr('data-token');
                var token       = "{{csrf_token()}}";
                $.ajax({ 
                    url: "/manager/testimonials/delete/" + id,
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

            $(document).on('click', '.btn-accept-testi', function () {
                var id          = $(this).attr('data-id');
                // var token       = $(this).attr('data-token');
                var token       = "{{csrf_token()}}";
                $.ajax({ 
                    url: "/manager/testimonials/accept/" + id,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                                "_token": token
                            },
                    success: function(response) { 
                        fetch_data(1);
                        // $('#editTesti').modal('hide');
                        showNotif("Acc data sukses")
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr);
                        console.log(status);
                        console.log(error);
                    }
                });
            });

            $(document).on('click', '.btn-edit-testi', function () {
            // $('.btn-edit-testi').on('click', function(){
                var id          = $(this).attr('data-id');
                var name        = $(this).attr('data-name');
                var desc        = $(this).attr('data-desc');
                var shop_name   = $(this).attr('data-shop-name');
                var city        = $(this).attr('data-city');
                var action      = $(this).attr('data-action');

                $('#edit-id').val(id);
                $('#edit-name').val(name);
                $('#edit-desc').val(desc);
                $('#edit-shop').val(shop_name);
                $('#edit-city').val(city);
                // $('#edit-testi').attr('action', action);
            })

            fetch_data(1);  

            $('#update').on('submit', function(e) {
                e.preventDefault();
                var fd  = new FormData(this);
                var id = $('#edit-id').val();
                $.ajax({ 
                    url: "/manager/testimonials/update/" + id,
                    type: 'POST',
                    data: fd,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) { 
                        fetch_data(1);
                        $('#editTesti').modal('hide');
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

                $.ajax({ 
                    url: "{{url('/manager/testimonials/store')}}",
                    type: 'POST',
                    data: fd,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) { 
                        // fetchData(endpoint, page);
                        fetch_data(1);
                        $('#newTesti').modal('hide');
                        showNotif("Simpan data sukses")
                    }
                });
            });

            function fetch_data(page) {
                $.ajax({
                    url:@if(auth()->user()->account_role == 'manager')
                            "{{url('/manager/testimonials/fetch_data')}}?page="+page
                        @else 
                            "{{url('/superadmin/testimonials/fetch_data')}}?page="+page
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

                var query = $('#search').val();

                $('li').removeClass('active');
                $(this).parent().addClass('active');
                fetch_data(page);
            });
        });
    </script>

    @stop
