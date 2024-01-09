@extends('public.layout.template')

@section('content')
    <section id="profile-member">
        <div class="page-title background-blue">
            <h1 class="text-center sol-blue">Profile</h1>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('public.member.includes.member-sidebar')
                </div>
                <div class="col-md-9">
                    @yield('member-content')
                </div>
            </div>
        </div>
    </section>

    <style>
        @media screen and (min-width: 1200px){
            #profile-member .container {
                max-width: 1300px;
            }
        }

    </style>

@endsection
