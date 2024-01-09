@php 
$transaction_new = \App\Order::whereIn('status', [1,2,3])->count();
$transaction_new_distributor = \App\Order::whereIn('status', [1,2,3])->where('site_code', auth()->user()->site_code)->count();
$chat_new = \App\Chat::where('status', null)->where('to_id', auth()->user()->id)->count();
$message_new = \App\Message::where('status', null)->count();
$complaint_new = \App\Complaint::where('status', null)->count();
$complaint_new_distributor = \App\Complaint::where('status', null)->with('user')->whereHas('user', function($query) { $query->where('site_code', auth()->user()->site_code);})->count();
@endphp
<div class="bg-light border-right" id="sidebar-wrapper">
    <div class="sidebar-title">
            <div class="sidebar-logo">
            <img src="{{asset('images/core/logo.svg')}}" class="img-fluid" style="width: 57px; height:52px">
            </div>
            <div class="panel-body">
                Hi, {{\Illuminate\Support\Facades\Auth::user()->name}}<br>
                <span style="color: #aaaaaa; font-size: 8pt;">Last Login:<br> {{date('d F Y - H:i:s', strtotime(\Illuminate\Support\Facades\Auth::user()->last_login)) }}</span>
            </div>
    </div>
    <div class="list-group list-group-flush">

      <ul class="nav flex-column">

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>General</span>
            <a class="d-flex align-items-center text-muted" href="#">
                <span data-feather="plus-circle"></span>
            </a>
        </h6>
        
          <li class="nav-item">
              <a class="nav-link
                  @if(Request::segment(2)=='')
              {{'active'}}
              {{-- @endif" href="{{url('/' . Auth::user()->account_role)}}"> --}}
              @endif" href="{{url('/admin')}}">
                  <img src="{{asset('images/core/icon-dashboard.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Dashboard <span class="sr-only">(current)</span>
              </a>
          </li>

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='profile')
                {{'active'}}
                @endif" href="{{url('/admin/profile')}}">
                    <img src="{{asset('images/core/icon-user-one.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                    Profile <span class="sr-only">(current)</span>
                </a>
            </li>
        @endif

          {{-- <li class="nav-item">
              <a class="nav-link
                  @if(Request::segment(2)=='setting')
              {{'active'}}
              @endif" href="{{url('/admin/setting')}}">
                  <img src="{{asset('images/core/icon-service.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Setting <span class="sr-only">(current)</span>
              </a>
          </li> --}}
        
        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
            <li class="nav-item">
                <a class="nav-link
                    @if(Request::segment(2)=='access')
                        {{'active'}}
                    @endif" href="
                                @if(auth()->user()->account_role == 'manager')
                                    {{url('/manager/access')}}
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    {{url('/superadmin/access')}}
                                @endif
                            ">
                    <img src="{{asset('images/core/icon-access.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                    Access <span class="sr-only">(current)</span>
                </a>
            </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
              <a 
                class="nav-link
                    @if(Request::segment(2)=='mapping-site')
                        {{'active'}}
                    @endif" 
                href="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('/manager/mapping-site')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('/superadmin/mapping-site')}}
                    @endif
                  ">
                  <img src="{{asset('images/core/icon-building.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Mapping Site <span class="sr-only">(current)</span>
              </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
          <li class="nav-item">
              <a 
                class="nav-link
                    @if(Request::segment(2)=='salesmen')
                        {{'active'}}
                    @endif" 
                href="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('/manager/salesmen')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('/superadmin/salesmen')}}
                    @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                            {{url('/distributor/salesmen')}}
                    @endif
                  ">
                  <img src="{{asset('images/core/icon-sales.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Salesmen <span class="sr-only">(current)</span>
              </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
            <li class="nav-item">
                {{-- <a 
                    class="nav-link
                        @if(Request::segment(2)=='customers')
                            {{'active'}}
                        @endif" 
                    href="
                        @if(auth()->user()->account_role == 'manager')
                            {{url('/manager/customers')}}
                        @elseif(auth()->user()->account_role == 'superadmin')
                            {{url('/superadmin/customers')}}
                        @elseif(auth()->user()->account_role == 'distributor')
                            {{url('/distributor/customers')}}
                        @endif
                    ">
                    <img src="{{asset('images/core/icon-user.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                    Customers <span class="sr-only">(current)</span>
                </a> --}}
                <a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-3" data-toggle="collapse" data-target="#submenu4" aria-expanded="false">
                    <img src="{{asset('images/core/icon-user.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                    Customers <span class="sr-only">(current)</span>
                    <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span>
                </a>
                <ul class="collapse 
                @if(Request::segment(2)=='customers') 
                    {{'show '}}
                @endif" id="submenu4" role="menu" aria-labelledby="btn-3">
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                        <li class="nav-sub-item">
                            <a 
                                href="
                                @if(auth()->user()->account_role == 'manager')
                                    {{url('/manager/customers')}}
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    {{url('/superadmin/customers')}}
                                @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                                    {{url('/distributor/customers')}}
                                @endif
                                " 
                                class="
                                    @if(Request::segment(2)=='customers' && Request::segment(3) == null)
                                        {{'active'}}
                                    @endif
                                ">Index</a>
                        </li>
                    @endif
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                        <li class="nav-sub-item">
                            <a 
                                href="
                                @if(auth()->user()->account_role == 'manager')
                                    {{url('/manager/customers/recaps')}}
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    {{url('/superadmin/customers/recaps')}}
                                @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                                    {{url('/distributor/customers/recaps')}}
                                @endif
                                " 
                                class="
                                    @if(Request::segment(3)=='recaps')
                                        {{'active'}}
                                    @endif
                                ">Recap Order</a>
                        </li>
                    @endif
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                        <li class="nav-sub-item">
                            <a 
                                href="
                                @if(auth()->user()->account_role == 'manager')
                                    {{url('/manager/customers/approval')}}
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    {{url('/superadmin/customers/approval')}}
                                @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                                    {{url('/distributor/customers/approval')}}
                                @endif
                                " 
                                class="
                                    @if(Request::segment(2)=='customers' && Request::segment(3) == 'approval')
                                        {{'active'}}
                                    @endif
                                ">Approval</a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

          {{-- <li class="nav-item">
              <a class="nav-link
                @if(Request::segment(2)=='customers-old')
              {{'active'}}
              @endif" href="{{url('/admin/customers-old')}}">
                  <img src="{{asset('images/core/icon-user.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Customers <span class="sr-only">(current)</span>
              </a>
          </li> --}}

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
          <li class="nav-item">
              <a 
                class="nav-link
                    @if(Request::segment(2)=='banners')
                        {{'active'}}
                    @endif" 
                href="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('/manager/banners')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('/superadmin/banners')}}
                    @elseif(auth()->user()->account_role == 'admin')
                        {{url('/admin/banners')}}
                    @endif
                ">
              <img src="{{asset('/images/core/icon-banner.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Banners <span class="sr-only">(current)</span>
              </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
          <li class="nav-item">
              <a 
                class="nav-link
                    @if(Request::segment(2)=='orders')
                        {{'active'}}
                    @endif" 
                href="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('/manager/orders')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('/superadmin/orders')}}
                    @elseif(auth()->user()->account_role == 'admin')
                        {{url('/admin/orders')}}
                    @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                        {{url('/distributor/orders')}}
                    @endif
                ">
              <img src="{{asset('/icon-order.png')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Orders <span class="sr-only">(current)</span> <span class="badge badge-danger pull-right">@if(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho'){{ $transaction_new_distributor }}@else{{ $transaction_new }}@endif</span>
              </a>
          </li>
        @endif
          {{-- <li class="nav-item">
              <a class="nav-link
                  @if(Request::segment(2)=='orders-old')
              {{'active'}}
              @endif" href="{{url('/admin/orders-old')}}">
              <img src="{{asset('/icon-order.png')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Orders <span class="sr-only">(current)</span> <span class="badge badge-danger pull-right">{{$transaction_new}}</span>
              </a>
          </li> --}}

        <!-- @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
              <a 
                class="nav-link
                    @if(Request::segment(2)=='vouchers')
                        {{'active'}}
                    @endif" 
                href="
                    @if(auth()->user()->account_role == 'manager')
                        {{url('/manager/vouchers')}}
                    @elseif(auth()->user()->account_role == 'superadmin')
                        {{url('/superadmin/vouchers')}}
                    @endif
                ">
              <img src="{{asset('/icon-voucher.png')}}" class="img-responsive sidebar-icon-png" width="40" >
                    Vouchers <span class="sr-only">(current)</span>
              </a>
          </li>
        @endif -->

        {{-- @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='helps')
            {{'active'}}
            @endif" 
            @if(auth()->user()->account_role == 'manager')
                href="{{url('/manager/missions')}}"
            @elseif (auth()->user()->account_role == 'superadmin')
                href="{{url('/')}}"
            @endif>
            <img src="{{asset('/report.png')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Missions<span class="sr-only">(current)</span>
            </a>
          </li>
        @endif --}}

        {{-- @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
        <li class="nav-item">
            <a 
              class="nav-link
                  @if(Request::segment(2)=='redeem-point')
                      {{'active'}}
                  @endif" 
              href="
                @if(auth()->user()->account_role == 'manager')
                    {{url('/manager/redeem-point')}}
                @elseif(auth()->user()->account_role == 'superadmin')
                    {{url('/superadmin/redeem-point')}}
                @endif
              ">
            <img src="{{asset('images/core/icon-sales.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Redeem Point <span class="sr-only">(current)</span>
            </a>
        </li>
        @endif --}}

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
        <li class="nav-item">
            <a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-7" data-toggle="collapse" data-target="#submenu7" aria-expanded="false">
                <img src="{{asset('/icon-voucher.png')}}" class="img-responsive sidebar-icon-png" width="40" >
                Offer
                <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span>
            </a>
                <ul class="collapse 
                @if(Request::segment(2)=='promo' || Request::segment(2)=='special-promo' || Request::segment(3)=='missions' || Request::segment(3)=='top-spender')
                    {{'show '}}
                @endif" id="submenu7" role="menu" aria-labelledby="btn-7">
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                    <li class="nav-sub-item">
                        <a 
                        class="
                            @if(Request::segment(2)=='promo')
                                {{'active'}}
                            @endif" 
                        href="
                            @if(auth()->user()->account_role == 'manager')
                                {{url('/manager/promo')}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('/superadmin/promo')}}
                            @elseif(auth()->user()->account_role == 'admin')
                                {{url('/admin/promo')}}
                            @endif
                        ">
                            Promo
                        </a>
                    </li>
                    @endif
            
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                    <li class="nav-sub-item">
                        <a 
                        class="
                            @if(Request::segment(2)=='special-promo')
                                {{'active'}}
                            @endif" 
                        href="
                            @if(auth()->user()->account_role == 'manager')
                                {{url('/manager/special-promo')}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('/superadmin/special-promo')}}
                            @elseif(auth()->user()->account_role == 'admin')
                                {{url('/admin/special-promo')}}
                            @endif
                        ">
                            Special Promo
                        </a>
                    </li>
                    @endif
            
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
                    <li class="nav-sub-item">
                        <a class="
                            @if(Request::segment(2)=='missions')
                        {{'active'}}
                        @endif" 
                        @if(auth()->user()->account_role == 'manager')
                            href="{{url('/manager/missions')}}"
                        @elseif (auth()->user()->account_role == 'superadmin')
                            href="{{url('/')}}"
                        @endif>
                            Missions
                        </a>
                    </li>
                    @endif
            
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                    <li class="nav-sub-item">
                        <a 
                        class="
                            @if(Request::segment(2)=='top-spender')
                                {{'active'}}
                            @endif" 
                        href="
                            @if(auth()->user()->account_role == 'manager')
                                {{url('/manager/top-spender')}}
                            @elseif(auth()->user()->account_role == 'superadmin')
                                {{url('/superadmin/top-spender')}}
                            @elseif(auth()->user()->account_role == 'admin')
                                {{url('/admin/top-spender')}}
                            @endif
                        ">
                            Top Spender
                        </a>
                    </li>
                    @endif
                </ul>
        </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
        <li class="nav-item">
            <a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-3" data-toggle="collapse" data-target="#submenu3" aria-expanded="false">
                <img src="{{asset('images/core/icon-box.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                Produk
                <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span>
            </a>
                <ul class="collapse 
                @if(Request::segment(2)=='products' || Request::segment(2)=='categories' || Request::segment(3)=='availability' || Request::segment(3)=='notif' || Request::segment(2)=='locations')
                    {{'show '}}
                @endif" id="submenu3" role="menu" aria-labelledby="btn-3">
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                        <li class="nav-sub-item">
                            <a 
                                href="
                                    @if(auth()->user()->account_role == 'manager')
                                        {{url('/manager/products')}}
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        {{url('/superadmin/products')}}
                                    @elseif(auth()->user()->account_role == 'admin')
                                        {{url('/admin/products')}}
                                    @elseif(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                                        {{url('/distributor/products')}}
                                    @endif
                                " 
                                class="
                                    @if(Request::segment(2)=='products')
                                        {{'active'}}
                                    @endif
                                ">Item</a>
                        </li>
                    @endif
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                        <li class="nav-sub-item">
                            <a 
                                href="
                                    @if(auth()->user()->account_role == 'manager')
                                        {{url('/manager/categories')}}
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        {{url('/superadmin/categories')}}
                                    @elseif(auth()->user()->account_role == 'admin')
                                        {{url('/admin/categories')}}
                                    @endif
                                " 
                                class="
                                    @if(Request::segment(2)=='categories')
                                        {{'active'}}
                                    @endif
                                ">Kategori</a>
                        </li>
                    @endif 
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                        <li class="nav-sub-item">
                            <a 
                                href="
                                    @if(auth()->user()->account_role == 'manager')
                                        {{url('/manager/product/availability')}}
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        {{url('/superadmin/product/availability')}}
                                    @elseif(auth()->user()->account_role == 'admin')
                                        {{url('/admin/product/availability')}}
                                    @endif
                                " 
                                class="
                                    @if(Request::segment(3)=='availability')
                                        {{'active'}}
                                    @endif
                                ">Persediaan Produk</a>
                        </li>
                    @endif
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                    <li class="nav-sub-item">
                        <a 
                            href="
                                @if(auth()->user()->account_role == 'manager')
                                    {{url('/manager/product/notif')}}
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    {{url('/superadmin/product/notif')}}
                                @elseif(auth()->user()->account_role == 'admin')
                                    {{url('/admin/product/notif')}}
                                @endif
                            " 
                            class="
                                @if(Request::segment(3)=='notif')
                                    {{'active'}}
                                @endif
                            ">Notifikasi Kenaikan Harga</a>
                    </li>
                @endif
                    <!-- @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                      <li class="nav-sub-item">
                          <a 
                            href="
                                @if(auth()->user()->account_role == 'manager')
                                    {{url('/manager/product-offers')}}
                                @elseif(auth()->user()->account_role == 'superadmin')
                                    {{url('/superadmin/product-offers')}}
                                @elseif(auth()->user()->account_role == 'admin')
                                    {{url('/admin/product-offers')}}
                                @endif
                            " 
                            class="
                                @if(Request::segment(2)=='product-offers')
                                    {{'active'}}
                                @endif
                            ">Penawaran</a>
                      </li>
                      @endif -->
                </ul>
          </li>
        @endif

          {{-- <li class="nav-item"><a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-3" data-toggle="collapse" data-target="#submenu3" aria-expanded="false">
              <img src="{{asset('images/core/icon-box.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Produk
                  <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span></a>
                  <ul class="collapse @if(Request::segment(2)=='products-old' || Request::segment(2)=='categories' || Request::segment(2)=='product-offers' || Request::segment(2)=='partner-logo' || Request::segment(2)=='locations')
                  {{'show '}}
                  @endif" id="submenu3" role="menu" aria-labelledby="btn-3">
                        <li class="nav-sub-item"><a href="{{url('/admin/products-old')}}" class="@if(Request::segment(2)=='products-old')
                                {{'active'}}
                                @endif">Item</a>
                        </li>
                        <li class="nav-sub-item"><a href="{{url('/admin/categories')}}" class="@if(Request::segment(2)=='categories')
                                {{'active'}}
                                @endif">Kategori</a>
                        </li>
                        <li class="nav-sub-item"><a href="{{url('/admin/product-offers')}}" class="@if(Request::segment(2)=='product-offers')
                                {{'active'}}
                                @endif">Penawaran</a>
                        </li>
                        <li class="nav-sub-item"><a href="{{url('/admin/partner-logo?type=vendor')}}" class="@if(Request::segment(2)=='partner-logo' && \Illuminate\Support\Facades\Request::get('type')=='vendor')
                            {{'active'}}
                            @endif">Brand</a>
                        </li>
                        <li class="nav-sub-item"><a href="{{url('/admin/locations')}}" class="@if(Request::segment(2)=='locations')
                            {{'active'}}
                            @endif">Gudang</a>
                        </li>
                        <li class="nav-sub-item"><a href="{{url('/admin/product-rating')}}" class="@if(Request::segment(2)=='product-rating')
                            {{'active'}}
                            @endif">Ulasan Pembeli</a>
                        </li>

                  </ul>
          </li> --}}
        
        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item"><a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-5" data-toggle="collapse" data-target="#submenu5" aria-expanded="false">
              <img src="{{asset('report.png')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Laporan
                  <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span></a>
                  <ul class="collapse @if(Request::segment(2)=='report-sales' || Request::segment(2)=='report-statistik' || Request::segment(2)=='report') 
                  {{'show '}}
                  @endif" id="submenu5" role="menu" aria-labelledby="btn-5">
                        <li class="nav-sub-item"><a href="{{url('/admin/report-sales')}}" class="@if(Request::segment(2)=='report-sales')
                                {{'active'}}
                                @endif">Penjualan</a>
                        </li>
                        <li class="nav-sub-item"><a href="{{url('/admin/report-statistik')}}" class="@if(Request::segment(2)=='report-statistik')
                                {{'active'}}
                                @endif">Statistik</a>
                        </li>
                        <li class="nav-sub-item">
                            <a 
                                href="
                                    @if(auth()->user()->account_role == 'manager')
                                        {{url('/manager/report')}}
                                    @elseif(auth()->user()->account_role == 'superadmin')
                                        {{url('/superadmin/report')}}
                                    @endif
                                " 
                                class="
                                    @if(Request::segment(2)=='report')
                                        {{'active'}}
                                    @endif
                                "
                            >Report</a>
                        </li>

                  </ul>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='chats')
            {{'active'}}
            @endif" href="{{url('/admin/chats')}}">
            <img src="{{asset('images/core/icon-chat.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Chats <span class="sr-only">(current)</span>
                  <span class="badge badge-danger pull-right">{{$chat_new}}</span>
            </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='complaints')
            {{'active'}}
            @endif"
            @if(auth()->user()->account_role == 'manager')
                href="{{url('/manager/complaints')}}"
            @elseif (auth()->user()->account_role == 'superadmin')
                href="{{url('/superadmin/complaints')}}"
            @elseif (auth()->user()->account_role == 'admin')
                href="{{url('/admin/complaints')}}"
            @elseif (auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                href="{{url('/distributor/complaints')}}"
            @endif
            >
            <img src="{{asset('images/core/icon-chat.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Complaints <span class="sr-only">(current)</span>
                  <span class="badge badge-danger pull-right">@if(auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho'){{ $complaint_new_distributor }}@else{{ $complaint_new }}@endif</span>
            </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='feedbacks')
                    {{'active'}}
                @endif" 

                @if(auth()->user()->account_role == 'manager')
                    href="{{url('/manager/feedbacks')}}"
                @elseif (auth()->user()->account_role == 'superadmin')
                    href="{{url('/superadmin/feedbacks')}}"
                @endif
                >
            <img src="{{asset('images/core/icon-contact.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Feedbacks <span class="sr-only">(current)</span>
            </a>
          </li>
        @endif

        {{-- @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='broadcast')
                    {{'active'}}
                @endif" 

                @if(auth()->user()->account_role == 'manager')
                    href="{{url('/manager/broadcast')}}"
                @elseif (auth()->user()->account_role == 'superadmin')
                    href="{{url('/superadmin/broadcast')}}"
                @endif
                >
                <img src="{{asset('images/core/icon-testimonial.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Broadcast Message<span class="sr-only">(current)</span>
            </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='alert')
                    {{'active'}}
                @endif" 

                @if(auth()->user()->account_role == 'manager')
                    href="{{url('/manager/alert')}}"
                @elseif (auth()->user()->account_role == 'superadmin')
                    href="{{url('/superadmin/alert')}}"
                @endif
                >
                <img src="{{asset('images/core/icon-testimonial.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Alert Pop Up<span class="sr-only">(current)</span>
            </a>
          </li>
        @endif --}}

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
        <li class="nav-item">
            <a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-8" data-toggle="collapse" data-target="#submenu8" aria-expanded="false">
                <img src="{{asset('images/core/icon-testimonial.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                Information
                <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span>
            </a>
                <ul class="collapse 
                @if(Request::segment(2)=='broadcast' || Request::segment(2)=='alert')
                    {{'show '}}
                @endif" id="submenu8" role="menu" aria-labelledby="btn-8">
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin' || auth()->user()->account_role == 'distributor' || auth()->user()->account_role == 'distributor_ho')
                        <li class="nav-sub-item">
                            <a class="
                                @if(Request::segment(2)=='broadcast')
                                    {{'active'}}
                                @endif" 
                
                                @if(auth()->user()->account_role == 'manager')
                                    href="{{url('/manager/broadcast')}}"
                                @elseif (auth()->user()->account_role == 'superadmin')
                                    href="{{url('/superadmin/broadcast')}}"
                                @endif
                                >Broadcast Message
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin' || auth()->user()->account_role == 'admin')
                        <li class="nav-sub-item">
                            <a class="
                                @if(Request::segment(2)=='alert')
                                    {{'active'}}
                                @endif" 
                
                                @if(auth()->user()->account_role == 'manager')
                                    href="{{url('/manager/alert')}}"
                                @elseif (auth()->user()->account_role == 'superadmin')
                                    href="{{url('/superadmin/alert')}}"
                                @endif
                                >Alert Pop Up
                            </a>
                        </li>
                    @endif 
                </ul>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
            <li class="nav-item">
                <a href="javascript:void(0)" class="toggle-custom nav-link" id="btn-3" data-toggle="collapse" data-target="#submenu6" aria-expanded="false">
                    <img src="{{asset('images/core/icon-news.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                    Landing Page <span class="sr-only">(current)</span>
                    <span class="fa fa-chevron-down pull-right" aria-hidden="true"></span>
                </a>
                <ul class="collapse 
                @if(Request::segment(2)=='testimonials' || Request::segment(2)=='blogs' || Request::segment(2)=='options') 
                    {{'show '}}
                @endif" id="submenu6" role="menu" aria-labelledby="btn-3">
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
                        <li class="nav-sub-item">
                            <a class="nav-link
                                @if(Request::segment(2)=='testimonials')
                                    {{'active'}}
                                @endif" 
                
                                @if(auth()->user()->account_role == 'manager')
                                    href="{{url('/manager/testimonials')}}"
                                @elseif (auth()->user()->account_role == 'superadmin')
                                    href="{{url('/superadmin/testimonials')}}"
                                @endif
                                >
                                    Testimonials <span class="sr-only">(current)</span>
                            </a>
                        </li>
                    @endif

                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
                        <li class="nav-sub-item">
                        <a class="nav-link
                            @if(Request::segment(2)=='blogs')
                                {{'active'}}
                            @endif" 

                            @if(auth()->user()->account_role == 'manager')
                                href="{{url('/manager/blogs')}}"
                            @elseif (auth()->user()->account_role == 'superadmin')
                                href="{{url('/superadmin/blogs')}}"
                            @endif
                            >
                            Blogs <span class="sr-only">(current)</span>
                        </a>
                        </li>
                    @endif
                    
                    @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
                        <li class="nav-sub-item">
                        <a class="nav-link
                            @if(Request::segment(2)=='options')
                                {{'active'}}
                            @endif" 

                            @if(auth()->user()->account_role == 'manager')
                                href="{{url('/manager/options')}}"
                            @elseif (auth()->user()->account_role == 'superadmin')
                                href="{{url('/superadmin/options')}}"
                            @endif
                            >
                                Options <span class="sr-only">(current)</span>
                        </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif


        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='jobs')
                    {{'active'}}
                @endif" 

                @if(auth()->user()->account_role == 'manager')
                    href="{{url('/manager/jobs')}}"
                @elseif (auth()->user()->account_role == 'superadmin')
                    href="{{url('/superadmin/jobs')}}"
                @endif
                >
            <img src="{{asset('images/core/icon-jobs.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Jobs <span class="sr-only">(current)</span>
            </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='help-categories')
            {{'active'}}
            @endif" 

            @if(auth()->user()->account_role == 'manager')
                href="{{url('/manager/help-categories')}}"
            @elseif (auth()->user()->account_role == 'superadmin')
                href="{{url('/superadmin/help-categories')}}"
            @endif
            >
            <img src="{{asset('images/core/icon-categories.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Help Categories <span class="sr-only">(current)</span>
            </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'manager' || auth()->user()->account_role == 'superadmin')
          <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='helps')
            {{'active'}}
            @endif" 
            @if(auth()->user()->account_role == 'manager')
                href="{{url('/manager/helps')}}"
            @elseif (auth()->user()->account_role == 'superadmin')
                href="{{url('/superadmin/helps')}}"
            @endif>
            <img src="{{asset('images/core/icon-help.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                  Helps <span class="sr-only">(current)</span>
            </a>
          </li>
        @endif

        @if (auth()->user()->account_role == 'superadmin')
        <li class="nav-item">
            <a class="nav-link
                @if(Request::segment(2)=='app-version')
            {{'active'}}
            @endif" href="{{url('/superadmin/app-version')}}">
            <img src="{{asset('images/core/icon-laptop.svg')}}" class="img-responsive sidebar-icon-png" width="40" >
                App Version <span class="sr-only">(current)</span>
            </a>
        </li>
        @endif
      </ul>

    </div>
  </div>

<style>
    img.sidebar-icon-png{
        float: left;
        margin-right: 15px;
        width: 20px;
    }

    .toggle-custom[aria-expanded='true'] .fa-chevron-down:before {
        content: "\f077";
    }
</style>
