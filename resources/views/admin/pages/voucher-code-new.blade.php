@extends('admin.layout.template')

@section('content')

    <div class="heading-section">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-4">
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
            </div>
            <div class="col-md-8 col-sm-8 col-xs-8">
                
            </div>
        </div>
    </div>
    <br>
    <section class="panel" id="create-new-voucher">
        <div class="card-body">
            <h4>Create new Voucher</h4>
            <hr>
            <form action="@if(auth()->user()->account_role == 'manager'){{ url('/manager/vouchers') }}@else{{ url('/superadmin/vouchers') }}@endif" method="post" enctype="multipart/form-data">
            <div class="row">
                    <div class="col-md-4 col-sm-4 col-xs-6">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="form-group">
                            <label>CODE</label>
                            <input type="text" class="form-control" placeholder="CODE" style="text-transform: uppercase"
                                name="code" required>
                        </div>
                        <div class="form-group">
                            <label>Type Voucher</label>
                            <select class="form-control" name="type" id="type-voucher">
                                <option value="percent" selected>Percent</option>
                                <option value="nominal" selected>Nominal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Percent</label>
                            <input type="text" class="form-control" id="input-percent" placeholder="contoh : 10%"
                                name="percent">
                        </div>
                        <div class="form-group">
                            <label>Potongan maksimal Untuk voucher dalam bentuk Percent</label>
                            <input type="text" class="form-control input-amount" placeholder="Potongan Maksimal"
                                name="max_nominal">
                        </div>
                        <div class="form-group">
                            <label>Nominal Potongan</label>
                            <input type="text" class="form-control input-amount" id="input-nominal"
                                placeholder="contoh : 30000" name="nominal">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi Kupon</label>
                            <textarea class="form-control" placeholder="Kupon apa gitu?" name="description"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Term & Condition</label>
                            <textarea class="form-control" placeholder="Termandcondition"
                                name="termandcondition"></textarea>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-6">
                        <div class="form-group">
                            <label>Penggunaan Maksimal Voucher</label>
                            <input type="number" class="form-control" value="" placeholder="Pengguna Maksimal"
                                name="max_use">
                        </div>
                        <div class="form-group">
                            <label>Maksimal Penggunaan Per User</label>
                            <input type="number" class="form-control" value="" placeholder="Pengguna Maksimal Tiap User"
                                name="max_use_user">
                        </div>
                        <div class="form-group">
                            <label>Penggunaan Harian</label>
                            <input type="number" class="form-control" value="" placeholder="Pengguna Harian Tiap User"
                                name="daily_use">
                        </div>
                        <div class="form-group">
                            <label>Minimal Transaksi</label>
                            <input type="text" class="form-control input-amount" value=""
                                placeholder="Transaksi Minimal" name="min_transaction" required>
                        </div>
                        <div class="form-group">
                            <label>Maximal Transaksi</label>
                            <input type="text" class="form-control input-amount" value=""
                                placeholder="Transaksi Maximal" name="max_transaction" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select class="form-control" name="category">
                                <option value="potongan">Potongan</option>
                                <option value="ongkir">Ongkir</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-6">
                        
                        <div class="form-group">
                            Start
                            <div class="row">
                                <div class="col-md-8 col-sm-8 col-xs-8"><input class="form-control datepicker"
                                        value="{{date('Y-m-d')}}" placeholder="2018-08-02" name="start_at"></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><input type="time" class="form-control"
                                        value="00:01" name="time_start_at"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>End</label>
                            <div class="row">
                                <div class="col-md-8 col-sm-8 col-xs-8"><input class="form-control datepicker"
                                        value="{{date('Y-m-d')}}" placeholder="2018-08-02" name="end_at"></div>
                                <div class="col-md-4 col-sm-4 col-xs-4"><input type="time" class="form-control"
                                        value="23:59" name="time_end_at"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option value="1">Aktif</option>
                                <option value="0">Non Aktif</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>File</label>
                            <input type="file" class="form-control" name="file">
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="form-group">
                            <hr>
                            <button type="submit" class="btn btn-blue">Create</button>
                            <a href="javascript:void(0)" class="btn" onclick="return closeForm()">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <script>
        function newVoucher() {
            $('#create-new-voucher').fadeIn('slow');
        }

        function closeForm() {
            $('#create-new-voucher').fadeOut('slow');
        }

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
