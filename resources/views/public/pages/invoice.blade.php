@extends('public.layout.template')

@section('content')
    @php
        $user = Auth::user();
    @endphp
    <div class="bread-crumb mb-5">
        <div class="container">
            <a href="{{ url('') }}" class="col-blue">Home</a>
            <span>/&nbsp;Invoice</span>
        </div>
    </div>

    <section id="sakura-checkout" class="html">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <h4 class="text-upper mb-3">Invoice</h4>

                    <div class="row" id="loading-address" style="display: none">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="box-border">
                                <div class="text-center">
                                    <img src="{{asset('loading.gif')}}" width="100px" style="margin:auto"><br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5>Alamat Pengiriman</h5>
                    <div class="box-address">
                        <p class="font-weight-bold">+data_add[i].address_name+utama</p>
                        <p>+data_add[i].address_phone<br>
                        data_add[i].address<br>
                        data_add[i].kecamatan, data_add[i].kota, data_add[i].provinsi</p>
                    </div>
                    <hr>
                    <h5>Rincian Pesanan</h5>
                    <div class="box-address">
                        <p class="font-weight-bold">No. Pesanan<span class="pull-right">ajskdhk</span></p>
                        <div class="product mb-2">
                            <!-- looping -->
                            @php
                                $total = 0;
                                $weight = 0;
                            @endphp
                            @foreach($cart as $row)
                            <a href="">
                                <div class="row">
                                    <div class="col-2">
                                        <img src="{{ asset($row->data_product->image) }}" alt="" class="w-100 p-1" style="border: solid 1px #ddd;">
                                    </div>
                                    <div class="col-8 fs-8">
                                        {{$row->data_product->name}} <br>
                                        Rp. {{number_format($row->price)}} | {{$row->qty}} Item
                                    </div>
                                </div>
                            </a>
                            @php 
                                $total = $total + ((float)$row->price*(float)$row->qty);
                                $weight = $weight + ((float)$row->data_product->weight*(float)$row->qty);
                            @endphp
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div style="border: solid 1px #ddd">
                        <div class="bg-grey p-3 font-weight-bold" style="border-bottom: solid 1px #ddd">Status Pengiriman<span class="pull-right"><a href="#">Lacak >></a></span></div>
                        <div class="ringkasan p-3">
                        <p>Kota Yogyakarta</p>
                        <p class="font-weight-bold">JNE : Resi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection