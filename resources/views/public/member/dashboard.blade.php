@extends('public.layout.member-layout')

@section('member-content')
	@php
        $user = Auth::user();
        $wishlists = App\Wishlist::where('user_id', Auth::user()->id)
									->orderBy('id','asc')->get();
		$orders = App\Order::where('customer_id', Auth::user()->id)
				->orderBy('id','asc')->get();
    @endphp
	<div class="container">
		<h5>Halo, {{ ucwords($user->name) }}</h5>

		<div class="row">
			<div class="col-md-3">
				<div class="dashboard-item">
					<p>Orders<br>
                    <span>{{count($orders)}}</span></p>
				</div>
			</div>
			<div class="col-md-3">
				<div class="dashboard-item">
					<p>Wishlist<br>
                    <span>{{count($wishlists)}}</span></p>
				</div>
			</div>
		</div>
	</div>
@endsection
