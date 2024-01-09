@extends('admin.layout.template')

@section('content')

            <div class="row">

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <section class="panel">
                        <header class="panel-heading">
                            Contact Mail
                        </header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-12 col-sm-12 col-xs-12">
                                    <div class="table-outer">
                                        <div class="table-inner">
                                            <form method="post" action="{{url('admin/delete-selected-contact')}}">
                                                @csrf

                                                <button type="submit" class="btn btn-blue" onclick="return confirm('Delete all selected data?')">Delete All Selected </button><br><br>
                                            <table class="table default-table dataTable">
                                                <thead>
                                                <tr  align="center">
                                                    <td><a href="javascript:void(0)" class="btn btn-blue btn-xs" onclick="return checkAll();"><span class="fa fa-check-square-o"></span></a></td>
                                                    <td>No</td>
                                                    <td>Name</td>
                                                    <td>Email</td>
                                                    <td>Subject</td>
                                                    <td>Type</td>
                                                    <td>Message</td>
                                                    <td>Time</td>
                                                    <td>Action</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $no = ($contact->currentpage() - 1) * $contact->perPage() + 1;
                                                @endphp
                                                @foreach($contact as $u)
                                                    <tr>
                                                        <td><input type="checkbox" name="selected[]" class="sub_chk" value="{{$u->id}}"></td>
                                                        <td width="50px">{{$no}}</td>
                                                        <td>{{$u->name}}</td>
                                                        <td>{{$u->email}}</td>
                                                        <td>{{$u->subject}}</td>
                                                        <td>{{$u->type}}</td>
                                                        <td>
                                                            @if($u->type=='career')
                                                            <a href="{{asset('uploads/file/'.$u->files)}}" target="_blank">Download Files</a>
                                                            @else
                                                            {{$u->message}}
                                                            @endif
                                                        </td>
                                                        <td>{{ Carbon\Carbon::parse($u->contact_time)->formatLocalized('%A, %d %B %Y %H:%I:%S')}}</td>
                                                        <td width="150px">
                                                            <a href="{{url('admin/delete-contact/'.$u->id)}}" class="btn btn-red btn-xs">Delete</a>
                                                        </td>
                                                    </tr>
                                                    @php $no=$no+1;@endphp
                                                @endforeach
                                                </tbody>
                                            </table>
                                            </form>
                                        </div>

                                        {{$contact->render()}}
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
            </script>

            @if(!empty(Session::get('status')) && Session::get('status') == 1)
                <script>
                    showNotif("{{Session::get('message')}}");
                </script>
            @endif


@stop
