@extends('admin.layout.template')

@section('content')
    <a href="@if(auth()->user()->account_role == 'manager'){{url('manager/promo')}}@else{{url('manager/promo')}}@endif" class="btn btn-blue"><span class="fa fa-arrow-left"></span> Kembali</a>
    <br><br>

    <section class="panel" >
        <header class="panel-heading">
            <h3>Detail Promo</h3>
        </header>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 col-sm-4 col-xs-4">
                    {{-- <img src="http://localhost:8000/no-images.png" width="400px"> --}}
                    @if (!file_exists( public_path() . $promo->banner))
                        <img src="/no-images.png" width="400px">
                    @elseif (file_exists( public_path() . $promo->banner))
                        <img src="{{ $promo->banner }}" width="400px">
                    @endif
                    {{-- <img src="https://muliaputramandiri.com/assets/images/Packshot/Deltomed/ANTANGIN%20PERMEN%20ANEKA%20RASA%20B50.jpg" class="img-fluid" width="400px"> --}}
                </div>  

                <div class="col-md-4 col-sm-4 col-xs-4">
                    <h4>Informasi Promo</h4>
                    <br>
                    <p><b>Title</b>  : {{ $promo->title }}</p>
                    <p><b>Description</b>  : {{ $promo->description }}</p>
                    <p><b>Highlight</b>  : {!! $promo->highlight !!}</p>
                    <p><b>Term & Condition</b> :   
                        @if($promo->termcondition == 1)
                            @if($promo->detail_termcondition == 1)
                                min : {{$promo->min_qty}} item
                                <br>
                            @else
                                min : 
                                @foreach($promo->sku as $row_sku)
                                    {{$row_sku->product->name}} ( {{$row_sku->min_qty}} {{$row_sku->satuan}} )
                                    <br>
                                @endforeach
                            @endif
                        @elseif($promo->termcondition == 2)
                            min : Rp. {{number_format($promo->min_transaction)}}
                        @else
                            @if($promo->detail_termcondition == 1)
                                min : {{$promo->min_qty}} item
                                <br>
                            @else
                                min : 
                                @foreach($promo->sku as $row_sku)
                                    {{$row_sku->product->name}} ( {{$row_sku->min_qty}} {{$row_sku->satuan}} )
                                    <br>
                                @endforeach
                            @endif
                            min : Rp. {{number_format($promo->min_transaction)}}
                        @endif
                    </p>
                    <p><b>Category</b> : 
                        @if($promo->category == 1)
                            Potongan Harga
                        @elseif($promo->category == 2)
                            Bonus Product
                        @else
                            Potongan Harga & Bonus Product
                        @endif
                    </p>
                    <p><b>Promo Mulai</b> : {{ Carbon\Carbon::parse($promo->start)->formatLocalized('%A, %d %B %Y')}}</p>
                    <p><b>Promo Berakhir</b> : {{ Carbon\Carbon::parse($promo->end)->formatLocalized('%A, %d %B %Y')}}</p>
                    <br><br>
                    <h3><b>Reward</b> :
                        @if($promo->category == 1) 
                            @foreach ($promo->reward as $reward_data)
                                @if($reward_data->reward_disc != null)
                                    Diskon {{$reward_data->reward_disc}}%
                                @elseif($reward_data->reward_nominal != null)
                                    Potongan Rp. {{number_format($reward_data->reward_nominal)}}
                                @elseif($reward_data->reward_point != null)
                                    {{$reward_data->reward_point}} Point
                                @endif
                            @endforeach
                        @elseif($promo->category == 2)
                            @foreach ($promo->reward as $reward_data)
                                - {{$reward_data->product->name}} ({{$reward_data->reward_qty}} {{$reward_data->satuan}})                                                
                            @endforeach
                        @else
                            @foreach ($promo->reward as $reward_data)
                                @if($reward_data->reward_disc != null)
                                    Diskon {{$reward_data->reward_disc}}%
                                @elseif($reward_data->reward_nominal != null)
                                    Potongan Rp. {{number_format($reward_data->reward_nominal)}}
                                @elseif($reward_data->reward_point != null)
                                    {{$reward_data->reward_point}} Point
                                @elseif($reward_data->reward_product_id != null)
                                    {{$reward_data->product->name}} ({{$reward_data->reward_qty}} {{$reward_data->satuan}})                                                
                                @endif
                                <br>
                            @endforeach
                        @endif
                    </h3>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <h4 style="margin-top:20px;">List Product</h4>
                    <div style="border: 1px solid #f1f1f1; padding: 15px">
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td width="10">No</td>
                                    <td>Name</td>
                                    <td>Brand</td>
                                    <td width="10">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sku as $row)
                                    <tr align="center">
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$row->product->name}}</td>
                                        <td>{{ucwords(strtolower($row->product->brand))}}</td>
                                        <td>
                                            <a href="                       
                                            @if(auth()->user()->account_role == "manager")
                                                {{url('manager/products/'.$row->product_id.'?slug='.$row->product->slug)}}
                                            @elseif(auth()->user()->account_role == "superadmin")
                                                {{url('superadmin/products/'.$row->product_id.'?slug='.$row->product->slug)}}
                                            @elseif(auth()->user()->account_role == "admin")
                                                {{url('admin/products/'.$row->product_id.'?slug='.$row->product->slug)}}
                                            @elseif(auth()->user()->account_role == "distributor")
                                                {{url('distributor/products/'.$row->product_id.'?slug='.$row->product->slug)}}
                                            @endif
                                            " class="btn btn-blue btn-sm">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{$sku->links()}}
                    </div>
                    <br>
                </div>
            </div>
        </div>
    </section>
@stop