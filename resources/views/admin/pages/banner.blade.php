@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<section class="panel">
    <header class="panel-heading">
        Banner
    </header>
    <div class="card-body">
        <div class="content-body">
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                             <a href="#" data-toggle="modal" data-target="#newBanner" class="btn btn-blue">New Banner</a>
                             <a href="
                                    @if($account_role == 'manager')
                                        {{url('manager/banner/priority')}}
                                    @elseif($account_role == 'superadmin')
                                        {{url('superadmin/banner/priority')}}
                                    @elseif($account_role == 'admin')
                                        {{url('admin/banner/priority')}}
                                    @endif
                                    " class="btn btn-blue float-right">Prioritas Banner</a><br><br>
                            <table class="table default-table dataTable">
                                <thead>
                                    <tr align="center">
                                        <td>No</td>
                                        {{-- <td>Location</td> --}}
                                        <td>Detail</td>
                                        <td width="150px">Photo</td>
                                        <td>Status</td>
                                        <!-- <td>Position</td> -->
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $no=1;
                                    @endphp

                                    @foreach($banner as $row)
                                    <tr>
                                        <td>{{$no++}}</td>
                                        <td style="display:none;">{{$row->page}}</td>
                                        <td>
                                            <b>{{$row->title}}</b><br>
                                            {{$row->banner_desc}}<hr>
                                            <b>{{$row->title_en}}</b><br>
                                            {{$row->banner_desc_en}}
                                        </td>
                                            <td>
                                            <img src="{{asset($row->images)}}" class="img-fluid" style="max-width: 150px">
                                            </td>
                                            <td>
                                            @if($row->status==1) <span class="status status-success">Active</span> @else <span class="status status-warning">Inactive</span> @endif
                                            </td>
                                            {{-- <td>
                                                {{$row->position}}
                                            </td> --}}
                                            <td>
                                                
                                                <div class="btn-group">
                                                <button type="button" class="btn btn-blue btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                  Action
                                                </button>
                                                <div class="dropdown-menu">
                                                <a href="javascript:void(0)" class="dropdown-item btn-edit-banner" data-toggle="modal" data-target="#editBanner"
                                                data-id="{{$row->id}}"
                                                data-title="{{$row->title}}"
                                                data-desc="{{$row->banner_desc}}"
                                                data-title_en="{{$row->title_en}}"
                                                data-desc_en="{{$row->banner_desc_en}}"
                                                data-position="{{$row->position}}"
                                                {{-- data-link="{{$row->link}}" --}}
                                                data-action="
                                                    @if(auth()->user()->account_role == 'manager')
                                                        {{url('manager/banners/')."/".$row->id}}
                                                    @elseif(auth()->user()->account_role == 'superadmin')
                                                        {{url('superadmin/banners/')."/".$row->id}}
                                                    @elseif(auth()->user()->account_role == 'admin')
                                                        {{url('admin/banners/')."/".$row->id}}
                                                    @endif
                                                "
                                                data-page="{{$row->page}}"
                                                data-images="{{asset($row->images)}}">Edit</a>

                                                <form action="@if(auth()->user()->account_role == 'manager'){{ url('manager/banner-status/'.$row->id) }}@elseif(auth()->user()->account_role == 'superadmin'){{ url('superadmin/banner-status/'.$row->id) }}@elseif(auth()->user()->account_role == 'admin'){{ url('admin/banner-status/'.$row->id) }}@endif" method="POST" onsubmit="return confirm('Confirmation?');">
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

                                                    <form action="@if(auth()->user()->account_role == 'manager'){{ url('manager/banners/'.$row->id) }}@elseif(auth()->user()->account_role == 'superadmin'){{ url('superadmin/banners/'.$row->id) }}@elseif(auth()->user()->account_role == 'admin'){{ url('admin/banners/'.$row->id) }}@endif" method="POST" onsubmit="return confirm('Do you really delete Data?');">
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
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    <div id="newBanner" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Banner</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/banners')}}@else{{url('superadmin/banners')}}@endif" method="post" enctype="multipart/form-data">
                        <div class="form-group row {{ $errors->has('title') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Banner Title</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="title" class="form-control" placeholder="Title" value="">
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('title') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Title En</label>
                            <div class="col-sm-12">
                                <input type="text" name="banner_title_en" class="form-control" placeholder="Description" value="">
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('banner_desc') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Banner Desc</label>
                            <div class="col-sm-12">
                                <input type="text" name="banner_desc" class="form-control" placeholder="Description" value="">
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('banner_desc') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Desc En</label>
                            <div class="col-sm-12">
                                <input type="text" name="banner_desc_en" class="form-control" placeholder="Description" value="">
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Link</label>
                            <div class="col-sm-12">
                                <input type="text" name="link" class="form-control" placeholder="Link" value="">
                            </div>
                        </div> --}}
                        {{-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Page</label>
                            <div class="col-sm-12">
                                @php 
                                    $menu = App\Menu::all();
                                @endphp
                                <select class="form-control" name="page">
                                    <option value="0">- Select Page -</option>
                                    @foreach($menu as $row)
                                        <option value="{{$row->slug}}">{{$row->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}

                        {{-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Position</label>
                            <div class="col-sm-12">
                                <select class="form-control" name="position">
                                    <option value="top">Top</option>
                                    <option value="middle">Middle</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('position') }} </span>
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Photo</label>
                            <div class="col-sm-12">
                                <input type="file" name="photo" value="" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('photo') }} </span>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save Banner</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>


    <div id="editBanner" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Banner</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="edit-banner" action="@if(auth()->user()->account_role == 'manager'){{url('manager/banners/')}}@else{{url('superadmin/banners/')}}@endif" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Title</label>
                            <div class="col-sm-12">
                                @method('put')
                                @csrf
                                <input id="edit-title" type="text" name="title" class="form-control" placeholder="Title" value="">
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                <input id="edit-id" type="hidden" name="id"  value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Title En</label>
                            <div class="col-sm-12">
                                <input id="edit-title_en" type="text" name="title_en" class="form-control" placeholder="Title" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Desc</label>
                            <div class="col-sm-12">
                                <input id="edit-desc" type="text" name="banner_desc" class="form-control" placeholder="Description" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Desc En</label>
                            <div class="col-sm-12">
                                <input id="edit-desc_en" type="text" name="banner_desc_en" class="form-control" placeholder="Description" value="">
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Link</label>
                            <div class="col-sm-12">
                                <input id="exit-link" type="text" name="link" class="form-control" placeholder="Link" value="">
                            </div>
                        </div> --}}
                        {{-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Banner Page</label>
                            <div class="col-sm-12">
                                <select id="edit-page" class="form-control" name="page">
                                @php 
                                    $menu = App\Menu::all();
                                @endphp
                                    <option value="0">- Select Page -</option>
                                    @foreach($menu as $row)
                                        <option value="{{$row->slug}}">{{$row->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        {{-- <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Position</label>
                            <div class="col-sm-12">
                                <select class="form-control" id="edit-position" name="position">
                                    <option value="top">Top</option>
                                    <option value="middle">Middle</option>
                                </select>
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Photo</label>
                            <div class="col-sm-12">
                                <img class="img img-responsive" id="edit-image" src="{{asset('images/no-images.png')}}" width="150px"><br><br>
                                <input type="file" name="photo" value="">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Update Banner</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        $(function(){
          $('.toggle-class').change(function(){
              var status = $(this).prop('checked') == true ? 1 : 0;
              var banner_id = $(this).data('id');

              $.ajax({
                  type: "GET",
                  dataType: "json",
                  url: '/admin/change-status-banner',
                  data: {'status': status, 'banner_id': banner_id},
                  success: function(data){
                    console.log(data.success)
                  }
              });
            })
        })
</script>

    <script>
        @if (count($errors) > 0)
            $('#newUser').modal('show');
        @endif

        $('.btn-edit-banner').on('click', function(){
            var id = $(this).attr('data-id');
            var title = $(this).attr('data-title');
            var title_en = $(this).attr('data-title_en');
            var page = $(this).attr('data-page');
            var images = $(this).attr('data-images');
            var desc = $(this).attr('data-desc');
            var desc_en = $(this).attr('data-desc_en');
            var position = $(this).attr('data-position');
            // var link = $(this).attr('data-link');
            var action = $(this).attr('data-action');

            console.log(position);

            $('#edit-id').val(id);
            $('#edit-title').val(title);
            $('#edit-title_en').val(title_en);
            $('#edit-page').val(page);
            $('#edit-desc').val(desc);
            $('#edit-desc_en').val(desc_en);
            $('#position').val(position);
            // $('#edit-link').val(link);
            $('#edit-image').attr('src', images);
            $('#edit-banner').attr('action', action);


        })
    </script>

    <style>
        .text-small{
            font-size: 8pt;
            color: red;
        }
    </style>

    @if(!empty(Session::get('status')) && Session::get('status') == 1)
    <script>
        showNotif("{{Session::get('message')}}");
    </script>
    @endif

    @stop
