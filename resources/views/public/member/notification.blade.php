@extends('public.layout.member-layout')

@section('member-content')
<div class="page-member-title">
    <h3>Notification</h3>
</div>

	<div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12  ">
            <section class="panel">
                <div class="panel-body">

                    <div class="row">
                        <div class="col-12 col-md-12 col-sm-12 col-xs-12  ">
                            <div class="table-outer">
                                <div class="table-inner">

                                            @php
                                                $notif_count = 0;
                                                $log = new \App\Log();
                                            @endphp

                                            @foreach($notification as $notif)

                                            @if($notif->table_name=="orders")
                                                @php
                                                    $transaction = App\Order::find($notif->table_id);
                                                @endphp

                                                @if(isset($transaction))
                                                        <div class="list-notif">
                                                                <h6 class="notif-date" title="{{$notif->log_time}}">{{$log->time_elapsed_string($notif->log_time)}}</h6>
                                                                <b><a href="{{url('member/open-notification/'.$notif->id)}}">#{{$transaction->id}}</a> <span style="text-transform: capitalize">{{$notif->activity}}</span> - {{$transaction->data_user->name}}</b>
                                                        </div>
                                                @endif
                                            @endif

                                            @php $notif_count++; @endphp

                                            @endforeach

                                            <input type="hidden" id="notif-count" value="{{$notif_count}}">


                                    <style>
                                        .list-notif{
                                            position: relative;
                                            border: 1px solid #f1f1f1;
                                            padding: 5px;
                                            padding-left: 15px;
                                            padding-right: 15px;
                                            width: 100%;
                                            -webkit-border-radius: 8px;
                                            -moz-border-radius: 8px;
                                            border-radius: 8px;
                                            border-left: 3px solid #05ab08;
                                            margin-bottom: 5px;
                                        }

                                        .list-notif b{
                                            font-size: 10pt;
                                        }

                                        .new-notif{
                                            margin: 10px;
                                            position: absolute;
                                            right: 0;
                                            top: 0;
                                            width: 8px;
                                            height: 8px;
                                            background: red;
                                            border-radius: 8px;
                                        }

                                        h6.notif-date{
                                            margin-top: 10px;
                                            color: #7d7d7d;
                                            font-size: 8pt;
                                        }
                                    </style>

                                </div>
                            </div>

                            {{$notification->render()}}
                        </div>
                    </div>
                </div>
        	</section>
        </div>
    </div>

@endsection
