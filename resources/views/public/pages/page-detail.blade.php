@extends('public.layout.template')

@section('content')

<section id="home" style="margin-top: 50px;">
    <header>
        
    </header>
</section>

<div class="container">
    <br>
    <div class="row">
        <div class="col-md-12">
        </div>
        <div class="col-md-12">
            <h2 class="card-title"><b>{!! $page->title !!}</b></h2>
            <div class="page-content">
                {!! $page->content !!}
            </div>
            <p class="card-text"></p>		
       </div>
   </div>
</div>
<br>

<style>
    .page-content img,
    .page-content iframe {
        max-width: 100% !important;
    }
</style>

@stop