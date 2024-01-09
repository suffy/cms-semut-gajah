@extends('admin.layout.template')

@section('content')



    <section class="panel" id="add-image" style="display:none">
        <header class="panel-heading">
            Post Images
        </header>

        <div class="card-body">
            <form method="post" action="{{url('admin/images')}}" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                        @csrf
                        <input type="hidden" name="url"  value="{{Request::url()}}" required>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Photo</label>
                            <div class="col-sm-12">
                                <input type="file" name="images" value="" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control" placeholder="Name" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Desc</label>
                            <div class="col-sm-12">
                                <input type="text" name="img_desc" class="form-control" placeholder="Description" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Keyword (SEO)</label>
                            <div class="col-sm-12">
                                <input type="text" name="keyword" class="form-control" placeholder="Keyword" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 col-sm-12 ">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label"></label>
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save Images</button> &nbsp
                                <a href="javascript:void(0)" class="btn btn-danger" onclick="return openCreate()">Close</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>

    </section>

    <section class="panel">
        <div class="card-body">
            <a href="javascript:void(0)" class="btn btn-blue" onclick="return openCreate();"><span class="fa fa-plus"></span> Create New</a>
        <br>
        <div class="row card-body">
            @foreach($images as $gallery)
            <div class="col-md-2 col-sm-3" style="padding:0px">
                <div class="box-border" style="background-size: cover; background-position: center center;">
                <a class="example-image-link" href="{{asset($gallery->path)}}"  data-lightbox="example-set">
                    <img class="example-image img-fluid mt-1 mb-1" src="{{asset($gallery->path)}}" alt="" style="height: 100%; object-fit: cover; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);"/>
                </a>
                    <div class="action-button">
                        <a href="{{url('admin/images/'.$gallery->id.'/edit')}}" class="btn btn-green btn-sm"><i class="fa fa-edit"></i></a>
                        <form action="{{ url('admin/images/'.$gallery->id) }}" method="post" onsubmit="return confirm('Do you really delete Data?');">
                            @method('delete')
                            @csrf
                            <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            <button type="submit" class="btn btn-red btn-sm" value="Delete" onclick="return confirm('Delete Data?')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        </div>
    </section>


    <script>
        function openCreate(){
            $("#add-image").fadeToggle();
        }
    </script>

    <style>
        .box-border{
            border: 1px solid #f1f1f1;
            position: relative;
            overflow: hidden;
            height: 150px;
            margin-bottom: 0px;
            text-align: center;
        }

        .box-border img{
            position: absolute;
            margin: auto;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .box-border .action-button{
            position: relative;
            display: inline-flex;
            margin-top: 110px;
            opacity: 0.4;
        }

        .box-border .action-button:hover{
            opacity: 1;
        }

        .action-button button,
        .action-button a{
            border-radius: 50%;
        }
    </style>




    

@stop
