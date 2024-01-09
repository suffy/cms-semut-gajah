@extends('admin.layout.template')

@section('content')

@php
    $account_role = auth()->user()->account_role;
@endphp

<a href="javascript:void(0)" class="btn btn-blue" data-toggle="modal" data-target="#promoModal">Tambah Promo Spesial</a>
<br>
<br>

<section class="panel" id="listPage">
    <header class="panel-heading">
        Promo Spesial
    </header>
    <div class="card-body">
        
        <div class="card-body">
            <div class="table-responsive">
                <div class="scroll-table-outer">
                    <div class="scroll-table-inner card-body">
                    
                        <table class="table default-table dataTable">
                            <thead>
                                <tr align="center">
                                    <td>No</td>
                                    <td>Banner</td>
                                    <td>Name</td>
                                    <td>Description</td>
                                    <td>Highlight</td>
                                    <td>Tipe</td>
                                    <td>Diskon</td>
                                    <td>Maksimal Potongan</td>
                                    <td>Status</td>
                                    <td width="75px">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($promos as $row)
                                <tr align="center">
                                    <td>{{$loop->iteration}}</td>
                                    <td class="align-middle">
                                        @if (!file_exists( public_path() . $row->banner))
                                            <img src="/no-images.png" width="75px">
                                        @elseif (file_exists( public_path() . $row->banner))
                                            <img src="{{ $row->banner }}" width="75px">
                                        @endif
                                    </td>
                                    <td class="align-middle">{{$row->title}}</td>
                                    <td class="align-middle">{!! $row->description !!}</td>
                                    <td class="align-middle">{!! $row->highlight !!}</td>
                                    <td class="align-middle">{{$row->spesial}}</td>
                                    @foreach ($row->reward as $reward_data)
                                        @php
                                            $reward_disc = $reward_data->reward_disc;
                                            $max = $reward_data->max;
                                        @endphp
                                        <td class="align-middle">{{ $reward_disc }}%</td>
                                        <td class="align-middle">Rp {{ number_format($max) }}</td>
                                    @endforeach
                                    <td class="align-middle" width="40px">
                                        <label class="switch">
                                            <input data-id="{{$row->id}}" data-user_role={{$account_role}} class="toggle-class success" type="checkbox" data-onstyle="success" data-offstyle="danger" data-toggle="toggle" data-on="Active" data-off="InActive" data-size="mini" {{ $row->status ? 'checked' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td class="align-middle">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-86px, 34px, 0px);">
                                                <li><a href="javascript:void(0)" class="dropdown-item" data-toggle="modal" data-target="#editPromoModal" onclick="setData('{{$row->id}}', '{{$row->title}}', '{{$row->special}}', '{{$reward_disc}}', '{{$max}}', '{!! $row->description !!}', '{!! $row->highlight !!}')">Edit</a></li>
                                                <li><a href="javascript:void(0)" class="dropdown-item" onclick="hapus('{{$row->id}}')">Delete</a></li>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                    {{ $promos->appends(Request::all())->links() }}
                </div>
            </div>
     
    </div>
</section>

    <!-- Modal -->
    <div id="promoModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Promo Spesial</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/special-promo/create')}}@elseif(auth()->user()->account_role == 'admin'){{url('admin/special-promo/create')}}@else{{url('superadmin/special-promo/create')}}@endif" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row has-error">
                            <label class="col-sm-12 col-form-label">Judul</label>
                            <div class="col-sm-12">
                                <input type="text" name="promo_title" class="form-control {{ $errors->has('promo_title') ? 'is-invalid' : '' }}" placeholder="Judul Promo" value="{{ old('promo_title') }}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('promo_title') }} </span>
                            </div>
                        </div>
                        <div class="form-group row has-error">
                            <label class="col-sm-12 col-form-label">Banner</label>
                            <div class="col-sm-12">
                                <input type="file" name="banner" class="form-control {{ $errors->has('banner') ? 'is-invalid' : '' }}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('banner') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('special') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Tipe</label>
                            <div class="col-sm-12">
                                <select id="special" name="special" class="form-control {{ $errors->has('special') ? 'is-invalid' : ''}}" required>
                                    <option disabled selected>Pilih Tipe</option>
                                    <option value="1">Pembelian Pertama</option>
                                    <option value="2">Promo berlaku sekali</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('special') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('reward_disc') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Diskon</label>
                            <div class="col-sm-12">
                                <input type="number" min="0" max="100" step="0.1" id="reward_disc" name="reward_disc" class="form-control {{ $errors->has('reward_disc') ? 'is-invalid' : '' }}" placeholder="Diskon" value="{{old('reward_discount')}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('reward_disc') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('max') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Maksimal Potongan</label>
                            <div class="col-sm-12">
                                <input type="number" min="0" step="1" name="max" class="form-control {{ $errors->has('max') ? 'is-invalid' : '' }}" placeholder="Maksimal Potongan" value="{{ old('max') }}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('max') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Deskripsi</label>
                            <div class="col-sm-12">
                                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Description"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Highlight</label>
                            <div class="col-sm-12">
                                <textarea class="editor" name="highlight"></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Simpan</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Tutup</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div id="editPromoModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Promo Spesial</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/special-promo/update')}}@elseif(auth()->user()->account_role == 'admin'){{url('admin/special-promo/update')}}@else{{url('superadmin/special-promo/update')}}@endif" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group row has-error">
                            <label class="col-sm-12 col-form-label">Judul</label>
                            <div class="col-sm-12">
                                <input type="hidden" id="edit_id" name="id">
                                <input type="text" id="edit_promo_title" name="promo_title" class="form-control {{ $errors->has('promo_title') ? 'is-invalid' : '' }}" placeholder="Judul Promo" value="{{ old('promo_title') }}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('promo_title') }} </span>
                            </div>
                        </div>
                        <div class="form-group row has-error">
                            <label class="col-sm-12 col-form-label">Banner</label>
                            <div class="col-sm-12">
                                <input type="file" name="banner" class="form-control {{ $errors->has('banner') ? 'is-invalid' : '' }}">
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('banner') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('special') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Tipe</label>
                            <div class="col-sm-12">
                                <select id="edit_special" name="special" class="form-control {{ $errors->has('special') ? 'is-invalid' : ''}}" required>
                                    <option disabled selected>Pilih Tipe</option>
                                    <option value="1">Pembelian Pertama</option>
                                    <option value="2">Promo berlaku sekali</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('special') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('reward_disc') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Diskon</label>
                            <div class="col-sm-12">
                                <input type="number" id="edit_reward_disc" min="0" max="100" step="0.1" name="reward_disc" class="form-control {{ $errors->has('reward_disc') ? 'is-invalid' : '' }}" placeholder="Diskon" value="{{old('reward_discount')}}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('reward_disc') }} </span>
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('max') ? 'has-error' : '' }}">
                            <label class="col-sm-12 col-form-label">Maksimal Potongan</label>
                            <div class="col-sm-12">
                                <input type="number" id="edit_max" min="0" step="1" name="max" class="form-control {{ $errors->has('max') ? 'is-invalid' : '' }}" placeholder="Maksimal Potongan" value="{{ old('max') }}" required>
                            </div>
                            <div class="col-sm-12">
                                <span class="text-small">  {{ $errors->first('max') }} </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Deskripsi</label>
                            <div class="col-sm-12">
                                <textarea id="edit_description" name="description" class="form-control" rows="5" placeholder="Description"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">Highlight</label>
                            <div class="col-sm-12">
                                <textarea class="editor" name="highlight" id="edit_highlight"></textarea>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-blue">Simpan</button> &nbsp &nbsp
                                <a href="javascript:void(0)" class="" data-dismiss="modal">Tutup</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

<script>
    $(function(){
        $('.toggle-class').change(function(){
            var status = $(this).prop('checked') == true ? 1 : 0;
            var promo_id = $(this).data('id');
            var role = $(this).data('user_role');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: '/'+ role +'/special-promo-status/'+promo_id,
                data: {'status': status, 'promo_id': promo_id},
                success: function(data){
                    showNotif("Perubahan status sukses")
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    showAlert(thrownError);
                }
            });
        })
    })

    function setData(id, title, special, reward_disc, max, description, highlight) {
        $('#edit_id').val(id);
        $('#edit_promo_title').val(title);
        $('#edit_special').val(special).change();
        $('#edit_reward_disc').val(reward_disc);
        $('#edit_max').val(max);
        // tinyMCE.get('edit_description').setContent(description);
        $('#edit_description').text(description);
        tinyMCE.get('edit_highlight').setContent(highlight);
        // $('#edit_highlight').text(highlight);
    }

    function hapus(id) {
        if(confirm('Yakin Akan Menghapus Data ?')) {
            var formData = {
                _token: "{{ csrf_token() }}",
                id: id,
                url: "{{ Request::url() }}",
            };

            var id = id;

            $.ajax({
                type: 'POST',
                url: @if(auth()->user()->account_role == 'manager')
                        '/manager/special-promo/delete/' + id
                        @elseif(auth()->user()->account_role == 'superadmin')
                            '/superadmin/special-promo/delete/' + id
                        @elseif(auth()->user()->account_role == 'admin')
                            '/admin/special-promo/delete/' + id
                        @endif,
                data: formData,
                dataType: "json",
                encode: true,
            }).done(function (data) {
                location.reload();
                showNotif("Data Berhasil Dihapus!");
            });
        } else {
            return false;
        }
    }
</script>
<style>
    .image-outer{
        width: 150px;
        height: 150px;
        -webkit-border-radius: 10px;
        -moz-border-radius: 10px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        border: 1px solid #f1f1f1;
        padding: 5px;
    }

    .image-outer img{
        position: absolute;
        width: 150px;
        height: auto;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
    }
</style>
@stop