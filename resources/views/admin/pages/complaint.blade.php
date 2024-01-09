@extends('admin.layout.template')

@section('content')

            <div class="row">

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <section class="panel">
                        <header class="panel-heading">
                            Complaints
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
                                                        <option value="complaints" @if(!\Illuminate\Support\Facades\Request::get('status')) selected @endif>New</option>
                                                        <option value="?status=1" @if(\Illuminate\Support\Facades\Request::get('status') == '1') selected @endif>Completed</option>
                                                        <option value="?status=confirmed" @if(\Illuminate\Support\Facades\Request::get('status') == 'confirmed') selected @endif>Confirmed</option>
                                                        <option value="?status=rejected" @if(\Illuminate\Support\Facades\Request::get('status') == 'rejected') selected @endif>Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4"></div>
                                                <div class="col-md-6 my-1">
                                                    <form method="get" action="@if(auth()->user()->account_role == 'manager'){{url('manager/complaints')}}
                                                                                @elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/complaints')}}
                                                                                @elseif(auth()->user()->account_role == 'distributor'){{url('distributor/complaints')}}@endif">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="search" placeholder="Search...">
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
                                                    <tr  align="center">
                                                        <td>No</td>
                                                        <td>Customer</td>
                                                        <td>Invoice</td>
                                                        <td>Product</td>
                                                        <td>Qty</td>
                                                        <td>Option</td>
                                                        {{-- <td>File</td> --}}
                                                        <td>Time</td>
                                                        <td>Status</td>
                                                        <td>Action</td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        $no = ($complaints->currentpage() - 1) * $complaints->perPage() + 1;
                                                    @endphp
                                                    @foreach($complaints as $complaint)
                                                        <tr>
                                                            <td width="50px">{{$no}}</td>
                                                            <td>{{$complaint->user->name}}</td>
                                                            <td>{{$complaint->order->invoice}}</td>
                                                            <td>
                                                                {{-- @foreach ($complaint->order_details as $item)
                                                                    - {{ $item->product->name }} <br>
                                                                @endforeach --}}
                                                                {{$complaint->product->name}}
                                                            </td>
                                                            <td>{{$complaint->qty}}</td>
                                                            <td>{{$complaint->option}}</td>
                                                            {{-- <td><img src="{{asset($complaint->file)}}" alt="file" width="100"></td> --}}
                                                            <td>
                                                                created at : {{ Carbon\Carbon::parse($complaint->created_at)->formatLocalized('%A, %d %B %Y %H:%M:%S')}} <br>
                                                                @if($complaint->confirm_at != null && $complaint->status != null) 
                                                                    confirm at : {{ Carbon\Carbon::parse($complaint->confirm_at)->formatLocalized('%A, %d %B %Y %H:%M:%S')}} <br>
                                                                @else
                                                                @endif
                                                                @if($complaint->rejected_at != null && $complaint->status != null)
                                                                    reject at : {{ Carbon\Carbon::parse($complaint->rejected_at)->formatLocalized('%A, %d %B %Y %H:%M:%S')}} <br>
                                                                @else
                                                                @endif
                                                                @if($complaint->send_at != null && $complaint->status != null) 
                                                                    send at : {{ Carbon\Carbon::parse($complaint->send_at)->formatLocalized('%A, %d %B %Y %H:%M:%S')}}
                                                                @else
                                                                @endif
                                                            </td>
                                                            <td align="center">
                                                                @if($complaint->status == 'confirmed')
                                                                    <span class="status status-success">{{$complaint->status}}</span>
                                                                @elseif($complaint->status == 'rejected')
                                                                    <span class="status status-danger">{{$complaint->status}}</span>
                                                                @else
                                                                @endif
                                                            </td>
                                                            <td width="150px">
                                                                <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/complaint/'.$complaint->id)}}
                                                                            @elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/complaint/'.$complaint->id)}}
                                                                            @elseif(auth()->user()->account_role == 'distributor'){{url('distributor/complaint/'.$complaint->id)}}@endif" 
                                                                            class="btn btn-sm btn-success mx-1 my-1 btn-reply" data-id="{{$complaint->id}}" id="btn-reply-{{$complaint->id}}">Reply</a>
                                                                
                                                                @if($complaint->status == null)
                                                                    <a class="btn btn-sm btn-primary mx-1 my-1 btn-confirm" data-id="{{$complaint->id}}" id="btn-confirm-{{$complaint->id}}" style="color:white;">Confirm</a>
                                                                @else
                                                                @endif

                                                                @if($complaint->option == 'retur barang' && $complaint->status != 'sended' && $complaint->status != 'rejected' && $complaint->status != null)
                                                                    <a class="btn btn-sm btn-primary mx-1 my-1 btn-send" data-id="{{$complaint->id}}" id="btn-send-{{$complaint->id}}" onclick="return confirm('Send ?')">Send</a>
                                                                @elseif($complaint->status == 'sended')
                                                                @endif
                                                                
                                                                <form method="post" enctype="multipart/form-data" action="@if(auth()->user()->account_role == 'manager'){{url('manager/complaint/'.$complaint->id)}}
                                                                                                                            @elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/complaint/'.$complaint->id)}}
                                                                                                                            @elseif(auth()->user()->account_role == 'distributor'){{url('distributor/complaint/'.$complaint->id)}}@endif">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <input type="hidden" name="url" value="{{Request::url()}}">
                                                                    <button type="submit" class="btn btn-sm btn-red mx-1" onclick="return confirm('Are you sure?')">Delete</button>
                                                                    
                                                                    @if($complaint->status == null)
                                                                        <a class="btn btn-sm btn-warning mx-1 my-1 btn-reject" data-id="{{$complaint->id}}" id="btn-reject-{{$complaint->id}}" style="color:white;">Reject</a>
                                                                    @else
                                                                    @endif
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

                                        {{$complaints->render()}}
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

                $('.btn-reply').on('click', function(){

                    var formData = {
                        _token: "{{ csrf_token() }}",
                        id: $(this).attr('data-id'),
                        url: "{{ Request::url() }}",
                    };

                    var id = $(this).attr('data-id');
                    
                    $.ajax({
                        type: 'POST',
                        url: @if(auth()->user()->account_role == 'manager')
                                    '/manager/complaint/' + id
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    '/superadmin/complaint/' + id
                                @elseif(auth()->user()->account_role == 'distributor')
                                    '/distributor/complaint/' + id
                                @endif,
                        data: formData,
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        console.log(data);
                    });

                    // event.preventDefault();
                })

                $('.btn-confirm').on('click', function(){
                    if(confirm("Confirm complaint?")) {
                        var formData = {
                            _token: "{{ csrf_token() }}",
                            id: $(this).attr('data-id'),
                            url: "{{ Request::url() }}",
                        };

                        var id = $(this).attr('data-id');
                        // var url = "/manager/complaint/confirm/" + id,
                        $.ajax({
                            type: 'post',
                            url: @if(auth()->user()->account_role == 'manager')
                                    '/manager/complaint/confirm/' + id
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        '/superadmin/complaint/confirm/' + id
                                    @elseif(auth()->user()->account_role == 'distributor')
                                        '/distributor/complaint/confirm/' + id
                                    @endif,
                            data: formData,
                            dataType: "json",
                            encode: true,
                            error: function (error) {
                                console.log(error)
                                } 
                        }).done(function (data) {
                            console.log(data);
                            location.reload();
                        });
                    } else {
                        return false;
                    }
                })

                $('.btn-reject').on('click', function(){
                    if(confirm("Reject complaint?")) {
                        var formData = {
                            _token: "{{ csrf_token() }}",
                            id: $(this).attr('data-id'),
                            url: "{{ Request::url() }}",
                        };

                        var id = $(this).attr('data-id');
                        // var url = "/manager/complaint/confirm/" + id,

                        $.ajax({
                            type: 'POST',
                            url: @if(auth()->user()->account_role == 'manager')
                                    '/manager/complaint/reject/' + id
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        '/superadmin/complaint/reject/' + id
                                    @elseif(auth()->user()->account_role == 'distributor')
                                        '/distributor/complaint/reject/' + id
                                    @endif,
                            data: formData,
                            dataType: "json",
                            encode: true,
                        }).done(function (data) {
                            console.log(data);
                            location.reload();
                        });
                    } else {
                        return false;
                    }
                })

                $('.btn-send').on('click', function(){

                    var formData = {
                        _token: "{{ csrf_token() }}",
                        id: $(this).attr('data-id'),
                        url: "{{ Request::url() }}",
                    };

                    var id = $(this).attr('data-id');
                    // var url = "/manager/complaint/confirm/" + id,

                    $.ajax({
                        type: 'POST',
                            url: @if(auth()->user()->account_role == 'manager')
                                    '/manager/complaint/sendStuff/' + id
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        '/superadmin/complaint/sendStuff/' + id
                                    @elseif(auth()->user()->account_role == 'distributor')
                                        '/distributor/complaint/sendStuff/' + id
                                    @endif,
                        data: formData,
                        dataType: "json",
                        encode: true,
                    }).done(function (data) {
                        console.log(data);
                        location.reload();
                    });
                })
            </script>

            @if(!empty(Session::get('status')) && Session::get('status') == 1)
                <script>
                    showNotif("{{Session::get('message')}}");
                </script>
            @endif


@stop