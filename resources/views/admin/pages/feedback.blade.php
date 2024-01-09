@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<section class="panel" id="listPage">
    <header class="panel-heading">
        Feedback
    </header>
    <div class="card-body">
        
        <div class="card-body">
            <form method="get" action="
                @if($account_role == 'manager')
                    {{url('manager/feedbacks')}}
                @elseif($account_role == 'superadmin')
                    {{url('superadmin/feedbacks')}}
                @endif
            ">
                <div class="row">
                    <div class="col-sm-4 col-md-4 col-lg-2">
                        {{-- <input type="date" name="search" class="search-input form-control"
                        placeholder="Date start..." autocomplete="off"> --}}
                        <input type="text" name="start" placeholder="Date Start..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-2">
                        {{-- <input type="date" name="search" class="search-input form-control"
                        placeholder="Date end..." autocomplete="off"> --}}
                        <input type="text" name="end" placeholder="Date End..." class="search-input form-control" onfocus="(this.type='date')" onblur="(this.type='text')" >
                    </div>
                    <div class="col-1">
                        <button type="submit" class="btn btn-blue">Filter</button>
                    </div>
                </form>
                    {{-- <div class="col-md-1"></div> --}}
                    <div class="col-md-12 col-lg-6 ml-auto">
                        <form method="get" action="
                            @if($account_role == 'manager')
                                {{url('manager/feedbacks')}}
                            @elseif($account_role == 'superadmin')
                                {{url('superadmin/feedbacks')}}
                            @endif
                        ">
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
            </div>
            <div class="table-responsive">
                <div class="scroll-table-outer">
                    <div class="scroll-table-inner card-body">
                    
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Nama</td>
                                    <td>Pesan Feedback</td>
                                    <td>Tanggal</td>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($feedbacks as $row)
                                <tr align="center">
                                    <td>{{$loop->iteration}}</td>
                                    <td class="align-middle">{{$row->user->name}}</td>
                                    <td class="align-middle">{{$row->message}}</td>
                                    <td class="align-middle">{{date('d M Y H:i:s', strtotime($row->created_at))}}</td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $feedbacks->appends(Request::all())->links() }}
                </div>
            </div>
     
    </div>
</section>
@stop