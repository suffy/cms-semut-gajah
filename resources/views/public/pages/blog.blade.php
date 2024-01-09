@extends('public.layout.template')

@section('content')
<br><br>
<div class="container">
    <form  action="{{url('blog')}} ">
    
    <div class="row">

        <div class="col-md-3 col-lg-3">
            <div class="form-group">
                <select class="form-control" id="orderby" name="orderby">
                    <option value="newest" @if(\Illuminate\Support\Facades\Request::get('orderby')=="newest") selected @endif>Terbaru</option>
                    <option value="oldest" @if(\Illuminate\Support\Facades\Request::get('orderby')=="oldest") selected @endif>Terlama</option>
                </select>
            </div>
        </div>
        
        <div class="offset-md-3 col-md-6 offset-lg-6 col-lg-3">
            <div class="search">
                <div class="custom-form-search">
                     <div class="form-group">
                        <span class="fa fa-search"></span>
                        <input type="text" name="search" class="search-input form-control" placeholder="Pencarian" autocomplete="off">
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</form>
    <br>
    <div class="row">
        @foreach ($post as $posts)
        <div class="col-md-4">
            <div class="card">
                <img src="{{ asset($posts->featured_image) }}" class="card-img-top img-fluid img-responsive" alt="...">
                <div class="card-body">
                    <form action="{{url('blog')}} ">
                        <input type="hidden" name="category" value="{{ $posts->category->id }}">
                        <button type="submit" class="btn cat">{{$posts->category->name}}</button>
                    </form>
                    <p class="card-title"><a href="blog/{{ $posts->slug }}">{!! $posts->title !!}</a></p>
                    <p class="card-text">{!!$posts->excerpt!!}</p>
                </div>
            </div>
            <br>
        </div>
        @endforeach
        <div class="col-md-12">
            {{ $post->appends(request()->query())->links() }}
        </div>
    </div>
</div>
<br><br>


<style>
    button.cat{
        border: 1px solid #0012bb;
        color: #0012bb;
        padding: 3px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        text-decoration: none;
        background: none
    }

    .card-title{
        margin-top: 10px;
    }

    .card-title a{
        color: #0012bb;
        text-decoration: none;
    }

    .search {
        position: relative;
        margin: 0 auto ;
        text-align: center;
    }
    .search input {
        outline:none;
        margin-bottom: 0;
    }
    .search .fa-search {
        position: absolute;
        right: 10px;
        top: 10px;
    }

    @media screen and (max-width: 768px) {
        .search input {
            margin-bottom: 0;
        }
    }
</style>

<script>
    $('#orderby').on('change', function(){
        $(this).closest("form").submit();
    })
</script>


@stop
