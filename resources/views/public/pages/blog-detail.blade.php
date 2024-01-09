@extends('public.layout.template')

@section('content')

<section id="home" style="margin-top: 50px;">
    <header>

    </header>
</section>


<div class="container page-content">
    <br>
    <div class="row">

        <div class="col-md-8">
            
            
            <div class="pull-right">
                <ul class="social-nav model-2">
                    <li><a class="twitter" href="https://twitter.com/intent/tweet?url={{Request::url()}}&text=" target="_blank"><i class="fa fa-twitter"></i></a></li>
                    <li><a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={{Request::url()}}" target="_blank"><i class="fa fa-facebook"></i></a></li>
                    <li><a class="google-plus" href="https://plus.google.com/share?url={{Request::url()}}" target="_blank"><i class="fa fa-google-plus"></i></a></li>
                    <li><a class="linkedin" href="http://www.linkedin.com/shareArticle?mini=true&url={{Request::url()}}&title=" target="_blank"><i class="fa fa-linkedin"></i></a></li>
                    <li><a class="pinterest" href="http://pinterest.com/pin/create/button/?url={{Request::url()}}&media=&description=" target="_blank"><i class="fa fa-pinterest-p"></i></a></li>
                </ul>
            </div>
            <h4 class="card-title">{!! $post->title !!}</h4>
            <form action="{{url('blog')}} ">
                <input type="hidden" name="category" value="{{ $post->category->id }}">
                <p>
                <a href="#" class="btn btn-cat" onclick="$(this).closest('form').submit()">{{$post->category->name}}</a>
                | <span class="fa fa-calendar"></span> {{date('l, d F Y', strtotime($post->created_at))}}</p>
            </form>
            
                
            <img src="{{ asset($post->featured_image) }}" style="width: 100%" class="card-img-top img-fluid img-responsive" alt="...">
            <p class="card-text">{!! $post->content !!}</p>

            <hr>
            <div class="pull-right">
                <ul class="social-nav model-2">
                    <li><a class="twitter" href="https://twitter.com/intent/tweet?url={{Request::url()}}&text=" target="_blank"><i class="fa fa-twitter"></i></a></li>
                    <li><a class="facebook" href="https://www.facebook.com/sharer/sharer.php?u={{Request::url()}}" target="_blank"><i class="fa fa-facebook"></i></a></li>
                    <li><a class="google-plus" href="https://plus.google.com/share?url={{Request::url()}}" target="_blank"><i class="fa fa-google-plus"></i></a></li>
                    <li><a class="linkedin" href="http://www.linkedin.com/shareArticle?mini=true&url={{Request::url()}}&title=" target="_blank"><i class="fa fa-linkedin"></i></a></li>
                    <li><a class="pinterest" href="http://pinterest.com/pin/create/button/?url={{Request::url()}}&media=&description=" target="_blank"><i class="fa fa-pinterest-p"></i></a></li>
                </ul>
            </div>
            
        </div>
        <div class="col-md-4">

            @php
                $newest = App\Post::where('status', 1)
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get();
            @endphp

            <h5>Lastest Article</h5>
            <div class="card">
                <div class="card-body article-sidebar">
                    @foreach ($newest as $item)
                        <div class="article-list">
                            <div class="img-list">
                                <img src="{{asset($item->featured_image)}}" class="img-fluid">
                            </div>
                            <div class="list-content">
                                <a href="{{url('blog/'.$item->slug)}}">{{$item->title}}</a>
                                <p>{{$post->category->name}}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <br><br>
            <h5>Tags</h5>
            <div class="card" id="article-tags">
                <div class="card-body article-sidebar">
                    @php 
                        $tags = explode(",", $post->tags);
                    @endphp
                    @foreach($tags as $tg)
                        <a href='{{url("blog?search=").$tg}}' class='btn btn-cat' style='margin-bottom: 3px;'>{{$tg}}</a>&nbsp
                    @endforeach
                </div>
            </div>


        </div>

    </div>
</div>
<br>

<style>

    
    .card-title{
        font-size: 16pt;
        font-weight: 600;
    }

    .btn-cat{
        border: 1px solid #0012bb;
        color: #0012bb;
        padding: 0px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 4px;
        background: none;
        text-decoration: none;
    }

    .btn-tags{
        border: 1px solid #f9df1f;
        color: #0012bb;
        padding: 0px;
        padding-left: 10px;
        padding-right: 10px;
        border-radius: 0px;
        background: none;
        text-decoration: none;
        margin-bottom: 5px;
    }

    .card-text img,
    .card-text iframe {
        max-width: 100% !important;
    }

    .article-list{
        position: relative;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .article-list .img-list{
        width: 30%;
        float: left;
    }
    .article-list .list-content{
        width: 70%;
        float: left;
        padding: 0px 10px 0px 10px;
    }

    .article-list .list-content a{
        text-decoration: none;
        color: #000000;
    }


/*=====================*/
.social-nav {
  padding: 0;
  list-style: none;
  display: inline-block;
  margin: 10px auto;
}
.social-nav li {
  display: inline-block;
}
.social-nav a {
  display: inline-block;
  float: left;
  width: 38px;
  height: 38px;
  font-size: 18px;
  color: #FFF;
  text-decoration: none;
  cursor: pointer;
  text-align: center;
  line-height: 48px;
  background: #000;
  position: relative;
  -moz-transition: 0.5s;
  -o-transition: 0.5s;
  -webkit-transition: 0.5s;
  transition: 0.5s;
}

.model-2 a {
  overflow: hidden;
  font-size: 18px;
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  border-radius: 4px;
  margin: 0 5px;
}
.model-2 a:hover {
  background: #fff;
}
.model-2 .twitter {
  background: #00ACED;
}
.model-2 .twitter:hover {
  color: #00ACED;
}
.model-2 .facebook {
  background: #3B579D;
 }
.model-2 .facebook:hover {
  color: #3B579D;
}
.model-2 .google-plus {
  background: #DD4A3A;
}
.model-2 .google-plus:hover {
  color: #DD4A3A;
}
.model-2 .linkedin {
  background: #007BB6;
}
.model-2 .linkedin:hover {
  color: #007BB6;
}
.model-2 .pinterest {
  background: #CB2026;
}
.model-2 .pinterest:hover {
  color: #CB2026;
}

.page-content img,
.page-content iframe {
    max-width: 100% !important;
}
</style>


@stop
