@extends('admin.layout.template')

@section('content')

<section class="panel">
    <header class="panel-heading">
        Sales
    </header>
    <div class="card-body">
        <div class="content-body">
        <div class="row">
            <div class="col-md-6">
                {{-- <a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#newCategory" ><span class="fa fa-plus"></span> Create New</a> --}}
            </div>
            <div class="col-md-6">
                <form method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search...">
                        <button class="btn btn-secondary" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <br>
        <br>
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer table-responsive">
                        <div class="table-inner">
                            <table class="table default-table dataTable">
                                <thead>
                                    <tr align="center">
                                        <td>Sales Code</td>
                                        <td>Sales Code ERP</td>
                                        <td>Name</td>
                                        <td>Site Code</td>
                                        {{-- <td>Action</td> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesmen as $salesman)
                                    <tr>
                                        <td>{{ $salesman->salesman->kodesales }}</td>
                                        <td>{{ $salesman->salesman->kodesales_erp }}</td>
                                        <td>{{ $salesman->salesman->namasales }}</td>
                                        <td>{{ $salesman->salesman->kode }}</td>
                                        {{-- <td class="text-center align-middle" width="60px">
                                            <a href="{{url('admin/categories/')}}" class="btn btn-blue btn-sm">Detail</a>
                                            <form method="post" enctype="multipart/form-data" action="{{url('admin/categories/')}}">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="id" value="">
                                                <input type="hidden" name="url" value="{{Request::url()}}">
                                                <input type="hidden" name="status" value="1">
                                                <button type="submit" class="btn btn-sm btn-red" onclick="return confirm('Are you sure?')">Hapus</button>
                                            </form>
                                            
                                        </td> --}}
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="4" align="center">{{ $salesmen->links() }}</td>
                                    </tr>
                                {{-- @include('admin.pages.pagination_data_salesman') --}}
                                </tbody>
                            </table>
                            <input type="hidden" name="hidden_page" id="hidden_page" value="1" />
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
                    <form action="{{url('admin/categories')}}" method="post" enctype="multipart/form-data">
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
    {{-- MODAL END --}}

    <script>
        // function fetch_data(page, query) {
        //     $.ajax({
        //         url:@if(auth()->user()->account_role == 'manager')
        //                 "{{url('manager/salesmen/fetch_data')}}?page="+page+"&search="+query
        //             @elseif(auth()->user()->account_role == 'distributor')
        //                 "{{url('distributor/salesmen/fetch_data')}}?page="+page+"&search="+query
        //             @else
        //                 "{{url('superadmin/salesmen/fetch_data')}}?page="+page+"&search="+query
        //             @endif,
        //         success: function(data) {
        //             $('tbody').html('');
        //             $('tbody').html(data);
        //         }
        //     })
        // }

        $('#search').on('keyup',function(){
            var query = $('#search').val();
            var page = $('#hidden_page').val();
            fetch_data(page, query);
        })

        // $(document).on('click', '.pagination a', function(event){
        //     event.preventDefault();
        //     var page = $(this).attr('href').split('page=')[1];
        //     $('#hidden_page').val(page);

        //     var query = $('#search').val();

        //     $('li').removeClass('active');
        //             $(this).parent().addClass('active');
        //     fetch_data(page, query);
        // });

        $(function(){
          $('.toggle-class').change(function(){
              var status = $(this).prop('checked') == true ? 1 : 0;
              var category_id = $(this).data('id');

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/admin/category-status/'+category_id,
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
