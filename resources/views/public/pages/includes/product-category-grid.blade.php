<div class="header-product d-flex align-items-center">
        <h3>Shop</h3> 
        <div class="ml-auto"> Menampilkan<br class="d-xs-block d-md-none"/> {{count($products)}} dari {{$product_all}}</div>
</div>

<div class="row" id="grid-content">
    @foreach ($products as $item)
    @php
        if(Auth::user()){
            $user_id = Auth::user()->id;
            $wishlists = App\Wishlist::where('product_id',$item->id)
                    ->where('user_id',$user_id)
                    ->first();
            $status = 0;
            if($wishlists){
                $status = 1;
            }else{
                $status=0;
            }
        }
    @endphp
    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="book-content product-box">
            <div class="product-badge col-white">
                <div class="bg-red mb-1 pl-2 pr-2">SALE</div>
                <div class="bg-green">NEW</div>
            </div>
            <div class="product-star">
                @if(Auth::user())

                    @if($status==0)
                    <a href="javascript:void(0)" id="fav{{$item->id}}"
                        class="button-favorite fav-class" data-id="{{$item->id}}"
                        data-user="{{$user_id}}">
                        <span class="fa fa-star"></span>
                    </a>
                    @else
                    <a href="javascript:void(0)" id="fav{{$item->id}}"
                        class="button-favorite active fav-class" data-id="{{$item->id}}"
                        data-user="{{$user_id}}">
                        <span class="fa fa-star"></span>
                    </a>
                    @endif

                @endif
            </div>
            <div class="box-description">
            <a href="{{ url('products', $item->slug) }}">
            <img src="{{ asset($item->image) }}" alt="" class="w-100 book-img">
            <div class="book-title-box">
                <div class="book-title">
                    {{ $item->name }}
                </div>
            </div>
            <div class="book-price-box">
                @if ($item->price_promo != null)
                    <span class="strike-through col-red">Rp. {{ number_format($item->price_promo, 0, ".", ".") }}</span>
                    <br>
                    <span>Rp. {{ number_format($item->price, 0, ".", ".") }}</span>
                @else
                    <span>Rp. {{ number_format($item->price, 0, ".", ".") }}</span>
                @endif
            </div>
            </a>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{$products->appends(\Illuminate\Support\Facades\Request::except('page'))->links()}}

<script>
    $('.pagination a').on('click', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var asset = "{{asset('/')}}";
        $.ajax({
            url: url,
            method: "GET",
            beforeSend:function(){
                $('#display-loading').show();
                $('#grid-content').css({opacity:0.4});
            },
            success:function(response){

                setTimeout(function(){
                    $('#product').html(response);
                    $('#display-loading').hide();
                    $('#grid-content').css({opacity:1});
                }, 1000)

            }

        });
    });

    $(function() {
            $('.fav-class').click(function() {
                var product_id = $(this).data('id');
                var user_id = $(this).data('user');
                var x = $(this);
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: '/member/wishlist-store/' + product_id + '/' + user_id,
                    success: function(data) {
                        console.log(data);
                        if (data.status == 1) {
                            x.addClass("active");
                            showNotif("Ditambahkan ke wishlist");
                        } else {
                            x.removeClass("active");
                            showAlert("Dihapus dari wishlist");
                        }
                    }
                });
            })
        })

</script>