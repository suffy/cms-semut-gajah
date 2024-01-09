@extends('admin.layout.template')

@section('content')

            <div class="row">

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <section class="panel">
                        <header class="panel-heading">
                            Messages
                        </header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="table-outer">
                                        <div class="table-inner">
                                            {{-- <form method="post" action="{{url('admin/delete-selected-contact')}}">
                                                @csrf --}}

                                                {{-- <button type="submit" class="btn btn-blue" onclick="return confirm('Delete all selected data?')">Delete All Selected </button><br><br> --}}
                                            <div class="row">
                                                <div class="col-md-2 my-1">
                                                    <select class="form-control" name="qty" onChange="location = this.value;">
                                                        <option value="messages" @if(!\Illuminate\Support\Facades\Request::get('status')) selected @endif>New</option>
                                                        <option value="?status=1" @if(\Illuminate\Support\Facades\Request::get('status') == '1') selected @endif>Readed</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4"></div>
                                                <div class="col-md-6 my-1">
                                                    <form method="get" action="@if(auth()->user()->account_role == 'manager'){{url('manager/messages')}}@else{{url('superadmin/messages')}}@endif">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="search" placeholder="Search by phone...">
                                                            <div class="input-group-append">
                                                            <button class="btn btn-secondary" type="submit">
                                                                <i class="fa fa-search"></i>
                                                            </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table default-table dataTable">
                                                    <thead>
                                                    <tr align="center">
                                                        <td>No</td>
                                                        <td>Name</td>
                                                        <td>Phone</td>
                                                        <td>Subject</td>
                                                        <td>Message</td>
                                                        <td>Time</td>
                                                        <td>Action</td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        $no = ($messages->currentpage() - 1) * $messages->perPage() + 1;
                                                    @endphp
                                                    @foreach($messages as $message)
                                                        <tr>
                                                            <td width="50px">{{$no}}</td>
                                                            <td>{{$message->name}}</td>
                                                            <td>{{$message->phone}}</td>
                                                            <td>{{$message->subject}}</td>
                                                            <td>{{$message->message}}</td>
                                                            <td>{{ Carbon\Carbon::parse($message->created_at)->formatLocalized('%A, %d %B %Y %H:%I:%S')}}</td>
                                                            <td width="150px">
                                                                <a href="#" class="btn btn-sm btn-success mx-1 btn-read" data-id="{{$message->id}}" id="btn-read-{{$message->id}}">Read</a>
                                                                <form method="post" enctype="multipart/form-data" action="{{url('manager/messages/'.$message->id)}}">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <input type="hidden" name="url" value="{{Request::url()}}">
                                                                    <button type="submit" class="btn btn-sm btn-red mx-1" onclick="return confirm('Are you sure?')">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                        @php $no=$no+1;@endphp
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            </form>
                                        </div>

                                        {{$messages->render()}}
                                    </div>
                                </div>

                            </div>


                        </div>
                    </section>
                </div>
            </div>

            <script>
                function checkAll(){
                    $('.sub_chk').each(function(){
                        $(this).attr('checked','checked');
                    })

                }

                $('.btn-read').on('click', function(){

                    var formData = {
                        _token: "{{ csrf_token() }}",
                        id: $(this).attr('data-id'),
                        url: "{{ Request::url() }}",
                    };

                    var id = $(this).attr('data-id');
                    
                    $('#btn-read-' + id).text('readed')
                    
                    $.ajax({
                        type: "POST",
                        url: "/manager/messages/" + id,
                        data: formData,
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        console.log(data);
                    });

                    event.preventDefault();
                })
            </script>

            @if(!empty(Session::get('status')) && Session::get('status') == 1)
                <script>
                    showNotif("{{Session::get('message')}}");
                </script>
            @endif


@stop
