<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PromoSku;
use App\Promo;
use App\ShoppingCart;
use App\OrderDetail;
use App\Order;
use App\User;
use App\CreditLimit;
use App\ProductPrice;
use App\AppVersion;
use App\ProductStrata;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CheckPromoController extends Controller
{
    protected $promoSku, $promo, $shoppingCart, $orderDetail, $user, $productPrice, $order, $appVersion;

    public function __construct(PromoSku $promoSku, Promo $promo, ShoppingCart $shoppingCart, OrderDetail $orderDetail, User $user, CreditLimit $creditLimit, ProductPrice $productPrice, Order $order, ProductStrata $productStrata, AppVersion $appVersion)
    {
        $this->promoSku         = $promoSku;
        $this->promo            = $promo;
        $this->shoppingCarts    = $shoppingCart;
        $this->orderDetail      = $orderDetail;
        $this->user             = $user;
        $this->creditLimit      = $creditLimit;
        $this->productPrice     = $productPrice;
        $this->order            = $order;
        $this->productStrata      = $productStrata;
        $this->appVersion       = $appVersion;
    }

    // array for select promo
    private function arraySelectPromo()
    {
        return ['promos.id', 'promos.title', 'promos.point', 'promos.termcondition', 'promos.all_transaction', 'promos.special', 'promos.detail_termcondition', 'promos.category', 'promos.detail_category', 'promos.min_qty', 'promos.min_sku', 'promos.type_cust', 'promos.class_cust', 'promos.min_transaction', 'promos.multiple', 'promos.reward_choose', 'promos.status', 'promo_rewards.reward_disc', 'promo_rewards.reward_nominal'];
    }

    // array for select product
    private function arraySelectProduct()
    {
        return ['id', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_renceng', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    // array for select product
    private function arraySelectProductOld()
    {
        return ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }


    public function check(Request $request)
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

            $id              = Auth::user()->id;
            $requests        = $request->json()->all();
            $app_version     = Auth::user()->app_version;
            $status_blacklist = Auth::user()->status_blacklist;

            // Start check status blacklist
            if ($status_blacklist == '1') {
                return response()->json([
                    'success'   => false,
                    'message'   => 'Anda Masuk Dalam Daftar Blacklist',
                    'data'      => null
                ], 200);
            }
            // End

            // Start check maintenance mode
            $maintenance    = $this->appVersion
                ->select(['maintenance'])
                ->orderBy('version', 'DESC')
                ->where('version', $app_version)
                ->first();

            if ($maintenance->maintenance == 'true') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon Maaf Server Sedang Dalam Perbaikan',
                    'data'    => NULL
                ], 200);
            }
            // End

            // Start method cek credit limit
            $checkCreditLimit = $this->checkCreditLimit($id, $requests);
            // End

            if ($app_version == '1.1.1') {
                foreach ($requests['data'] as $product) {
                    $shopping_cart_product[] = $product['product_id'];
                    $shopping_cart_id[]      = $product['id'];
                }
                $validate_price              = [];
            } else {
                // ambil shopping cart id dan masukkan kedalam array
                foreach ($requests['data'] as $product) {
                    $shopping_cart_product[] = $product['product_id'];
                    $shopping_cart_id[]      = $product['id'];
                    $validate_product[]      = [
                        'id'                    => $product['id'],
                        'product_id'            => $product['product_id'],
                        'status_promosi_coret'  => $product['status_promosi_coret']
                    ];
                }

                $validate_price     = $this->checkPrice($validate_product);
            }

            // Chek Disc Strata
            // get value disc strata
            $strataDisc = $this->productStrata;
            $orderMin = $strataDisc->first()->min_transaction;

            // get product disc strata in shopping cart
            $productStrata = $this->shoppingCarts
                ->whereIn('id', $shopping_cart_id)
                ->whereIn('product_id', $strataDisc->pluck('product_id'))
                ->get();

            // sum total price product disc strata in shopping cart
            $totalPrice = $productStrata->sum('total_price');
            // Cek price transaction
            if (round($totalPrice) >= round($orderMin)) {
                foreach ($productStrata as $update) {
                    $shoppingCart = $this->shoppingCarts->find($update->id);
                    // Get Disc 
                    $disc = $strataDisc->where('product_id', $shoppingCart->product_id)->first()->disc_percent;

                    // count Price and Disc
                    $harga_retail = $shoppingCart->price_apps * $shoppingCart->qty;
                    $totalPrice = $harga_retail - ($harga_retail * ($disc / 100));
                    $orderDisc  = $harga_retail - $totalPrice;

                    // Update price and disc
                    $shoppingCart->total_price              = $totalPrice;
                    $shoppingCart->order_disc               = $orderDisc;
                    $shoppingCart->disc_cabang              = $disc;
                    $shoppingCart->status_class             = 1;
                    $shoppingCart->save();
                }
            }
            // End

            // ambil product_id yang promo masih aktif
            $promo_products         = $this->promoSku
                ->whereHas('promo', function ($query) {
                    return $query->where('status', '=', 1);
                })
                ->whereIn('product_id', $shopping_cart_product)
                // ->pluck('product_id')
                ->pluck('promo_id')
                ->toArray();

            // ambil data order jika customer sudah pernah order
            $first_order            = $this->order
                ->where('customer_id', $id)
                ->where('status', '!=', '10')
                ->first();

            // method check promo
            $data                   = $this->checkPromo($shopping_cart_product, $promo_products, $first_order);

            //  jika shoppingcart tidak ada promo
            if (count($data) < 1) {
                $result_promo[]     =   array(
                    'promo_id'              => null,
                    'promo_title'           => null,
                    'promo_description'     => null,
                    'promo_status'          => null,
                    'promo_reward'          => null,
                    'promo_multiple'        => null,
                    'promo_choose'          => null
                );

                $id_shoppingCart = [];
                foreach ($requests['data'] as $tes) {
                    array_push($id_shoppingCart, $tes['id']);
                }

                // method jika tidak ada promo
                $detail_promo           = $this->countWithoutPromo($id_shoppingCart);
                // jika ada promo
            } else {
                // panggil method validatePromo                 
                $promo_result           = $this->validatePromo($shopping_cart_id, $data, $id, $first_order);
                $return_detail_promo    = $this->countPromo($shopping_cart_id, $promo_result, $id);

                $detail_promo           = $return_detail_promo[0];      // update nominal disc
                $promo_result           = $return_detail_promo[1];      // update nominal disc
                $promo_id               = array();                      // inisiasi array
                foreach ($promo_result as $promo) {                      // perulangan
                    array_push($promo_id, $promo['promo_id']);
                }

                $avail_promo_id = array();
                foreach ($detail_promo[3] as $promo) {
                    if ($promo['promo_id'] != null) {
                        array_push($avail_promo_id, $promo['promo_id']);
                    }
                }
                $common_promo_id = array_intersect($promo_id, $avail_promo_id);
                $result_promo    = array();
                if (count($common_promo_id) > 0) {
                    foreach ($promo_result as $promo) {
                        $cek = in_array($promo['promo_id'], $common_promo_id);
                        if ($cek == true) {
                            array_push($result_promo, $promo);
                        } else if ($promo['promo_reward'] == null) {
                            array_push($result_promo, $promo);
                        }
                    }
                } else {
                    $result_promo = $promo_result;
                }
            }

            if ($app_version == '1.1.1') {
                $shoppingCartResponse = $this->shoppingCarts
                    ->select('shopping_cart.*', 'shopping_cart.order_disc as item_discount', 'products.brand_id', 'products.name', 'products.image_backup as image', 'products.ratio', 'products.min_pembelian', 'products.status_herbana', 'products.status_promosi_coret', 'products.status')
                    ->join('products', 'products.id', '=', 'shopping_cart.product_id')
                    ->whereIn('shopping_cart.id', $shopping_cart_id)
                    ->where('shopping_cart.user_id', $id)
                    ->get();
            } else {
                $shoppingCartResponse = $this->shoppingCarts
                    ->select('shopping_cart.*', 'shopping_cart.order_disc as item_discount', 'products.brand_id', 'products.name', 'products.image', 'products.ratio', 'products.min_pembelian', 'products.status_herbana', 'products.status_promosi_coret', 'products.kecil', 'products.status')
                    ->join('products', 'products.id', '=', 'shopping_cart.product_id')
                    ->whereIn('shopping_cart.id', $shopping_cart_id)
                    ->where('shopping_cart.user_id', $id)
                    ->get();
            }

            // start modul if app version 1.1.1 and promo choose
            if ($app_version == '1.1.1') {
                $bug                 = $shoppingCartResponse->whereNull('promo_id')->first();
                if ($bug) {
                    $all_id = $this->promo->where('status', 1)->where('all_transaction', 1)->pluck('id')->first();
                    $bug->promo_id = $all_id;
                    $bug->save();
                }
            }
            // end

            // start modul if promo choose more than 1 product
            $prev_promo = [];
            foreach ($shoppingCartResponse as &$resp) {
                if (in_array($resp->promo_id, $prev_promo)) {
                    $resp->promo_id = null;
                }
                array_push($prev_promo, $resp->promo_id);
            }
            // End

            return response()->json([
                'success'           => true,
                'message'           => 'Count Promo Successfully',
                'credit_limit'      => $checkCreditLimit,
                'shopping_carts'    => $shoppingCartResponse,
                'promo_result'      => $result_promo,
                'detail'            => $detail_promo,
                'validate_price'    => $validate_price
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get promo cart failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    // for check shopping cart containt promo
    private function checkPromo($shopping_cart_product, $promo_products, $first_order)
    {
        // ambil product_id dari shoppingcart jika ada di promoSku
        // $promo_contain = array_intersect($shopping_cart_product, $promo_products);
        // ambil array untuk select kolom promo
        $array         = $this->arraySelectPromo();
        // array untuk select kolom reward
        $arrayReward   = ['id', 'promo_id', 'reward_disc', 'max', 'reward_point', 'reward_nominal', 'reward_product_id', 'reward_qty', 'satuan'];
        // array untuk select kolom promosku
        $arraySku      = ['id', 'promo_id', 'product_id', 'min_qty', 'satuan'];

        // ambil promosku yang ada pada shoppingcart
        // $sku           = $this->promoSku
        //                             ->whereIn('product_id', $promo_contain)
        //                             ->pluck('promo_id');
        $sku           = $promo_products;

        // ambil data promo 
        $promo         = $this->promo
            ->select($array)
            ->where('status', 1)
            ->whereIn('promos.id', $sku)
            ->join('promo_rewards', 'promo_id', '=', 'promos.id')
            ->with(['reward' => function ($q) use ($arrayReward) {
                $q = $q->select($arrayReward);
                return $q;
            }, 'sku' => function ($q) use ($arraySku) {
                $q = $q->select($arraySku);
                return $q;
            }])
            // ->orderBy('promo_rewards.reward_disc', 'ASC')
            ->orderByRaw('promo_rewards.reward_disc::int ASC')
            ->orderByRaw('promo_rewards.reward_nominal::int ASC')
            // ->orderBy('promo_rewards.reward_nominal', 'ASC')
            ->get();

        // jika order an pertama
        if ($first_order) {
            // check promo semua atau sekali pakai
            $promo_all     = $this->promo
                ->select($array)
                ->join('promo_rewards', 'promo_id', '=', 'promos.id')
                ->with(['reward' => function ($q) use ($arrayReward) {
                    $q = $q->select($arrayReward);
                    return $q;
                }, 'sku' => function ($q) use ($arraySku) {
                    $q = $q->select($arraySku);
                    return $q;
                }])
                ->where('promos.status', 1)
                ->where('promos.all_transaction', 1)
                ->where(function ($q) {
                    $q->where('promos.special', 2)
                        ->orWhere('promos.all_transaction', 1);
                })
                // ->orderBy('promo_rewards.reward_disc', 'ASC')
                // ->orderBy('promo_rewards.reward_nominal', 'ASC')
                ->orderByRaw('promo_rewards.reward_disc::int ASC')
                ->orderByRaw('promo_rewards.reward_nominal::int ASC')
                ->get();
        } else {
            // jika promo special dan dapat semua
            $promo_all     = $this->promo
                ->select($array)
                ->join('promo_rewards', 'promo_id', '=', 'promos.id')
                ->with(['reward' => function ($q) use ($arrayReward) {
                    $q = $q->select($arrayReward);
                    return $q;
                }, 'sku' => function ($q) use ($arraySku) {
                    $q = $q->select($arraySku);
                    return $q;
                }])
                ->where('promos.status', 1)
                ->where(function ($query) {
                    $query->where('promos.all_transaction', 1)
                        ->orWhere('promos.special', 1);
                })
                // ->orderBy('promo_rewards.reward_disc', 'ASC')
                // ->orderBy('promo_rewards.reward_nominal', 'ASC')
                ->orderByRaw('promo_rewards.reward_disc::int ASC')
                ->orderByRaw('promo_rewards.reward_nominal::int ASC')
                ->get();
        }
        $merged = $promo->merge($promo_all);
        return $merged;
    }

    // for validate promo and give reward
    private function validatePromo($shopping_cart_id, $data, $id, $first_order)
    {
        $total_transactions = 0;
        $total_qty          = 0;
        $promo_reward       = null;
        $promo_all          = null;
        $promo_result       = array();
        $promo_status       = array();
        $promo_special      = null;
        $response           = array();
        // $count              = count($data);
        $cek_id             = [];
        $promo_multiple     = null;

        // looping $data is promo wich contain in cart
        foreach ($data as $row) {
            $shopping_cart_id_promo = [];
            $total_qty              = 0;
            $total_transactions     = 0;
            $promo_choose           = null;
            $total_sku_cart         = 0;

            // looping promo sku which is containt product
            foreach ($row->sku as $p) {
                // ambil data shopping cart
                $carts = $this->shoppingCarts
                    ->whereIn('id', $shopping_cart_id)
                    ->where('user_id', $id)
                    ->where('product_id', $p->product_id)
                    // ->where('promo_id', $p->promo_id)
                    // ->select('total_price', 'qty', 'konversi_sedang_ke_kecil', 'promo_id')
                    ->first();

                // jika termcondition minimal jumlah product
                if ($row->termcondition == 1) {
                    $promo_multiple = null;
                    // jika detail_termcondition minimal jumlah total product
                    if ($row->detail_termcondition == 1) {
                        // jika cart tidak null                                          
                        if (!is_null($carts)) {
                            $carts_qty = $carts->qty;
                            $total_qty += $carts->qty;

                            // product_id push to array $cek_id
                            array_push($cek_id, $carts->product_id);
                            // product_id push to array shopping_cart_id_promo
                            array_push($shopping_cart_id_promo, $carts->product_id);
                            // cek if total_qty greater than min_qty from sku promo
                            if ($row->min_qty <= $total_qty) {

                                // kondisi promo jika butuh tipe dan class customer
                                if ($row->type_cust && $row->class_cust) {
                                    if (auth()->user()->kode_type == $row->type_cust && auth()->user()->salur_code == $row->class_cust) {
                                        // inisiasi variable $promo_status
                                        $promo_status = 1;
                                        // update shoppingcarts if cart get promo
                                        $this->shoppingCarts
                                            ->whereIn('product_id', $shopping_cart_id_promo)
                                            ->where('user_id', $id)
                                            ->update(['promo_id' => $row->id]);
                                    } else {
                                        // untuk memberikan pesan error
                                        $message_class  = 1;
                                        $promo_status = NULL;
                                        // update promo_id di shopping_cart = null
                                        $carts->promo_id = NULL;
                                        $carts->save();
                                    }
                                } else if ($row->type_cust) {
                                    if (auth()->user()->kode_type == $row->type_cust) {
                                        // set variable promo_status
                                        $promo_status = 1;
                                        // update shopping cart promo_id if cart contains promo
                                        $this->shoppingCarts
                                            ->whereIn('product_id', $shopping_cart_id_promo)
                                            ->where('user_id', $id)
                                            ->update(['promo_id' => $row->id]);
                                    } else {
                                        // untuk memberikan pesan error
                                        $message_class  = 1;
                                        $promo_status = NULL;
                                        // update promo_id di shopping_cart = null
                                        $carts->promo_id = NULL;
                                        $carts->save();
                                    }
                                    // jika class customer
                                } else if ($row->class_cust) {
                                    if (auth()->user()->salur_code == $row->class_cust) {
                                        // set variable promo_status
                                        $promo_status = 1;
                                        // update shopping cart promo_id if cart contains promo
                                        $this->shoppingCarts
                                            ->whereIn('product_id', $shopping_cart_id_promo)
                                            ->where('user_id', $id)
                                            ->update(['promo_id' => $row->id]);
                                    } else {
                                        // untuk memberikan pesan error
                                        $message_class  = 1;
                                        $promo_status = NULL;
                                        // update promo_id di shopping_cart = null
                                        $carts->promo_id = NULL;
                                        $carts->save();
                                    }
                                    // jika tidak butuh tipe dan class
                                } else {
                                    // inisiasi variable $promo_status
                                    $promo_status = 1;
                                    // update shoppingcarts if cart get promo
                                    $this->shoppingCarts
                                        ->whereIn('product_id', $shopping_cart_id_promo)
                                        ->where('user_id', $id)
                                        ->update(['promo_id' => $row->id]);
                                }
                            } else {
                                // check if product_id at shopping cart got 2 promo
                                // cek jika ada product_id di cart dapat 2 promo
                                $counts = count(array_keys($cek_id, $carts->product_id));

                                if ($counts == 1) {
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                    $promo_status = NULL;
                                } else {
                                    // set promo_status null if the second loop didn't get promo
                                    $promo_status = NULL;
                                }
                            }
                        } else {
                            $total_qty += 0;
                        }

                        // jika detail_termcondition minimal jumlah per product
                    } else if ($row->detail_termcondition == 2) {
                        if (!is_null($carts)) {
                            // get min product at promo sku
                            $min = $row->sku->where('promo_id', $row->id)
                                ->where('min_qty', '!=', NULL)
                                ->first();
                            // get qty at shoppingcart
                            $carts_qty = $this->shoppingCarts->where('product_id', $min->product_id)->pluck('qty')->first();

                            // push to array $cek_id 
                            array_push($cek_id, $carts->product_id);
                            // push to array $shopping_cart_id_promo
                            array_push($shopping_cart_id_promo, $carts->product_id);
                            // if qty at cart greater than min_qty at promo_sku
                            if ($carts_qty >= $min->min_qty) {

                                // kondisi promo jika butuh tipe dan class customer
                                if ($row->type_cust && $row->class_cust) {
                                    if (auth()->user()->kode_type == $row->type_cust && auth()->user()->salur_code == $row->class_cust) {
                                        // set variable promo_status
                                        $promo_status = 1;
                                        // update shopping cart promo_id if cart contains promo
                                        $this->shoppingCarts
                                            ->whereIn('product_id', $shopping_cart_id_promo)
                                            ->where('user_id', $id)
                                            ->update(['promo_id' => $row->id]);
                                    } else {
                                        // untuk memberikan pesan error
                                        $message_class  = 1;
                                        $promo_status = NULL;
                                        // update promo_id di shopping_cart = null
                                        $carts->promo_id = NULL;
                                        $carts->save();
                                    }
                                    // jika tipe customer
                                } else if ($row->type_cust) {
                                    if (auth()->user()->kode_type == $row->type_cust) {
                                        // set variable promo_status
                                        $promo_status = 1;
                                        // update shopping cart promo_id if cart contains promo
                                        $this->shoppingCarts
                                            ->whereIn('product_id', $shopping_cart_id_promo)
                                            ->where('user_id', $id)
                                            ->update(['promo_id' => $row->id]);
                                    } else {
                                        // untuk memberikan pesan error
                                        $message_class  = 1;
                                        $promo_status = NULL;
                                        // update promo_id di shopping_cart = null
                                        $carts->promo_id = NULL;
                                        $carts->save();
                                    }
                                    // jika class customer
                                } else if ($row->class_cust) {
                                    if (auth()->user()->salur_code == $row->class_cust) {
                                        // set variable promo_status
                                        $promo_status = 1;
                                        // update shopping cart promo_id if cart contains promo
                                        $this->shoppingCarts
                                            ->whereIn('product_id', $shopping_cart_id_promo)
                                            ->where('user_id', $id)
                                            ->update(['promo_id' => $row->id]);
                                    } else {
                                        // untuk memberikan pesan error
                                        $message_class  = 1;
                                        $promo_status = NULL;
                                        // update promo_id di shopping_cart = null
                                        $carts->promo_id = NULL;
                                        $carts->save();
                                    }
                                    // jika tidak butuh tipe dan class
                                } else {
                                    // set variable promo_status
                                    $promo_status = 1;
                                    // update shopping cart promo_id if cart contains promo
                                    $this->shoppingCarts
                                        ->whereIn('product_id', $shopping_cart_id_promo)
                                        ->where('user_id', $id)
                                        ->update(['promo_id' => $row->id]);
                                    // $carts_qty = $carts->qty;
                                    // $carts->promo_id = $row->id;
                                    // $carts->save();
                                }
                            } else {
                                // cek jika ada product_id di cart dapat 2 promo
                                $counts = count(array_keys($cek_id, $carts->product_id));

                                if ($counts == 1) {
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                    $promo_status = NULL;
                                } else {
                                    $promo_status = NULL;
                                }
                            }
                        }
                    }
                    // jika termcondition minimal transaksi
                } else if ($row->termcondition == 2) {
                    $promo_multiple = null;
                    // jika cart tidak kosong
                    if (!is_null($carts)) {
                        $total_transactions += $carts->total_price;
                        // cek jika 1 product dapat 2 promo
                        array_push($cek_id, $carts->product_id);
                        array_push($shopping_cart_id_promo, $carts->product_id);

                        // jika total_transaksi lebih besar dari min_transaksi
                        if ($row->min_transaction <= $total_transactions) {
                            // kondisi promo jika butuh tipe dan class customer
                            if ($row->type_cust && $row->class_cust) {
                                if (auth()->user()->kode_type == $row->type_cust && auth()->user()->salur_code == $row->class_cust) {
                                    // set variable promo_status
                                    $promo_status = 1;
                                    // update shopping cart promo_id if cart contains promo
                                    $this->shoppingCarts
                                        ->whereIn('product_id', $shopping_cart_id_promo)
                                        ->where('user_id', $id)
                                        ->update(['promo_id' => $row->id]);
                                } else {
                                    // untuk memberikan pesan error
                                    $message_class  = 1;
                                    $promo_status = NULL;
                                    // update promo_id di shopping_cart = null
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                }
                            } else if ($row->type_cust) {
                                if (auth()->user()->kode_type == $row->type_cust) {
                                    // set variable promo_status
                                    $promo_status = 1;
                                    // update shopping cart promo_id if cart contains promo
                                    $this->shoppingCarts
                                        ->whereIn('product_id', $shopping_cart_id_promo)
                                        ->where('user_id', $id)
                                        ->update(['promo_id' => $row->id]);
                                } else {
                                    // untuk memberikan pesan error
                                    $message_class  = 1;
                                    $promo_status = NULL;
                                    // update promo_id di shopping_cart = null
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                }
                                // jika class customer
                            } else if ($row->class_cust) {
                                if (auth()->user()->salur_code == $row->class_cust) {
                                    // set variable promo_status
                                    $promo_status = 1;
                                    // update shopping cart promo_id if cart contains promo
                                    $this->shoppingCarts
                                        ->whereIn('product_id', $shopping_cart_id_promo)
                                        ->where('user_id', $id)
                                        ->update(['promo_id' => $row->id]);
                                } else {
                                    // untuk memberikan pesan error
                                    $message_class  = 1;
                                    $promo_status = NULL;
                                    // update promo_id di shopping_cart = null
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                }
                                // jika tidak butuh tipe dan class
                            } else {
                                // kondisi promo gimmick disini 
                                $promo_status = 1;
                                $this->shoppingCarts
                                    ->whereIn('product_id', $shopping_cart_id_promo)
                                    ->where('user_id', $id)
                                    ->update(['promo_id' => $row->id]);

                                // $carts->promo_id = $row->id;
                                // $carts->save();
                            }
                        } else {
                            $counts = count(array_keys($cek_id, $carts->product_id));
                            if ($counts == 1) {
                                $carts->promo_id = NULL;
                                $carts->save();
                                $promo_status = NULL;
                            } else {
                                $promo_status = NULL;
                            }
                        }
                    } else {
                        $total_transactions += 0;
                    }
                } else if ($row->termcondition == 3) {                                               // if termcondition minimal transaksi && jumlah product
                    if ($row->detail_termcondition == 1) {                                           // if detail_termcondition min transaksi && min total product
                        if (!is_null($carts)) {
                            $total_transactions += $carts->total_price;
                            $total_qty += $carts->qty;
                            array_push($cek_id, $carts->product_id);
                            array_push($shopping_cart_id_promo, $carts->product_id);

                            if ($row->min_transaction <= $total_transactions && $row->min_qty <= $total_qty) {
                                $promo_status = 1;
                                $this->shoppingCarts
                                    ->whereIn('product_id', $shopping_cart_id_promo)
                                    ->where('user_id', $id)
                                    ->update(['promo_id' => $row->id]);
                            } else {
                                $counts = count(array_keys($cek_id, $carts->product_id));
                                if ($counts == 1) {
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                    $promo_status = NULL;
                                } else {
                                    $promo_status = NULL;
                                }
                            }
                        } else {
                            $total_transactions += 0;
                            $total_qty += 0;
                        }
                    } else if ($row->detail_termcondition == 2) {                                    // if detail_termcondition min transaksi && qty per product
                        if (!is_null($carts)) {
                            $total_transactions += $carts->total_price;
                            $min = $row->sku->where('promo_id', $row->id)
                                ->where('min_qty', '!=', NULL)
                                ->first();
                            array_push($cek_id, $carts->product_id);
                            array_push($shopping_cart_id_promo, $carts->product_id);

                            $carts_qty = $this->shoppingCarts->where('product_id', $min->product_id)->pluck('qty')->first();
                            if ($carts->qty >= $min->min_qty) {
                                $success = 1;
                                // $carts_qty = $carts->qty;
                            } else {
                                $success = 0;
                                $min_qty = $min->min_qty;
                                // $carts_qty = $carts->qty;
                            }
                            if ($row->min_transaction <= $total_transactions && $success >= 1) {
                                $promo_status = 1;
                                // $carts->promo_id = $row->id;
                                // $carts->save();
                                $this->shoppingCarts
                                    ->whereIn('product_id', $shopping_cart_id_promo)
                                    ->where('user_id', $id)
                                    ->update(['promo_id' => $row->id]);
                            } else {
                                $counts = count(array_keys($cek_id, $carts->product_id));
                                if ($counts == 1) {
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                    $promo_status = NULL;
                                } else {
                                    $promo_status = NULL;
                                }
                            }
                        } else {
                            $total_transactions     += 0;
                        }
                    } else if ($row->detail_termcondition == 3) {                                    // if detail_termcondtion min transaksi && min sku
                        if (!is_null($carts)) {
                            $total_transactions += $carts->total_price;
                            $total_sku_cart     += 1;

                            array_push($cek_id, $carts->product_id);
                            array_push($shopping_cart_id_promo, $carts->product_id);
                            if ($row->min_transaction <= $total_transactions && $total_sku_cart >= $row->min_sku) {
                                $promo_status   = 1;
                                $this->shoppingCarts
                                    ->whereIn('product_id', $shopping_cart_id_promo)
                                    ->where('user_id', $id)
                                    ->update(['promo_id' => $row->id]);
                            } else {
                                $counts = count(array_keys($cek_id, $carts->product_id));
                                if ($counts == 1) {
                                    $carts->promo_id = NULL;
                                    $carts->save();
                                    $promo_status = NULL;
                                } else {
                                    $promo_status = NULL;
                                }
                            }
                        } else {
                            $total_transactions     += 0;
                        }
                    }
                }
            }

            // memberi pesan jika dapat atau tidak dapat promo
            if ($row->termcondition == 1) {                                                          // if termcondition jumlah product
                if ($row->detail_termcondition == 1) {                                               // if detail_termcondition total product
                    $promo_multiple = null;
                    // jika dapat promo
                    if ($promo_status == 1) {
                        $promo_result = "Selamat anda mendapatkan promo " . $row->title;

                        // jika promo kelipatan
                        if ($row->multiple) {
                            $qty_batas          = $total_qty;
                            $promo_multiple     = $row->multiple;
                            $no                 = 0;
                            foreach ($row->reward as $r) {
                                for ($i = 1; $i <= $qty_batas; $i++) {
                                    if ($i % $row->min_qty == 0) {
                                        $total_qty += $r->reward_qty;
                                    }
                                }
                            }
                            $qty_reward = $total_qty - $qty_batas;
                        }
                        // jika tidak dapat promo
                    } else {
                        $promo_result = "Anda belum memenuhi syarat promo " . $row->title . ". Untuk mendapatkan promo tambahkan product sejumlah " . ($row->min_qty - $total_qty) . " sesuai syarat yang berlaku";
                        // check jika promo berdasarkan class dan tipe 
                        if (isset($message_class)) {
                            $promo_result = "Tipe dan kelas toko anda tidak memenuhi syarat promo";
                        }
                    }
                } else if ($row->detail_termcondition == 2) {                                        // if detail_termcondition per product
                    $min_qty = $row->sku->where('promo_id', $row->id)
                        ->where('min_qty', '!=', NULL)
                        ->pluck('min_qty')
                        ->first();

                    $qty_batas        =    $carts_qty;
                    if ($promo_status == 1) {
                        $promo_result = "Selamat anda mendapatkan promo " . $row->title;
                        if ($row->multiple) {
                            $promo_multiple   =   $row->multiple;
                            foreach ($row->reward as $r) {
                                for ($i = 1; $i <= $qty_batas; $i++) {
                                    if ($i % $min_qty == 0) {
                                        $carts_qty += $r->reward_qty;
                                    }
                                }
                            }
                            $qty_reward = $carts_qty - $qty_batas;
                        }
                    } else {
                        // return $min_qty - $carts_qty;
                        $promo_result = "Anda belum memenuhi syarat promo " . $row->title . ". Untuk mendapatkan promo, tambahkan product sejumlah " . ($min_qty - $carts_qty) . " sesuai syarat yang berlaku";
                        // check jika promo berdasarkan class dan tipe 
                        if (isset($message_class)) {
                            $promo_result = "Tipe dan kelas toko anda tidak memenuhi syarat promo";
                        }
                    }
                }
            } else if ($row->termcondition == 2) {                                                   // if termcondition jumlah transaksi
                if ($promo_status == 1) {
                    $promo_result = "Selamat anda mendapatkan promo " . $row->title;
                } else {
                    $promo_result = "Anda belum memenuhi syarat promo " . $row->title . ". Untuk mendapatkan promo, tambahkan transaksi sejumlah Rp. " . (number_format(round($row->min_transaction - $total_transactions))) . " sesuai syarat yang berlaku";
                    // check jika promo berdasarkan class dan tipe 
                    if (isset($message_class)) {
                        $promo_result = "Tipe dan kelas toko anda tidak memenuhi syarat promo";
                    }
                }
            } else if ($row->termcondition == 3) {                                                   // if termcondition jumlah transaksi && product
                $promo_multiple = null;

                if ($row->detail_termcondition == 1) {                                                                                   // if detail_termcondition jumlah transaksi && total product
                    if ($promo_status == 1) {
                        $promo_result = "Selamat anda mendapatkan promo " . $row->title;
                        if ($row->multiple) {
                            $qty_batas        =    $total_qty;

                            $promo_multiple   =   $row->multiple;
                            foreach ($row->reward as $r) {
                                for ($i = 1; $i <= $qty_batas; $i++) {
                                    if ($i % $row->min_qty == 0) {
                                        $total_qty += $r->reward_qty;
                                    }
                                }
                            }
                            $qty_reward = $total_qty - $qty_batas;
                        }
                    } else {
                        if ($row->min_transaction <= $total_transactions) {
                            $transaction = null;
                        } else {
                            $transaction = 'transaksi sejumlah ' . ($row->min_transaction - $total_transactions);
                        }
                        if ($row->min_qty <= $total_qty) {
                            $qty = null;
                        } else if ($transaction) {
                            $qty = 'dan product sejumlah ' . ($row->min_qty - $total_qty);
                        } else {
                            $qty = 'product sejumlah ' . ($row->min_qty - $total_qty);
                        }
                        $promo_result = "Anda belum memenuhi syarat promo " . $row->title . ". Untuk mendapatkan promo, tambahkan " . $transaction . ' ' . $qty;
                    }
                } else if ($row->detail_termcondition == 2) {                                                                            // if detail_termcondition jumlah transaksi && per product 
                    if ($promo_status == 1) {
                        $promo_result = "Selamat anda mendapatkan promo " . $row->title;
                        $min_qty = $row->sku->where('promo_id', $row->id)->where('min_qty', '!=', NULL)->pluck('min_qty')->first();
                        $qty_batas        =    $carts_qty;
                        if ($promo_status == 1) {
                            if ($row->multiple) {
                                $promo_multiple   =   $row->multiple;
                                foreach ($row->reward as $r) {
                                    for ($i = 1; $i <= $qty_batas; $i++) {
                                        if ($i % $min_qty == 0) {
                                            $carts_qty += $r->reward_qty;
                                        }
                                    }
                                }
                                $qty_reward = $total_qty - $carts_qty;
                            }
                        }
                    } else {
                        if ($row->min_transaction <= $total_transactions) {
                            $transaction = null;
                        } else {
                            $transaction = 'transaksi sejumlah Rp. ' . (number_format(round($row->min_transaction - $total_transactions)));
                        }
                        if ($success >= 1) {
                            $qty = null;
                        } else if ($transaction) {
                            $qty = 'dan product sejumlah ' . ($min_qty - $carts_qty);
                        } else {
                            $qty = 'product sejumlah ' . ($min_qty - $carts_qty);
                        }
                        $promo_result = "Anda belum memenuhi syarat promo " . $row->title . ". Untuk mendapatkan promo, tambahkan " . $transaction . ' ' . $qty;
                    }
                } else if ($row->detail_termcondition == 3) {                                                                            // if detail_termcondition jumlah transaksi && sku
                    if ($promo_status == 1) {
                        $promo_result   = "Selamat anda mendapatkan promo " . $row->title;
                    } else {
                        if ($row->min_transaction <= $total_transactions) {
                            $transaction = null;
                        } else {
                            $transaction = 'transaksi sejumlah Rp. ' . (number_format(round($row->min_transaction - $total_transactions)));
                        }
                        if ($total_sku_cart >= $row->min_sku) {
                            $qty = null;
                        } else if ($transaction) {
                            $qty = 'dan product SKU sejumlah ' . ($row->min_sku - $total_sku_cart);
                        } else {
                            $qty = 'product SKU sejumlah ' . ($row->min_sku - $total_sku_cart);
                        }
                        $promo_result = "Anda belum memenuhi syarat promo " . $row->title . ". Untuk mendapatkan promo, tambahkan " . $transaction . ' ' . $qty;
                    }
                }
            }

            if ($row->all_transaction == 1) {                                                        // if promo for all transaction
                if ($row->special == 2) {
                    $checkOrder = $this->order
                        ->select('id')
                        ->where('status', '!=', 10)
                        ->whereNull('deleted_at')
                        ->where('customer_id', $id)
                        // ->whereHas('data_item', function($q) use($row) {
                        //     $q->where('promo_id', $row->id);
                        // })
                        ->whereHas('data_promo', function ($q) use ($row) {
                            $q->where('promo_id', $row->id);
                        })
                        ->first();

                    // jika sudah dapat promo
                    if (is_null($checkOrder)) {
                        $promo_result   = "Selamat anda mendapatkan promo " . $row->title;
                        $promo_status   = 1;
                        $promo_all      = null;
                        $promo_multiple = null;
                        $promo_special  = 2;
                    } else {
                        $promo_result   = "Maaf anda sudah pernah mendapatkan promo " . $row->title;
                        $promo_status   = null;
                        $promo_all      = null;
                        $promo_multiple = null;
                        $promo_special  = null;
                    }
                } else {
                    $promo_result   = "Selamat anda mendapatkan promo " . $row->title;
                    $promo_status   = 1;
                    $promo_all      = 1;
                    $promo_multiple = null;
                    $promo_special  = null;
                }
            }

            if ($first_order == null) {
                if ($row->special == 1) {
                    $promo_result       = "Selamat anda mendapatkan promo special " . $row->title;
                    $promo_status       = 1;
                    $promo_all          = null;
                    $promo_multiple     = null;
                    $promo_special      = 1;
                }
            }

            // give promo status && reward
            if ($promo_status) {                                                                                                         // give promo status 
                $promo_reward['product']    = null;
                $promo_reward['disc']       = null;
                $promo_reward['nominal']    = null;
                $promo_reward['point']      = null;
                $promo_reward['max']        = null;
                if ($row->reward_choose == '1') {
                    foreach ($row->reward as $r) {                                                                                           //  foreach promo reward
                        if ($r->reward_product_id) {                                                                                         // insert reward to variable
                            if ($promo_multiple) {
                                $promo_choose[]      =   array(
                                    'qty'               => ($qty_reward / $row->reward->count()),
                                    'product_id'        => $r->product->kodeprod,
                                    'product_name'      => $r->product->name,
                                    'konversi_satuan'   => $r->product->konversi_sedang_ke_kecil
                                );
                            } else {
                                $promo_choose[]      =   array(
                                    'qty'               => $r->reward_qty,
                                    'product_id'        => $r->product->kodeprod,
                                    'product_name'      => $r->product->name,
                                    'konversi_satuan'   => $r->product->konversi_sedang_ke_kecil
                                );
                            }
                        }
                    }
                } else {
                    foreach ($row->reward as $r) {                                                                                           //  foreach promo reward
                        if ($r->reward_product_id) {                                                                                         // insert reward to variable
                            if ($promo_multiple) {
                                $promo_reward['product'][]     =   array(
                                    'qty'               => $qty_reward,
                                    'product_id'        => $r->product->kodeprod,
                                    'product_name'      => $r->product->name,
                                    'konversi_satuan'   => $r->product->konversi_sedang_ke_kecil
                                );
                            } else {
                                $promo_reward['product'][]    =   array(
                                    'qty'               => $r->reward_qty,
                                    'product_id'        => $r->product->kodeprod,
                                    'product_name'      => $r->product->name,
                                    'konversi_satuan'   => $r->product->konversi_sedang_ke_kecil
                                );
                            }
                        } else {
                            if ($r->reward_disc) {
                                if ($r->max) {
                                    $promo_reward['disc']    = (float)$r->reward_disc;
                                    $promo_reward['max']     = $r->max;
                                }
                                $promo_reward['disc']    = (float)$r->reward_disc;
                                $promo_reward['max']     = $r->max;
                            } else if ($r->reward_point) {
                                $promo_reward['point']   = (int)$r->reward_point;
                            } else if ($r->reward_nominal) {
                                $promo_reward['nominal'] = (int)$r->reward_nominal;
                            }
                        }
                    }
                }

                if ($promo_reward['product'] == null) {                                                                                  // if product reward not product
                    $promo_reward['product'][] = array(
                        'qty'               => null,
                        'product_id'        => null,
                        'product_name'      => null,
                        'konversi_satuan'   => null
                    );
                } else if ($promo_choose == null) {
                    $promo_choose[] = array(
                        'qty'               => null,
                        'product_id'        => null,
                        'product_name'      => null,
                        'konversi_satuan'   => null
                    );
                }
            } else {
                $promo_reward = NULL;
            }

            $response[] = array(                                                                                                        // insert variable to array
                'promo_id'          => $row->id,
                'promo_title'       => $row->title,
                'promo_description' => $promo_result,
                'promo_status'      => $promo_status,
                'promo_all'         => $promo_all,
                'promo_special'     => $promo_special,
                'promo_reward'      => $promo_reward,
                'promo_multiple'    => $promo_multiple,
                'promo_choose'      => $promo_choose
            );
        }
        return $response;
    }

    // to count transaction if get promo 
    private function countPromo($shopping_cart_id, $promo_result, $user_id)
    {
        $total_point                      = null;
        $total                            = null;
        $total_price_before_promo         = 0;
        $total_price_after_promo          = 0;
        $total_price_after_promo_special  = 0;
        $total_price_before_promo_special = 0;
        $promo_product                    = null;
        $promo_point                      = 0;
        $array                            = null;
        $response                         = [];
        $promo_special                    = [];

        // iterate to get cart and total transaction
        $no = 0;
        foreach ($promo_result as &$row) {
            $cart = $this->shoppingCarts
                ->whereIn('id', $shopping_cart_id)
                ->where('user_id', $user_id)
                ->where('promo_id', $row['promo_id'])
                ->whereNull('deleted_at')
                ->select(DB::raw("SUM(total_price) as total_price"))
                ->first();

            if ($row['promo_special'] == 1 || $row['promo_special'] == 2) {
                $promo_special = [
                    'promo_id'      => $row['promo_id'],
                    'promo_title'   => $row['promo_title'],
                    'discount'      => $row['promo_reward']['disc'],
                    'max'           => $row['promo_reward']['max']
                ];
            }

            if ($row['promo_all'] == null) {                                         // if promo for selected product
                if ($cart->total_price) {                                            // if cart have total_price

                    if ($row['promo_reward']['disc']) {
                        $disc               = $cart->total_price * (float)$row['promo_reward']['disc'] / 100;
                        if ($row['promo_reward']['max']) {
                            if ($disc > $row['promo_reward']['max']) {
                                $disc       = $row['promo_reward']['max'];
                            }
                        }
                        $total_price_disc   = $cart->total_price - $disc;

                        // get total price before promo
                        $total_price_before_promo += $cart->total_price;
                        // get total price after promo
                        $total_price_after_promo += $total_price_disc;

                        // $total = $total_price_disc;
                        $total      = (float)$row['promo_reward']['disc'] . "% " . "(" . round($disc) . ")";   // give discount description
                        $row['promo_reward']['nominal'] = round($disc);                                         // give nominal discount 
                        $promo_id   =   $row['promo_id'];
                    } else if ($row['promo_reward']['nominal']) {
                        $total_price_potongan = $cart->total_price - $row['promo_reward']['nominal']; // count nominal

                        $total_price_before_promo += $cart->total_price;                            // get total price before promo
                        $total_price_after_promo += $total_price_potongan;                          // get total price after promo

                        // $total = $total_price_potongan;
                        $total      = $row['promo_reward']['nominal'];
                        $promo_id   =   $row['promo_id'];
                    } else if ($row['promo_reward']['point']) {                                       // count nominal  
                        $total          = null;
                        $total_point = $row['promo_reward']['point'];
                        $promo_id   =   $row['promo_id'];
                        $promo_point += $cart->total_price;                            // get total price before promo
                    } else if (count($row['promo_reward']['product']) >= 1) {                         // check if promo product
                        $total          = null;
                        $total_point    = null;
                        $promo_product  += $cart->total_price;
                        $promo_id       = $row['promo_id'];
                    }

                    $array[] = array(
                        'promo_id'      => $promo_id,
                        'promo_title'   => $row['promo_title'],
                        'harga_promo'   => $total,
                        'point_promo'   => $total_point
                    );
                } else {
                    if ($array == null) {
                        $array[] = array(
                            'promo_id'      => null,
                            'promo_title'   => null,
                            'harga_promo'   => null,
                            'point_promo'   => null
                        );
                    }
                }
            } else if ($row['promo_all'] == 1 && is_null($row['promo_special'])) {                                        // if promo for all transaction
                $promo_product += $cart->total_price;
                $array[] = array(
                    'promo_id'      => $row['promo_id'],
                    'promo_title'   => $row['promo_title'],
                    'harga_promo'   => null,
                    'point_promo'   => null
                );
            }
        }

        $cart_non_promo = $this->shoppingCarts
            ->whereIn('id', $shopping_cart_id)
            ->where('user_id', $user_id)
            ->whereNull('promo_id')
            ->whereNull('deleted_at')
            ->select(DB::raw("SUM(total_price) as total_price"))
            ->first();

        if ($promo_product) {
            $cart_non_promo->total_price += $promo_product;
            if ($promo_point) {
                $cart_non_promo->total_price += $promo_point;
            }
        } else if ($promo_point) {
            $cart_non_promo->total_price += $promo_point;
            if ($promo_product) {
                $cart_non_promo->total_price += $promo_product;
            }
        }

        if ($promo_special) {
            if ($total_price_after_promo) {
                $total_price_before_promo_special += $total_price_after_promo;
            }

            if ($cart_non_promo->total_price) {
                $total_price_before_promo_special += $cart_non_promo->total_price;
            }

            $disc             = round($total_price_before_promo_special * $promo_special['discount'] / 100);
            if ($disc > $promo_special['max']) {
                $disc         = $promo_special['max'];
            }
            $total_price_after_promo_special = $total_price_before_promo_special - $disc;

            $array[] = array(
                'promo_id'      => $promo_special['promo_id'],
                'promo_title'   => $promo_special['promo_title'],
                'harga_promo'   => $promo_special['discount'] . "% " . "(" . round($disc) . ")",
                'point_promo'   => $total_point
            );

            $non_promo          = ['total_non_promo'           => 0];
            $total_price_before = ['total_price_before_promo'  => $total_price_before_promo_special];
            $total_price_after  = ['total_price_after_promo'   => $total_price_after_promo_special];
            array_push($response, $total_price_before, $total_price_after, $non_promo, $array);

            $promo_result[count($promo_result) - 1]['promo_reward']['nominal'] = round($disc); // set promo result

            return [$response, $promo_result];
        }


        $non_promo          = array('total_non_promo' => $cart_non_promo->total_price);
        $total_price_before = array('total_price_before_promo' => $total_price_before_promo);
        $total_price_after  = array('total_price_after_promo' => $total_price_after_promo);
        array_push($response, $total_price_before, $total_price_after, $non_promo, $array);
        return [$response, $promo_result];
    }

    private function countWithoutPromo($id_shoppingCart)
    {
        $total_price_before_promo   = 0;
        $total_price_after_promo    = 0;
        $non_promo                  = 0;

        // looping untuk mendapatkan total price
        $non_promo = $this->shoppingCarts
            ->whereIn('id', $id_shoppingCart)
            ->select(DB::raw("SUM(total_price) as total_price"))
            ->first();

        $response           = array();

        $non_promo          = array('total_non_promo' => $non_promo->total_price);
        $total_price_before = array('total_price_before_promo' => $total_price_before_promo);
        $total_price_after  = array('total_price_after_promo' => $total_price_after_promo);
        array_push($response, $total_price_before, $total_price_after, $non_promo);
        return $response;
    }

    private function checkCreditLimit($customer_id, $requests)
    {
        $user               = $this->user->find($customer_id);
        $availCreditLimit   = array();
        $response           = null;
        $brand              = array();

        $creditCart = new Collection();

        foreach ($requests['data'] as $item) {
            $creditCart->push((object)[
                'id'                        => $item['id'],
                'user_id'                   => $item['user_id'],
                'product_id'                => $item['product_id'],
                'qty'                       => $item['qty'],
                'price_apps'                => $item['price_apps'],
                'total_price'               => $item['total_price'],
                'brand_id'                  => $item['brand_id']
            ]);
        }

        $creditCarts = $creditCart->groupBy('brand_id');

        $creditCartsCount = $creditCarts->map(function ($group) {
            return $group->sum('total_price');
        });

        $month = Carbon::now()->format('m');

        $creditUsers = $this->orderDetail
            ->select(
                'products.brand_id',
                DB::raw('sum(order_detail.total_price) as total_price')
            )
            ->whereMonth('order_detail.created_at', $month)
            ->where('orders.customer_id', $customer_id)
            ->groupBy('products.brand_id')
            ->join('products', 'products.id', '=', 'order_detail.product_id')
            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
            ->get();

        foreach ($creditUsers as $history) {
            $histories[$history->brand_id] =  $history->total_price;
        }

        $creditLimit =  $this->creditLimit
            ->where('customer_code', $user->customer_code)
            ->select([DB::raw('substr(brand_id, 3, 5) as brand_id'), 'credit_limit'])
            ->get();

        foreach ($creditLimit as $credit) {
            $cek = isset($histories[$credit['brand_id']]) ? 1 : 0;
            if ($cek == 1) {
                if ($histories[$credit['brand_id']] >= $credit['credit_limit']) {
                    array_push($brand, $credit['brand_id']);
                } else {
                    $availCredit = $credit['credit_limit'] - $histories[$credit['brand_id']];
                    $availCreditLimit[] = array(
                        'brand_id' => $credit['brand_id'],
                        'credit_limit' => $availCredit
                    );
                }
            } else {
                $availCredit = $credit['credit_limit'];
                $availCreditLimit[] = array(
                    'brand_id' => $credit['brand_id'],
                    'credit_limit' => $availCredit
                );
            }
        }

        foreach ($availCreditLimit as $index => $key) {
            $cek = isset($creditCartsCount[$key['brand_id']]) ? 1 : 0;

            if ($cek == 1) {
                if ($creditCartsCount[$key['brand_id']] >= $key['credit_limit']) {
                    array_push($brand, $credit['brand_id']);
                }
            }
        }

        if (count($brand) > 1) {
            $brand = implode(",", $brand);
            $response[] = array('message' => 'Transaksi dengan brand id ' . $brand . ' Sudah melebihi credit limit');
        }

        return $response;
    }

    private function checkPrice($validate_product)
    {
        $validate_id    = [];
        $salur_code     = auth()->user()->salur_code;
        $class          = auth()->user()->class;

        foreach ($validate_product as $val) {
            // Get shoping Cart
            $cart       = $this->shoppingCarts->where('id', $val['id'])->first();

            // Get Product
            $product    = $this->productPrice->join('products', 'products.id', '=', 'product_id')
                ->where('product_id', $val['product_id'])
                ->select('product_prices.*', 'products.brand_id', 'products.status_herbana')
                ->first();

            // Cek Status Disc
            $discClass = $this->productStrata->where('product_id', $val['product_id'])->first();

            // Disc By strata
            if ($discClass) {
                if ($val['status_promosi_coret'] == 1) {
                    if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                    }
                } else {
                    if ($cart->price_apps != $product->harga_ritel_gt) {
                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                    }
                }
            }

            // Disc By Class
            if (!$discClass) {
                if ($salur_code == 'WS' || $salur_code == 'SO' || $salur_code == 'SW') {
                    if ($class == 'GROSIR' || $class == 'STAR OUTLET') {
                        if ($product->status_herbana == null || $product->status_herbana == 0) {
                            if ($product->brand_id == '005') {
                                // $totalPrice = $harga_ritel_gt;
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            } else if ($product->brand_id == '001') {
                                // $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (4.5/100));
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            } else if ($product->brand_id == '002' || $product->brand_id == '004' || $product->brand_id == '012' || $product->brand_id == '013' || $product->brand_id == '014') {
                                // $totalPrice = $harga_grosir_mt;
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            } else {
                                // $totalPrice = $harga_grosir_mt;
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            }
                        } else {
                            // $totalPrice = $harga_grosir_mt;
                            if ($val['status_promosi_coret'] == 1) {
                                if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                    $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                }
                            } else {
                                if ($cart->price_apps != $product->harga_grosir_mt) {
                                    $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                }
                            }
                        }
                    } elseif ($class == 'SEMI GROSIR') {
                        if ($product->status_herbana == null || $product->status_herbana == 0) {
                            if ($product->brand_id == '005') {
                                // $totalPrice = $harga_ritel_gt;
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            } else if ($product->brand_id == '001') {
                                // $totalPrice = $harga_ritel_gt - ($harga_ritel_gt * (3/100));
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_ritel_gt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            } else if ($product->brand_id == '002' || $product->brand_id == '004' || $product->brand_id == '012' || $product->brand_id == '013' || $product->brand_id == '014') {
                                // $totalPrice = $harga_grosir_mt;
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            } else {
                                // $totalPrice = $harga_grosir_mt;
                                if ($val['status_promosi_coret'] == 1) {
                                    if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                } else {
                                    if ($cart->price_apps != $product->harga_grosir_mt) {
                                        $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                    }
                                }
                            }
                        } else {
                            // $totalPrice = $harga_grosir_mt;
                            if ($val['status_promosi_coret'] == 1) {
                                if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                    $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                }
                            } else {
                                if ($cart->price_apps != $product->harga_grosir_mt) {
                                    $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                                }
                            }
                        }
                    } else {
                        // $totalPrice = $harga_grosir_mt;
                        if ($val['status_promosi_coret'] == 1) {
                            if ($cart->price_apps != $product->harga_promosi_coret_grosir_mt) {
                                $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                            }
                        } else {
                            if ($cart->price_apps != $product->harga_grosir_mt) {
                                $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                            }
                        }
                    }
                } elseif ($salur_code == 'RT') {
                    if ($class == 'RITEL') {
                        // $totalPrice = $harga_ritel_gt;
                        if ($val['status_promosi_coret'] == 1) {
                            if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                                $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                            }
                        } else {
                            if ($cart->price_apps != $product->harga_ritel_gt) {
                                $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                            }
                        }
                    } else {
                        // $totalPrice = $harga_ritel_gt;
                        if ($val['status_promosi_coret'] == 1) {
                            if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                                $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                            }
                        } else {
                            if ($cart->price_apps != $product->harga_ritel_gt) {
                                $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                            }
                        }
                    }
                } else {
                    // $totalPrice = $harga_ritel_gt;
                    if ($val['status_promosi_coret'] == 1) {
                        if ($cart->price_apps != $product->harga_promosi_coret_ritel_gt) {
                            $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                        }
                    } else {
                        if ($cart->price_apps != $product->harga_ritel_gt) {
                            $validate_id[] = ['id' => $val['id'], 'product_id' => $val['product_id']];
                        }
                    }
                }
            }
        }
        // return id_shopingchart and product_id 
        return $validate_id;
    }
}
