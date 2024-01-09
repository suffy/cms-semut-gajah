@extends('admin.layout.template')

@section('content')



    <section class="panel">
        <header class="panel-heading">
            Images
        </header>

        <div class="card-body">

            <div class="row">

                <form method="post" action="{{url('admin/images/'.$image->id)}}" enctype="multipart/form-data">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <img src="{{asset($image->path)}}" class="img-fluid">
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="url"  value="{{Request::url()}}" required>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                <input type="text" name="name" class="form-control" placeholder="Name" value="{{$image->name}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Desc</label>
                            <div class="col-sm-12">
                                <input type="text" name="img_desc" class="form-control" placeholder="Description" value="{{$image->description}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Keyword (SEO)</label>
                            <div class="col-sm-12">
                                <input type="text" name="keyword" class="form-control" placeholder="Keyword" value="{{$image->keyword}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label"></label>
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save Images</button> &nbsp
                                <a href="{{url('admin/images')}}" class="btn btn-danger">Back to Images</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </section>



    <style>
        .box-border{
            border: 1px solid #f1f1f1;
            position: relative;
            overflow: hidden;
            height: 200px;
            margin-bottom: 30px;
        }

        .box-border img{
            position: absolute;
            margin: auto;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .box-border button{
            position: absolute;
            right: 0;
            margin: 10px;
        }
    </style>




    

@stop
