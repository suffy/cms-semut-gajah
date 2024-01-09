@extends('public.layout.member-layout')

@section('member-content')
  <div class="page-member-title">
    <h3>Wishlist Detail</h3>
  </div>
  <div class="row">
    <div class="col-md-5">
      <img src="{{asset('themes/solarindo')}}/images/media1.png" alt="" class="img-responsive" width="100%">
    </div>
    <div class="col-md-7">
      <h5>{{ $media->name }}</h5>
      <div style="margin-bottom: 15px">
        @if($media->status == 0)
          <span class="button-green" style="display: inline-block">available</span>
        @else
          <span class="button-red" style="display: inline-block">unavailable</span>
        @endif
        <div class="rating sol-yellow" style="display: inline-block; margin-left: 10px;">
          <i class="fa fa-star"></i>
          <i class="fa fa-star"></i>
          <i class="fa fa-star"></i>
          <i class="fa fa-star-o"></i>
          <i class="fa fa-star-o"></i>
        </div>
      </div>
      <span class="clearfix"> </span>
      <table class="specification" style="margin-bottom: 20px">
          <col width="120">
          <col width="">
          <tr>
            <td>Nama Vendor</td>
            <td>: xxx</td>
          </tr>
          <tr>
            <td>Alamat</td>
            <td>: {{ $media->address }}</td>
          </tr>
          <tr>
            <td>Kategori</td>
            <td>: {{ $media->category->category_name }}</td>
          </tr>
          <tr>
            <td>Venue</td>
            <td>: {{ $media->venue->venue_name }}</td>
          </tr>
          <tr>
            <td>Kota</td>
            <td>: {{ $media->city->city_name }}</td>
          </tr>
          <tr>
            <td>Harga</td>
            <td>: {{ $media->price }} / xxx</td>
          </tr>
          <tr>
            <td>Waktu Sewa</td>
            <td>: xxx</td>
          </tr>
          <tr>
            <td>No. Telp</td>
            <td>: xxx</td>
          </tr>
          <tr>
            <td>Email</td>
            <td>: xxx</td>
          </tr>
        </table>
        <div class="row">
          <div class="col-md-6">
              <a href="" class="btn button-yellow" style="width:100%">Action</a>
        </div>
        <div class="col-md-6">
          
          <a href="" class="btn button-blue" style="width:100%;">See Map</a>
        </div>
        </div>
    </div>
  </div>
@endsection
