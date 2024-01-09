@extends('admin.layout.template')

@section('content')


<section class="panel">
    <header class="panel-heading">
        <b>Profile</b>
    </header>
    <div class="card-body">
        <form action="{{ url('admin/profile/update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('put')
            
            <img src="@if($user->photo != null) {{ asset($user->photo) }} @else {{ asset('no-images.png') }} @endif" width="200px" height="200px">
            <input type="file" name="photo" id="photo">
            <h4>Account Information</h4>
            <label for="name">Name</label>
            <div class="form-group row">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="name" id="name" value="{{ $user->name }}" placeholder="Your Name" required>
                </div>
            </div>
            <label for="email">Email</label>
            <div class="form-group row">
                <div class="col-md-6">
                    <input type="email" class="form-control" name="email" id="email" value="{{ $user->email }}" placeholder="Your Email" required>
                </div>
            </div>
            <label for="phone">Phone</label>
            <div class="form-group row">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="phone" id="phone" value="{{ $user->phone }}" placeholder="+6281111111111" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
        <br>
        <form action="{{ url('admin/profile/update-password') }}" method="POST">
            @csrf
            @method('put')
            <h4>Password</h4>
            <label for="password">Password</label>
            <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}">
                <div class="col-md-6">
                    <input type="password" class="form-control" name="password" id="password" value="" placeholder="Your Password" required>
                </div>
            </div>
            <label for="email">Confirm Password</label>
            <div class="form-group row {{ $errors->has('password') ? 'has-error' : '' }}">
                <div class="col-md-6">
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirm" value="" placeholder="Your Password" required>
                </div>
                <div class="col-sm-12">
                    <span class="text-small">  {{ $errors->first('password') }} </span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</section>

<style>
    .text-small{
        font-size: 8pt;
        color: red;
    }
</style>
@stop
