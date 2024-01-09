@extends('public.layout.member-layout')

@section('member-content')

<a href="/member/profile" class="btn btn-secondary">Kembali</a>
<br><br>
  <div class="page-member-title">
    <h3>Edit Profile</h3>
  </div>
  <form method="post" action="/member/profile/{{$user->id}}" enctype="multipart/form-data">
    @method('patch')
    @csrf
    <input type="hidden" name="url" value="{{Request::url()}}">
    <div class="form-group">
        @if($user->photo=="")
        <img class="img-thumbnail" id="photo" src="{{asset('images/no-photo.jpg')}}" style="margin: 10px 0; display: block">
        @else
        <img class="img-thumbnail" id="photo" src="{{asset($user->photo)}}" style=" max-width: 150px; margin: 10px 0; display: block">
        @endif
        <br>
        <input type="file" class="form-control" name="photo">
    </div>
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" class="form-control" name="name" id="name" value="{{ $user->name }}">
    </div>
    <div class="form-group">
      <label for="phone">Phone</label>
      <input type="text" class="form-control" name="phone" id="phone" value="{{ $user->phone }}">
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" class="form-control" name="email" id="email" value="{{ $user->email }}" required>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" class="form-control" name="password" id="password" value="">
      <span class="sol-red" style="font-size: .8rem">*Kosongkan jika tidak mengganti password</span>
    </div>

    <hr>
    <button type="submit" class="btn btn-primary">Simpan </button>

  </form>
@endsection
