@extends('admin.layout.template')

@section('content')

<form id="formUpdate" action="{{url('admin/pages/'.$page->id)}}" method="post" enctype="multipart/form-data">
<div class="row">
    <div class="col-md-9 col-sm-12 col-xs-12">
        <section id="newPost" class="panel">
            <header class="panel-heading">
                Create New Pages
            </header>
            <div class="card-body">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                          <a class="nav-item nav-link active" id="nav-id" data-toggle="tab" href="#tab-id" role="tab" aria-controls="nav-id" aria-selected="true">Bahasa Indonesia</a>
                        </div>
                    </nav>

                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show card-body active" id="tab-id" role="tabpanel" aria-labelledby="nav-id" style=" border: 1px solid #dddddd"> 
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Title</label>
                                <div class="col-sm-12">
                                    @method('put')
                                    @csrf
                                    <input id="edit-title" type="text" name="title" class="form-control" placeholder="Title" value="{{$page->title}}" required>
                                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                    <input id="edit-id" type="hidden" name="id"  value="{{$page->id}}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label">Content</label>
                                <div class="col-sm-12">
                                    <a href="" data-toggle="modal" class="btn btn-blue btn-sm" data-target="#addImages">Add Media Images</a><br><br>
                                    <textarea class="editor"  name="content">{{$page->content}}</textarea>
                                </div>
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
                    <label>Page Type</label>
                    <select name="type" class="form-control">
                        <option value="pages" @if($page->post_type=="pages") selected @endif>Pages</option>
                        <option value="menu" @if($page->post_type=="menu") selected @endif>Menu</option>
                    </select>
                    <hr>
                    <label>Position</label>
                    <select name="position" class="form-control">
                        <option value="menu" @if($page->position=="menu") selected @endif>Menu</option>
                        <option value="footer-1" @if($page->position=="footer-1") selected @endif>Footer 1</option>
                        <option value="footer-2" @if($page->position=="footer-2") selected @endif>Footer 2</option>
                        <option value="footer-3" @if($page->position=="footer-3") selected @endif>Footer 3</option>
                        <option value="footer-4" @if($page->position=="footer-4") selected @endif>Footer 4</option>
                        <option value="none" @if($page->position=="none") selected @endif>None</option>
                    </select>
                    <hr>
                        <label><input type="radio" name="status" value="1" @if($page->status=='1')checked @endif> Publish</label> &nbsp
                        <label><input type="radio" name="status" value="0" @if($page->status=='0')checked @endif> Draft</label>
                    <hr>

                <button type="submit" class="btn btn-blue">Save Post</button>
                <button type="button" class="btn btn-danger" onclick="return togglePost()">Cancel</button>

            </div>
        </section>
            
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

            var url = "{{url('admin/get-post-images')}}";
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
                    url:"{{url('admin/store-post-images')}}",
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


    @if(!empty(Session::get('status')) && Session::get('status') == 1)
        <script>
            showNotif("{{Session::get('message')}}");
        </script>
    @endif

    @if(!empty(Session::get('status')) && Session::get('status') == 2)
        <script>
            showAlert("{{Session::get('message')}}");
        </script>
    @endif


@stop
