@extends('admin.layout.template')

@section('content')

<a 
    href="
        @if(auth()->user()->account_role == 'manager')
            {{url('/manager/vouchers')}}
        @elseif(auth()->user()->account_role == 'admin')
            {{url('/superadmin/vouchers')}}
        @endif
    " 
    class="btn btn-blue"
><span class="fa fa-arrow-left"></span> &nbsp Kembali</a>
<br><br>
<section class="panel" id="create-new-voucher">
<div class="card-body" >
    <h3>My Voucher</h3>
    <hr>
        <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-4">
            <form action="@if(auth()->user()->account_role == 'manager'){{url('/manager/vouchers/'.$voucher->id)}}@else{{url('/superadmin/vouchers/'.$voucher->id)}}@endif" method="post" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                @method('put')
                <div class="form-group">
                    <label>CODE</label>
                    <input type="hidden" name="id" value="{{$voucher->id}}">
                    <input type="text" class="form-control" placeholder="CODE" value="{{$voucher->code}}" style="text-transform: uppercase" name="code" required>
                </div>
                <div class="form-group">
                    <label>Type Voucher</label>
                    <select class="form-control" name="type" id="type-voucher">
                        <option value="percent" @if($voucher->type=="percent") selected @endif>Percent</option>
                        <option value="nominal"  @if($voucher->type=="nominal") selected @endif>Nominal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Percent</label>
                    <input type="text" class="form-control" value="{{$voucher->percent}}" id="input-percent" placeholder="contoh : 10%" name="percent">
                </div>
                <div class="form-group">
                    <label>Nominal Potongan</label>
                    <input type="text" class="form-control input-amount" value="{{$voucher->nominal}}" id="input-nominal" placeholder="contoh : 30000" name="nominal">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select class="form-control" name="category">
                        <option value="1" @if($voucher->category=="potongan") selected @endif>Potongan</option>
                        <option value="2" @if($voucher->category=="ongkir") selected @endif>Ongkir</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea class="form-control" placeholder="Potongan apa gitu?" name="description">{{$voucher->description}}</textarea>
                </div>
                <div class="form-group">
                    <label>Term & Condition</label>
                    <textarea class="form-control" placeholder="Termandcondition"
                        name="termandcondition"></textarea>
                </div>
                <div class="form-group">
                    <label>Potongan maksimal Untuk voucher dalam bentuk Percent</label>
                    <input type="text" class="form-control input-amount" placeholder="Potongan Maksimal" value="{{$voucher->max_nominal}}"  name="max_nominal">
                </div>
                <div class="form-group">
                    <label>Penggunaan Maksimal Voucher</label>
                    <input type="number" class="form-control" placeholder="Pengguna Maksimal" value="{{$voucher->max_use}}"  name="max_use">
                </div>
                <div class="form-group">
                    <label>Maksimal Penggunaan Per User</label>
                    <input type="number" class="form-control" placeholder="Pengguna Maksimal Tiap User"  value="{{$voucher->max_use_user}}"  name="max_use_user">
                </div>
                <div class="form-group">
                    <label>Penggunaan Harian</label>
                    <input type="number" class="form-control" placeholder="Pengguna Harian Tiap User" value="{{$voucher->daily_use}}"  name="daily_use">
                </div>
                <div class="form-group">
                    <label>Minimal Transaksi</label>
                    <input type="text" class="form-control input-amount" placeholder="Transaksi Minimal" value="{{$voucher->min_transaction}}"  name="min_transaction">
                </div>
                <div class="form-group">
                    <label>Maximal Transaksi</label>
                    <input type="text" class="form-control input-amount" placeholder="Transaksi Maximal" value="{{$voucher->max_transaction}}"  name="max_transaction">
                </div>
                <div class="form-group">
                    <label>Start</label>
                    <div class="row">
                        <div class="col-md-7"><input class="form-control datepicker" value="{{substr($voucher->start_at, 0, 10)}}" placeholder="2018-08-02" name="start_at"></div>
                        <div class="col-md-5"><input type="time" class="form-control" value="{{substr($voucher->start_at, 11, 16)}}" name="time_start_at"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>End</label>
                    <div class="row">
                        <div class="col-md-7"><input class="form-control datepicker" value="{{substr($voucher->end_at, 0, 10)}}" placeholder="2018-08-02" name="end_at"></div>
                        <div class="col-md-5"><input type="time" class="form-control" value="{{substr($voucher->end_at, 11, 16)}}" name="time_end_at"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Jarak Maksimal</label>
                    <input type="number" class="form-control" value="{{$voucher->max_distance}}" placeholder="max distance" name="max_distance" >
                </div>
                <div class="form-group">
                    <label>Jarak Minimal</label>
                    <input type="number" class="form-control" value="{{$voucher->min_distance}}" placeholder="min distance" name="min_distance">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="status" value="1" @if($voucher->status=="1") checked @endif>
                            Aktif
                        </label><br>
                        <label class="form-check-label" >
                            <input class="form-check-input" type="radio" name="status" value="0" @if($voucher->status=="0") checked @endif>
                            Tidak Aktif
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>File</label>
                    <input type="file" class="form-control" name="file">
                    <img src="{{ asset($voucher->file) }}" alt="">
                </div>

                <div class="form-group">
                    <div class="name-on-card">Created by : {{\App\User::find($voucher->admin_id)->first_name}}</div>
                </div>
                <div class="form-group">
                    <hr>
                    @if($voucher->user==0)
                    <button type="submit" class="btn btn-blue">Update Voucher</button>
                    @endif
                </div> 
                
            </form>
            <hr>
        </div>

        <div class="col-md-8 col-sm-8 col-xs-8">
            
            <table class="table default-table">
                <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">ID</th>
                    <th class="text-center">Tanggal Order</th>
                    <th class="text-center">Detail</th>
                </tr>
                </thead>
                <tbody>        
                @foreach($order as $no => $ord)
                    <tr>
                         <td>{{$loop->iteration+($order->currentpage() - 1) * $order->perPage()}}</td>
                        <td><a href="@if(auth()->user()->account_role == 'manager'){{url('manager/order-detail/'.$ord->id)}}@else{{url('superadmin/order-detail/'.$ord->id)}}@endif">{{$ord->id}}</a></td>
                        <td>{{$ord->order_time}}</td>
                        <td>{{$ord->name}}<br>
                            {{$ord->address}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <br><br>
            {{$order->render()}}
        </div>
    </div>
</div>
</section>

<script>

    $("#type-voucher").on('change', function(){
        var stat = $(this).val();
        if(stat=="nominal"){
            $("#input-percent").attr("disabled", "true");
            $("#input-nominal").removeAttr("disabled");
        }else if(stat=="percent"){
            $("#input-nominal").attr("disabled","true");
            $("#input-percent").removeAttr("disabled");
        }
    })
    $(document).ready(function(){
        $( '.input-amount' ).each( function() {
            $( this ).val( formatAmount( $( this ).val() ) );
        });
    })

</script>

@endsection


@section('scripts')

<script>

var coupon_id = "{{$voucher->id}}";


</script>

<style>
    .btn-remove-to{
        background-color: #f44336;
        border: 1px solid #f44336;
        margin: 3px 10px 3px 0px;
    }

    .btn-add-to{
        background-color: #4CAF50;
        border: 1px solid #4CAF50;
        margin: 3px 10px 3px 0px;
    }
</style>

@endsection
