@extends('admin.layout.template')

@section('content')


<section class="panel" id="listFeature">
    <header class="panel-heading">
        Feature
    </header>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-outer">
                    <div class="table-inner">
                        <a href="{{url('admin/features/create')}}" class="btn btn-blue" onclick="return toggleFeature()">New Feature</a> &nbsp
                        <br><br>
                        <table class="table default-table dataTable">
                            <thead>
                                <tr>
                                    <td>No</td>
                                    <td>Icon</td>
                                    <td>Name</td>
                                    <td>Category</td>
                                    <td>Status</td>
                                    <td>#</td>
                                </tr>
                            </thead>
                            <tbody>

                                @php

                                $no = 1;
                                @endphp

                                @foreach($features as $row)

                                <tr>
                                    <td>{{ ($features->currentpage()-1) * $features->perpage() + $no++ }}</td>
                                    <td>
                                    @if($row->icon!==null)
                                        <img src="{{asset($row->icon)}}" class="img-fluid" style="max-width: 70px">
                                    @endif
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->category->name}}</td>
                                    <td>@if($row->status==1) <span class="status status-success">Active</span> @else <span class="status status-danger">Non Active</span> @endif</td>
                                    <td>

                                    <div class="btn-group">
                                    <button type="button" class="btn btn-blue btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Action
                                    </button>
                                        <div class="dropdown-menu">
                                        <a href="{{url('admin/features/'.$row->id)}}" class="dropdown-item btn-edit-features">Show</a>
                                        <a href="{{url('admin/features/'.$row->id.'/edit')}}" class="dropdown-item btn-edit-features">Edit</a>
                                        <form action="{{ url('admin/feature-status/'.$row->id) }}" method="POST" onsubmit="return confirm('Confirmation?');">
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
                                        <form action="{{ url('admin/features/'.$row->id) }}" method="POST" onsubmit="return confirm('Do you really delete Data?');">
                                            @method('delete')
                                            @csrf
                                            <a href="javascript:void(0)" class="dropdown-item " onclick="$(this).closest('form').submit()">Delete</a>
                                        </form>
                                        </div>
                                    </div>
                     
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>

                        {{$features->render()}}
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

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
