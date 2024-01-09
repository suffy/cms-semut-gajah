@extends('admin.layout.template')

@section('content')

    <section class="panel">
        <header class="panel-heading">
            Social Media
        </header>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">

                    <a href="#" data-toggle="modal" data-target="#newCity" class="btn btn-blue">New Social Media</a><br><br>

                    <div class="table-outer">
                        <div class="table-inner">

                            <table class="table default-table dataTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Icon</th>
                                        <th>Social Media Name</th>
                                        <th>URL</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($social_media as $row)
                                    <tr>
                                        <td width="50px" class="text-center">{{$loop->iteration}}</td>
                                        <td>
                                            <img src="{{asset($row->icon)}}" class="img-fluid" style="width: 50px">
                                        </td>
                                        <td> {{ $row->name }} </td>
                                        <td> {{ $row->url }} </td>

                                        <td>
                                        @if($row->status==1) <span class="status status-success">Active</span> @else <span class="status status-warning">Draft</span> @endif
                                        </td>
                                        <td width="150px">
                                            <a href="javascript:void(0)"
                                            class="btn btn-green btn-xs button-edit-social_media"
                                            data-id="{{ $row->id }}"
                                            data-name="{{ $row->name }}"
                                            data-url="{{ $row->url }}"
                                            data-update="{{ url('admin/social-medias/'.$row->id) }}"
                                            data-status="{{ $row->status }}"
                                            data-icon="{{asset($row->icon)}}"
                                            data-toggle="modal"
                                            data-target="#editCity"
                                            style="display: inline-block;"><i class="fa fa-edit"></i></a>
                                            <form action="{{ url('admin/social-medias/'.$row->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Do you really delete Data?');">
                                                @method('delete')
                                                @csrf
                                                <button type="submit" class="btn btn-red btn-xs" value="Delete" onclick="return confirm('Delete Data?')"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="pull-left">
                        {{ $social_media->appends($_GET)->links() }}
                    </div>
                </div>

            </div>


        </div>
    </section>


    <!-- Modal -->
    <div id="newCity" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Social Media</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url('admin/social-medias')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label"> Social Media Name</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="name" class="form-control" placeholder="Social Media Name" value="" required>
                                <input type="hidden" name="url_redirect"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">URL</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="link" class="form-control" placeholder="URL" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Status</label>
                            <div class="col-sm-12">
                                <label><input type="radio" name="status" value="1" checked> Active</label> &nbsp
                                <label><input type="radio" name="status" value="0"> Inactive</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <input type="file" class="file" name="icon" accept="image/*" onchange="tampilkanPreview(this,'tampilkanFoto')" required>
                                <p></p>
                                <center><img id="tampilkanFoto" src="" alt="" width="60%"></center>
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

    <div id="editCity" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Social Media</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="updateCity" action="#" method="post" enctype="multipart/form-data">
                        @method('put')
                        @csrf
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label"> Social Media Name</label>
                            <div class="col-sm-12">
                                <input type="hidden" id="input-id-edit" name="id" value="" required>
                                <input type="text" id="input-name-edit" name="name" class="form-control" placeholder="Social Media Name" value="" required>
                                <input type="hidden" name="url_redirect"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">URL</label>
                            <div class="col-sm-12">
                                <input type="text" id="input-url-edit" name="link" class="form-control" placeholder="Name" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Status</label>
                            <div class="col-sm-12">
                                <label><input type="radio" name="status" value="1" checked> Active</label> &nbsp
                                <label><input type="radio" name="status" value="0"> Inactive</label>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Icon</label>
                            <div class="col-sm-12">
                                <div>
                                    <img class="img img-fluid" id="edit-icon" src="{{asset('cms/img/no-images.png')}}" style="width:50px; margin: 10px auto">
                                </div>
                                <input type="file" class="file" name="icon" value="">
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
            <!-- /Modal content-->
        </div>
    </div>

    <style>
        .default-table pre{
            font-family: sans-serif;
            line-height: 18pt;
            background: #fff;
            border: 1px solid #f9f9f9;

        }

        .table-outer{
            overflow: auto;
        }

        .table-inner{
            width: 100%;
        }
    </style>


<script>
    $(function(){
    $('.toggle-class').change(function(){
        var status = $(this).prop('checked') == true ? 1 : 0;
        var social_media_id = $(this).data('id');

        $.ajax({
            type: "GET",
            dataType: "json",
            url: '/admin/change-status-social_media',
            data: {'status': status, 'social_media_id': social_media_id},
            success: function(data){
                console.log(data.success)
            }
        });
    })
})
</script>

<script type="text/javascript">

    $('.button-edit-social_media').on('click', function(e){
        var update = $(this).data('update');
        var id = $(this).attr('data-id');
        var name = $(this).attr('data-name');
        var url = $(this).attr('data-url');
        var icon = $(this).attr('data-icon');
        var status = $(this).attr('data-status');

        $('#updateCity').attr('action', update);
        $('#input-id-edit').val(id);
        $('#input-name-edit').val(name);
        $('#input-url-edit').val(url);
        $("input[name=status][value='"+status+"']").prop("checked",true);
        $('#edit-icon').attr('src', icon);
    });

    $('.file').bind('change', function() {
        //this.files[0].size gets the size of your file.
        if(this.files[0].size>2000000){
            alert("File size is too big, please select another file (max 2MB)");
            $(this).val('');
        }
    });
</script>


    

@stop

