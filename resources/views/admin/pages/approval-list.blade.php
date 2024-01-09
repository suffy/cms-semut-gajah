@extends('admin.layout.template')

@section('content')
<section class="panel">
    <header class="panel-heading">
        Customers Register
    </header>

    <div class="card-body">

        <div class="col-search">
            <form method="get" action="
                @if(auth()->user()->account_role == 'manager')
                    {{url('manager/customers/approval')}}
                @elseif(auth()->user()->account_role == 'superadmin')
                    {{url('superadmin/customers/approval')}}
                @elseif(auth()->user()->account_role == 'admin')
                    {{url('admin/customers/approval')}}
                @elseif(auth()->user()->account_role == 'distributor')
                    {{url('distributor/customers/approval')}}
                @endif">
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

        <div class="row">
            <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                <div class="table-outer table-responsive">
                    <div class="table-inner">
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>Tanggal</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Phone</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{date('d M Y', strtotime($user->created_at))}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->phone}}</td>
                                    <td width="50px">
                                        <a href="
                                                @if(auth()->user()->account_role == 'manager')
                                                    {{url('manager/approval-detail/' . $user->id)}}
                                                @elseif(auth()->user()->account_role == 'superadmin')
                                                    {{url('superadmin/approval-detail/' . $user->id)}}
                                                @elseif(auth()->user()->account_role == 'admin')
                                                    {{url('admin/approval-detail/' . $user->id)}}
                                                @elseif(auth()->user()->account_role == 'distributor')
                                                    {{url('distributor/approval-detail/' . $user->id)}}
                                                @endif
                                            " data-id="" data-name="" data-email=""
                                            class="btn btn-primary btn-sm button-edit-user"><i class="fa fa-eye"></i>
                                            Detail</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{$users->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection