<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Log;
use App\Product;
use App\Wishlist;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class WishlistController extends Controller
{
    protected $products, $wishlists, $logs;

    public function __construct(Product $product, Wishlist $wishlist, Log $log)
    {
        $this->products = $product;
        $this->wishlists = $wishlist;
        $this->logs = $log;
    }

    // array for select product
    private function arraySelectProduct()
    {
        return ['id', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online', 'kecil', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    // array for select product
    private function arraySelectProductOld()
    {
        return ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    public function get(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
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

        $id = Auth::user()->id;

        try {
            $wishlists = $this->wishlists->query();
            $app_version    = Auth::user()->app_version;
            if ($app_version == '1.1.1') {
                $arrayProduct = $this->arraySelectProductOld();
            } else {
                $arrayProduct = $this->arraySelectProduct();
            }
            // search wishlists
            if ($request->search) {
                $wishlists  = $wishlists
                    ->where('user_id', $id)
                    ->whereHas('product', function ($query) use ($request) {
                        return $query->where('name', 'ilike', '%' . $request->search . '%');
                    });
            } else {
                // get wishlists
                $wishlists  = $wishlists
                    ->where('user_id', $id);
            }

            $wishlists = $wishlists
                ->with(['product' => function ($query) use ($arrayProduct) {
                    $query->select($arrayProduct)
                        ->with(['price']);
                }, 'review'])
                ->orderBy('id', 'DESC')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get wishlist successfully',
                'data'    => $wishlists
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get wishlist failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
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

        $validator = Validator::make(
            $request->all(),
            [
                'product_id'    => 'required',
                'price_apps'    => 'required'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 400);
        }

        $id = Auth::user()->id;
        $app_version    = Auth::user()->app_version;
        if ($app_version == '1.1.1') {
            $arrayProduct = $this->arraySelectProductOld();
        } else {
            $arrayProduct = $this->arraySelectProduct();
        }

        try {
            // insert
            $wishlists              = $this->wishlists;

            $wishlists->user_id     = $id;
            $wishlists->product_id  = $request->product_id;
            $wishlists->price_apps  = $request->price_apps;
            $wishlists->half        = $request->half ? $request->half : NULL;
            $wishlists->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User add product to wishlist with product_id " . $request->product_id;
            $logs->data_content = $wishlists;
            $logs->table_name   = 'users, user_address';
            $logs->column_name  = 'user_id, product_id';
            $logs->from_user    = Auth::user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            // response
            $wishlists = $this->wishlists
                ->with(['product' => function ($query) use ($arrayProduct) {
                    $query->select($arrayProduct)
                        ->with(['price', 'product_image'  => function ($q) {
                            $q->select('id', 'product_id', 'path');
                        }]);
                }, 'review'])
                ->find($wishlists->id);

            return response()->json([
                'success' => true,
                'message' => 'Create wishlist successfully',
                'data'    => $wishlists
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Create wishlist failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
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
            // delete
            $this->wishlists->destroy($id);

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User delete product from wishlist with id " . $id;
            $logs->table_name   = 'users, user_address';
            $logs->from_user    = Auth::user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            return response()->json([
                'success' => true,
                'message' => 'Delete wishlist successfully',
                'data'    => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete wishlist failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
