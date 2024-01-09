@extends('public.layout.member-layout')

@section('member-content')
<div class="page-member-title">
    <h3>List Media</h3>
</div>

<a href="{{url('member/medias/create')}}" class="btn btn-primary">New Media</a>
<hr>
<br>
<div class="row">
    @foreach($media as $med)
    <div class="col-sm-6 col-md-4">
        <div class="card-box-media">
            <div class="card-img">
                <div class="promo-img">
                    <a href="#">
                        <div class="media-cat">
                            <div class="media-cat-text">
                                <span class="media-img-circle">
                                    <img src="{{ asset($med->category->icon) }}" alt="" class="img-fluid media-img">
                                </span>
                                <span class="sol-blue">{{$med->category->category_name}}</span>
                            </div>
                        </div>
                    </a>
                </div>
                @if(count($med->media_gallery)==0)
                <img src="{{asset('images/no-images.png')}}" class="img-fluid media-grid-cover" width="100%">
                @endif
                @foreach($med->media_gallery as $no => $gal)
                @if($no==0)
                <a href="{{ url('products/'.$med->id."/".str_replace(" ", "-",$med->name)) }}">
                    <img src="{{asset($gal->path)}}" class="img-fluid media-grid-cover" width="100%">
                </a>
                @endif
                @endforeach
            </div>
            <div class="text" style="padding-top: 15px;">
                <a href="{{ url('products/'.$med->id.'/'.str_replace(" ","-",$med->name)) }}" target="_blank">
                    <p class="sub-service sol-blue">{{ $med->name }}</p>
                </a>

                <p>{{$med->city->city_name}}</p>
                <span class="sol-red"> Rp {{number_format($med->price)}}</span>
                <hr>

                <a href="{{url('member/medias/'.$med->id)}}" class="btn btn-primary btn-sm">Details</a>
            </div>
        </div>
    </div>
    @endforeach
</div>




<style>
    p {
        margin-bottom: 5px;
    }

</style>

@endsection
