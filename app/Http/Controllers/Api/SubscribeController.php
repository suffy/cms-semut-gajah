<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Log;
use App\Product;
use App\Subscribe;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubscribeController extends Controller
{
    protected $subscribes, $products, $logs;

    public function __construct(Subscribe $subscribe, Product $product, Log $log)
    {
        $this->subscribes = $subscribe;
        $this->products = $product;
        $this->logs = $log;
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

        $id             = Auth::user()->id;
        $arrayNew       = ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'kecil', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
        $app_version    = Auth::user()->app_version; 

        if($app_version == '1.1.1') {
            $array      = ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
        } else {   
            $array      = ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'kecil', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'status_renceng', 'created_at', 'updated_at'];
        }

        try {
            $subscribes = $this->subscribes->query();

            if ($request->type == 'week') {         //get subscribe per week
                $subscribes = $subscribes
                            ->where('user_id', $id)
                            ->where('time', 'week');
            } else if ($request->type == '2_week') { //get subscribe per 2 week
                $subscribes = $subscribes
                            ->where('user_id', $id)
                            ->where('time', '2_week');
            } else if ($request->type == 'month') {  //get subscribe per month
                $subscribes = $subscribes
                            ->with('product.price')
                            ->where('user_id', $id)
                            ->where('time', 'month');
            } else {                                //get all subscribe
                $subscribes = $subscribes
                            ->where('user_id', $id);
            }

            $subscribes = $subscribes
                                    ->with(['product' => function($query) use ($array) {
                                        $query->select($array)
                                                ->with('price');
                                    }])
                                    ->orderBy('id', 'DESC')
                                    ->paginate(10);

            // counting total
            $total = $subscribes->count();

            return response()->json([
                'success' => true,
                'message' => 'Get subscribe successfully',
                'total'   => $total,
                'data'    => $subscribes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get subscribe failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
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

        $status_blacklist   = Auth::user()->status_blacklist;
        if($status_blacklist == '1') {
            return response()->json([
                'success'   => false,
                'message'   => 'Anda Masuk Dalam Daftar Blacklist',
                'data'      => null
            ], 200);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'product_id'    => 'required',
                'qty'           => 'required',
                'time'          => 'required',
                'start_at'      => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 201);
        }

        $id = Auth::user()->id;

        if($request->qty == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity must not be zero',
                'data'    => null
            ], 500);
        }
        
        try {

            // Insert
            $subscribe              = $this->subscribes;

            $subscribe->user_id     = $id;
            $subscribe->product_id  = $request->product_id;
            $subscribe->qty         = $request->qty;
            $subscribe->time        = $request->time;
            $subscribe->status      = '1';
            $subscribe->half        = $request->half ? $request->half : NULL;
            $subscribe->notes       = $request->notes;
            $subscribe->start_at    = $request->start_at;

            $subscribe->save();


            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User add product to subscribe with product_id : " . $request->product_id;
            $logs->data_content = $subscribe;
            $logs->table_name   = 'subscribe';
            $logs->column_name  = 'user_id, product_id, qty, time, status, notes';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            return response()->json([
                'success' => true,
                'message' => 'Create subscribe successfully',
                'data'    => $subscribe
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Create subscribe failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
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

        $status_blacklist   = Auth::user()->status_blacklist;
        if($status_blacklist == '1') {
            return response()->json([
                'success'   => false,
                'message'   => 'Anda Masuk Dalam Daftar Blacklist',
                'data'      => null
            ], 200);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'product_id'    => 'required',
                'qty'           => 'required',
                'time'          => 'required',
                'status'        => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 201);
        }

        $user_id = Auth::user()->id;

        //qty != 0
        if($request->qty == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Quantity must not be zero',
                'data'    => null
            ], 500);
        }

        try {
            // Update
            $subscribe              = $this->subscribes->find($id);

            $subscribe->user_id     = $user_id;
            $subscribe->product_id  = $request->product_id;
            $subscribe->qty         = $request->qty;
            $subscribe->time        = $request->time;
            $subscribe->status      = $request->status;
            $subscribe->half        = $request->half ? $request->half : NULL;
            $subscribe->notes       = $request->notes;
            $subscribe->start_at    = $request->start_at;

            $subscribe->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User update product in subscribe with product_id : " . $request->product_id;
            $logs->data_content = $subscribe;
            $logs->table_name   = 'subscribe';
            $logs->column_name  = 'user_id, product_id, qty, time, status, notes';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            return response()->json([
                'success' => true,
                'message' => 'Update subscribe successfully',
                'data'    => $subscribe
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update subscribe failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
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
            $this->logs
                    ->where('activity', 'like', '%subscribe h-%')
                    ->where('table_id', $id)
                    ->update(['status' => 0]);

            $this->subscribes->destroy($id);
            
            // remove log to show in notification list

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User delete product in subscribe with id : " . $id;
            $logs->table_name   = 'subscribe';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            return response()->json([
                'success' => true,
                'message' => 'Delete subscribe successfully',
                'data'    => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete subscribe failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function notification(Request $request)
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
            $notifications = $this->logs->query();
            $id = Auth::user()->id;

            // get notif readed
            if ($request->status == 'readed') {
                $notifications  = $notifications
                                ->where(function($query)
                                {
                                    return $query->where('activity', 'subscribe h-2')
                                        ->orWhere('activity', 'subscribe h-1');
                                })
                                ->where('table_name', 'subscribes')
                                ->where(function($query) use ($id) {
                                    // check user login                                    
                                    $query->where('from_user', $id)
                                    ->orWhere('to_user', $id);
                                })
                                ->where('user_seen', '1')
                                ->whereNull('status')
                                ->orderBy('log_time', 'DESC');

                $total = $this->logs
                                    ->where('user_seen', 1)
                                    ->where('activity', 'subscribe h-2')
                                    ->orWhere('activity', 'subscribe h-1')
                                    ->where(function($query) use ($id) {
                                        // check user login
                                        $query->where('from_user', $id)
                                            ->orWhere('to_user', $id);
                                    })
                                    ->count();
            }

            // get notif unreaded
            if ($request->status == 'unreaded') {
                $notifications  = $notifications
                                ->whereNull('user_seen')
                                ->where(function($query)
                                {
                                    return $query->where('activity', 'subscribe h-2')
                                        ->orWhere('activity', 'subscribe h-1');
                                })
                                ->where('table_name', 'subscribes')
                                ->where(function($query) use ($id) {
                                    // check user login
                                    $query->where('from_user', $id)
                                        ->orWhere('to_user', $id);
                                })
                                ->whereNull('status')
                                ->orderBy('log_time', 'DESC');
                
                // counting total
                $total = $this->logs
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
            }

            if ($request->status) {
                $notifications = $notifications->paginate(10);
            } else {
                // get notif h-2 & h-1
                $notifications  = $notifications
                                ->where(function($query)
                                {
                                    return $query->where('activity', 'subscribe h-2')
                                        ->orWhere('activity', 'subscribe h-1');
                                })
                                ->where('table_name', 'subscribes')
                                ->where(function($query) use ($id) {
                                    // check user login                                    
                                    $query->where('from_user', $id)
                                        ->orWhere('to_user', $id);
                                })
                                ->whereNull('status')
                                ->orderBy('log_time', 'DESC')
                                ->paginate(10);
                                
                // counting total
                $total = $this->logs
                                    ->whereNull('user_seen')
                                    ->whereNull('status')
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
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Get subscribes notification successfully',
                'total'   => $total,
                'data'    => $notifications
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get subscribes notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function detail($id)
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
            $subscribes = $this->subscribes
                            ->whereId($id)
                            ->with('product.price')
                            ->first();

            return response()->json([
                'success' => true,
                'message' => 'Get subscribe details successfully',
                'data'    => $subscribes
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get subscribe details fails!',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function notificationUpdate($id)
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
            // update status readed
            $notification = $this->logs->find($id);

            $notification->user_seen = '1';

            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Update subscribes h-2 notification successfully',
                'data'    => $notification
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update subscribes h-2 notification failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
