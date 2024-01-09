@extends('admin.layout.template')

@section('content')

@if(Request::segment(4)=='edit')
<section id="newFeature" class="panel">
    <header class="panel-heading">
        Edit Feature
    </header>
    <div class="card-body">
        <form action="{{url('admin/features/'.$feature->id)}}" method="POST" enctype="multipart/form-data">
            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Name</label>
                <div class="col-sm-6">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" class="form-control" placeholder="Name" value="{{$feature->name}}" required>
                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                    <input type="hidden" name="id" value="{{$feature->id}}" required>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Icon</label>
                <div class="col-sm-6">
                    <img src="{{asset($feature->icon)}}" width="150"><br><br>
                    <input type="file" name="icon" value="">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Category</label>
                <div class="col-sm-6">
                    <select name="category_id" class="form-control">
                        @foreach($category as $row)
                            <option value="{{$row->id}}" @if($feature->category_id==$row->id) selected @endif>{{$row->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Content</label>
                <div class="col-sm-8">
                    <a href="" data-toggle="modal" class="btn btn-blue btn-sm" data-target="#addImages">Add Media Images</a><br><br>
                    <textarea class="editor"  name="content">{{$feature->content}}</textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-blue">Update Feature</button>
                    <a href="{{url('admin/features')}}" class="btn btn-danger">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</section>
@else

<section id="newFeature" class="panel">
    <header class="panel-heading">
        Feature
    </header>
    <div class="card-body">
        <p><b>Name</b> : <br>{{$feature->name}}</p>
        <hr>
        <p><b>Category</b> : <br>{{$feature->category->name}}</p>
        <hr>
        <p><b>Content</b> : <br>{!!$feature->content!!}</p>
        <hr>
        <a href="{{url('admin/features/'.$feature->id.'/edit')}}" class="btn btn-blue">Edit</a>
        <a href="{{url('admin/features/')}}" class="btn btn-red">Cancel</a>
    </div>
</section>
@endif

<style>
    .modal-outer{
        overflow: auto;
        max-height: 650px;
    }

    .modal-inner{
        height: 100%;
    }
</style>

<style>
    .image-outer{
        width: 150px;
        height: 150px;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        border: 1px solid #f1f1f1;
        padding: 5px;
    }

    .image-outer img{
        position: absolute;
        width: 150px;
        height: auto;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
    }
</style>



<div id="addImages" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-name">Add Images</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-8">
                        <div id="image-list"></div>
                    </div>
                    <div class="col-sm-4">
                        <form id="form-images-ajax" method="post" action="{{url('admin/store-post-images')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Photo</label>
                                <div class="col-sm-12">
                                    <input type="file" name="images" value="" required>
                                    <input type="hidden" name="url" value="0" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Name</label>
                                <div class="col-sm-12">
                                    <input type="text" name="name" class="form-control" placeholder="Name" value="">
                                </div>
                            </div>
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
                            <div class="form-group row" id="loading" style="display: none;">
                                <div class="col-sm-12">
                                    <img src="{{asset('images/metaball-loader.gif')}}" width="40px">
                                    Loading ...
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label"></label>
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-blue" id="button-submit-images" onclick="return false;">Save Images</button> &nbsp
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

    <style>
        .modal-outer{
            overflow: auto;
            max-height: 650px;
        }

        .modal-inner{
            height: 100%;
        }
    </style>

    <script>

        getStorageGallery();

        function getStorageGallery(){

            var url = "{{url('admin/image-lists')}}";
            var asset = "{{asset('/')}}";

            $.ajax({
                url: url,
                method: "GET",
                success: function(response){

                    $('#image-list').html(response);
                }

            });

        }

        $('#button-submit-images').on('click', function(){

            var data = new FormData($('#form-images-ajax')[0]);

            var images = $('#images').val();

            if(images==''){
                alert("Please fill the blank");
            }else{
                $.ajax({
                    type:"POST",
                    url:"{{url('admin/store-image')}}",
                    data:data,
                    mimeType: "multipart/form-data",
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function() {
                        $('#loading').fadeIn();
                    },
                    success:function(rsp)
                    {

                        response = JSON.parse(rsp);
                        if(response.status==1){

                            setTimeout(function(){
                                $('#form-images-ajax').trigger('reset');
                                getStorageGallery();
                                $('#loading').fadeOut();
                            }, 3000)

                        }else{
                            getStorageGallery();
                        }
                    }
                });
            }

        })

    </script>

<script>
    

    function toggleFeature(){
        $('#newFeature').fadeToggle();
        $('#listFeature').fadeToggle();
    }


</script>


@stop
