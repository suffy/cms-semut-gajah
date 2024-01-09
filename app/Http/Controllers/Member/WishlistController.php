<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Product;
use App\User;
use App\Wishlist;
use Session;

class WishlistController extends Controller
{
	protected $product;
	protected $wishlist;

	public function __construct(Product $product, Wishlist $wishlist)
	{
		$this->product = $product;
		$this->wishlist = $wishlist;
	}
	// public function search(Request $request)
	// {
	// 	$category = Category::orderBy('id','asc')->get();
	// 	$city     = City::orderBy('id','asc')->get();
	// 	$venue    = Venue::orderBy('id','asc')->get();
	// 	$search = $request->get('search');
	// 	$media = Db::table('medias')
	// 	->orderBy('name','asc')
	// 	->where('name', 'like', '%'.$search.'%')
	// 	->paginate(10);

	// 	return view('public.member.wishlist', compact('media'), compact('category'), compact('city'), compact('venue'));

	// }

	public function index() {
		$wishlists = $this->wishlist->where('user_id', Auth::user()->id)
                    ->orderBy('id','asc')->get();
		$product = $this->product->orderBy('id')->get();
		return view('public.member.wishlist', compact(['product', 'wishlists']));
	}
	// public function detail(media $media) {

	// 	return view('public.member.wishlist-detail',  compact('media'), compact('category'), compact('city'), compact('venue'));;
	// }

	public function store($product_id, $user_id){
		$wishlists = $this->wishlist->where('product_id', $product_id)
					->where('user_id', $user_id)->first();
		// return response()->json(['success'=>$wishlists, 'status' => '1']);
        $status = 0;
		if($wishlists){
            $status = 0;
			$wishlists->delete();
		}else{
			$wishlists = $this->wishlist->create([
							'product_id'=> $product_id,
							'user_id' => $user_id
						]);
            $status = 1;
		}
		return response()->json(['success'=>'Status change successfully.', 'status' => $status]);
	}
}
