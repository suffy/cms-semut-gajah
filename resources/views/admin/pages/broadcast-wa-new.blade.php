@extends('admin.layout.template')

@section('content')

    @php
        $account_role = auth()->user()->account_role;
    @endphp

    <div class="heading-section">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4">
                <a 
                    href="
                        @if($account_role == 'manager')
                            {{url('/manager/broadcast')}}
                        @elseif($account_role == 'superadmin')
                            {{url('/superadmin/broadcast')}}
                        @endif
                    " 
                    class="btn btn-blue"
                ><span class="fa fa-arrow-left"></span> &nbsp Kembali</a>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-8">
                
            </div>
        </div>
    </div>
    <br>
    <section class="panel col-md-6">
        <header class="panel-heading">
            Form Tambah Broadcast Whatsapp
        </header>
        <div class="card-body">
            <form action="
                        @if($account_role == 'manager')
                            {{url('manager/broadcast/store')}}
                        @elseif($account_role == 'superadmin')
                            {{url('superadmin/broadcast/store')}}
                        @endif
                        " 
                        method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Judul</label>
                            <div class="col-sm-12">
                                <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" placeholder="Judul" value="{{old('title')}}">
                                <div class="invalid-feedback">
                                    {{ $errors->first('title') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('schedule') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Jadwal</label>
                            <div class="col-12">
                                <input type="text" name="schedule" placeholder="Pilih jadwal" class="search-input form-control {{ $errors->has('schedule') ? 'is-invalid' : '' }}" onfocus="(this.type='datetime-local')" onblur="(this.type='datetime-local')" value="{{old('schedule')}}" >
                                <div class="invalid-feedback">
                                    {{ $errors->first('schedule') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group {{ $errors->has('classification') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Klasifikasi Broadcast</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('classification') ? 'is-invalid' : '' }}" name="classification" aria-label="Default select example" id="classification" onchange="showcs()">
                                    <option selected="true" disabled="disabled">Silahkan Pilih</option>
                                    <option value="distributor" @if (old('classification') == 'distributor') selected="selected" @endif>Per Distributor</option>
                                    <option value="user" @if (old('classification') == 'user') selected="selected" @endif>Per User</option>
                                </select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('classification') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group {{ $errors->has('classification_distributor') ? 'has-error' : '' }} row" style="display:none;" id="cs1">
                            <label class="col-sm-12 col-form-label">Distributor</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('classification_distributor') ? 'is-invalid' : '' }}" aria-label="Default select example" name="classification_distributor" id="classification_distributor"></select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('classification_distributor') }}
                                </div>
                            </div> 
                        </div>
                        <div class="form-group {{ $errors->has('classification_user') ? 'has-error' : '' }} row" style="display:none;" id="cs2">
                            <label class="col-sm-12 col-form-label">Customer</label>
                            <div class="col-sm-12">
                                <select class="form-control {{ $errors->has('classification_user') ? 'is-invalid' : '' }}" aria-label="Default select example" name="classification_user" id="classification_user"></select>
                                <div class="invalid-feedback">
                                    {{ $errors->first('classification_user') }}
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-blue float-right" onclick="setData()" style="margin-top:20px;margin-bottom:20px;width:200px;">Tambah ke Tabel</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-outer">
                            <div class="table-inner">
                
                                <div class="table-responsive">
                                    <table class="table default-table" id="tableDistributor" style="display:none;">
                                        <thead>
                                            <tr align="center">
                                                <td>Site Code</td>
                                                <td>Nama</td>
                                                <td>Aksi</td>
                                            </tr>
                                        </thead>
                                        <tbody id="tableDistributorBody">
        
                                        </tbody>
                                    </table>
        
                                    <table class="table default-table" id="tableUser" style="display:none;">
                                        <thead>
                                            <tr align="center">
                                                <td>Customer Code</td>
                                                <td>Nama</td>
                                                <td>Aksi</td>
                                            </tr>
                                        </thead>
                                        <tbody id="tableUserBody">
        
                                        </tbody>
                                    </table>
                                </div>
        
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Template Pesan<span class="text-small"> * Gunakan kata {name} untuk nama user </span></label>
                            <div class="col-12">
                                <textarea name="message" class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}" rows="5">{{old('message')}}</textarea>
                                <div class="invalid-feedback">
                                    {{ $errors->first('message') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }} row">
                            <label class="col-sm-12 col-form-label">Tipe Broadcast</label>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input {{ $errors->has('type') ? 'is-invalid' : '' }}" type="radio" name="type" id="type_wa" value="wa">
                                    <label class="form-check-label {{ $errors->has('type') ? 'is-invalid' : '' }}" for="type">
                                        Whatsapp
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input {{ $errors->has('type') ? 'is-invalid' : '' }}" type="radio" name="type" id="type_apps" value="apps">
                                    <label class="form-check-label {{ $errors->has('type') ? 'is-invalid' : '' }}" for="type">
                                        Apps Notification
                                    </label>
                                </div>
                            </div>
                            <div class="invalid-feedback">
                                {{ $errors->first('type') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-blue float-right" style="margin-top:20px;width:200px;">Simpan Data</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        $(document).ready(function () {
            $('#classification_distributor').select2({
                placeholder: "Pilih Distributor",
                ajax: {
                    url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/all-mapping-site')}}" 
                        @elseif(auth()->user()->account_role == 'superadmin') 
                            "{{url('superadmin/all-mapping-site')}}"
                    @endif,
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data){
                        return {
                            results: $.map(data, function(item){
                                return {
                                    text: item.kode +' - '+ item.branch_name +' - '+ item.nama_comp,
                                    id: item.kode
                                }
                            })
                        };
                    },
                    cache: true,
                    error: function(error) {
                        console.log(error);
                    }
                }
            });
            
            $('#classification_user').select2({
                placeholder: "Pilih Customer",
                ajax: {
                    url: @if(auth()->user()->account_role == 'manager')
                            "{{url('manager/all-customer')}}" 
                        @elseif(auth()->user()->account_role == 'superadmin') 
                            "{{url('superadmin/all-customer')}}"
                    @endif,
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data){
                        return {
                            results: $.map(data, function(item){
                                return {
                                    text: item.customer_code +' - '+ item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true,
                    error: function(error) {
                        console.log(error);
                    }
                }
            });
        });

        //Function onchange untuk menampilkan dan menghilangkan pilih distributor dan pilih user
        function showcs() {
            var cs = $('#classification').val();
            $('#classification_distributor').empty();
            $('#classification_user').empty();
            $('#tableDistributorBody').empty();
            if(cs == 'distributor') {
                $('#cs1').show();
                $('#cs2').hide();
                $('#tableDistributor').show();
                $('#tableUser').hide();
            } else if(cs == 'user') {
                $('#cs1').hide();
                $('#cs2').show();
                $('#tableDistributor').hide();
                $('#tableUser').show();
            } else {
                $('#cs1').hide();
                $('#cs2').hide();
                $('#tableDistributor').hide();
                $('#tableUser').hide();
            }
        }

        function setData() {
            var cs = $('#classification').val();
            if(cs == 'distributor') {
                console.log("tes 1");
                var site_code   = $('#classification_distributor').val();
                var distributor = $('#select2-classification_distributor-container').html();

                $('#classification_distributor').empty();
                addTableDistributor(site_code, distributor);
            } else if(cs == 'user') {
                console.log("tes 2");
                var user_id   = $('#classification_user').val();
                var user      = $('#select2-classification_user-container').html();

                $('#classification_user').empty();
                addTableUser(user_id, user);
            }
        }

        var x = 0;
        function addTableDistributor(site_code, distributor) {
            x++;
            $('#tableDistributorBody').append('<tr align="center" id="rowdistributor' + x + '"><td><h6>' + site_code +'</h6><input type="hidden" style="outline:none;border:0;" name="site_code[]" id="site_code" value="' + site_code + 
                                                        '"></td><td><h6>' + distributor.split("-")[1] +'</h6><input type="hidden" style="outline:none;border:0;" name="distributor[]" id="distributor" value="' + distributor.split("-")[1] + 
                                                        '"></td><td><button type="button" id="' + x + '" class="btn btn-danger btn-small remove_row_distributor">&times;</button></td></tr>');
        }

        $(document).on('click', '.remove_row_distributor', function() {
            var row_distributor = $(this).attr("id");
            $('#rowdistributor' + row_distributor + '').remove();
        });

        var y = 0;
        function addTableUser(user_id, user) {
            y++;
            $('#tableUserBody').append('<tr align="center" id="rowuser' + y + '"><td><h6>'+user.split("-")[0]+'</h6><input type="hidden" style="outline:none;border:0;" name="user_id[]" id="user_id" value="' + user_id + 
                                                        '"></td><td><h6>'+user.split("-")[1]+'</h6><input type="hidden" style="outline:none;border:0;" name="user[]" id="user" value="' + user.split("-")[1] + 
                                                        '"></td><td><button type="button" id="' + y + '" class="btn btn-danger btn-small remove_row_user">&times;</button></td></tr>');
        }

        $(document).on('click', '.remove_row_user', function() {
            var row_user = $(this).attr("id");
            $('#rowuser' + row_user + '').remove();
        });
    </script>

    <style>
        .text-small{
            font-size: 8pt;
            color: red;
        }
    </style>
    
    @endsection
