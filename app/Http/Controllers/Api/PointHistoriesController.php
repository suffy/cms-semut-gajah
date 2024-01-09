<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PointHistory;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PointHistoriesController extends Controller
{
    protected $pointHistory;
    
    public function __construct(PointHistory $pointHistory)
    {
        $this->pointHistory = $pointHistory;
    }

    public function index(Request $request)
    {
        try {                                                                   // check token
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
            $id         = Auth::user()->id;
            $appVersion = auth()->user()->app_version;

            if($appVersion == '1.1.1') {
                $arrayProduct      = ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $arrayProduct      = ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];            
            }

            $arrayOrder = ['id', 'invoice', 'customer_id', 'subscribe_id', 'name', 'phone', 'app_version', 'address', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'payment_method', 'payment_total', 'payment_final', 'payment_point', 'delivery_fee', 'notes', 'status', 'status_faktur', 'site_code', 'complaint_id', 'review_at', 'point', 'created_at', 'updated_at', 'deleted_at', 'delivery_service'];

            $point_histories = $this->pointHistory->query();

            $point_histories->where('customer_id', $id);

            if ($request->order == 'asc') {
                $point_histories  = $point_histories->orderBy('created_at', 'asc');
            } else if ($request->order == 'desc') {
                $point_histories = $point_histories->orderBy('created_at', 'desc');
            } else {
                $point_histories = $point_histories->orderBy('created_at', 'desc');
            }

            $point_histories = $point_histories
                                        ->with(['order' => function($q) use ($arrayOrder, $arrayProduct)
                                        {
                                            $q->select($arrayOrder)
                                                ->with(['data_item.product' => function($query) use ($arrayProduct) {
                                                    $query->select($arrayProduct);
                                                }]);
                                        }, 'topSpender'])
                                        ->paginate(10);
            
            return response()->json([
                'success'   => true,
                'messages'  => 'Get Point Histories Successfully',
                'data'      => $point_histories
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get Point Histories failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
