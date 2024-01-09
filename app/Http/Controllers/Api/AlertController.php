<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\TopSpender;
use App\Promo;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AlertController extends Controller
{
    // array for select product
    private function arraySelectProduct()
    {
        return ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru'];
    }

    // array for select product
    private function arraySelectProductOld()
    {
        return ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru'];
    }

    public function promo($id)
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
            $userId         = auth()->user()->id;
            $app_version    = auth()->user()->app_version;
            if($app_version == '1.1.1') {
                $array      = $this->arraySelectProductOld();             
            } else {
                $array      = $this->arraySelectProduct();             
            }
            
            $promos = Promo::with(['sku.product' => function($query) use ($array, $userId) {
                                        $query->select($array)
                                        ->with(['price', 'review' => function($q) {
                                                $q->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                                    ->groupBy('product_id');
                                            }, 'cart' => function($q) use ($userId) {
                                                $q->where('user_id', $userId)
                                                    ->select('id', 'user_id', 'product_id', 'qty');
                                        }]);
                                    }, 'reward.product'  => function($query) use ($array, $userId) {
                                        $query->select($array)
                                        ->with(['price', 'review' => function($q) {
                                                $q->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                                    ->groupBy('product_id');
                                            }, 'cart' => function($q) use ($userId) {
                                                $q->where('user_id', $userId)
                                                    ->select('id', 'user_id', 'product_id', 'qty');
                                        }]);
                                    }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Get promo successfully',
                'data'    => $promos
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get promo failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function topSpender($id)
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
            $topSpenders = TopSpender::with(['rank_reward' => function($q) {
                $q->select('id', 'top_spender_id', 'pos', 'nominal');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Get top spender successfully',
                'data'    => $topSpenders
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get top spender failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
