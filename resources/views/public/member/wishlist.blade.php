@extends('public.layout.member-layout')

@section('member-content')
<div class="page-member-title">
    <h3>Wishlists</h3>
</div>
<div class="wishlist">
    <div class="row">
        <div class="col-md-12">
            <div class="section-title">
                @if(count($wishlists) > 0)
                <p>Berikut adalah semua produk yang anda sukai.</p>
                @else
                <p>Belum ada produk yang anda sukai untuk saat ini.</p>
                @endif
            </div>
            <div class="row">
                @foreach($wishlists as $item)

                @if($item->product)
                <div class="col-md-4">
                    <div class="book-content product-box">
                        <img src="{{ asset($item->product->image) }}" alt="" class="w-100 book-img">
                        <div class="book-title-box">
                            <a href="{{ url('products', $item->product->slug) }}" class="book-title">
                                {{ $item->product->name }}
                            </a>
                        </div>
                        <div class="book-price-box">
                            @if ($item->product->price_promo != null)
                            <span class="strike-through col-red">Rp. {{ number_format($item->product->price_promo, 0, ".", ".") }}</span>
                            <br>
                            <span>Rp. {{ number_format($item->product->price, 0, ".", ".") }}</span>
                            @else
                            <span>Rp. {{ number_format($item->product->price, 0, ".", ".") }}</span>
                            @endif
                        </div>
                        
                    </div>
                </div>
                @endif

                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
