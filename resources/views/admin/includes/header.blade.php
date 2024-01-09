<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
    <button class="btn btn-primary" id="menu-toggle">
        <span class="fa fa-navicon"></span>
    </button>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ml-auto mt-2 mt-lg-0">
        <li class="nav-item mr-5 mt-1">
          <a class="nav-link" href="#">
            <img src="@if(auth()->user()->photo != null){{asset(auth()->user()->photo)}}@else{{asset('images/core/icon-user-one.svg')}}@endif" class="img-responsive rounded-circle" width="20" > {{ auth()->user()->name }}
          </a>
        </li>
        <li class="nav-item">
          <a class="btn btn-danger mt-2" href="{{ route('logout') }}" 
            onclick="event.preventDefault();
            document.getElementById('logout-form').submit();"
          >Log Out</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </li>
      </ul>
    </div>
  </nav>
