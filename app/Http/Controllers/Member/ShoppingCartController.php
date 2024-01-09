<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Product;
use App\ShoppingCart;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    //

    protected $user;
    protected $product;
    protected $shopping_cart;

    public function __construct(User $user, Product $product, ShoppingCart $shopping_cart)
    {
        $this->user = $user;
        $this->product = $product;
        $this->shopping_cart = $shopping_cart;
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\Response
         */
        
    }

    public function cartList(Request $request){

        $user = Auth::user();
        $cart = $this->shopping_cart->where('user_id', $user->id)
                                ->get();

        return view('public.member.includes.member-shoppingcart')
                ->with('cart', $cart);

    }

    public function cartCount(Request $request){

        $user = Auth::user();

        if(!isset($user)){
            $status = 0;
            $msg = 'User must login';
            $data = [];
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

        $cart = $this->shopping_cart->where('user_id', $user->id)
                                ->get();

        $data = count($cart);

        $status = 200;
        $msg = 'Item count cart';
        return response()->json(compact('status', 'msg', 'data'), 200);

    }

    public function addToCart(Request $request){
        $user = Auth::user();

        if(!isset($user)){
            $status = 0;
            $msg = 'User must login';
            $data = [];
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

        $product_id = $request->product_id;
        $qty = $request->qty;
        $user_id = $user->id;

        $product = $this->product->where('id', $product_id)->first();
        $item = $this->shopping_cart->where('user_id', $user->id)
                                    ->where('product_id', $product_id)
                                    ->first();
        if($item){

            $item->qty = $qty;
            $item->price = $product->price;
            $item->total_price = $product->price*$qty;
            $item->save();

            $data = $item;

            $status = 200;
            $msg = 'Item added to cart';
            return response()->json(compact('status', 'msg', 'data'), 200);

        }else{

            $item = new $this->shopping_cart;
            $item->user_id = $user->id;
            $item->product_id = $product_id;
            $item->qty = $qty;
            $item->price = $product->price;
            $item->total_price = $product->price*$qty;

            $item->save();

            $data = $item;

            $status = 200;
            $msg = 'Item updated to cart';
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

    }

    public function changeQty(Request $request){
        $user = Auth::user();

        if(!isset($user)){
            $status = 0;
            $msg = 'User must login';
            $data = [];
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

        $cart_id = $request->cart_id;
        $qty = $request->qty;

        $item = $this->shopping_cart->find($cart_id);
        if($item){
            $product = $this->product->where('id', $item->product_id)->first();

            $item->qty = $qty;
            $item->price = $product->price;
            $item->total_price = $product->price*$qty;
            $item->save();

            $data = $item;

            $status = 200;
            $msg = 'Item updated to cart';
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

    }

    public function removeFromCart(Request $request){
        $user = Auth::user();

        if(!isset($user)){
            $status = 0;
            $msg = 'User must login';
            $data = [];
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

        $product_id = $request->product_id;
        $user_id = $user->id;

        $item = $this->shopping_cart->where('user_id', $user->id)
                                    ->where('product_id', $product_id)
                                    ->first();
        if($item){
            
            $item->forceDelete();
            $data = [];

            $status = 200;
            $msg = 'Item deleted from cart';
            return response()->json(compact('status', 'msg', 'data'), 200);

        }else{

            $data = [];

            $status = 200;
            $msg = 'No Item Found';
            return response()->json(compact('status', 'msg', 'data'), 200);
        }
    }

    public function destroyCart(Request $request){

        $user = Auth::user();

        if(!isset($user)){
            $status = 0;
            $msg = 'User must login';
            $data = [];
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

        $product_id = $request->product_id;
        $qty = $request->qty;
        $user_id = $user->id;

        $item = $this->shopping_cart->where('user_id', $user->id)
                                    ->get();
        if($item){
            
            foreach($item as $row):
                $row->delete();
            endforeach;

            $data = [];

            $status = 200;
            $msg = 'Item deleted from cart';
            return response()->json(compact('status', 'msg', 'data'), 200);

        }else{

            $data = [];

            $status = 200;
            $msg = 'Item deleted from cart';
            return response()->json(compact('status', 'msg', 'data'), 200);
        }

    }

}
