@extends('admin.layout.template')

@section('content')


<section class="panel" id="listPage">
    <header class="panel-heading">
        Page
    </header>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-outer">
                    <div class="table-inner">
                        <a href="{{url('admin/pages/create')}}" class="btn btn-blue" onclick="return togglePage()">New Page</a> &nbsp
                        <br><br>
                        <table class="table default-table dataTable">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Title</td>
                                    <td>Type</td>
                                    <td>Position</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>

                                @php

                                $no = 1;
                                @endphp

                                @foreach($page as $row)

                                <tr>
                                    <td>{{$no++}}</td>
                                    <td>{{$row->title}}<br>
                                        {{$row->title_en}}
                                    </td>
                                    <td>{{$row->post_type}}</td>
                                    <td>{{$row->position}}</td>
                                    <td>@if($row->status==1) <span class="status status-success">Published</span> @else <span class="status status-warning">Draft</span> @endif</td>
                                    <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-blue dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Action
                                        </button>
                                        <div class="dropdown-menu">
                                            <a href="{{url('admin/pages/'.$row->id.'/edit')}}" class="dropdown-item btn-edit-post" data-id="{{$row->id}}">Edit</a>
                                            <form action="{{ url('admin/pages/'.$row->id) }}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
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

                        {{$page->render()}}
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>


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





@stop
