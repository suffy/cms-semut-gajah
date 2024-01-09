@extends('admin.layout.template')

@section('content')

    <section class="panel" id="listPost">
        <header class="panel-heading">
            Post
        </header>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            <a href="{{url('admin/posts/create')}}?type={{$type}}" class="btn btn-blue" onclick="return togglePost()">New Post</a>
                            <a href="{{url('admin/images')}}" class="btn btn-blue" >Post Images</a>
                            <a href="{{url('admin/post-categories')}}" class="btn btn-blue" >Post Category</a>
                            <br><br>
                            <table class="table default-table dataTable">
                                <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Images</td>
                                    <td>Title</td>
                                    <td>Preview</td>
                                    <td>Author</td>
                                    <td>Category</td>
                                    <td>Created Time</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>

                                @php

                                    $no = 1;
                                @endphp

                                @foreach($post as $row)

                                    <tr>
                                        <td>{{$loop->iteration+($post->currentpage() - 1) * $post->perPage()}}</td>
                                        <td>
                                            <!-- <div class="image-outer" style="background-image: url({{asset($row->featured_image)}}); background-size: cover; background-position: center center">
                                            </div> -->
                                            @if ($row->featured_image == null)
                                            <div>
                                                <a class="example-image-link" href="{{asset('images/no-images.png')}}"  data-lightbox="example-set">
                                                    <img class="example-image img-fluid mt-1 mb-1" src="{{asset('images/no-images.png')}}" alt="" style="width: 150px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);"/>
                                                </a>
                                            </div>
                                            @else
                                                <div>
                                                    <a class="example-image-link" href="{{asset($row->featured_image)}}"  data-lightbox="example-set">
                                                        <img class="example-image img-fluid mt-1 mb-1" src="{{asset($row->featured_image)}}" alt="" style="width: 150px; height: 50px; object-fit: cover; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);"/>
                                                    </a>
                                                </div>
                                            @endif

                                            @if ($row->featured_video != null)
                                            <div class="iframe-rwd">{!!$row->featured_video!!}"</div>
                                            @endif
                                        </td>
                                        <td>{{$row->title}}<br>
                                            {{$row->title_en}}
                                        </td>
                                        <td>{{$row->excerpt}}<br>
                                            {{$row->excerpt_en}}</td>
                                        <td>{{$row->author->name}}</td>
                                        <td>{{$row->category->name}}</td>
                                        <td>{{$row->created_at}}</td>
                                        <td>@if($row->status==1) <span class="status status-success">Published</span> @else <span class="status status-warning">Draft</span> @endif</td>
                                        <td>


                                            <div class="btn-group">
                                                <button type="button" class="btn btn-blue btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  Action
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="{{url('admin/posts/'.$row->id.'/edit')}}" class="dropdown-item btn-edit-post" data-id="{{$row->id}}">Edit</a>
                                                    <form action="{{ url('admin/post-status/'.$row->id) }}" method="POST" onsubmit="return confirm('Confirmation?');">
                                                    @method('post')
                                                    @csrf
                                                    <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                                        @if($row->status==1)
                                                        <input type="hidden" value="0" name="status">
                                                        <a href="#" class="dropdown-item " onclick="$(this).closest('form').submit()">Deactivate</a>
                                                        @else
                                                        <input type="hidden" value="1" name="status">
                                                        <a href="#" class="dropdown-item " onclick="$(this).closest('form').submit()">Activate</a>
                                                        @endif
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ url('admin/posts/'.$row->id) }}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
                                                        @method('delete')
                                                        @csrf
                                                        <a href="#" class="dropdown-item " onclick="$(this).closest('form').submit()">Delete</a>
                                                    </form>
                                                </div>
                                              </div>

                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <br>
                        {{ $post->appends(request()->query())->links() }}
                    </div>
                </div>

            </div>

        </div>
    </section>


    <style>
        .image-outer{
            width: 200px;
            height: 100px;
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

    


@stop
