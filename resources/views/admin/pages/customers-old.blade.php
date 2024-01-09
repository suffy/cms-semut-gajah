@extends('admin.layout.template')

@section('content')

    <section class="panel">
        <header class="panel-heading">
            User
        </header>

        <div class="card-body">

            <div class="row">
                <div class="col-md-2 col-sm-3 col-xs-6">
                    <div class="box-border">
                        <div class="card-body">
                            @php 
                                $user_all = App\User::where('account_type', '4')->count();
                            @endphp
                            Total<br>
                            <h3>{{$user_all}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-3 col-xs-6">
                    <div class="box-border">
                        <div class="card-body">
                            Aktif<br>
                            @php 
                                $user_all = App\User::where('account_type', '4')->where('account_status', '1')->count();
                            @endphp
                            <h3>{{$user_all}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-3 col-xs-6">
                    <div class="box-border">
                        <div class="card-body">
                            Tidak Aktif<br>
                            @php 
                                $user_all = App\User::where('account_type', '4')->where('account_status', '0')->count();
                            @endphp
                            <h3>{{$user_all}}</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="table-outer">
                        <div class="table-inner">
                            {{-- <a href="#" data-toggle="modal" data-target="#newUser" class="btn btn-blue">New User</a><br><br> --}}
                            <table class="table default-table dataTable">
                                <thead>
                                <tr  align="center">
                                    <td>No</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Created At</td>
                                    <td>Last Login At</td>
                                    <td>Orders</td>
                                    <td>Completed</td>
                                    <td>Status</td>
                                    <td>Action</td>
                                </tr>
                                </thead>
                                <tbody>
                                @php $no=1;@endphp
                                @foreach($customers as $u)
                                    <tr>
                                        <td width="50px">{{$no}}</td>
                                        <td>{{$u->name}}</td>
                                        <td>{{$u->email}}</td>
                                        <td>{{ Carbon\Carbon::parse($u->created_at)->formatLocalized('%A, %d %B %Y %H:%I:%S')}}</td>
                                        <td>@if(isset($u->last_login)){{ Carbon\Carbon::parse($u->last_login)->formatLocalized('%A, %d %B %Y %H:%I:%S')}}@else new account @endif</td>
                                        <td>
                                            @php $count = App\Order::where('customer_id', $u->id)->count(); @endphp
                                            {{$count}}
                                        </td>
                                        <td>
                                            @php $count = App\Order::where('customer_id', $u->id)->where('status', '4')->count(); @endphp
                                            {{$count}}
                                        </td>
                                        <td width="70px">
                                        @if($u->account_status==1)
                                            <div class="status status-success"><i class="fa fa-check"></i> Aktif</div>
                                        @elseif($u->account_status==0)
                                            <div class="status status-info"><i class="fa fa-clock-o"></i> Baru</div>
                                        @elseif($u->account_status==3)
                                            <div class="status status-danger"><i class="fa fa-close red"></i> Banned</div>
                                        @endif
                                        </td>
                                        <td width="50px">
                                            <a href="{{url('admin/customer-detail', $u->id)}}" data-id="{{$u->id}}" data-name="{{$u->name}}" data-email="{{$u->email}}" class="btn btn-green btn-sm button-edit-user">Detail</a>
                                        </td>
                                    </tr>
                                    @php $no=$no+1;@endphp
                                @endforeach
                                </tbody>
                            </table>

                            {{$customers->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </section>


    <!-- Modal -->
    <div id="newUser" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New User</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{url('admin/store-user')}}" method="post" enctype="multipart/form-data">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Name</label>
                            <div class="col-sm-12">
                                @csrf
                                <input type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                <input type="hidden" name="url"  value="{{Request::url()}}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Email</label>
                            <div class="col-sm-12">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Password</label>
                            <div class="col-sm-12">
                                <input type="password" name="password" class="form-control" placeholder="Password" value="" required>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Save User</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>


@stop
