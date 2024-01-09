<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Log;
use App\Product;
use App\ProductStrata;
use App\ProductPrice;
use App\ShoppingCart;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    protected $users, $product, $shoppingCarts, $logs, $promo, $promo_sku, $productStrata;

    public function __construct(User $user, Product $product, ProductPrice $productPrice, ShoppingCart $shoppingCart, ProductStrata $productStrata, Log $log)
    {
        $this->users            = $user;
        $this->product          = $product;
        $this->productPrice     = $productPrice;
        $this->shoppingCarts    = $shoppingCart;
        $this->productStrata    = $productStrata;
        $this->logs             = $log;
    }

    private function arraySelect()
    {
        return [
            'shopping_cart.id',
            'shopping_cart.user_id',
            'shopping_cart.product_id',
            'shopping_cart.promo_id',
            'shopping_cart.satuan_online',
            'shopping_cart.konversi_sedang_ke_kecil',
            'shopping_cart.min_pembelian',
            'shopping_cart.half',
            'shopping_cart.qty_konversi',
            'shopping_cart.qty',
            'shopping_cart.price_apps',
            'shopping_cart.total_price',
            'shopping_cart.order_disc',
            'shopping_cart.disc_cabang',
            'shopping_cart.status_disc',
            'shopping_cart.status', 'shopping_cart.order_disc as item_discount', 'products.brand_id', 'products.name', 'products.image', 'products.ratio', 'products.min_pembelian', 'products.status_herbana', 'products.status_promosi_coret', 'products.status'
        ];
    }

    // array for select product
    private function arraySelectOld()
    {
        return ['shopping_cart.*', 'shopping_cart.order_disc as item_discount', 'products.brand_id', 'products.name', 'products.image_backup as image', 'products.ratio', 'products.min_pembelian', 'products.status_herbana', 'products.status_promosi_coret', 'products.status'];
    }

    public function get(Request $request)
    {

        if ($cekAuth = $this->checkAuth()) {
            return $cekAuth;
        }

        // check user login
        $id             = Auth::user()->id;
        $app_version    = Auth::user()->app_version;
        if ($app_version == '1.1.1') {
            $array      = $this->arraySelectOld();
        } else {
            $array      = $this->arraySelect();
        }

        $strataDisc = $this->productStrata->pluck('product_id');
        $shoppingCart = $this->shoppingCarts;
        $statusClass = $shoppingCart
            ->where('user_id', $id)
            ->where('status_class', 1)
            ->whereIn('product_id', $strataDisc)
            ->get();

        $updateStatusDisc = $shoppingCart
            ->where('user_id', $id)
            ->whereNull('status_disc')
            ->get();

        // Update Status Disc
        if ($updateStatusDisc->IsNotEmpty()) {
            foreach ($updateStatusDisc as $value) {
                $discClass = $this->productStrata->where('product_id', $value->product_id)->first();
                $status_disc = 'strata';

                if (!$discClass) {
                    $status_disc = 'class';
                }

                $this->shoppingCarts->where('id', $value->id)->update([
                    'status_disc' => $status_disc
                ]);
            }
        }

        // Status ShoppingCart
        if ($statusClass->IsNotEmpty()) {
            foreach ($statusClass as $value) {
                $shoppingCart = $shoppingCart->where('id', $value->id)->first();
                $this->shoppingCarts->where('id', $value->id)->update([
                    'total_price'              => $shoppingCart->price_apps * $shoppingCart->qty,
                    'order_disc'               => 0,
                    'disc_cabang'              => 0,
                    'status_class'             => null,
                ]);
            }
        }

        try {
            $shoppingCarts  = $this->shoppingCarts
                ->select($array)
                ->join('products', 'products.id', '=', 'shopping_cart.product_id')
                ->where('shopping_cart.user_id', $id)
                ->with(['data_product' => function ($query) {
                    $query->select('id', 'kecil');
                    $query->with(['promo_sku' => function ($q) {
                        $q->leftJoin('promos', 'promos.id', '=', 'promo_id')
                            ->select('promo_skus.product_id', 'promo_skus.promo_id', 'promos.title', 'promos.id')
                            ->where('promos.status', 1);
                    }]);
                }])
                ->orderBy('id', 'DESC')
                ->get();

            $shoppingCartsCount = $shoppingCarts->count();

            return response()->json([
                'success'       => true,
                'message'       => 'Get shopping cart successfully',
                'data'          => $shoppingCarts,
                'total'         => $shoppingCartsCount
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get shopping cart failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if ($cekAuth = $this->checkAuth()) {
            return $cekAuth;
        }

        // check auth login
        $id             = Auth::user()->id;
        $salurCode      = Auth::user()->salur_code;
        $class          = Auth::user()->class;
        $app_version    = Auth::user()->app_version;
        $status_blacklist = Auth::user()->status_blacklist;

        if ($status_blacklist == '1') {
            return response()->json([
                'success'   => false,
                'message'   => 'Anda Masuk Dalam Daftar Blacklist',
                'data'      => null
            ], 200);
        }

        try {
            $carts = $this->shoppingCarts
                ->where('user_id', '=', $id)
                ->where('product_id', '=', $request->product_id)
                ->first();

            // check variable half
            $half       = NULL;
            if ($request->half) {
                $half   = 1;
            }
            // end check

            //add qty
            if (!is_null($carts)) {
                $qty            = $carts->qty += $request->qty;
                $shoppingCarts  = $this->handlingPriceUpdate($request, $carts->id, $id, $salurCode, $class, $qty, $half);
            } else {
                // create shopping cart
                $shoppingCarts = $this->handlingPrice($request, $id, $salurCode, $class);
            }

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User add product to shopping cart with product_id : " . $request->product_id;
            $logs->data_content = $shoppingCarts;
            $logs->table_name   = 'shopping_cart';
            $logs->column_name  = 'user_id, product_id, price, qty, notes, total_price';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Create shopping cart successfully',
                'data'    => $shoppingCarts
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Create shopping cart failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
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

        // check auth login
        $userId             = Auth::user()->id;
        $salurCode          = Auth::user()->salur_code;
        $class              = Auth::user()->class;
        $app_version        = Auth::user()->app_version;
        $status_blacklist   = Auth::user()->status_blacklist;
        if ($app_version == '1.1.1') {
            $array      = $this->arraySelectOld();
        } else {
            $array      = $this->arraySelect();
        }

        try {
            if ($status_blacklist == '1') {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Anda Masuk Dalam Daftar Blacklist',
                    'data'      => null
                ], 200);
            }

            $cart = $this->shoppingCarts
                ->where('user_id', '=', $userId)
                ->where('product_id', '=', $request->product_id)
                ->first();

            // update shopping cart
            $shoppingCarts = $this->handlingPriceUpdate($request, $id, $userId, $salurCode, $class, $request->qty, $cart->half);
            // return response()->json($shoppingCarts); 

            // logs
            $logs = $this->logs;

            $this->logs->updateOrCreate(
                ['table_id'     => $shoppingCarts->id],
                [
                    'log_time'     => Carbon::now(),
                    'activity'      => "User update product at shopping cart with product_id : " . $request->product_id,
                    'table_name'    => 'shopping_cart',
                    'column_name'   => 'user_id, product_id, price, qty, notes, total_price',
                    'from_user'     => auth()->user()->id,
                    'to_user'       => null,
                    'data_content'  => $shoppingCarts,
                    'platform'      => 'apps',
                    'created_at'    => Carbon::now()
                ]
            );

            $cartResponse  = $this->shoppingCarts
                ->select($array)
                ->join('products', 'products.id', '=', 'shopping_cart.product_id')
                ->where('user_id', $userId)
                ->with(['data_product' => function ($query) {
                    $query->select('id', 'kecil');
                    $query->with(['promo_sku' => function ($q) {
                        $q->leftJoin('promos', 'promos.id', '=', 'promo_id')
                            ->select('promo_skus.product_id', 'promo_skus.promo_id', 'promos.title', 'promos.id')
                            ->where('promos.status', 1);
                    }]);
                }])
                ->orderBy('id', 'DESC')
                ->get();

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Update shopping cart successfully',
                'data'    => $cartResponse
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update shopping cart failed',
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
            $status_blacklist = auth()->user()->status_blacklist;

            if ($status_blacklist == '1') {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Anda Masuk Dalam Daftar Blacklist',
                    'data'      => null
                ], 200);
            }
            // delete shopping cart
            $shoppingCarts  = $this->shoppingCarts->destroy($id); // soft-delete

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "User delete product at shopping cart with product_id : " . $id;
            $logs->table_name   = 'shopping_cart';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "apps";

            $logs->save();

            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Delete shopping cart successfully',
                'data'    => $shoppingCarts
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete shopping cart failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function handlingPrice($request, $id, $salurCode, $class)
    {
        // get herbana status
        $herbana    = $this->product
            ->select('id', 'status_promosi_coret', 'kodeprod', 'status_renceng', 'konversi_sedang_ke_kecil', 'status_herbana')
            ->where('id', $request->product_id)
            ->with(['price' => function ($query) {
                $query->select('id', 'product_id', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt', 'harga_promosi_coret_semi_grosir', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_semi_grosir');
            }])
            ->first();

        // check if status_renceng true
        $konversi = $herbana->konversi_sedang_ke_kecil;
        $half     = NULL;
        if ($request->half == '1') {
            if ($herbana->status_renceng) {
                $konversi = $konversi / 2;
                $half     = 1;
            }
        }

        $qty_konversi = ($konversi * $request->qty);

        $totalPrice = 0;
        if ($herbana->status_promosi_coret) {
            $harga_ritel_gt         = $herbana->price->harga_promosi_coret_ritel_gt * $request->qty;
            $harga_grosir_mt        = $herbana->price->harga_promosi_coret_grosir_mt * $request->qty;
            // $harga_semi_grosir      = $productPrice->harga_promosi_coret_semi_grosir * $request->qty;  
            // check if halc
            if ($half == 1) {
                $harga_ritel_gt  = $harga_ritel_gt / 2;
                $harga_grosir_mt = $harga_grosir_mt / 2;
            }
        } else {
            $harga_ritel_gt         = $herbana->price->harga_ritel_gt * $request->qty;
            $harga_grosir_mt        = $herbana->price->harga_grosir_mt * $request->qty;
            // $harga_semi_grosir      = $productPrice->harga_semi_grosir * $request->qty;  
            // check if halc
            if ($half == 1) {
                $harga_ritel_gt  = $harga_ritel_gt / 2;
                $harga_grosir_mt = $harga_grosir_mt / 2;
            }
        }

        // Cek Status Disc By strata or By Class
        $discClass = $this->productStrata->where('product_id', $request->product_id)->first();

        // Disc By strata
        $totalPrice = $harga_ritel_gt;
        $orderDisc  = 0;
        $discCabang = 0;
        $status_disc = 'strata';

        // Disc By class
        if (!$discClass) {
            // RT => Retail
            // WS => 
            // SO => 
            // SW =>
            $status_disc = 'class';
            if ($salurCode == 'WS' || $salurCode == 'SO' || $salurCode == 'SW') {
                if ($class == 'GROSIR' || $class == 'STAR OUTLET') {
                    if ($herbana->status_herbana == null || $herbana->status_herbana == 0) {
                        if ($request->brand_id == '005') {
                            $totalPrice = $harga_ritel_gt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else if ($request->brand_id == '001') {
                            $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (4.5 / 100));
                            $orderDisc  = $harga_ritel_gt - $totalPrice;
                            $discCabang = 4.5;
                        } else if ($request->brand_id == '002' || $request->brand_id == '004' || $request->brand_id == '012' || $request->brand_id == '013' || $request->brand_id == '014') {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        }
                    } else {
                        $totalPrice = $harga_grosir_mt;
                        $orderDisc  = 0;
                        $discCabang = 0;
                    }
                } elseif ($class == 'SEMI GROSIR') {
                    if ($herbana->status_herbana == null || $herbana->status_herbana == 0) {
                        if ($request->brand_id == '005') {
                            $totalPrice = $harga_ritel_gt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else if ($request->brand_id == '001') {
                            $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (3 / 100)); //31.695,72
                            $orderDisc  = $harga_ritel_gt - $totalPrice; //980,28
                            $discCabang = 3;
                        } else if ($request->brand_id == '002' || $request->brand_id == '004' || $request->brand_id == '012' || $request->brand_id == '013' || $request->brand_id == '014') {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        }
                    } else {
                        $totalPrice = $harga_grosir_mt;
                        $orderDisc  = 0;
                        $discCabang = 0;
                    }
                } else {
                    $totalPrice = $harga_grosir_mt;
                    $orderDisc  = 0;
                    $discCabang = 0;
                }
            } elseif ($salurCode == 'RT') {
                if ($class == 'RITEL') {
                    $totalPrice = $harga_ritel_gt;
                    $orderDisc  = 0;
                    $discCabang = 0;
                } else {
                    $totalPrice = $harga_ritel_gt;
                    $orderDisc  = 0;
                    $discCabang = 0;
                }
            } else {
                $totalPrice = $harga_ritel_gt;
                $orderDisc  = 0;
                $discCabang = 0;
            }
        }

        $shoppingCarts  = $this->shoppingCarts;

        $shoppingCarts->user_id                  = $id;
        $shoppingCarts->product_id               = $request->product_id;
        $shoppingCarts->satuan_online            = $request->satuan_online;
        $shoppingCarts->konversi_sedang_ke_kecil = $request->konversi_sedang_ke_kecil;
        $shoppingCarts->half                     = $half;
        $shoppingCarts->qty_konversi             = $qty_konversi;
        $shoppingCarts->notes                    = $request->notes;
        $shoppingCarts->qty                      = $request->qty;
        $shoppingCarts->price_apps               = $request->price_apps;
        $shoppingCarts->total_price              = $totalPrice;
        $shoppingCarts->order_disc               = $orderDisc;
        $shoppingCarts->disc_cabang              = $discCabang;
        $shoppingCarts->status_disc              = $status_disc;

        $shoppingCarts->save();

        return $shoppingCarts;
    }

    public function handlingPriceUpdate($request, $id, $userId, $salurCode, $class, $qty, $half)
    {
        // get herbana status
        $herbana    = $this->product
            ->select('id', 'status_promosi_coret', 'status_renceng', 'konversi_sedang_ke_kecil', 'status_herbana')
            ->where('id', $request->product_id)
            ->with(['price' => function ($query) {
                $query->select('id', 'product_id', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt', 'harga_promosi_coret_semi_grosir', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_semi_grosir');
            }])
            ->first();
        // check if status_renceng true
        $konversi = $herbana->konversi_sedang_ke_kecil;
        if ($half == '1') {
            if ($herbana->status_renceng) {
                $konversi = $konversi / 2;
            }
        }

        $qty_konversi = ($konversi * $qty);

        if ($herbana->status_promosi_coret) {
            $harga_ritel_gt         = $herbana->price->harga_promosi_coret_ritel_gt * $qty;
            $harga_grosir_mt        = $herbana->price->harga_promosi_coret_grosir_mt * $qty;
            // $harga_semi_grosir      = $herbana->price->harga_promosi_coret_semi_grosir * $qty;
            if ($half == 1) {
                $harga_ritel_gt  = $harga_ritel_gt / 2;
                $harga_grosir_mt = $harga_grosir_mt / 2;
            }
        } else {
            $harga_ritel_gt         = $herbana->price->harga_ritel_gt * $qty;
            $harga_grosir_mt        = $herbana->price->harga_grosir_mt * $qty;
            // $harga_semi_grosir      = $herbana->price->harga_semi_grosir * $qty;  
            if ($half == 1) {
                $harga_ritel_gt  = $harga_ritel_gt / 2;
                $harga_grosir_mt = $harga_grosir_mt / 2;
            }
        }

        // Cek Status Disc
        $discClass = $this->productStrata->where('product_id', $request->product_id)->first();

        // Disc By strata
        $totalPrice = $harga_ritel_gt;
        $orderDisc  = 0;
        $discCabang = 0;
        $status_disc = 'strata';

        // Disc By class
        if (!$discClass) {
            $status_disc = 'class';
            $totalPrice = 0;
            if ($salurCode == 'WS' || $salurCode == 'SO' || $salurCode == 'SW') {
                if ($class == 'GROSIR' || $class == 'STAR OUTLET') {
                    if ($herbana->status_herbana == null || $herbana->status_herbana == 0) {
                        if ($request->brand_id == '005') {
                            $totalPrice = $harga_ritel_gt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else if ($request->brand_id == '001') {
                            $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (4.5 / 100));
                            $orderDisc  = $harga_ritel_gt - $totalPrice;
                            $discCabang = 4.5;
                        } else if ($request->brand_id == '002' || $request->brand_id == '004' || $request->brand_id == '012' || $request->brand_id == '013' || $request->brand_id == '014') {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        }
                    } else {
                        $totalPrice = $harga_grosir_mt;
                        $orderDisc  = 0;
                        $discCabang = 0;
                    }
                } elseif ($class == 'SEMI GROSIR') {
                    if ($herbana->status_herbana == null || $herbana->status_herbana == 0) {
                        if ($request->brand_id == '005') {
                            $totalPrice = $harga_ritel_gt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else if ($request->brand_id == '001') {
                            $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (3 / 100));
                            $orderDisc  = $harga_ritel_gt - $totalPrice;
                            $discCabang = 3;
                        } else if ($request->brand_id == '002' || $request->brand_id == '004' || $request->brand_id == '012' || $request->brand_id == '013' || $request->brand_id == '014') {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        } else {
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                            $discCabang = 0;
                        }
                    } else {
                        $totalPrice = $harga_grosir_mt;
                        $orderDisc  = 0;
                        $discCabang = 0;
                    }
                } else {
                    $totalPrice = $harga_grosir_mt;
                    $orderDisc  = 0;
                    $discCabang = 0;
                }
            } elseif ($salurCode == 'RT') {
                if ($class == 'RITEL') {
                    $totalPrice = $harga_ritel_gt;
                    $orderDisc  = 0;
                    $discCabang = 0;
                } else {
                    $totalPrice = $harga_ritel_gt;
                    $orderDisc  = 0;
                    $discCabang = 0;
                }
            } else {
                $totalPrice = $harga_ritel_gt;
                $orderDisc  = 0;
                $discCabang = 0;
            }
        }

        // update shopping cart
        $shoppingCarts  = $this->shoppingCarts->find($id);

        $shoppingCarts->user_id                  = $userId;
        $shoppingCarts->product_id               = $request->product_id;
        $shoppingCarts->satuan_online            = $request->satuan_online;
        $shoppingCarts->konversi_sedang_ke_kecil = $herbana->konversi_sedang_ke_kecil;
        $shoppingCarts->half                     = $half;
        $shoppingCarts->qty_konversi             = $qty_konversi;
        $shoppingCarts->notes                    = $request->notes;
        $shoppingCarts->qty                      = $qty;
        $shoppingCarts->price_apps               = $request->price_apps;
        $shoppingCarts->total_price              = $totalPrice;
        $shoppingCarts->order_disc               = $orderDisc;
        $shoppingCarts->disc_cabang              = $discCabang;
        $shoppingCarts->status_disc              = $status_disc;

        $shoppingCarts->save();

        return $shoppingCarts;
    }

    public function handlingValidate(Request $request)
    {
        try {
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

            $requests        = $request->json()->all();

            $userId     = Auth::user()->id;
            $salurCode  = Auth::user()->salur_code;
            $class      = Auth::user()->class;
            $response   = array();
            foreach ($requests['validate_price'] as $data) {
                $cart = $this->shoppingCarts->where('id', $data['id'])->first();

                // get herbana status
                $herbana    = $this->product
                    ->select('id', 'status_promosi_coret', 'brand_id', 'konversi_sedang_ke_kecil', 'status_herbana', 'status_renceng')
                    ->where('id', $data['product_id'])
                    ->with(['price' => function ($query) {
                        $query->select('id', 'product_id', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt', 'harga_promosi_coret_semi_grosir', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_semi_grosir');
                    }])
                    ->first();
                // $qty_konversi = ($herbana->konversi_sedang_ke_kecil * $cart['qty']);

                // check if status_renceng true
                $konversi = $herbana->konversi_sedang_ke_kecil;
                if ($cart->half == '1') {
                    if ($herbana->status_renceng) {
                        $konversi = $konversi / 2;
                    }
                }

                $qty_konversi   = ($konversi * $cart->qty);

                if ($herbana->status_promosi_coret) {
                    $harga_ritel_gt         = $herbana->price->harga_promosi_coret_ritel_gt * $cart['qty'];
                    $harga_grosir_mt        = $herbana->price->harga_promosi_coret_grosir_mt * $cart['qty'];
                    $harga_semi_grosir      = $herbana->price->harga_promosi_coret_semi_grosir * $cart['qty'];
                } else {
                    $harga_ritel_gt         = $herbana->price->harga_ritel_gt * $cart['qty'];
                    $harga_grosir_mt        = $herbana->price->harga_grosir_mt * $cart['qty'];
                    $harga_semi_grosir      = $herbana->price->harga_semi_grosir * $cart['qty'];
                }

                $discClass = $this->productStrata->where('product_id', $data['product_id'])->first();

                // Disc By strata
                $totalPrice = $harga_ritel_gt;
                $orderDisc  = 0;
                $discCabang = 0;
                $priceApps  = $harga_ritel_gt / $cart['qty'];
                $status_disc = 'strata';

                // Disc By class
                if (!$discClass) {
                    $status_disc = 'class';
                    $totalPrice = 0;
                    $priceApps      = 0;
                    if ($salurCode == 'WS' || $salurCode == 'SO' || $salurCode == 'SW') {
                        if ($class == 'GROSIR' || $class == 'STAR OUTLET') {
                            if ($herbana->status_herbana == null || $herbana->status_herbana == 0) {
                                if ($herbana->brand_id == '005') {
                                    $priceApps = $harga_ritel_gt / $cart['qty'];
                                    $totalPrice = $harga_ritel_gt;
                                    $orderDisc  = 0;
                                } else if ($herbana->brand_id == '001') {
                                    $priceApps = $harga_ritel_gt / $cart['qty'];
                                    $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (4.5 / 100));
                                    $orderDisc  = $harga_ritel_gt - $totalPrice;
                                } else if ($herbana->brand_id == '002' || $herbana->brand_id == '004' || $herbana->brand_id == '012' || $herbana->brand_id == '013' || $herbana->brand_id == '014') {
                                    $priceApps = $harga_grosir_mt / $cart['qty'];
                                    $totalPrice = $harga_grosir_mt;
                                    $orderDisc  = 0;
                                } else {
                                    $priceApps = $harga_grosir_mt / $cart['qty'];
                                    $totalPrice = $harga_grosir_mt;
                                    $orderDisc  = 0;
                                }
                            } else {
                                $priceApps = $harga_grosir_mt / $cart['qty'];
                                $totalPrice = $harga_grosir_mt;
                                $orderDisc  = 0;
                            }
                        } elseif ($class == 'SEMI GROSIR') {
                            if ($herbana->status_herbana == null || $herbana->status_herbana == 0) {
                                if ($herbana->brand_id == '005') {
                                    $priceApps = $harga_ritel_gt / $cart['qty'];
                                    $totalPrice = $harga_ritel_gt;
                                    $orderDisc  = 0;
                                } else if ($herbana->brand_id == '001') {
                                    $priceApps = $harga_ritel_gt / $cart['qty'];
                                    $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (3 / 100));
                                    $orderDisc  = $harga_ritel_gt - $totalPrice;
                                } else if ($herbana->brand_id == '002' || $herbana->brand_id == '004' || $herbana->brand_id == '012' || $herbana->brand_id == '013' || $herbana->brand_id == '014') {
                                    $priceApps = $harga_grosir_mt / $cart['qty'];
                                    $totalPrice = $harga_grosir_mt;
                                    $orderDisc  = 0;
                                } else {
                                    $priceApps = $harga_grosir_mt / $cart['qty'];
                                    $totalPrice = $harga_grosir_mt;
                                    $orderDisc  = 0;
                                }
                            } else {
                                $priceApps = $harga_grosir_mt / $cart['qty'];
                                $totalPrice = $harga_grosir_mt;
                                $orderDisc  = 0;
                            }
                        } else {
                            $priceApps = $harga_grosir_mt / $cart['qty'];
                            $totalPrice = $harga_grosir_mt;
                            $orderDisc  = 0;
                        }
                    } elseif ($salurCode == 'RT') {
                        if ($class == 'RITEL') {
                            $priceApps = $harga_ritel_gt / $cart['qty'];
                            $totalPrice = $harga_ritel_gt;
                            $orderDisc  = 0;
                        } else {
                            $priceApps = $harga_ritel_gt / $cart['qty'];
                            $totalPrice = $harga_ritel_gt;
                            $orderDisc  = 0;
                        }
                    } else {
                        $priceApps = $harga_ritel_gt / $cart['qty'];
                        $totalPrice = $harga_ritel_gt;
                        $orderDisc  = 0;
                    }
                }

                $cart->user_id                  = $userId;
                $cart->product_id               = $data['product_id'];
                $cart->qty_konversi             = $qty_konversi;
                $cart->price_apps               = $priceApps;
                $cart->total_price              = $totalPrice;
                $cart->order_disc               = $orderDisc;

                $cart->save();

                array_push($response, $cart);
            }
            return response()->json([
                'success' => true,
                'message' => 'Update shopping cart successfully',
                'data'    => $response
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validate cart failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
