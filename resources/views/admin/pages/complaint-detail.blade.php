@extends('admin.layout.template')
@section('content')

@if(auth()->user()->account_role == 'manager')
<a href="/manager/complaints" class="btn btn-default">Kembali</a>
@elseif(auth()->user()->account_role == 'superadmin')
<a href="/superadmin/complaints" class="btn btn-default">Kembali</a>
@elseif(auth()->user()->account_role == 'distributor')
<a href="/distributor/complaints" class="btn btn-default">Kembali</a>
@endif

<br><br>
<section class="panel">


    <header class="panel-heading">
        <b>Detail Complaints | Invoice : {{$product_complaint->order->invoice}}</b>
    </header>
    <div class="panel-body" id="panel-user">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    @if($product_complaint->product->image=="")
                        <img src="{{asset('images/no-photo.jpg')}}" class="img-fluid">
                    @else
                        <h3>{{ $product_complaint->product->name }}</h3>
                        <img src="{{ asset($product_complaint->product->image) }}" class="img-fluid" width="50" height="50">
                    @endif
                </div>
                <div class="col-md-4">
                    <h3>Complaints</h3>
                    @foreach ($complaints as $complaint)
                        <div class="box-border @if($complaint->user_id == auth()->user()->id)text-right @endif">
                            <div class="card-body">
                                <b>{{ $complaint->title }}</b>
                                
                                <p>{{ $complaint->content }}</p> <br>
                                
                                @foreach ($complaint->complaint_file as $complaint_file)
                                    @if ($complaint_file->file_1 != null)
                                        <a href="{{ asset($complaint_file->file_1) }}" target="_blank">
                                            <img src="{{ asset($complaint_file->file_1) }}" alt="file" width="100">
                                        </a>
                                    @endif
                                    @if ($complaint_file->file_2 != null)
                                        <a href="{{ asset($complaint_file->file_2) }}" target="_blank">
                                            <img src="{{ asset($complaint_file->file_2) }}" alt="file" width="100">
                                        </a>
                                    @endif
                                    @if ($complaint_file->file_3 != null)
                                        <a href="{{ asset($complaint_file->file_3) }}" target="_blank">
                                            <img src="{{ asset($complaint_file->file_3) }}" alt="file" width="100">
                                        </a>
                                    @endif
                                    @if ($complaint_file->file_4 != null)
                                        <a href="{{ asset($complaint_file->file_4) }}" download>download video</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
            </div>
            <div class="col-md-4">
            <h3>Reply Complaint</h3>
                <form action="@if(auth()->user()->account_role == 'manager'){{url('manager/complaint/send/' . $complaint->complaint->id)}}
                                @elseif(auth()->user()->account_role == 'superadmin'){{url('superadmin/complaint/send/' . $complaint->complaint->id)}}
                                @elseif(auth()->user()->account_role == 'distributor'){{url('distributor/complaint/send/' . $complaint->complaint->id)}}@endif" 
                                method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Judul</label>
                        <div class="col-sm-12">
                            <input type="text" name="title" class="form-control" placeholder="Title" value="" required>
                            <input type="hidden" name="url"  value="{{Request::url()}}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">Content</label>
                        <div class="col-sm-12">
                            <textarea name="content" class="form-control" placeholder="Content" required></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-12 col-form-label">File</label>
                        <div class="col-sm-12">
                            <input type="file" name="file" class="form-control" placeholder="File" value="">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">Send</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    
</script>

<style>
    /* SWITCH  */
    /* The switch - the box around the slider */
    .switch {
        position: relative;
        width: 60px;
        height: 34px;
        float: right;
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

    #panel-user h3{
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
</style>

@endsection
