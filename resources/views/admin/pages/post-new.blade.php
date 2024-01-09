@extends('admin.layout.template')

@section('content')

<form action="{{url('admin/posts')}}" method="post" enctype="multipart/form-data">
<div class="row">
    <div class="col-md-9 col-sm-12 col-xs-12">
        <section id="newPost" class="panel">
            <header class="panel-heading">
                Create New Post
            </header>
            <div class="card-body">

                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                          <a class="nav-item nav-link active" id="nav-id" data-toggle="tab" href="#tab-id" role="tab" aria-controls="nav-id" aria-selected="true">Bahasa Indonesia</a>
                          <a class="nav-item nav-link" id="nav-en" data-toggle="tab" href="#tab-en" role="tab" aria-controls="nav-en" aria-selected="false">Inggris</a>
                        </div>
                    </nav>

                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show card-body active" id="tab-id" role="tabpanel" aria-labelledby="nav-id" style=" border: 1px solid #dddddd">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Judul</label>
                                <div class="col-sm-12">
                                    @csrf
                                    <input type="text" name="title" class="form-control" placeholder="Title" value="" required>
                                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Konten</label>
                                <div class="col-sm-12">
                                    <a href="" data-toggle="modal" class="btn btn-blue btn-sm" data-target="#addImages">Add Media Images</a><br><br>
                                    <textarea class="editor"  name="content"></textarea>
                                </div>
                            </div>
                            <hr>
                            <header class="panel-heading">
                                SEO
                            </header>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Text Preview</label>
                                <div class="col-sm-12">
                                    <textarea name="excerpt" class="form-control" placeholder="Preview" value=""></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Kata Kunci</label>
                                <div class="col-sm-12">
                                    <textarea name="keyword" class="form-control" placeholder="Keywords"></textarea>
                                </div>
                            </div>
                            
                        </div>
                        <div class="tab-pane fade card-body" id="tab-en" role="tabpanel" aria-labelledby="nav-en" style=" border: 1px solid #dddddd">
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Title</label>
                                <div class="col-sm-12">
                                    <input type="text" name="title_en" class="form-control" placeholder="Title" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Content</label>
                                <div class="col-sm-12">
                                    <a href="" data-toggle="modal" class="btn btn-blue btn-sm" data-target="#addImages">Add Media Images</a><br><br>
                                    <textarea class="editor"  name="content_en"></textarea>
                                </div>
                            </div>
                            <hr>
                            <header class="panel-heading">
                                SEO
                            </header>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Text Preview</label>
                                <div class="col-sm-12">
                                    <textarea name="excerpt_en" class="form-control" placeholder="Preview" value=""></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Keyword</label>
                                <div class="col-sm-12">
                                    <textarea name="keyword_en" class="form-control" placeholder="Keywords"></textarea>
                                </div>
                            </div>
                        </div>

            </div>
        </section>

    </div>
    <div class="col-md-3 col-sm-12 col-xs-12">
        <section id="newPost" class="panel">
            <header class="panel-heading">
                Publish
            </header>
            <div class="card-body">
                <label>Post Type</label>
                <select name="type" class="form-control">
                    @if($type=="news" || $type=="events") 
                    <option value="news" @if($type=="news") selected @endif>News</option>
                    <option value="events" @if($type=="events") selected @endif>Events</option>
                    @endif
                    @if($type=="educations") 
                    <option value="educations" @if($type=="educations") selected @endif>Educations</option>
                    @endif
                    @if($type=="pages") 
                    <option value="pages" @if($type=="pages") selected @endif>About Us</option>
                    @endif
                    @if($type=="careers") 
                    <option value="careers" @if($type=="careers") selected @endif>Careers</option>
                    @endif
                </select>
                <hr>
                <label><input type="radio" name="status" value="1" checked> Publish</label> &nbsp
                <label><input type="radio" name="status" value="0"> Draft</label>
                <hr>

                <button type="submit" class="btn btn-blue">Save Post</button>
                <button type="button" class="btn btn-danger" onclick="return togglePost()">Cancel</button>

            </div>
        </section>
        <section id="newPost" class="panel">
            <header class="panel-heading">
                Cover
            </header>
            <div class="card-body">

                <img id="blah" src="{{asset('images/no-images.png')}}" alt="your image" class="img-fluid"/><br><br>
                <input type='file' id="imgInp" class="file" name="featured_image"/>

                <script>
                    function readURL(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();

                        reader.onload = function(e) {
                        $('#blah').attr('src', e.target.result);
                        }

                        reader.readAsDataURL(input.files[0]); // convert to base64 string
                    }
                    }

                    $("#imgInp").change(function() {
                    readURL(this);
                    });
                </script>

            </div>
            <header class="panel-heading">
                Youtube Video Cover
            </header>
            <div class="card-body">
                <textarea type="text" class="form-control" id="input-featured_video" name="featured_video"></textarea>
                <span>* Embedded video cover</span>
            </div>

            <header class="panel-heading">
                Upload Video
            </header>
            <div class="card-body">
                <input type='file' name="video"/>
            </div>

            <hr>
            
            <header class="panel-heading">
                Category
            </header>
            <div class="card-body" style="height: 300px; overflow-x: scroll;">
                @foreach($category as $s)
                    @if($s->category_parent==0)
                    <label><input type="radio" name="category_id" value="{{$s->id}}" required> &nbsp {{$s->name}}</label><br>
                    @foreach($s->sub_cat as $row2)
                    &nbsp<span class="fa fa-angle-right"></span><label style="margin-left: 15px;"><input type="radio" name="category_id" value="{{$row2->id}}" required> &nbsp {{$row2->name}}</label><br>
                    @endforeach
                    <hr style="margin-top: 5px">
                    @endif
                @endforeach
            </div>
            <header class="panel-heading">
                Tags
            </header>
            <div class="card-body">
                <textarea type="text" class="form-control" id="input-tags" name="tags"></textarea>
                    *pisahkan dengan comma ( , )
                    <div id="tag-places" style="margin-top: 10px; margin-bottom: 10px;"></div>
            </div>
        </section>
        <script>
            $('#input-tags').on('keyup', function(){
                var result = $(this).val();
                result = result.split(","); 
                var html = "";
                for(var i=0; i<result.length; i++){
                    html += "<a href='#input-tags' class='btn status status-info' style='margin-bottom: 3px;'>"+result[i]+"</a>&nbsp";
                }

                $('#tag-places').html(html);

                if(result==""){
                    $('#tag-places').html("");
                }
            })

        </script>

    </div>
</div>
</form>


<div id="addImages" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Images</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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


    


@stop
