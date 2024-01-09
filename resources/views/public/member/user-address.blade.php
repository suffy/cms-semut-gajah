@extends('public.layout.member-layout')

@section('member-content')

    <section class="panel" id="panel-user">
        <header class="panel-heading">
            <b>User Address</b>
        </header>
        <div class="">
            <div class="card-body">
                <div class="border-light">
                    <button class="btn btn-primary" data-target="#newAddress" data-toggle="modal" >Tambah Alamat</button><br><br>
                    <div class="table-responsive">
                        <table class="table default-table dataTable">
                            <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nama</th>
                                <th>Label</th>
                                <th>No telp</th>
                                <th>Alamat</th>
                                {{-- <th>Kecamatan</th>
                                <th>Provinsi</th>
                                <th>Kode Pos</th> --}}
                                <th>Alamat Utama</th>
                                {{-- <th>Status</th> --}}
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($user_address as $row)
                                    <tr>
                                        <td class="text-center">{{$loop->iteration}}</td>
                                        <td>{{$row->name}}</td>
                                        <td>{{$row->address_name}}</td>
                                        <td>{{$row->address_phone}}</td>
                                        <td>{{$row->address}}</td>
                                        {{-- <td>{{$row->subdistrict->subdistrict_name}}</td>
                                        <td>{{$row->province->province_name}}</td>
                                        <td>{{$row->kode_pos}}</td> --}}
                                        <td class="text-center">
                                            @if($row->default_address=="1")
                                                <i class="status status-success"><span class="fa fa-check"></span></i>
                                            @else
                                                <i class="status status-danger"><span class="fa fa-close red"></span></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)"
                                                class="btn btn-primary btn-sm button-edit-address"
                                                data-toggle="modal"
                                                data-target="#editAddress"
                                                data-id="{{ $row->id }}"
                                                data-url="{{ url('member/update-address', $row->id) }}"
                                                data-name="{{ $row->name }}"
                                                data-address-name="{{ $row->address_name }}"
                                                data-address-phone="{{ $row->address_phone }}"
                                                data-address="{{ $row->address }}"
                                                data-phone="{{ $row->address_phone }}"
                                                data-subdistrict="{{ $row->kecamatan }}"
                                                data-province="{{ $row->provinsi }}"
                                                data-city="{{ $row->kota }}"
                                                data-kode-pos="{{ $row->kode_pos }}"
                                                data-default-address="{{ $row->default_address }}"
                                                >
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <form action="{{ url('member/delete-address/'.$row->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm" value="Delete" onclick="return confirm('Delete Data?')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div id="newAddress" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Address Information</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <form action="{{url('member/address')}}" method="post" enctype="multipart/form-data">

                        @csrf
                        <input type="hidden" name="url"  value="{{Request::url()}}" required>
                        <input type="hidden" name="user_id"  value="{{$user->id}}" required>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Nama</label>
                            <div class="col-md-9">
                                <input type="text" name="name" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Label Alamat</label>
                            <div class="col-md-9">
                                <input type="text" name="address_name" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Alamat</label>
                            <div class="col-md-9">
                                <input type="text" name="address" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-sm-3 col-xs-3 ">Telp Penerima</label>
                            <div class="col-md-9">
                                <input type="text" name="address_phone" class="form-control" value="">
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
                                {{-- <a href="javascript:void(0)" onclick="return closeAdd()">Close</a> --}}
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="editAddress" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Address</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="updateAddress" action="#" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_id"  value="{{$user->id}}" required>
                        <input type="hidden" name="id" id="input-id-edit"  value="" required>

                        <div class="form-group row">
                            <label class="col-3">Name</label>
                            <div class="col-9">
                                <input type="text" id="input-name-edit" name="name" class="form-control" placeholder="Name" value="" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3">Label Alamat</label>
                            <div class="col-9">
                                <input type="text" id="input-address-name-edit" name="address_name" class="form-control" placeholder="Address Label" value="" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3">Alamat</label>
                            <div class="col-md-9">
                                <input type="text" name="address" id="input-address-edit" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3">Telp Penerima</label>
                            <div class="col-md-9">
                                <input type="text" name="address_phone" id="input-address-phone-edit" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3">Provinsi</label>
                            <div class="col-md-9">
                                @php
                                    $provinsi = \App\LocalProvince::all();
                                @endphp
                                <select name="provinsi" id="input-province-edit" class="form-control" required>
                                    @foreach($provinsi as $p)
                                        <option value="{{$p->province_id}}">{{$p->province_name}}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3">Kota</label>
                            <div class="col-md-9">
                                @php
                                    $kota = \App\LocalCities::all();
                                @endphp
                                <select name="kota" id="input-city-edit" class="form-control" required>
                                    @foreach($kota as $p)
                                        <option value="{{$p->city_id}}">{{$p->city_name}}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3">Kecamatan</label>
                            <div class="col-md-9">
                                @php
                                    $kec = \App\LocalSubDistrict::all();
                                @endphp
                                <select name="kecamatan" id="input-subdistrict-edit" class="form-control" required>
                                    @foreach($kec as $p)
                                        <option value="{{$p->subdistrict_id}}">{{$p->subdistrict_name}}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-3">Kode Pos</label>
                            <div class="col-md-9">
                                <input type="text" name="kode_pos" id="input-kode-pos-edit" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="" class="col-3">Alamat Utama</label>
                            <div class="col-9">
                                <label class="switch">
                                    <input type="checkbox"
                                     id="input-default-address-edit"
                                     name="default_address"
                                     data-onstyle="success"
                                     data-offstyle="danger"
                                     data-on="Active"
                                     data-off="InActive">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary">Update</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Close</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /Modal content-->
        </div>
    </div>

    <style>
        #panel-user .status-user{
            font-size: 10pt;
        }

        #panel-user h4{
            color: #000000;
            font-size: 11pt;
            font-weight: 600;
            background: #f1f1f1;
            padding: 10px;
            padding-left: 15px;
            padding-right: 15px;
        }

        .form-group b{
            font-size: 10pt;
            color: #999999;
            font-weight: 100;
        }
        .form-group p{
            color: #000000;
        }

        /* SWITCH  */
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            width: 60px;
            height: 34px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            height: inherit;
            opacity: 1;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #007bff;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #007bff;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* Radio button image  */
        .input-hidden {
            position: absolute;
            left: -9999px;
        }

        input[type="radio"]:checked + label > img,
        input[type="radio"] + label > img:hover {
            border: 1px solid #fff;
            box-shadow: 0 0 3px 3px #007bff;
        }

        /* Stuff after this is only to make things more pretty */
        input[type="radio"] + label > img {
            border: 1px solid #abced4;
            width: 150px;
            height: 150px;
            transition: 500ms all;
            cursor: pointer;
        }
    </style>

@endsection

@section('js')
    <script>
        $(document).on('click', '.delete-address', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            swal({
                    title: "Are you sure!",
                    type: "error",
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes!",
                    showCancelButton: true,
                },
                function() {
                    $.ajax({
                        type: "POST",
                        url: "{{url('member/delete-address')}}",
                        data: {id:id},
                        success: function (data) {
                                    //
                            }
                    });
            });
        });
    </script>
    <script>

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

        $('.button-edit-address').on('click', function(e){
            var url = $(this).data('url');
            var id = $(this).attr('data-id');
            var name = $(this).attr('data-name');
            var address_name = $(this).attr('data-address-name');
            var address = $(this).attr('data-address');
            var address_phone = $(this).attr('data-address-phone');
            var kode_pos = $(this).attr('data-kode-pos');
            var province = $(this).attr('data-province');
            var city = $(this).attr('data-city');
            var subdistrict = $(this).attr('data-subdistrict');
            var default_address = $(this).attr('data-default-address');

            $('#updateAddress').attr('action', url);
            $('#input-id-edit').val(id);
            $('#input-name-edit').val(name);
            $('#input-address-name-edit').val(address_name);
            $('#input-address-edit').val(address);
            $('#input-address-phone-edit').val(address_phone);
            $('#input-kode-pos-edit').val(kode_pos);
            $('#input-province-edit').val(province);
            $('#input-city-edit').val(city);
            $('#input-subdistrict-edit').val(subdistrict);
            if(default_address == 1){
                $('#input-default-address-edit').prop('checked','checked');
                $('#input-default-address-edit').val(default_address);
            }
        });
    </script>
@endsection