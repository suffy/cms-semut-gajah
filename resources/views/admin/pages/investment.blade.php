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
                            <a href="{{url('admin/posts/create')}}" class="btn btn-blue" onclick="return togglePost()">New Post</a> &nbsp
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
                                    <td>Publish</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>

                                @php

                                    $no = 1;
                                @endphp

                                @foreach($post as $art)

                                    <tr>
                                        <td>{{$no++}}</td>
                                        <td>
                                            <div class="image-outer" style="background-image: url({{asset($art->featured_image)}}); background-size: cover; background-position: center center">
                                            </div>
                                        </td>
                                        <td>{{$art->title}}</td>
                                        <td>{{$art->excerpt}}</td>
                                        <td>{{$art->author->name}}</td>
                                        <td>{{$art->category->name}}</td>
                                        <td>{{$art->created_at}}</td>
                                        <td>@if($art->status==1) Published @else Pending @endif</td>
                                        <td>


                                            <div class="btn-group">
                                                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  Action
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="{{url('admin/posts/'.$art->id.'/edit')}}" class="dropdown-item btn-edit-post" data-id="{{$art->id}}">Edit</a>
                                                    <form action="{{ url('admin/posts/'.$art->id) }}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
                                                        @method('delete')
                                                        @csrf
                                                        <a href="#" class="dropdown-item " onclick="$(this).closest('form').submit()">Delete</a>
                                                    </form>
                                                  <div class="dropdown-divider"></div>
                                                  <a class="dropdown-item" href="#">Separated link</a>
                                                </div>
                                              </div>

                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>

                            {{$post->render()}}
                        </div>
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
