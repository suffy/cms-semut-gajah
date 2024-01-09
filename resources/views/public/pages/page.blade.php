@extends('public.layout.template')

@section('content')

<section id="home" style="margin-top: -50px;">

</section>


<div class="container">
    <br>
     <h2 class="card-title" style="margin-bottom: 60px;"><b>Other Pages</b></h2>
    <div class="row">
        
     @foreach ($pages as $page)
     <div class="col-md-4">
        <div class="">
            <div class="card-body">
                <a href="page/{{ $page->slug }}">{!! $page->title !!}</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
</div>
<br></br>


<style>
    a.cat{
        border: 1px solid #f9df1f;
        color: #0012bb;
        padding: 3px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        text-decoration: none;
    }

    .card-title{
        margin-top: 10px;
    }

    .card-title a{
        color: #0012bb;
        text-decoration: none;
    }
</style>

@stop
