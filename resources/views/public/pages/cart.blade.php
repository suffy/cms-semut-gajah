@extends('public.layout.template')

@section('content')

    <div class="bread-crumb mb-5">
        <div class="container">
        <a href="{{url('')}}" class="col-blue">Home</a>
            <span>&nbsp;/&nbsp;Cart</span>
        </div>
    </div>

    <div class="container">
        <div class="row" id="page-cart-display-loading" style="display: none">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="box-border">
                    <div class="text-center">
                        <img src="{{asset('loading.gif')}}" width="100px" style="margin:auto"><br>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php 
        $user = Auth::user();
    @endphp

    @if(isset($user) && $user->account_type=="4")

    <div class="container">
        <section id="page-shopping-cart">

        </section>
    </div>

    @else
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Login') }}</div>

                        <div class="card-body">
                            <form method="POST" action="{{url('member/auth')}}" enctype="multipart/form-data">
                                @csrf
                                <input id="fcm_token" type="hidden" name="token" value="">
                                <div class="form-group row">
                                    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                            <label class="form-check-label" for="remember">
                                                {{ __('Remember Me') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Login') }}
                                        </button>

                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                                {{ __('Forgot Your Password?') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>
    @endif
@endsection

@section('js')

@endsection