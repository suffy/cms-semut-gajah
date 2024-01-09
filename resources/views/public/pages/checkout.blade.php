@extends('public.layout.template')

@section('content')
    @php
        $user = Auth::user();
        $address = App\UserAddress::where('user_id', Auth::user()->id)
                                    ->orderBy('id','asc')->get();
    @endphp
    <div class="bread-crumb mb-5">
        <div class="container">
            <a href="{{ url('cart') }}" class="col-blue">Shopping Cart</a>
            <span>&nbsp;/&nbsp;Checkout</span>
        </div>
    </div>

    <section id="sakura-checkout" class="html">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <h4 class="text-upper mb-3">Checkout</h4>

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
                    
                    <br>
                    
                    <div id="selected-address">
                        
                    </div>
                    <hr>
                    
                    <div class="row">
                        <div class="col-6">
                            <a href="#changeAddress" data-toggle="modal" data-dismiss="modal" class="btn button-white w-100">Pilih Alamat Lain</a>
                        </div>
                        <div class="col-6">
                            {{-- <a href="{{url('member/address')}}" class="btn button-blue w-100">Tambah Alamat Baru</a> --}}
                            <a href="#addAddress" data-dismiss="modal" data-toggle="modal" class="btn button-blue w-100">Tambah Alamat Baru</a>
                        </div>
                    </div>
                    <br><br>
                </div>

                <div class="col-md-5">
                    <div style="border: solid 1px #ddd">
                    
                            
                        <div class="bg-grey p-3 font-weight-bold" style="border-bottom: solid 1px #ddd">Ringkasan Belanja</div>
                        <div class="ringkasan p-3">
                            <div class="product mb-2">
                                <!-- looping -->
                                @php 
                                    $total = 0;
                                    $weight = 0;
                                @endphp
                                @foreach($cart as $row)

                                @if($row->data_product)
                        
                       
                                <div class="row">
                                    <div class="col-2">
                                        <img src="{{ asset($row->data_product->image) }}" alt="" class="w-100 p-1" style="border: solid 1px #ddd;">
                                    </div>
                                    <div class="col-8 fs-8">
                                        {{$row->data_product->name}} <br>
                                        Rp. {{number_format($row->price, 2, ',', '.')}} | {{$row->qty}} Item
                                    </div>
                                </div>
                                @php 
                                    $total = $total + ((float)$row->price*(float)$row->qty);
                                    $weight = $weight + ((float)$row->data_product->weight*(float)$row->qty);
                                @endphp

                                @else
                                <div class="row">
                                    <div class="col-2">
                                        
                                    </div>
                                    <div class="col-8 fs-8">
                                        Produk sudah tidak tersedia <br>
                                        Rp. {{number_format($row->price, 2, ',', '.')}} | {{$row->qty}} Item
                                    </div>
                                </div>

                                @endif
                                @endforeach
                            </div>
                            <hr>
                                @php 
                                    $weight = (float)$weight * 1000;
                                @endphp
                            <div class="text-right">Total Belanja : Rp. {{number_format($total, 2, ',', '.')}}</div>
                            <div class="text-right">Berat : {{number_format($weight, 2)}} gram</div>
                            <div class="clearfix"></div>

                            <div class="row" id="loading-ongkir" style="display: none">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="box-border">
                                        <div class="text-center">
                                            <img src="{{asset('loading.gif')}}" width="100px" style="margin:auto"><br>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="post" action="{{url('member/create-order')}}">
                                @csrf
                            <div class="shipping">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label>Pilih Jasa Pengiriman</label>
                                            <select class="form-control" name="courier" id="courier" required>
                                                <option value="0">- Pilih Pengiriman -</option>
                                                <option value="cod">COD / Bayar Ditempat</option>
                                                <option value="jne">JNE</option>
                                                <option value="pos">POS</option>
                                                <option value="tiki">Tiki</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Durasi</label>
                                            <select class="form-control" name="ongkir" id="duration" required>
                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Catatan Tambahan</label>
                                            <textarea type="text" name="notes" class="form-control" placeholder="Catatan"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="pull-left">
                                    <div id="label-courier">Biaya Pengiriman</div>
                                </div>
                                <div class="pull-right">
                                    <div id="label-price">Rp</div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <hr>
                            <div class="pull-left">
                                Sub Total
                            </div>
                            <div class="pull-right">
                                <div id="label-sub-total">Rp {{number_format($total, 2, ',', '.')}}</div>
                            </div>
                            <div class="clearfix"></div>
                            <hr>
                            <h4>Voucher</h4>
                            <div class="row">
                                <div class="col-9">
                                    <input id="voucher-code" name="voucher-code" value="" class="form-control" placeholder="Masukkan kode voucher jika ada">
                                    <a href="javascript:void(0)" onclick="return resetVoucher()" style="font-size: 8pt; color: red">Reset voucher</a>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-default btn-sm btn-search-voucher">Gunakan</button>
                                </div>
                            </div>
                            <div id="voucher-label"></div>
                            <div class="pull-left">
                                Discount
                            </div>
                            <div class="pull-right">
                                <div id="label-discount">Rp 0</div>
                            </div>
                            <div class="clearfix"></div>
                            <hr>
                            <h4>Pembayaran Final</h4>
                            <div id="label-payment-final">Rp {{number_format($total, 2, ',', '.')}}</div>
                            <div class="clearfix"></div>
                            <hr>

                            <input type="hidden" id="user-id" name="user-id" value="{{$user->id}}">
                            <input type="hidden" id="sub-total" name="sub-total" value="{{$total}}">
                            <input type="hidden" id="payment-total" name="payment-total" value="{{$total}}">
                            <input type="hidden" id="payment-final" name="payment-final" value="{{$total}}">
                            <input type="hidden" id="delivery-fee" name="delivery-fee" value="0">
                            <input type="hidden" id="payment-discount" name="payment-discount" value="0">
                            <input type="hidden" id="voucher-id" name="voucher-id" value="">
                            <input type="hidden" id="form-weight" name="weight" value="{{$weight}}">
                            <input type="hidden" id="selected-destination-id" name="destination-id" value="">
                            <input type="hidden" id="selected-address-id" name="address-id" value="">
                            <button type="submit" class="btn button-blue w-100 text-upper mt-4" >Proses Sekarang</button>
                        </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="addAddress" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Address Information</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <form action="{{url('member/checkout-new-address')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_id"  value="{{$user->id}}" required>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Nama</label>
                            <div class="col-md-9">
                                <input type="text" name="name" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Label Alamat</label>
                            <div class="col-md-9">
                                <input type="text" name="address_name" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Alamat</label>
                            <div class="col-md-9">
                                <input type="text" name="address" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Telp Penerima</label>
                            <div class="col-md-9">
                                <input type="text" name="address_phone" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Provinsi</label>
                            <div class="col-md-9">
                                @php
                                    $provinsi = \App\LocalProvince::all();
                                @endphp
                                <select name="provinsi" id="selected-provinsi" class="form-control" required>
                                    <option value="0">- Pilih Provinsi -</option>
                                    @foreach($provinsi as $p)
                                        <option value="{{$p->province_id}}">{{$p->province_name}}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Kota</label>
                            <div class="col-md-9">
                                
                                <select name="kota" id="selected-kota" class="form-control" required>
                                    
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Kecamatan</label>
                            <div class="col-md-9">
                                
                                <select name="kecamatan" id="selected-kecamatan" class="form-control" required>
                                    
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Kode Pos</label>
                            <div class="col-md-9">
                                <input type="text" name="kode_pos" class="form-control" value="">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <button type="submit" class="btn button-blue">Save Address</button> &nbsp &nbsp
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="changeAddress">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="text-center">
                        <h5>Pilih Alamat Pengiriman</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="box-address text-center">
                        <a href="#addAddress" data-dismiss="modal" data-toggle="modal" class="btn button-blue">TAMBAH ALAMAT</a>
                    </div>
                    <div id="user-address"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

<script>

    var courier = "jne";
    var destination = "0";
    var origin = "501";
    var weight = "0";
    var payment_final = "0";

    var code = "";
    var csrf = "";
    var customer_id = "";
    var delivery_fee = "";
    var payment_total = "";
    var selected_address_id = "";

    $('#courier').change(function(){
        courier = $(this).val();

        if(courier=="cod"){
            $('#duration').html("<option value='cod'>Datang ke Toko</option> <option value='cod-kirim'>Antar ke Rumah</option>");
        }else{
            checkOngkir();
        }
    })

    function resetVoucher(){
        $('#voucher-code').val("");

        $('#voucher-label').html("")
        $('#voucher-id').val("")
        $('#label-discount').html("Rp 0")

        $('#label-payment-final').html(parseInt(payment_total).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }))
        $('#payment-discount').val(0);
        $('#payment-final').val(payment_total);
        
    }

    function resetOngkir(){
        
        $('#label-price').html("Rp 0");
        $('#delivery-fee').val("0");
        sub_total = $('#sub-total').val();
        $('#label-payment-total').html("Rp "+(formatAmount(sub_total)));
        $('#payment-total').val(sub_total);

        $('#duration').html("");
        $('#courier option[value=0]').attr('selected','selected');
    }

    $('.btn-search-voucher').on('click', function(e){

        e.preventDefault();

         code = $('#voucher-code').val();
         csrf = "{{ csrf_token() }}";
         customer_id = $('#user-id').val();
         delivery_fee = $('#delivery-fee').val();
         payment_total = $('#payment-total').val();
         selected_address_id = $('#selected-address-id').val();

        if(selected_address_id=="" || delivery_fee=="0"){
            showAlert("Belum pilih pengiriman");
            return false;
        }

        var formData = {
            "_token": csrf,
            "code": code, 
            "customer_id": customer_id, 
            "payment_total": payment_total, 
        };

        console.log(formData);
        
        $.ajax({
                type: "POST",
                url: "{{url('find-voucher')}}",
                data: formData,
                headers: {'X-CSRF-TOKEN': csrf},
                
                beforeSend: function () {

                },
                success: function (response) {
                    console.log(response);
                    if(response.data==undefined){
                        showAlert(response.msg);
                    }else{
                        showNotif(response.msg);
                        $('#voucher-label').html("<br><div class='alert alert-success'>"+response.data.description+"</div>")
                        $('#voucher-id').val(response.data.id)
                        $('#label-discount').html(response.data.nominal.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }))

                        var final = parseInt(payment_total-parseInt(response.data.nominal))
                        $('#label-payment-final').html(final.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }))
                        $('#payment-discount').val(response.data.nominal);
                        $('#payment-final').val(final);
                        
                    }
                },
                error: function (xhr, status, error) {
                    setTimeout(function () {
                        console.log(xhr.responseText)
                    }, 2000);
                }
            });

    });

    function checkOngkir(){

        destination = $('#selected-destination-id').val();
        weight = $('#form-weight').val();

        if(weight=="0"){
            weight=1;
        }

        if(destination==""){
            alert('Belum Pilih alamat');
            return false;
            $('#courier').val("");
        }

        var url_ongkir = "{{url('check-ongkir?')}}"+"origin="+origin+"&destination="+destination+"&courier="+courier+"&weight="+weight;

        console.log(url_ongkir);

        $.ajax({
            type:"GET",
            url: url_ongkir,
            beforeSend:function(){
                $('#loading-ongkir').show();
            },
            success:function(response){

                $('#loading-ongkir').hide();
                console.log(response);

                var resp = JSON.parse(response);

                $('#label-courier').html(resp.rajaongkir.results[0].name)
                console.log(resp.rajaongkir.results[0].costs[0].cost[0].value)

                var rajaongkir = resp.rajaongkir.results[0].costs;
                var dur = ""
                dur += "<option value='0'>- Pilih Service -</option>"; 
                for(var i=0; i<rajaongkir.length; i++){
                    dur += "<option value='"+rajaongkir[i].service+"-"+rajaongkir[i].cost[0].value+"' data-value='"+rajaongkir[i].cost[0].value+"'>"+rajaongkir[i].service+" - Rp"+rajaongkir[i].cost[0].value+" Estimasi "+rajaongkir[i].cost[0].etd+"Hari</option>"; 
                }

                $('#duration').html(dur);

                $('#duration').on('change', function(){
                    var price = parseInt($('option:selected', this).attr('data-value'));
                    var sub_total = parseInt($('#sub-total').val());
                    $('#label-price').html(price.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }));
                    $('#delivery-fee').val(price);
                    var total = parseInt(price)+parseInt(sub_total);
                    $('#label-sub-total').html(total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }));
                    $('#label-payment-final').html(total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }));
                    $('#payment-total').val(total);
                })
            }
        });

    }

    $('#selected-provinsi').on('change', function() {
			var selected = this.value;

			var url = "{{url('api/alamat?')}}provinsi="+selected;

			// $('.select-provinsi').html("");
			$('#selected-kota').html("");
            $('#selected-kecamatan').html("");
            
            console.log(url)

			$.ajax({
				url: url,
				method: "get",
				success: function(resp){

					for(var i=0; i<resp.data.kota.length;i++){
						console.log(resp.data.kota[i]);
						$('#selected-kota').append("<option value='"+resp.data.kota[i].city_id+"'>"+resp.data.kota[i].city_name+"</option>")
					}

					$('#selected-kota').on('change', function() {

						var selected_kota = this.value;

						var url = "{{url('api/alamat?')}}provinsi="+selected+"&kota="+selected_kota;

						$.ajax({
							url: url,
							method: "get",
							success: function (resp1) {

								for (var i = 0; i < resp1.data.kecamatan.length; i++) {
									console.log(resp1.data.kecamatan[i].subdistrict_id);
									$('#selected-kecamatan').append("<option value='" + resp1.data.kecamatan[i].subdistrict_id + "'>" + resp1.data.kecamatan[i].subdistrict_name + "</option>")
								}


							}
						});


					});

				}
			})

		});


</script>

@php
    $user = Auth::user();
@endphp

@if(isset($user) && $user->account_type=="4")
<script>

    var url_address = "{{url('member/address-list/'.$user->id)}}";
    $(document).ready(function(){
        getAddressList();
    })

    function getAddressList(){
        console.log(url_address);
        $.ajax({
            type:"GET",
            url: url_address,
            beforeSend:function(){
                $('#loading-address').show();
            },
            success:function(response){

                var data_add = response.data;

                var html_add = "";
                var selected = "";
                var utama = "";
                for(var i=0; i<data_add.length; i++){

                    if(data_add[i].default_address=='1'){
                        utama = "&nbsp;<span class='kupon'>utama</span>";

                        selected =
                        '<div class="box-address">'+
                        '<p class="font-weight-bold">'+data_add[i].name+utama+'</p>'+
                        '<p class="font-weight-bold">'+data_add[i].address_name+'</p>'+
                        '<p>'+data_add[i].address_phone+'<br>'+
                        data_add[i].address+'<br>'+
                        data_add[i].kecamatan+', '+data_add[i].kota+', '+data_add[i].provinsi+'</p>'+
                        '</div>';

                        $('#selected-address').html(selected);
                        $('#selected-address-id').val(data_add[i].id);
                        $('#selected-destination-id').val(data_add[i].kota);

                    }else{

                        if(utama==""){
                            selected = '<div class="alert alert-danger">Belum ada alamat default, silahkan pilih alamat terdaftar</div>';
                        }
                    }

                    html_add +=
                    '<div class="box-address">'+
                    '<p class="font-weight-bold">'+data_add[i].name+utama+'</p>'+
                    '<p class="font-weight-bold">'+data_add[i].address_name+'</p>'+
                    '<p>'+data_add[i].address_phone+'</p>'+
                    '<p>'+data_add[i].address+'</p>'+
                    '<button class="btn button-blue selected-button" data-id="'+data_add[i].id+'" data-kotaid="'+data_add[i].kota+'" data-name="'+data_add[i].name+'" data-address="'+data_add[i].address+'" data-phone="'+data_add[i].address_phone+'"   style="width: 100%;">PILIH ALAMAT</button>'+
                    '</div>';
                }

                setTimeout(function(){
                    $('#user-address').html(html_add);
                    $('#selected-address').html(selected);
                    $('#loading-address').hide();

                    $('.selected-button').on('click', function(){
                        var id = $(this).attr('data-id');
                        var name = $(this).attr('data-name');
                        var phone = $(this).attr('data-phone');
                        var address = $(this).attr('data-address');
                        var kotaid = $(this).attr('data-kotaid');

                        var select_insert =
                        '<div class="box-address">'+
                            '<p class="font-weight-bold">'+name+'</p>'+
                            '<p>'+phone+'</p>'+
                            '<p>'+address+'</p>'+
                            '</div>';

                        $('#selected-address').html(select_insert);
                        $('#selected-address-id').val(id);
                        $('#selected-destination-id').val(kotaid);

                        $('#changeAddress').modal('hide');
                        showNotif(address+ " Terpilih")

                        resetOngkir();
                    })
                }, 2000);

            }
        })
    }
</script>

@endif

@endsection