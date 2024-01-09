<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransactionController extends Controller
{
    protected $orders;

    public function __construct(Order $order)
    {
        $this->orders = $order;
    }

    // array for select product
    private function arraySelectProduct()
    {
        return ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    // array for select product
    private function arraySelectProductOld()
    {
        return ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

  // array for select product
  private function arraySelectOrderDetailCancel()
  {
      return ['id', 'product_id', 'order_id', 'konversi_sedang_ke_kecil', 'qty_konversi', 'qty_cancel', 'price_apps', 'total_price_cancel', 'notes', 'product_review_id', 'promo_id', 'disc_cabang', 'disc_principal', 'point_principal', 'bonus', 'bonus_name', 'bonus_qty', 'bonus_konversi', 'point'];
  }

  // array for select product
  private function arraySelectOrderDetailSuccess()
  {
      return ['id', 'product_id', 'order_id', 'konversi_sedang_ke_kecil', 'qty_konversi', 'qty_update', 'price_apps', 'total_price_update', 'notes', 'product_review_id', 'promo_id', 'disc_cabang', 'disc_principal', 'point_principal', 'bonus', 'bonus_name', 'bonus_qty', 'bonus_konversi', 'point'];
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
        $id             = Auth::user()->id;
        $app_version    = Auth::user()->app_version;
        if($app_version == '1.1.1') {
            $array      = $this->arraySelectProductOld();             
        } else {
            $array      = $this->arraySelectProduct();             
        }
        $arrayOrder         = ['id', 'invoice','stock_status', 'customer_id', 'subscribe_id', 'name', 'phone', 'address', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'payment_method', 'order_time', 'status', 'payment_total', 'payment_final', 'payment_point', 'status_faktur', 'point', 'status_complaint', 'status_review', 'site_code', 'created_at', 'updated_at', 'delivery_service', 'delivery_fee'];
        $arrayOrderDetail   = ['id', 'product_id', 'order_id', 'small_unit', 'konversi_sedang_ke_kecil', 'half', 'qty_konversi', 'qty', 'price_apps', 'total_price', 'product_review_id', 'promo_id', 'disc_cabang', 'rp_cabang', 'disc_principal', 'rp_principal', 'point_principal', 'bonus', 'bonus_qty', 'bonus_name', 'bonus_konversi', 'point'];
        $arrayOrderDetailCancel = $this->arraySelectOrderDetailCancel();
        $arrayOrderDetailSuccess = $this->arraySelectOrderDetailSuccess();

        // request search
        $searchString = $request->search;
        
        try {
            $transactions   = $this->orders->query();
            
            if ($searchString) {
                $transactions   = $transactions
                                ->whereHas('data_item.product', function($query) use ($searchString){
                                    $query->where('name', 'like', '%'.ucwords($searchString).'%');
                                    // $query->whereRaw(
                                    //     "MATCH(search_name) AGAINST(?)", 
                                    //     array($searchString)
                                    // );
                                })
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F');
            }

            if ($request->status == 'new transaction') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->where('status', '1');
            } 

            if ($request->status == 'order confirmed') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->where('status', '2');
            } 

            if ($request->status == 'delivery process') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->where('status', '3');
            } 

            if ($request->status == 'completed') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->where('status', '4');
            } 

            if ($request->status == 'canceled') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->where('status', '10');
            } 

            if ($request->status == 'R') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'R');
            } 

            if ($request->status == 'Redeem') {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'Redeem');
            } 

            if ($request->date) {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->whereDate('order_time', '>', Carbon::now()->subDays($request->date));
            }

            if($request->status && $request->date) {
                if($request->status == 'new transaction') {
                    $status = '1';
                } else if($request->status == 'order confirmed') {
                    $status = '2';
                } else if($request->status == 'delivery process') {
                    $status = '3';
                } else if($request->status == 'completed') {
                    $status = '4';
                } else if($request->status == 'canceled') {
                    $status = '10';
                } else if($request->status == 'R') {
                    $status = 'R';
                }

                $transactions   = $transactions
                                            ->where('customer_id', $id)
                                            ->where('status_faktur', 'F')
                                            ->whereDate('order_time', '>', Carbon::now()->subDays($request->date))
                                            ->where('status', $status);

                if($request->search) {
                    $transactions = $transactions
                    ->whereHas('data_item.product', function($query) use ($searchString){
                        $query->where('search_name', 'like', '%'.$searchString.'%');
                        // $query->whereRaw(
                        //     "MATCH(name) AGAINST(?)", 
                        //     array($searchString)
                        // );
                    });
                }

                if ($status == 'R') {
                $transactions   = $transactions
                                            ->where('customer_id', $id)
                                            ->where('status_faktur', $status)
                                            ->whereDate('order_time', '>', Carbon::now()->subDays($request->date));
                }
            }

            if ($request->start_date && $request->end_date) {
                $transactions   = $transactions
                                ->where('customer_id', $id)
                                ->where('status_faktur', 'F')
                                ->whereBetween('order_time', [$request->start_date, $request->end_date]);
            }
      
            if ($request->status && $request->start_date && $request->end_date) {
                if($request->status == 'new transaction') {
                    $status = '1';
                } else if($request->status == 'order confirmed') {
                    $status = '2';
                } else if($request->status == 'delivery process') {
                    $status = '3';
                } else if($request->status == 'completed') {
                    $status = '4';
                } else if($request->status == 'canceled') {
                    $status = '10';
                } else if($request->status == 'R') {
                    $status = 'R';
                }


            
                
                if($request->search) {
                    $transactions = $transactions
                    ->whereHas('data_item.product', function($query) use ($searchString){
                        // $query->where('name', 'like', '%'.$searchString.'%');
                        $query->whereRaw(
                            "MATCH(search_name) AGAINST(?)", 
                            array($searchString)
                        );
                    });
                }

                if ($status == 'R') {
                    $transactions   = $transactions
                                                ->where('customer_id', $id)
                                                ->where('status_faktur', $status)
                                                ->whereBetween('order_time', [$request->start_date, $request->end_date]);
                }
            }

            // base query product with param
            if ($searchString || $request->status || $request->date) {
                if ($request->order == 'asc') {
                    $transactions   = $transactions->orderBy('created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $transactions = $transactions->orderBy('created_at', 'desc');
                } else {
                    $transactions = $transactions->orderBy('created_at', 'desc');
                }
            } else {
                $transactions   = $transactions
                                    ->where('customer_id', $id)
                                    ->where('status_faktur', 'F');

                if ($request->order == 'asc') {
                    $transactions   = $transactions->orderBy('created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $transactions = $transactions->orderBy('created_at', 'desc');
                } else {
                    $transactions = $transactions->orderBy('created_at', 'desc');
                }
            }

            $transactions = $transactions                                
                                ->with(['data_item' => function($query) use($array, $arrayOrderDetail) {
                                    $query->where('product_id', '!=', null)
                                        ->select($arrayOrderDetail)
                                        ->with(['product' => function ($q) use ($array) {
                                            $q->select($array)->with('price');
                                        }]);
                                },
                                'data_cancel' => function($query) use($array,$arrayOrderDetailCancel) {
                                    $query->select($arrayOrderDetailCancel)
                                    ->with(['product' => function ($q) use ($array) {
                                        $q->select($array)->with('price');
                                    }]);
                                },  
                                'data_success' => function($query) use($array,$arrayOrderDetailSuccess) {
                                    $query->select($arrayOrderDetailSuccess)
                                    ->with(['product' => function ($q) use ($array) {
                                        $q->select($array)->with('price');
                                    }]);
                                },  
                                'data_promo' => function($query) use($arrayOrderDetail) {
                                    $query->select($arrayOrderDetail);
                                }, 'data_review', 'data_complaint'])
                                ->select($arrayOrder)
                                ->paginate(30);

            return response()->json([
                'success' => true,
                'message' => 'Get transactions successfully',
                'data'    => $transactions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get transactions failed',
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
            // check user login
            $app_version    = Auth::user()->app_version;
            // $transactions   = $this->orders->query();
            if($app_version == '1.1.1') {
                $array      = $this->arraySelectProductOld();             
            } else {
                $array      = $this->arraySelectProduct();             
            }
           
            $arrayOrder         = ['id', 'invoice','stock_status', 'customer_id', 'subscribe_id', 'name', 'phone', 'address', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'payment_method', 'order_time', 'status', 'payment_total', 'payment_final', 'payment_point', 'status_faktur', 'point', 'status_complaint', 'status_review', 'site_code', 'created_at', 'updated_at', 'delivery_service', 'delivery_fee'];
            $arrayOrderDetail   = ['id', 'product_id', 'order_id', 'small_unit', 'konversi_sedang_ke_kecil', 'half', 'qty_konversi', 'qty', 'price_apps', 'total_price', 'product_review_id', 'promo_id', 'disc_cabang', 'rp_cabang', 'disc_principal', 'rp_principal', 'point_principal', 'bonus', 'bonus_qty', 'bonus_name', 'bonus_konversi', 'point'];
            $arrayOrderDetailCancel = $this->arraySelectOrderDetailCancel();
            $arrayOrderDetailSuccess = $this->arraySelectOrderDetailSuccess();

            
            $transactions = $this->orders                             
                                ->with(['data_item' => function($query) use($array, $arrayOrderDetail) {
                                    $query->where('product_id', '!=', null)
                                        ->select($arrayOrderDetail)
                                        ->with(['product' => function ($q) use ($array) {
                                            $q->select($array)->with('price');
                                        }]);
                                }, 
                                'data_cancel' => function($query) use($array,$arrayOrderDetailCancel) {
                                    $query->select($arrayOrderDetailCancel)
                                    ->with(['product' => function ($q) use ($array) {
                                        $q->select($array)->with('price');
                                    }]);
                                },  
                                'data_success' => function($query) use($array,$arrayOrderDetailSuccess) {
                                    $query->select($arrayOrderDetailSuccess)
                                    ->with(['product' => function ($q) use ($array) {
                                        $q->select($array)->with('price');
                                    }]);
                                },  
                                'data_promo' => function($query) use($arrayOrderDetail) {
                                    $query->select($arrayOrderDetail);
                                }, 'data_review', 'data_complaint'])
                                ->select($arrayOrder)
                                ->where('id', $id)
                                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Get transactions successfully',
                'data'    => $transactions
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get transactions failed',
                'data'    => $e->getMessage()
            ], 500);
        }

    }
}
