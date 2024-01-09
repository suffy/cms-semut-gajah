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
                        {{url('/manager/promo')}}
                    @elseif($account_role == 'superadmin')
                        {{url('/superadmin/promo')}}
                    @elseif($account_role == 'admin')
                        {{url('/admin/promo')}}
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
    <header class="panel-heading">
        Prioritas Banner Atas
    </header>
    <div class="card-body">
        <form id="form-priority" action="@if(auth()->user()->account_role == 'manager'){{url('manager/promo/priority/top/store')}}@elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/promo/priority/top/store')}}@else{{url('admin/promo/priority/top/store')}}@endif" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group row">
                <label class="col-sm-12 col-form-label">Pilih Banner (Maks 5 Banner)</label>
                <div class="col-sm-12">
                    <div class="row">
                        @foreach($banners as $banner)
                        <div class="col-4 mt-4">
                            <div class="row">
                                <div class="col-1">
                                    <input class="priority-checkbox" type="checkbox" name="top[]" value="{{ $banner->id }}" @if($banner->priority_top == 1) checked @endif>
                                </div>
                                <div class="col-8">
                                    <img src="{{ $banner->banner }}" alt="banner" style="max-width: 100%;">
                                </div>
                                <div class="col-3" @if($banner->priority_top != 1) style="display: none;" @else style="display: block;" @endif id="pos{{$banner->id}}">
                                    <label for="pos">Posisi Urutan</label>
                                    <input type="text" name="pos[]" id="posinput{{$banner->id}}" style="width: 100%;" @if($banner->priority_top_position != null) value="{{$banner->priority_top_position}}" @endif>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-blue float-right">Simpan</button> &nbsp &nbsp
                </div>
            </div>
        </form>
    </div>
</section>
<script>
    const limit = 5;
    $('input.priority-checkbox').on('change', function (event) {
        if($("input[name='top[]']:checked").length > limit) {
            this.checked = false;
            showAlert("Hanya dapat memilih 5 banner!");
        } else {
            if($(this).prop("checked")) {
                $("#pos" + $(this).val()).show();
                $("#posinput" + $(this).val()).removeAttr("disabled");
            } else {
                $("#pos" + $(this).val()).hide();
                $("#posinput" + $(this).val()).val(null);
                $("#posinput" + $(this).val()).attr("disabled", true);
            }
        }
    })
</script>
@stop