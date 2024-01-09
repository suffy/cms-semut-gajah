@extends('admin.layout.template')

@section('content')

@php
$account_role = auth()->user()->account_role;
@endphp

<section class="panel col-md-6">
    <header class="panel-heading">
        Alert Notification
    </header>
    <div class="card-body">
        @if ($alert)
        @if ($alert->type == "image")
        <center><img src="{{asset($alert->parameter)}}" class="img img-responsive" width="250px" id="frame"></center>
        @endif
        @endif
        <form action="
                    @if($account_role == 'manager')
                        {{url('manager/alert/store')}}
                    @elseif($account_role == 'superadmin')
                        {{url('superadmin/alert/store')}}
                    @endif
                    " method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }} row">
                        <label for="title" class="col-12 col-form-label">Judul</label>
                        <div class="col-12">
                            <input type="text" name="title" id="title" class="form-control" placeholder="Judul"
                                value="{{ isset($alert->title) ? $alert->title : null }}">
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }} row">
                        <label for="description" class="col-12 col-form-label">Deskripsi</label>
                        <div class="col-12">
                            <textarea name="description" id="description" class="form-control" rows="5"
                                placeholder="Description">{{ isset($alert->description) ? $alert->description : null }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group {{ ($errors->has('start') || $errors->has('end')) ? 'has-error' : '' }} row">
                        <label for="start" class="col-12 col-form-label">Tanggal</label>
                        <div class="col-6">
                            <input type="text" name="start" placeholder="Tanggal Mulai..."
                                class="search-input form-control" onfocus="(this.type='date')"
                                onblur="(this.type='text')" value="{{ isset($alert->start) ? $alert->start : null }}">
                        </div>
                        <div class="col-6">
                            <input type="text" name="end" placeholder="Tanggal Selesai..."
                                class="search-input form-control" onfocus="(this.type='date')"
                                onblur="(this.type='text')" value="{{ isset($alert->end) ? $alert->end : null }}">
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }} row">
                        <label for="type" class="col-12 col-form-label">Tipe</label>
                        <div class="col-12">
                            <select class="form-control" name="type" id="type" onchange="show()">
                                <option selected="true" disabled="disabled">Pilih Tipe</option>
                                @if($alert)
                                <option value="info" @if($alert->type == 'info') selected @endif>Informasi</option>
                                <option value="promo" @if($alert->type == 'promo') selected @endif>Promo</option>
                                <option value="top-spender" @if($alert->type == 'top-spender') selected @endif>Top
                                    Spender</option>
                                <option value="link" @if($alert->type == 'link') selected @endif>Link</option>
                                <option value="image" @if($alert->type == 'image') selected @endif>Image</option>
                                @else
                                <option value="info">Informasi</option>
                                <option value="promo">Promo</option>
                                <option value="top-spender">Top Spender</option>
                                <option value="link">Link</option>
                                <option value="image">Image</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('parameter') ? 'has-error' : '' }} row" id="p1"
                        style="display: none;">
                        <label for="parameter" class="col-12 col-form-label">Parameter Link</label>
                        <div class="col-12">
                            <input type="text" name="parameter" id="parameter1" class="form-control"
                                placeholder="Link URL" disabled
                                value="{{ isset($alert->parameter) ? $alert->parameter : null }}">
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('parameter') ? 'has-error' : '' }} row" id="p2"
                        style="display: none;">
                        <label for="parameter" class="col-12 col-form-label">Parameter Promo</label>
                        <div class="col-12">
                            <select class="form-control" aria-label="Default select example" name="parameter"
                                id="parameter2" disabled></select>
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('parameter') ? 'has-error' : '' }} row" id="p3"
                        style="display: none;">
                        <label for="parameter" class="col-12 col-form-label">Parameter Top Spender</label>
                        <div class="col-12">
                            <select class="form-control" aria-label="Default select example" name="parameter"
                                id="parameter3" disabled></select>
                        </div>
                    </div>
                    <div class="form-group {{ $errors->has('parameter') ? 'has-error' : '' }} row" id="p4"
                        style="display: none;">
                        <label for="parameter" class="col-12 col-form-label">Image Alert</label>
                        <div class="col-12">
                            <input class="form-control" type="file" name="image" id="parameter4">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-blue float-right">Simpan Data</button>
                </div>
            </div>
        </form>
    </div>
</section>
<script>
    $(document).ready(function () {
        function readURL(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    // $(previewId).css('src', e.target.result );
                    $(previewId).attr('src', e.target.result);
                    $(previewId).hide();
                    $(previewId).fadeIn(850);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#parameter4").change(function () {
            readURL(this, '#frame');
        });



        console.log($('#type').val());
        if ($('#type').val() == 'info') {
            $('#p1').hide();
            $('#p2').hide();
            $('#p3').hide();
            $('#p4').hide();
            $('#frame').hide();
            $('#parameter1').attr({
                "disabled": true
            });
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": true
            });
        } else if ($('#type').val() == 'promo') {
            $('#p1').hide();
            $('#p2').show();
            $('#p3').hide();
            $('#p4').hide();
            $('#frame').hide();
            $('#parameter1').attr({
                "disabled": true
            });
            $('#parameter2').removeAttr("disabled");
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": true
            });
        } else if ($('#type').val() == 'top-spender') {
            $('#p1').hide();
            $('#p2').hide();
            $('#p3').show();
            $('#p4').hide();
            $('#frame').hide();
            $('#parameter1').attr({
                "disabled": true
            });
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').removeAttr("disabled");
            $('#parameter4').attr({
                "disabled": true
            });
        } else if ($('#type').val() == 'link') {
            $('#p1').show();
            $('#p2').hide();
            $('#p3').hide();
            $('#p4').hide();
            $('#frame').hide();
            $('#parameter1').removeAttr("disabled");
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": true
            });
        } else if ($('#type').val() == 'image') {
            $('#p1').hide();
            $('#p2').hide();
            $('#p3').hide();
            $('#p4').show();
            $('#frame').show();
            $('#title').attr('disabled', 'disabled');
            $('#description').attr("disabled", true);
            $('#title').val("");
            $('#description').val("");
            $('#parameter1').attr('disabled', 'disabled');
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": false
            });

        }

        $('#parameter2').select2({
            placeholder: "Pilih Promo",
            ajax: {
                url: 'alert/promo',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.title,
                                id: item.id
                            }
                        })
                    };
                },
                cache: true,
                error: function (error) {
                    console.log(error);
                }
            }
        });

        $('#parameter3').select2({
            placeholder: "Pilih Top Spender",
            ajax: {
                url: 'alert/top-spender',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.title,
                                id: item.id
                            }
                        })
                    };
                },
                cache: true,
                error: function (error) {
                    console.log(error);
                }
            }
        });
    });

    function show() {
        const type = $('#type').val();
        console.log(type);
        if (type == 'info') {
            $('#p1').hide();
            $('#p2').hide();
            $('#p3').hide();
            $('#p4').hide();
            $('#title').removeAttr('disabled');
            $('#description').attr("disabled", false);
            $('#parameter1').attr({
                "disabled": true
            });
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": true
            });
            $('#frame').hide();
        } else if (type == 'promo') {
            $('#p1').hide();
            $('#p2').show();
            $('#p3').hide();
            $('#p4').hide();
            $('#title').removeAttr('disabled');
            $('#description').attr("disabled", false);
            $('#parameter1').attr({
                "disabled": true
            });
            $('#parameter2').removeAttr("disabled");
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": true
            });
            $('#frame').hide();

        } else if (type == 'top-spender') {
            $('#p1').hide();
            $('#p2').hide();
            $('#p3').show();
            $('#p4').hide();
            $('#title').removeAttr('disabled');
            $('#description').attr("disabled", false);
            $('#parameter1').attr({
                "disabled": true
            });
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').removeAttr("disabled");
            $('#parameter4').attr({
                "disabled": true
            });
            $('#frame').hide();
        } else if (type == 'link') {
            $('#p1').show();
            $('#p2').hide();
            $('#p3').hide();
            $('#p4').hide();
            $('#title').removeAttr('disabled');
            $('#description').attr("disabled", false);
            $('#parameter1').removeAttr("disabled");
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": true
            });
            $('#frame').hide();

        } else if (type == 'image') {
            $('#p1').hide();
            $('#p2').hide();
            $('#p3').hide();
            $('#p4').show();
            $('#frame').show();
            $('#title').attr('disabled', 'disabled');
            $('#description').attr("disabled", true);
            $('#title').val("");
            $('#description').val("");
            $('#parameter1').attr("disabled");
            $('#parameter2').attr({
                "disabled": true
            });
            $('#parameter3').attr({
                "disabled": true
            });
            $('#parameter4').attr({
                "disabled": false
            });
        }
    }

</script>
@stop
