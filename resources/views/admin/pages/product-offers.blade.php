@extends('admin.layout.template')

@section('content')

    <section class="panel">
        <header class="panel-heading">
            Penawaran
        </header>
        <div class="card-body">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-12 col-md-12 col-sm-12 col-xs-12  ">
                        <a href="#" data-toggle="modal" data-target="#createOffers" class="btn btn-blue">Buat Penawaran</a><br><br>
                        <div class="table-outer">
                            <div class="table-inner">
                                <table class="table default-table">
                                    <thead>
                                    <tr align="center">
                                        <td>No</td>
                                        <td>Icon</td>
                                        <td>Judul</td>
                                        <td>Deskripsi</td>
                                        <td>Mulai Hari</td>
                                        <td>Berakhir</td>
                                        <td>Status</td>
                                        <td>Action</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                  
                                        @php
                                            $no=1;                                       
                                        @endphp

                                        @foreach($offers as $offer)
                                        <tr>
                                            <td class="text-center">{{$no++}}</td>
                                            <td>
                                                <img src="{{asset($offer->icon)}}" class="img-fluid" style="max-width: 200px">
                                            </td>
                                            <td>{{$offer->title}}</td>
                                            <td>{!! substr($offer->description, 0, 200) !!}</td>
                                            <td>{{$offer->day_start}}</td>
                                            <td>{{$offer->day_end}}</td>
                                            <td>
                                            @php
                                                if($offer->status==1){
                                                    echo "<span class='status status-success'>Active</span>";
                                                }else if($offer->status==2){
                                                    echo "<span class='status status-warning'>Non Aktif</span>";
                                                }
                                            @endphp
                                            </td>
                                            <td>

                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Action
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                    <li><a href="@if(auth()->user()->account_role == 'manager'){{url('manager/offers-detail/'.$offer->id)}}@else{{url('superadmin/offers-detail/'.$offer->id)}}@endif" class="dropdown-item" >Detail</a></li>
                                                    <li><a href="javascript:void(0)" class="dropdown-item btn-edit-offers" data-toggle="modal" data-target="#editOffers"
                                                   data-id="{{$offer->id}}"
                                                   data-title="{{$offer->title}}"
                                                   data-desc="{{$offer->description}}"
                                                   data-start="{{substr($offer->day_start, 0, 10)}}"
                                                   data-start_time="{{substr($offer->day_start, 11, 16)}}"
                                                   data-end="{{substr($offer->day_end, 0, 10)}}"
                                                   data-end_time="{{substr($offer->day_end, 11, 16)}}"
                                                   data-icon="{{asset($offer->icon)}}"
                                                   data-location="{{$offer->location}}"
                                                   data-status="{{$offer->status}}">Edit</a></li>
                                                    <li><a href="@if(auth()->user()->account_role == 'manager'){{url('manager/delete-offers/'.$offer->id)}}@else{{url('superadmin/delete-offers/'.$offer->id)}}@endif" class="dropdown-item" onclick="return confirm('Yakin Akan Menghapus Data ?');">Delete</a></li>
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

    <div id="createOffers" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Buat Penawaran</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/store-offers')}}@else{{url('superadmin/store-offers')}}@endif" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-md-12">Judul</label>
                            <div class="col-md-12">
                                @csrf
                                <input type="text" name="title" class="form-control" placeholder="Title" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-12">Deskripsi</label>
                            <div class="col-md-12">
                                <textarea name="description" id="mytextarea"></textarea>
                            </div>
                        </div>                       
                         
                        <div class="form-group">
                        <label>Mulai</label>
                        <div class="row">
                            <div class="col-md-7"><input class="form-control datepicker" value="" placeholder="2018-08-02" name="start_at"></div>
                            <div class="col-md-5"><input type="time" class="form-control" value="" name="time_start_at"></div>
                        </div>
                        </div>

                      <div class="form-group">
                        <label>Berakhir</label>
                        <div class="row">
                            <div class="col-md-7"><input class="form-control datepicker" value="" placeholder="2018-08-02" name="end_at"></div>
                            <div class="col-md-5"><input type="time" class="form-control" value="" name="time_end_at"></div>
                        </div>
                      </div>

                        <div class="form-group row">
                            <label class="col-md-12">Icon</label>
                            <div class="col-md-12">
                                <input type="file" class="file" name="icon" value="" required>
                            </div>
                        </div>

                      <div class="form-group row">
                            <label class="col-md-12">Status</label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <select id="status" name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="2">Non Aktif</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-blue">Simpan Penawaran</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div id="editOffers" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Penawaran</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/update-offers')}}@else{{url('superadmin/update-offers')}}@endif" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-md-12">Judul</label>
                            <div class="col-md-12">
                                @csrf
                                <input id="title" type="text" name="title" class="form-control" placeholder="Title" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                                <input id="data-id" type="hidden" name="data-id"  value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-12">Deskripsi</label>
                            <div class="col-md-12">
                                <textarea id="description" type="text" name="description"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                        <label>Mulai</label>
                        <div class="row">
                            <div class="col-md-7"><input class="form-control datepicker" value="" placeholder="2018-08-02" id="start_at" name="start_at"></div>
                            <div class="col-md-5"><input type="time" class="form-control" value="" id="time_start_at" name="time_start_at"></div>
                        </div>
                        </div>

                      <div class="form-group">
                        <label>Berakhir</label>
                        <div class="row">
                            <div class="col-md-7"><input class="form-control datepicker" value="" placeholder="2018-08-02" id="end_at" name="end_at"></div>
                            <div class="col-md-5"><input type="time" class="form-control" value="" id="time_end_at" name="time_end_at"></div>
                        </div>
                      </div>

                        <div class="form-group row">
                            <label class="col-md-12">Icon</label>
                            <div class="col-md-12">
                            <img id="icon" src="{{asset('cms/img/no-images.png')}}" width="300px"><hr>
                            <input type="file" class="file" id="icon" name="icon" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-12">Status</label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <select id="data-status" name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="2">Non Aktif</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-blue">Update Penawaran</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.0.6/jquery.tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#mytextarea'
        });

        tinymce.init({
            selector: '#description'
        });
    </script>

    <script>
          $(function () {
          $('#mulai, #akhir').datepicker({
            autoclose: true
          })         
      });

        $('.btn-edit-offers').on('click', function(){

            var id = $(this).attr('data-id');
            var title = $(this).attr('data-title');
            var desc = $(this).attr('data-desc');
            var start = $(this).attr('data-start');
            var start_time = $(this).attr('data-start_time');
            var end = $(this).attr('data-end');
            var end_time = $(this).attr('data-end_time');
            var status = $(this).attr('data-status');
            var icon = $(this).attr('data-icon');
            var location = $(this).attr('data-location');
            var location = location.split(",");

            $('#data-id').val(id);   
            $('#title').val(title);            
            $('#data-status').val(status);

            $('#start_at').val(start);
            $('#time_start_at').val(start_time);

            $('#end_at').val(end);
            $('#time_end_at').val(end_time);

            $elements = $('input[name="location[]"]').prop("checked",false);            
            for (var j = 0; j < location.length; j++) {                
                $elements.filter('[value="' + location[j] + '"]').prop("checked",true);
            }

            tinyMCE.get('description').setContent(desc);
            $('#icon').attr('src', icon);
            
        })

        $('.file').bind('change', function() {

            //this.files[0].size gets the size of your file.
            if(this.files[0].size>2000000){
                alert("File size is too big, please select another file (max 2MB)");
                $(this).val('');
            }

        });
    </script>

@stop
