<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Promo;
use App\PromoSku;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PromoController extends Controller
{
    protected $promo, $promoSku;

    public function __construct(Promo $promo, PromoSku $promoSku)
    {
        $this->promo    = $promo;
        $this->promoSku = $promoSku;
    }

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
    
    public function get(Request $request)
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
            $app_version = Auth::user()->app_version;
            $promos      = $this->promo->query();
            $userId      = auth()->user()->id;
            if($app_version == '1.1.1') {
                $array      = $this->arraySelectProductOld();             
            } else {
                $array      = $this->arraySelectProduct();             
            }
            
            if($request->status == 'active') { 
                $promos = $promos
                            ->where('status', '1')
                            ->limit(10);
                
                if ($request->order == 'asc') {
                    $promos   = $promos->orderBy('created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $promos = $promos->orderBy('created_at', 'desc');
                } else {
                    $promos = $promos->orderBy('created_at', 'desc');
                }
            }

            $promos = $promos
                        ->where('status', '1');

            if($request->status == 'non active') { 
                $promos = $promos
                            ->where('status', '0')
                            ->limit(10);
                
                if ($request->order == 'asc') {
                    $promos   = $promos->orderBy('created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $promos = $promos->orderBy('created_at', 'desc');
                } else {
                    $promos = $promos->orderBy('created_at', 'desc');
                }
            }

            if ($request->id) {
                $promos = $promos
                            ->whereId($request->id)
                            ->with(['sku.product' => function($query) use ($array, $userId) {
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
                            }])
                            ->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Get promo detail successfully',
                    'data'    => $promos
                ], 200);
            }

            // search products
            if ($request->search) {
                $promos = $promos
                                ->where('highlight', 'like', '%' . $request->search . '%'); 
            }

            if ($request->order == 'asc') {
                $promos   = $promos->orderBy('id', 'asc');
            } else if ($request->order == 'desc') {
                $promos = $promos->orderBy('id', 'desc');
            } else {
                $promos = $promos->orderBy('id', 'desc');
            }

                if($app_version == '1.1.1') {
                    $promos = $promos
                                        ->with(['sku', 'reward.product' => function($query) use ($array) {
                                            $query->select($array);
                                        }])
                                        ->paginate(10);
                } else {
                    $promos = $promos
                                    ->select('promos.*', 
                                                DB::raw("COALESCE(promos.start, DATE('" . Carbon::now()->startOfMonth()->format('Y-m-d') . "') ) as start"),
                                                DB::raw("COALESCE(promos.end,  DATE('" . Carbon::now()->endOfMonth()->format('Y-m-d')   . "') ) as end"),
                                            )
                                    ->with(['sku' => function($query) use ($array, $userId) {
                                        $query->take(10)
                                        ->with(['product' => function($q) use ($array, $userId) {
                                            $q->select($array)
                                            ->with(['price', 'review' => function($querry) {
                                                    $querry->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                                    ->groupBy('product_id');
                                                }, 'cart' => function($querry) use ($userId) {
                                                    $querry->where('user_id', $userId)
                                                    ->select('id', 'user_id', 'product_id', 'qty');
                                            }]);
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
                                        }])
                                    ->paginate(10);
                }
            
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

    public function detail($id)
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
            $app_version = Auth::user()->app_version;
            $userId      = auth()->user()->id;
            if($app_version == '1.1.1') {
                $array      = $this->arraySelectProductOld();             
            } else {
                $array      = $this->arraySelectProduct();             
            }

            // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );
            // array_push($array, 'promo_skus.promo_id');
            
            $promoSku       = $this->promoSku
                                        ->where('promo_id', $id)
                                        // ->join('products', 'products.id', '=', 'promo_skus.product_id')
                                        // ->select($array)
                                        ->with(['product' => function($query) use ($array, $userId) {
                                                $query->select($array)
                                                ->with(['product_image', 'price', 'review' => function($q) {
                                                    $q->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                                    ->groupBy('product_id');
                                                }, 'cart' => function($q) use ($userId) {
                                                    $q->where('user_id', $userId)
                                                    ->select('id', 'user_id', 'product_id', 'qty');
                                                }]);
                                        }])
                                        ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get promo detail product successfully',
                'data'    => $promoSku
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get promo failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
