@extends('admin.layout.template')

@section('content')

@php 
    $ordering = \Illuminate\Support\Facades\Request::get('ordering');
@endphp

<div class="heading-section">

    <h4>Daftar Code Voucher</h4>
    <a 
        href="
            @if(auth()->user()->account_role == 'manager')
                {{url('manager/vouchers/create')}}
            @elseif(auth()->user()->account_role == 'superadmin')
                {{url('superadmin/vouchers/create')}}
            @endif
        " 
        class="btn btn-blue pull-right"
    >Tambah Voucher</a>
    <div class="panel-menu-content">
        <div class="search">
            <form class="" action="@if(auth()->user()->account_role == 'manager'){{url('manager/voucher-code')}}@else{{url('manager/voucher-code')}}@endif">
                <div class="row">
                    <div class="col-2">
                        <select name="ordering" class="form-control">
                            <option value="">- Urutkan Berdasar -</option>
                            <option value="code" @if($ordering=="code") selected @endif>Code</option>
                            <option value="newest" @if($ordering=="newest") selected @endif>Terbaru</option>
                            <option value="oldest" @if($ordering=="oldest") selected @endif>Terlama</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <input type="text" name="search" class="search-input form-control"
                        placeholder="Search by code.." autocomplete="off">
                    </div>
                    <div class="col-1">
                        <button type="submit" class="btn btn-blue">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<br>

<script>
   
    $("#type-voucher").on('change', function () {
        var stat = $(this).val();

        if (stat == "nominal") {
            $("#input-percent").attr("disabled", "true");
            $("#input-nominal").removeAttr("disabled");
        } else if (stat == "percent") {
            $("#input-nominal").attr("disabled", "true");
            $("#input-percent").removeAttr("disabled");
        }
    })
</script>
<section class="panel">
    <header class="panel-heading">
        VOUCHER CODE
    </header>
    
    <div class="card-body">
        <div class="row">

            @foreach($voucher as $no => $vc)
            <div class="col-md-4 col-sm-4 col-xs-4">
                <div class="
                        @if($vc->category==1)
                        card-orange
                        @elseif($vc->category==2)
                        card-blue
                        @elseif($vc->category==3)
                        card-green
                        @endif">
                    <div class="bank-name" title="BestBank">{{$vc->code}}</div>

                    <div class="data">
                        <div class="pan">Type : {{$vc->type}}</div>
                        <div class="first-digits">Rp {{number_format($vc->nominal)}}</div>
                        <div class="pan">Kategori :
                            @if($vc->category==1)
                            Potongan
                            @elseif($vc->category==2)
                            Ongkos Kirim
                            @elseif($vc->category==3)
                            Refund
                            @endif
                        </div>
                        <div class="exp-date-wrapper">
                            <div class="left-label">Start : {{$vc->start_at}}</div>
                            <div class="left-label">Exp : {{$vc->end_at}} </div>
                            <div class="left-label">Digunakan : {{$vc->used}}</div>
                        </div>
                    </div>
                    <a 
                        href="
                            @if(auth()->user()->account_role == 'manager')
                                {{url('manager/vouchers/'.$vc->id)}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('superadmin/vouchers/'.$vc->id)}}
                            @endif
                        " 
                        class="btn btn-details btn-xs pull-right"
                    >
                        Details</a>
                </div>

            </div>
            @endforeach
        </div>
        {{$voucher->appends($_GET)->links()}}
    </div>
</section>
<style>
    .btn-details {
        background: #ffffff;
        color: red;
        margin-top: -15px;
    }

    @font-face {
        font-family: 'Iceland';
        font-style: normal;
        font-weight: 400;
        src: local('Iceland'), local('Iceland-Regular'), url(https://fonts.gstatic.com/s/iceland/v8/rax9HiuFsdMNOnWPaKtMBA.ttf) format('truetype');
    }

    @font-face {
        font-family: 'Open Sans';
        font-style: normal;
        font-weight: 400;
        src: local('Open Sans Regular'), local('OpenSans-Regular'), url(https://fonts.gstatic.com/s/opensans/v17/mem8YaGs126MiZpBA-UFVZ0e.ttf) format('truetype');
    }

    @font-face {
        font-family: 'Open Sans';
        font-style: normal;
        font-weight: 800;
        src: local('Open Sans ExtraBold'), local('OpenSans-ExtraBold'), url(https://fonts.gstatic.com/s/opensans/v17/mem5YaGs126MiZpBA-UN8rsOUuhs.ttf) format('truetype');
    }

    .card-orange {
        border: 2px solid #fc4a1a;
        color: #000;
        margin-bottom: 30px;
        -webkit-border-radius: 15px;
        -moz-border-radius: 15px;
        border-radius: 15px;
        padding: 30px;
    }

    .card-blue {
        color: #000;
        border: 2px solid #23A6D5;
        -webkit-animation: Gradient 15s ease infinite;
        -moz-animation: Gradient 15s ease infinite;
        animation: Gradient 15s ease infinite;
        margin-bottom: 30px;
        -webkit-border-radius: 15px;
        -moz-border-radius: 15px;
        border-radius: 15px;
        padding: 30px;
    }

    .card-green {
        color: #000;
        border: 2px solid #12cc29;
        -webkit-animation: Gradient 15s ease infinite;
        -moz-animation: Gradient 15s ease infinite;
        animation: Gradient 15s ease infinite;
        margin-bottom: 30px;
        -webkit-border-radius: 15px;
        -moz-border-radius: 15px;
        border-radius: 15px;
        padding: 30px;
    }

    .bank-name {
        float: right;

        font: 800 21px 'Open Sans', Arial, sans-serif;
    }
</style>
@if(!empty(Session::get('status')) && Session::get('status') == 1)
<script>
    showNotif("{{Session::get('message')}}");
</script>
@endif
<script>
    $(document).ready(function () {
        $('.input-amount').each(function () {
            $(this).val(formatAmount($(this).val()));
        });
    })
</script>
@endsection
