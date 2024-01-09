<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Log;
use App\User;
use App\Chat;
use App\ShoppingCart;
use Carbon\Carbon;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class TotalNotificationController extends Controller
{
    protected $users, $chats, $logs, $shoppingCarts;

    public function __construct(Chat $chat, User $user, Log $log, ShoppingCart $shoppingCart)
    {
        $this->users            = $user;
        $this->chats            = $chat;
        $this->logs             = $log;
        $this->shoppingCarts    = $shoppingCart;
    }

    public function get(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }
        
        // check user login
        $id = Auth::user()->id;

        try {
            $total_order  = $this->logs
                                    ->where('activity', 'not like', '%Delete order with id%')
                                    ->where('activity', 'not like', '%new order failed%')
                                    ->where('activity', 'not like', '%update status order with%')
                                    ->where('activity', 'not like', '%order with invoice%')
                                    ->where('activity', 'not like', '%successfully ordered complaint with id%')
                                    ->where('table_name', 'like', '%orders%')
                                    ->whereNull('user_seen')
                                    ->where(function($query) use ($id) {                                    
                                        $query->where('from_user', $id)
                                                ->orWhere('to_user', $id);
                                    })
                                    ->count();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get total orders notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            $total_chat  = $this->chats
                            ->with('to_user')
                            ->where('to_id', $id)
                            ->where('status', null)
                            ->count();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get chat notifications failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try{
            $total_complaint  = $this->logs
                                ->where('activity', 'not like', '%new complaint%')
                                ->where('activity', 'not like', '%successfully sent credit%')
                                ->where('activity', 'not like', '%successfully ordered complaint%')
                                ->where('table_name', 'like', '%complaints%')
                                ->where('user_seen', null)
                                ->where('to_user', $id)
                                ->count();

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get total complaints notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            // counting total
            $total_subscribe = $this->logs
                            ->whereNull('user_seen')
                            ->where(function($query) {                           
                                $query->where('activity', 'subscribe h-2')
                                ->orWhere('activity', 'subscribe h-1');
                            })
                            ->where(function($query) use ($id) {
                                // check user login
                                $query->where('from_user', $id)
                                    ->orWhere('to_user', $id);
                            })
                            ->count();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get total subscribes notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            // counting total
            $total_broadcast = $this->logs
                            ->where('activity', 'notif broadcast message apps')
                            ->whereNull('user_seen')
                            ->where('to_user', $id)
                            ->count();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get total broadcasts notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            $total_cart  = $this->shoppingCarts
                            // ->select('shopping_cart.*', 'products.brand_id', 'products.name', 'products.image', 'products.min_pembelian')
                            // ->selectRaw('shopping_cart.*, (shopping_cart.price_apps * shopping_cart.qty - shopping_cart.total_price) as order_discount')            // order = item_discount
                            // ->join('products', 'products.id', '=', 'shopping_cart.product_id')
                            ->where('user_id', $id)
                            ->count();

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get total shopping carts notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        $allData = array(
                            'total_order'       => array($total_order), 
                            'total_chat'        => array($total_chat), 
                            'total_complaint'   => array($total_complaint), 
                            'total_subscribe'   => array($total_subscribe),
                            'total_cart'        => array($total_cart),
                            'total_broadcast'   => array($total_broadcast)
                        );

        return response()->json([
            'success' => true,
            'message' => 'Count all notification successfully',
            'data'    => $allData
        ], 200);
    }

    public function broadcastNotification(Request $request)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        try {
            $broadcasts = $this->logs->query();

            // check user login
            $id = Auth::user()->id;

            $broadcasts  = $broadcasts
                                ->select('logs.*')
                                ->where('activity', 'notif broadcast message apps')
                                ->where('table_name', 'like', '%broadcast_wa%')
                                ->where(function($query) use ($id) {                                    
                                    $query->where('to_user', $id);
                                })
                                ->orderBy('log_time', 'DESC');

                            // counting total
            $total  = $this->logs
                                ->where('activity', 'notif broadcast message apps')
                                ->where('table_name', 'like', '%broadcast_wa%')
                                ->where('user_seen', null)
                                ->where('to_user', $id)
                                ->count();

            if ($request->order == 'asc') {
                $broadcasts = $broadcasts->orderBy('log_time', 'asc');
            } else if ($request->order == 'desc') {
                $broadcasts = $broadcasts->orderBy('log_time', 'desc');
            } else {
                $broadcasts = $broadcasts->orderBy('log_time', 'desc');
            }

            $broadcasts = $broadcasts->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get broadcasts notification successfully',
                'total'   => $total,
                'data'    => $broadcasts
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get broadcasts notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function seenBroadcastNotification($id)
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data'    => null
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data'    => null
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data'    => null
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data'    => null
            ], 400);
        }

        try {
            $notification = $this->logs->find($id);

            $notification->user_seen = '1';

            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Seen broadcast notification successfully',
                'data'    => $notification
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seen broadcast notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    } 
}
