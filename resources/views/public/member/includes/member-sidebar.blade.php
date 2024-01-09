<div class="profile-menu">

    @php 
        $user = Auth::user();
        $wishlists = App\Wishlist::where('user_id', Auth::user()->id)
                                    ->orderBy('id','asc')->get();
    @endphp

    <div class="profile">
        @if($user->photo=="")
        <img class="img-fluid" id="photo" src="{{asset('user-photo.png')}}">
        @else
        <img class="img-fluid" id="photo" src="{{asset($user->photo)}}">
        @endif
        <div class="profile-name">
            <h5>{{ ucwords($user->name) }}</h5>
        </div>

    </div>


    <style>
        .scroll {
            position: relative;
            white-space: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        .vertical-align-middle {
        }

        .scroll::-webkit-scrollbar {
            display: none;
        }

        .mobile-nav{
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .mobile-nav .mobile-item{
            padding: 3px 5px 3px 5px;
            color: #000000;
            border-bottom: 2px solid transparent;
        }

        .mobile-nav .mobile-item.active{
            color: darkblue;
            border-bottom: 2px solid darkblue;
            background: #e1f1fd;
            border-radius: 0px;
        }

        .sidebar-div a {
            display: block;
            font-size: 12pt;
            color: #000000;
        }

        .sidebar-div a:hover {
            color: #0012bb;
        }

        .sidebar-div .nav-link.active {
            color: #0012bb;
            background: #e1f1fd;
            border: 1px solid #0012bb;
        }

        .sidebar-div i {
            width: 40px;
            text-align: center;
            font-size: 1.3rem;
            color: #0012bb;
        }

        #show-on-mobile{
            display: none;
        }

        #hide-on-mobile{
            display: block;
        }

        @media screen and (max-width:768px){
            #show-on-mobile{
                display: block;
            }
            #hide-on-mobile{
                display: none;
            }
        }

    </style>

    

    <div class="sidebar-div">
        <ul class="nav flex-column">
            <li class="nav-break">GENERAL</li>
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='dashboard')
                    {{'active'}}
                    @endif" href="{{url('/member/dashboard')}}">
                 Dashboard <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='order')
                    {{'active'}}
                    @endif" href="{{url('/member/order')}}">
                    Order<span class="sr-only">(current)</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='history')
                    {{'active'}}
                    @endif" href="{{url('/member/history')}}">
                    History<span class="sr-only">(current)</span>
                </a>
            </li> -->

            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='wishlist')
                    {{'active'}}
                    @endif" href="{{url('/member/wishlist')}}">
                    Wishlists <span class="badge badge-danger pull-right">{{count($wishlists)}}</span>
                </a>
            </li>

            <li class="nav-break">ACCOUNT</li>
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='profile')
                    {{'active'}}
                    @endif" href="{{url('/member/profile')}}">
                    Profile<span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='notifications')
                    {{'active'}}
                    @endif" href="{{url('/member/notifications')}}">
                    Notifications<span class="sr-only">(current)</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='address')
                    {{'active'}}
                    @endif" href="{{url('/member/address')}}">
                    Alamat Pengiriman
                </a>
            </li>
            <li class="nav-break">SIGN OUT</li>
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='logout')
                    {{'active'}}
                    @endif" href="{{url('member/logout')}}">
                    Logout<span class="sr-only">(current)</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<br><br>
