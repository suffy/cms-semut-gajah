<?php

namespace App\Http\Controllers\Api;

use App\CreditLimit;
use App\Http\Controllers\Controller;
use App\Log;
use App\MappingSite;
use App\Order;
use App\OrderDetail;
use App\PointHistory;
use App\Product;
use App\ProductStrata;
use App\ShoppingCart;
use App\User;
use App\UserAddress;
use App\Voucher;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    protected $orders, $orderDetail, $users, $userAddress, $logs, $shoppingCarts, $vouchers, $mappingSites, $creditLimit, $orderPromo, $orderPromoReward, $products, $productStrata;

    public function __construct(Order $order, OrderDetail $orderDetail, User $user, UserAddress $userAddress, Log $log, ShoppingCart $shoppingCart, Voucher $voucher, MappingSite $mappingSite, CreditLimit $creditLimit, PointHistory $pointHistory, Product $product, ProductStrata $productStrata)
    {
        $this->orders = $order;
        $this->orderDetail = $orderDetail;
        $this->users = $user;
        $this->userAddress = $userAddress;
        $this->logs = $log;
        $this->shoppingCarts = $shoppingCart;
        $this->vouchers = $voucher;
        $this->mappingSites = $mappingSite;
        $this->creditLimit = $creditLimit;
        $this->pointHistory = $pointHistory;
        $this->products = $product;
        $this->productStrata      = $productStrata;
    }

    // array for select product
    private function arraySelectOrder()
    {
        return ['id', 'invoice', 'customer_id', 'subscribe_id', 'name', 'phone', 'app_version', 'address', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'payment_method', 'payment_total', 'notes', 'status', 'status_faktur', 'site_code', 'complaint_id', 'review_at', 'point', 'created_at', 'updated_at', 'deleted_at', 'delivery_service'];
    }

    // array for select product
    private function arraySelectOrderDetail()
    {
        return ['id', 'product_id', 'order_id', 'konversi_sedang_ke_kecil', 'qty_konversi', 'qty', 'price_apps', 'total_price', 'notes', 'created_at', 'updated_at', 'deleted_at', 'product_review_id', 'promo_id', 'disc_cabang', 'rp_cabang', 'disc_principal', 'rp_principal', 'point_principal', 'bonus', 'bonus_name', 'bonus_qty', 'bonus_konversi', 'point'];
    }

    // array for select product
    private function arraySelectProduct()
    {
        return ['id', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    // array for select product
    private function arraySelectProductOld()
    {
        return ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    }

    public function get()
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        // check user login
        $id = Auth::user()->id;
        $arrayOrder = $this->arraySelectOrder();
        $arrayOrderDetail = $this->arraySelectOrderDetail();
        $arrayProduct = $this->arraySelectProduct();

        try {
            $orders = $this->orders
                ->select($arrayOrder)
                ->with(['data_item' => function ($query) use ($arrayOrderDetail, $arrayProduct) {
                    $query->select($arrayOrderDetail);
                    $query->with(['product' => function ($q) use ($arrayProduct) {
                        $q->select($arrayProduct);
                    }]);
                }])
                ->orderBy('created_at', 'desc')
                ->where('customer_id', $id)
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get orders successfully',
                'data' => $orders,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get orders failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if ($cekAuth = $this->checkAuth()) {
            return $cekAuth;
        }

        // check user login
        $id = Auth::user()->id;
        // $user_code  = $this->users->find($id);
        // get information user for order
        $user = $this->userAddress
            ->join('users', 'users.id', '=', 'user_address.user_id')
            ->where('users.id', $id)
            ->where('user_address.default_address', '1')
            ->first();
        $array = [];
        $app_version = Auth::user()->app_version;
        if ($app_version == '1.1.1') {
            $arrayProduct = $this->arraySelectProductOld();
        } else {
            $arrayProduct = $this->arraySelectProduct();
        }

        DB::beginTransaction();

        try {
            $requests = $request->json()->all();

            // get site code
            $siteCode = $this->mappingSites->find($user->mapping_site_id);

            $salurCode = $user->salur_code;
            $class = $user->class;
            // save platform

            // if($app_version =! '1.1.1' || $app_version =! '1.1.2' || $app_version =! '1.1.3') {

            if (isset($request['data']['platform'])) {
                $platform = $request['data']['platform'];
            } else {
                $platform = 'app';
            }
            // }

            // module checkshoppingcart
            // start module to check shoppingCart
            $arrayProductId = array_column($requests['products'], 'product_id');
            $arrayShoppingId = array_column($requests['products'], 'id');
            $carts = $this->shoppingCarts->whereIn('product_id', $arrayProductId)->where('user_id', $id)->get();

            if (count($carts) == 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Create order failed, data from cart is null',
                    'data' => null,
                ], 400);
            }

            $strataDisc = $this->productStrata->pluck('product_id');
            $statusClass = $this->shoppingCarts->whereIn('id', $arrayShoppingId)
                ->whereIn('product_id', $strataDisc)
                ->where('status_class', 1)
                ->get();

            if ($statusClass->IsNotEmpty()) {
                foreach ($statusClass as $value) {
                    $shoppingCart = $this->shoppingCarts->find($value->id);
                    $shoppingCart->status_class     = null;
                    $shoppingCart->save();
                }
            }

            $priceFinal = $totalprice = $carts->sum('total_price');
            // end

            // get voucher info
            $voucher = null;
            if ($request['data']['voucher_id'] != '') {
                $voucher = $this->vouchers->findOrFail($requests['data']['voucher_id']);
                if ($voucher->status && $voucher->min_transaction <= $priceFinal) {
                    if ($voucher->percent) {
                        $priceFinal = $priceFinal - ($voucher->percent / 100 * $priceFinal);
                    }

                    if ($voucher->nominal) {
                        $priceFinal = $priceFinal - $voucher->nominal;
                    }
                }
            }

            // finalPrice - harga promo
            if (!is_null($requests['order_promo'])) {
                foreach ($requests['order_promo'] as $prm) {
                    $rp_xtra = isset($prm['promo_reward']['nominal']) ? $prm['promo_reward']['nominal'] : 0;
                    $priceFinal = $priceFinal - $rp_xtra;
                }
            }
            // if (isset($requests['data']['payment_point']) && $requests['data']['payment_point']) {
            //     $priceFinal = $priceFinal - $requests['data']['payment_point'];
            // }

            // insert into orders table
            if ($request['data']['voucher_id'] != '') {
                $orders = $this->orders
                    ->create([
                        'invoice' => $user->customer_code . strtotime(Carbon::now()),
                        'customer_id' => $id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'app_version' => $user->app_version,
                        'provinsi' => $user->provinsi,
                        'kota' => $user->kota,
                        'kecamatan' => $user->kecamatan,
                        'kelurahan' => $user->kelurahan,
                        'kode_pos' => $user->kode_pos,
                        'address' => $user->address,
                        'site_code' => $user->site_code,
                        'payment_method' => $requests['data']['payment_method'],
                        'payment_total' => round($priceTotal),
                        // 'payment_total' => round($requests['data']['payment_total']),
                        'payment_discount' => $requests['data']['payment_discount'],
                        'payment_final' => round($priceFinal),
                        // 'payment_final' => round($requests['data']['payment_final']),
                        'coupon_id' => $requests['data']['voucher_id'],
                        'payment_discount_code' => $voucher->code,
                        'order_time' => Carbon::now()->format('Y-m-d H:i:s'),
                        'status' => '1',
                        'status_faktur' => 'F',
                        'delivery_service' => $requests['data']['delivery_service'],
                    ]);

                // insert order into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders', [
                    'X-API-KEY' => config('erp.x_api_key'),
                    'token' => config('erp.token_api'),
                    'order_id' => $orders->id,
                    'invoice' => $orders->invoice,
                    'customer_id' => $user->customer_code,
                    'subscribe_id' => null,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'location' => null,
                    'id_provinsi' => null,
                    'provinsi' => $user->provinsi,
                    'id_kota' => null,
                    'kota' => $user->kota,
                    'id_kelurahan' => null,
                    'kelurahan' => $user->kelurahan,
                    'id_kecamatan' => null,
                    'kecamatan' => $user->kecamatan,
                    'kode_pos' => $user->kode_pos,
                    'latitude' => null,
                    'longitude' => null,
                    'payment_method' => $requests['data']['payment_method'],
                    'payment_link' => null,
                    'payment_date' => null,
                    'payment_total' => round($priceTotal),
                    // 'payment_total' => round($requests['data']['payment_total']),
                    'coupon_id' => $requests['data']['voucher_id'],
                    'payment_discount_code' => $voucher->code,
                    'payment_discount' => $requests['data']['payment_discount'],
                    'payment_code' => null,
                    'order_weight' => null,
                    'order_distance' => null,
                    'delivery_status' => null,
                    'delivery_fee' => null,
                    'delivery_track' => null,
                    'delivery_time' => null,
                    'delivery_date' => null,
                    'order_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'confirmation_time' => null,
                    'notes' => null,
                    'status' => '1',
                    'status_faktur' => 'F',
                    'site_code' => $siteCode->kode,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'deleted_at' => null,
                    'payment_final' => round($priceFinal),
                    // 'payment_final' => round($requests['data']['payment_final']),
                    'photo' => null,
                    'courier' => null,
                    'delivery_service' => $requests['data']['delivery_service'],
                    'status_update_erp' => null,
                    'server' => config('server.server'),
                ]);
            } else {
                // $payment_final = round($requests['data']['payment_final']);

                // check jika ada payment_point
                if (isset($requests['data']['payment_point']) && $requests['data']['payment_point']) {

                    // check point lebih besar dari user
                    if ($user->point < $requests['data']['payment_point']) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'message' => 'Create order failed, less point than user',
                            'data' => null,
                        ], 400);
                    }
                    // end

                    $userP = $this->users->find($id);
                    $userP->point -= $requests['data']['payment_point'];
                    $userP->save();
                    // $payment_final = round($requests['data']['payment_final']) - $requests['data']['payment_point'];
                    $priceFinal = round($priceFinal) - $requests['data']['payment_point'];
                }

                $orders = $this->orders
                    ->create([
                        'invoice' => $user->customer_code . strtotime(Carbon::now()),
                        'customer_id' => $id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'platform' => $platform,
                        'app_version' => $user->app_version,
                        'provinsi' => $user->provinsi,
                        'kota' => $user->kota,
                        'kecamatan' => $user->kecamatan,
                        'kelurahan' => $user->kelurahan,
                        'kode_pos' => $user->kode_pos,
                        'address' => $user->address,
                        'site_code' => $user->site_code,
                        'payment_method' => $requests['data']['payment_method'],
                        // 'payment_total' => round($requests['data']['payment_total']),
                        'payment_total' => round($totalprice),
                        'payment_discount' => $requests['data']['payment_discount'],
                        // 'payment_final' => $payment_final,
                        'payment_final' => $priceFinal,
                        'payment_point' => isset($requests['data']['payment_point']) ? round($requests['data']['payment_point']) : null,
                        'coupon_id' => $requests['data']['voucher_id'],
                        'payment_discount_code' => null,
                        'order_time' => Carbon::now()->format('Y-m-d H:i:s'),
                        'status' => '1',
                        'status_faktur' => 'F',
                        'delivery_service' => $requests['data']['delivery_service'],
                    ]);

                $array = [
                    'invoice' => $orders->invoice,
                    'id' => $orders->id,
                ];

                if (isset($requests['data']['payment_point']) && $requests['data']['payment_point']) {
                    $pointHistory = $this->pointHistory
                        ->create([
                            'customer_id' => $user->id,
                            'order_id' => $orders->id,
                            'kredit' => $requests['data']['payment_point'],
                            'status' => 'point dari order invoice ' . $orders->invoice,
                        ]);

                    $logs = $this->logs
                        ->create([
                            'log_time' => Carbon::now(),
                            'activity' => 'successfully spent point -' . $requests['data']['payment_point'],
                            'table_id' => $orders->id,
                            'data_content' => $pointHistory,
                            'table_name' => 'users, point_histories',
                            'column_name' => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                            'from_user' => $user->id,
                            'to_user' => null,
                            'platform' => 'apps',
                        ]);
                }

                // insert order into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders', [
                    'X-API-KEY' => config('erp.x_api_key'),
                    'token' => config('erp.token_api'),
                    'order_id' => $orders->id,
                    'invoice' => $orders->invoice,
                    'customer_id' => $user->customer_code,
                    'subscribe_id' => null,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'location' => null,
                    'id_provinsi' => null,
                    'provinsi' => $user->provinsi,
                    'id_kota' => null,
                    'kota' => $user->kota,
                    'id_kelurahan' => null,
                    'kelurahan' => $user->kelurahan,
                    'id_kecamatan' => null,
                    'kecamatan' => $user->kecamatan,
                    'kode_pos' => $user->kode_pos,
                    'latitude' => null,
                    'longitude' => null,
                    'payment_method' => $requests['data']['payment_method'],
                    'payment_link' => null,
                    'payment_date' => null,
                    // 'payment_total' => round($requests['data']['payment_total']),
                    'payment_total' => round($totalprice),
                    'coupon_id' => $requests['data']['voucher_id'],
                    'payment_discount_code' => null,
                    'payment_discount' => $requests['data']['payment_discount'],
                    'payment_code' => null,
                    'order_weight' => null,
                    'order_distance' => null,
                    'delivery_status' => null,
                    'delivery_fee' => null,
                    'delivery_track' => null,
                    'delivery_time' => null,
                    'delivery_date' => null,
                    'order_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'confirmation_time' => null,
                    'notes' => null,
                    'status' => '1',
                    'status_faktur' => 'F',
                    'site_code' => $siteCode->kode,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'deleted_at' => null,
                    // 'payment_final' => $payment_final,
                    'payment_final' => $priceFinal,
                    'payment_point' => isset($requests['data']['payment_point']) ? round($requests['data']['payment_point']) : null,
                    'photo' => null,
                    'courier' => null,
                    'delivery_service' => $requests['data']['delivery_service'],
                    'status_update_erp' => null,
                    'server' => config('server.server'),
                ]);
            }

            // array payment_total
            // $paymentTotals = [];
            foreach ($requests['products'] as $request) {
                // check credit limit
                $creditLimits = $this->creditLimit
                    ->where('customer_code', $user->customer_code)
                    ->where('brand_id', $request['brand_id'])
                    ->get();

                // soft-delete
                $this->shoppingCarts
                    ->where('user_id', $id)
                    ->where('product_id', $request['product_id'])
                    ->delete();

                $data_product = $this->products
                    ->where('id', $request['product_id'])
                    ->with('price')
                    ->first();

                $promo_id = isset($request['promo_id']) ? $request['promo_id'] : null;

                $strataDisc = $this->productStrata->where('product_id', $request['product_id'])->first();

                // Disc by strata
                $disc_cabang = $request['disc_cabang'];
                $kecil = $request['kecil'];
                $half = $request['half'];

                // disc by Class
                if (!$strataDisc) {
                    // for version 1.1.4
                    if ($app_version != '1.1.4') {
                        $kecil = null;
                        $half = null;
                        if ($salurCode == 'WS' || $salurCode == 'SO' || $salurCode == 'SW') {
                            if ($class == 'GROSIR' || $class == 'STAR OUTLET') {
                                if ($data_product->status_herbana == null || $data_product->status_herbana == 0) {
                                    if ($request['brand_id'] == '005') {
                                        $disc_cabang = 0;
                                    } else if ($request['brand_id'] == '001') {
                                        $disc_cabang = 4.5;
                                    } else if ($request['brand_id'] == '002' || $request['brand_id'] == '004' || $request['brand_id'] == '012' || $request['brand_id'] == '013' || $request['brand_id'] == '014') {
                                        $disc_cabang = 0;
                                    } else {
                                        $disc_cabang = 0;
                                    }
                                } else {
                                    $disc_cabang = 0;
                                }
                            } elseif ($class == 'SEMI GROSIR') {
                                if ($data_product->status_herbana == null || $data_product->status_herbana == 0) {
                                    if ($request['brand_id'] == '005') {
                                        $disc_cabang = 0;
                                    } else if ($request['brand_id'] == '001') {
                                        $disc_cabang = 3;
                                    } else if ($request['brand_id'] == '002' || $request['brand_id'] == '004' || $request['brand_id'] == '012' || $request['brand_id'] == '013' || $request['brand_id'] == '014') {
                                        $disc_cabang = 0;
                                    } else {
                                        $disc_cabang = 0;
                                    }
                                } else {
                                    $disc_cabang = 0;
                                }
                            } else {
                                $disc_cabang = 0;
                            }
                        } elseif ($salurCode == 'RT') {
                            if ($class == 'RITEL') {
                                $disc_cabang = 0;
                            } else {
                                $disc_cabang = 0;
                            }
                        } else {
                            $disc_cabang = 0;
                        }
                    } else {
                        $disc_cabang = $request['disc_cabang'];
                        $kecil = $request['kecil'];
                        $half = $request['half'];
                    }
                }

                // $qtyKonversi = $data_product->konversi_sedang_ke_kecil * $request['qty'];
                $qtyKonversi = $request['qty_konversi'];
                $konversi_sedang_ke_kecil = $request['qty_konversi'] / $request['qty'];
                $harga_product_konversi = 0;
                if ($konversi_sedang_ke_kecil != 0) {
                    $harga_product_konversi = $data_product->price->harga_ritel_gt / $konversi_sedang_ke_kecil;
                }

                if ($disc_cabang > 0) {
                    // $rp_principal = ($disc_cabang/100) * $request['total_price'];
                    $price = $request['price_apps'] * $request['qty'];
                    $rp_cabang = ($disc_cabang / 100) * $price;
                } else {
                    $rp_cabang = 0;
                }

                //start count promo
                $point = round($request['total_price'] * $data_product->ratio, 1);
                //end

                // insert into order_detail table
                $orderDetail = $this->orderDetail
                    ->create([
                        'product_id' => $request['product_id'],
                        'order_id' => $orders->id,
                        'konversi_sedang_ke_kecil' => $request['konversi_sedang_ke_kecil'],
                        'qty_konversi' => $qtyKonversi,
                        'half' => $half,
                        'large_unit' => isset($request['large_unit']) ? $request['large_unit'] : null,
                        'medium_unit' => isset($request['medium_unit']) ? $request['medium_unit'] : null,
                        // 'small_unit'                => isset($request['small_unit']) ? $request['small_unit'] : null,
                        'small_unit' => $kecil,
                        'qty' => $request['qty'],
                        'point' => $point,
                        'notes' => $request['notes'],
                        'price_apps' => $request['price_apps'],
                        'total_price' => $request['total_price'],
                        'disc_cabang' => $disc_cabang,
                        'rp_cabang' => $rp_cabang,
                        'promo_id' => $promo_id,
                    ]);

                // insert order into erp
                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders_detail', [
                    'X-API-KEY' => config('erp.x_api_key'),
                    'token' => config('erp.token_api'),
                    // 'product_id'        => $request['product_id'],
                    'product_id' => $data_product->kodeprod, // update 21-09-21
                    'harga_product' => $data_product->price->harga_ritel_gt,
                    // 'harga_product_konversi'    => ($data_product->price->harga_ritel_gt / $data_product->konversi_sedang_ke_kecil),
                    'harga_product_konversi' => $harga_product_konversi,
                    'order_id' => $orders->id,
                    'invoice' => $orders->invoice,
                    'large_price' => null,
                    'large_qty' => null,
                    'large_unit' => isset($request['large_unit']) ? $request['large_unit'] : null,
                    'medium_price' => null,
                    'medium_qty' => null,
                    'medium_unit' => isset($request['medium_unit']) ? $request['medium_unit'] : null,
                    'small_price' => null,
                    'small_qty' => $request['qty'],
                    'small_unit' => isset($request['small_unit']) ? $request['small_unit'] : null,
                    // 'harga_product'     => $request['price_apps'], // update 21 - 09 -21
                    // 'qty_konversi'      => $request['qty_konversi'], // update 21 - 09 - 21
                    'qty_konversi' => $qtyKonversi,
                    'item_disc' => $request['item_discount'], // update 08 - 10 - 21
                    'total_price' => $request['total_price'],
                    'notes' => $request['notes'],
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'deleted_at' => null,
                    'product_review_id' => null,
                    'location_id' => null,
                    'description' => null,
                    'status' => null,
                    'status_update_erp' => null,
                    'last_updated_erp' => null,
                    'disc_cabang' => $disc_cabang,
                    'rp_cabang' => $rp_cabang,
                    // 'disc_principal'            => $disc_principal,
                    // 'rp_principal'              => $rp_principal
                ]);

                // push total_price for payment_total
                // array_push($paymentTotals, $totalPrice);
            }

            if (!is_null($requests['order_promo'])) {

                foreach ($requests['order_promo'] as $request) {
                    // return response()->json($request);

                    // foreach($request['promo_reward'] as $promo) {

                    $disc_xtra = isset($request['promo_reward']['disc']) ? $request['promo_reward']['disc'] : null;
                    $rp_xtra = isset($request['promo_reward']['nominal']) ? $request['promo_reward']['nominal'] : null;
                    $point_xtra = isset($request['promo_reward']['point']) ? $request['promo_reward']['point'] : null;

                    // $bonus          =   isset($promo['product']['promo_product_id']) ? $promo['product']['promo_product_id'] : null ;
                    // $bonus_konversi =   isset($promo['product']['promo_product_id']) ? (int)$promo['product']['promo_product_qty'] * (int)$promo['product']['promo_product_konversi'] : null;
                    if (!is_null($request['promo_reward'])) {
                        if (count($request['promo_reward']['product']) >= 1) {
                            foreach ($request['promo_reward']['product'] as $product) {
                                $data_product = $this->products
                                    ->where('id', $product['product_id'])
                                    ->first();

                                if (!is_null($data_product)) {
                                    $kode_prod = $data_product->kodeprod;
                                } else {
                                    $kode_prod = null;
                                }
                                // return response()->json($data_product);

                                $orderDetail = $this->orderDetail
                                    ->create([
                                        'product_id' => null,
                                        'order_id' => $orders->id,
                                        'konversi_sedang_ke_kecil' => null,
                                        'qty_konversi' => null,
                                        'large_unit' => null,
                                        'medium_unit' => null,
                                        'small_unit' => null,
                                        'qty' => null,
                                        'notes' => null,
                                        'price_apps' => null,
                                        'total_price' => null,
                                        'promo_id' => $request['promo_id'],
                                        'disc_cabang' => null,
                                        'disc_principal' => (float) $disc_xtra,
                                        'rp_principal' => (int) $rp_xtra,
                                        'point_principal' => (int) $point_xtra,
                                        'bonus' => $product['product_id'],
                                        'bonus_name' => $product['product_name'],
                                        'bonus_qty' => (int) $product['qty'],
                                        'bonus_konversi' => (int) $product['qty'] * (int) $product['konversi_satuan'],
                                    ]);

                                Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders_detail', [
                                    'X-API-KEY' => config('erp.x_api_key'),
                                    'token' => config('erp.token_api'),
                                    // 'product_id'        => $request['product_id'],
                                    'product_id' => $kode_prod, // get kode product by promo reward
                                    'order_id' => $orders->id,
                                    'invoice' => $orders->invoice,
                                    'large_price' => null,
                                    'large_qty' => null,
                                    'large_unit' => null,
                                    'medium_price' => null,
                                    'medium_qty' => null,
                                    'medium_unit' => null,
                                    'small_price' => null,
                                    'small_qty' => null,
                                    'small_unit' => null,
                                    'harga_product' => null,
                                    'qty_konversi' => null,
                                    'item_disc' => null,
                                    'total_price' => null,
                                    'notes' => null,
                                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                    'deleted_at' => null,
                                    'product_review_id' => null,
                                    'location_id' => null,
                                    'description' => null,
                                    'status' => null,
                                    'status_update_erp' => null,
                                    'last_updated_erp' => null,
                                    'disc_cabang' => null,
                                    'disc_principal' => (float) $disc_xtra,
                                    'rp_principal' => (int) $rp_xtra,
                                    // 'disc_principal'    => null,
                                    // 'disc_extra'         => (float)$disc_xtra,
                                    // 'rp_extra'           => (int)$rp_xtra,
                                    'bonus' => null,
                                    'bonus_konversi' => (int) $product['qty'] * (int) $product['konversi_satuan'],
                                ]);
                            }
                        } else {
                            $orderDetail = $this->orderDetail
                                ->create([
                                    'product_id' => null,
                                    'order_id' => $orders->id,
                                    'konversi_sedang_ke_kecil' => null,
                                    'qty_konversi' => null,
                                    'large_unit' => null,
                                    'medium_unit' => null,
                                    'small_unit' => null,
                                    'qty' => null,
                                    'notes' => null,
                                    'price_apps' => null,
                                    'total_price' => null,
                                    'promo_id' => $request['promo_id'],
                                    'disc_cabang' => null,
                                    'disc_principal' => (float) $disc_xtra,
                                    'rp_principal' => (int) $rp_xtra,
                                    'point_principal' => (int) $point_xtra,
                                    'bonus' => null,
                                    'bonus_qty' => null,
                                    'bonus_konversi' => null,
                                ]);

                            Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders_detail', [
                                'X-API-KEY' => config('erp.x_api_key'),
                                'token' => config('erp.token_api'),
                                // 'product_id'        => $request['product_id'],
                                'product_id' => null,
                                'order_id' => $orders->id,
                                'invoice' => $orders->invoice,
                                'large_price' => null,
                                'large_qty' => null,
                                'large_unit' => null,
                                'medium_price' => null,
                                'medium_qty' => null,
                                'medium_unit' => null,
                                'small_price' => null,
                                'small_qty' => null,
                                'small_unit' => null,
                                'harga_product' => null,
                                'qty_konversi' => null,
                                'item_disc' => null,
                                'total_price' => null,
                                'notes' => null,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'deleted_at' => null,
                                'product_review_id' => null,
                                'location_id' => null,
                                'description' => null,
                                'status' => null,
                                'status_update_erp' => null,
                                'last_updated_erp' => null,
                                'disc_cabang' => null,
                                'disc_principal' => (float) $disc_xtra,
                                'rp_principal' => $rp_xtra,
                                // 'disc_principal'    => null,
                                // 'disc_extra'         => (float)$disc_xtra,
                                // 'rp_extra'           => $rp_xtra,
                                'bonus' => null,
                                'bonus_konversi' => null,
                            ]);
                        }
                    }
                    // }
                }
            }

            // update payment_total at orders table
            // $paymentTotal   = $this->orders
            //                 ->where('id', $orders->id)
            //                 ->update([
            //                     'payment_total' => array_sum($paymentTotals),
            //                     'payment_final' => array_sum($paymentTotals),
            //                 ]);

            // for response
            $orderResponse = $this->orders
                ->where('id', $orders->id)
                ->with(['data_item.product' => function ($query) use ($arrayProduct) {
                    $query->select($arrayProduct);
                }])
                ->first();
            DB::commit();
            Cache::flush();

            // // logs
            $logs = $this->logs
                ->create([
                    'log_time' => Carbon::now(),
                    'activity' => 'new order',
                    'table_id' => $orders->id,
                    'data_content' => $orderResponse,
                    'table_name' => 'orders, order_detail',
                    'column_name' => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders.delivery_service, order_detail.product_id, order_detail.order_id, order_detail.price_apps, order_detail.konversi_sedang_ke_kecil, order_detail.qty_konversi, order_detail.qty, order_detail.total_price',
                    'from_user' => auth()->user()->id,
                    'to_user' => null,
                    'platform' => 'apps',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Create order successfully',
                'data' => $orderResponse,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            if (count($array) > 0) {
                Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                    'X-API-KEY' => config('erp.x_api_key'),
                    'token' => config('erp.token_api'),
                    // 'invoice'               => $orders->invoice,
                    'invoice' => $array['invoice'],
                    'kode' => $user->site_code,
                    'status_update_erp' => '10',
                ]);

                // logs
                $logs = $this->logs
                    ->create([
                        'log_time' => Carbon::now(),
                        'activity' => 'new order failed with invoice ' . $array['invoice'],
                        'table_id' => $array['id'],
                        'data_content' => 'total_payment: ' . $priceFinal . ', user_id: ' . $user->id,
                        'table_name' => 'orders, order_detail',
                        'column_name' => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders.delivery_service, order_detail.product_id, order_detail.order_id, order_detail.price_apps, order_detail.konversi_sedang_ke_kecil, order_detail.qty_konversi, order_detail.qty, order_detail.total_price',
                        'from_user' => auth()->user()->id,
                        'to_user' => null,
                        'platform' => 'apps',
                    ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Create order failed',
                    'data' => $e->getMessage(),
                ], 500);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Create order failed',
                    'data' => $e->getMessage(),
                ], 500);
            }
        }
    }

    public function detail($id)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            $orders = $this->orders
                ->whereId($id)
                ->with('data_item.product')
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Get order details successfully',
                'data' => $orders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get order details fails!',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        // check user login
        $userId = Auth::user()->id;

        DB::beginTransaction();

        try {
            $requests = $request->json()->all();

            // get information user for order
            $user = $this->userAddress
                ->join('users', 'users.id', '=', 'user_address.user_id')
                ->where('users.id', $userId)
                ->where('user_address.default_address', '1')
                ->first();

            // get site code
            $siteCode = $this->mappingSites
                ->find($user->mapping_site_id);

            // get voucher info
            $voucher = null;
            if ($request['data']['voucher_id'] != '') {
                $voucher = $this->vouchers->findOrFail($requests['data']['voucher_id']);
            }

            // update into orders table
            if ($request['data']['voucher_id'] != '') {
                $orders = $this->orders->where('id', $request['data']['id'])
                    ->update([
                        'customer_id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'provinsi' => $user->provinsi,
                        'kota' => $user->kota,
                        'kecamatan' => $user->kecamatan,
                        'kelurahan' => $user->kelurahan,
                        'kode_pos' => $user->kode_pos,
                        'address' => $user->address,
                        'site_code' => $user->site_code,
                        'payment_method' => $requests['data']['payment_method'],
                        'payment_total' => $requests['data']['payment_total'],
                        'payment_final' => $requests['data']['payment_final'],
                        'coupon_id' => $request['data']['voucher_id'],
                        'payment_discount_code' => $voucher->code,
                        'status' => '1',
                        'delivery_service' => $requests['data']['delivery_service'],
                    ]);
            } else {
                $orders = $this->orders->where('id', $request['data']['id'])
                    ->update([
                        'customer_id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'provinsi' => $user->provinsi,
                        'kota' => $user->kota,
                        'kecamatan' => $user->kecamatan,
                        'kelurahan' => $user->kelurahan,
                        'kode_pos' => $user->kode_pos,
                        'address' => $user->address,
                        'site_code' => $user->site_code,
                        'payment_method' => $requests['data']['payment_method'],
                        'payment_total' => $requests['data']['payment_total'],
                        'payment_final' => $requests['data']['payment_final'],
                        'coupon_id' => $request['data']['voucher_id'],
                        'payment_discount_code' => null,
                        'status' => '1',
                        'delivery_service' => $requests['data']['delivery_service'],
                    ]);
            }

            // array payment_total
            // $paymentTotals = [];

            foreach ($requests['products'] as $request) {
                // total price
                // $totalPrice = $request['price'] * $request['qty'];

                // update into order_detail table
                $orderDetail = $this->orderDetail
                    ->where('order_id', $requests['data']['id'])
                    ->where('product_id', $request['product_id'])
                    ->update([
                        'product_id' => $request['product_id'],
                        'order_id' => $requests['data']['id'],
                        'konversi_sedang_ke_kecil' => $request['konversi_sedang_ke_kecil'],
                        'qty_konversi' => $request['qty_konversi'],
                        'large_unit' => isset($request['large_unit']) ? $request['large_unit'] : null,
                        'medium_unit' => isset($request['medium_unit']) ? $request['medium_unit'] : null,
                        'small_unit' => isset($request['small_unit']) ? $request['small_unit'] : null,
                        'qty' => $request['qty'],
                        'notes' => $request['notes'],
                        'price_apps' => $request['price_apps'],
                        'total_price' => $request['total_price'],
                    ]);

                // push total_price for payment_total
                // array_push($paymentTotals, $totalPrice);

                // for response
                $orderResponse = $this->orders
                    ->where('id', $requests['data']['id'])
                    ->with('data_item.product')
                    ->first();

                // logs
                $logs = $this->logs
                    ->create([
                        'log_time' => Carbon::now(),
                        'activity' => 'Update order with id : ' . $requests['data']['id'],
                        'table_id' => $requests['data']['id'],
                        'data_content' => $orderResponse,
                        'table_name' => 'orders, order_detail',
                        'column_name' => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders.delivery_service, order_detail.product_id, order_detail.order_id, order_detail.price, order_detail.price, order_detail.large_qty, order_detail.large_unit, order_detail.medium_qty, order_detail.medium_unit, order_detail.small_qty, order_detail.small_unit, order_detail.total_price',
                        'from_user' => auth()->user()->id,
                        'to_user' => null,
                        'platform' => 'apps',
                    ]);
            }

            // update payment_total at orders table
            //  $paymentTotal  = $this->orders
            //                 ->where('id', $requests['data']['id'])
            //                 ->update([
            //                     'payment_total' => array_sum($paymentTotals)
            //                 ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Update order successfully',
                'data' => $orderResponse,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Update order failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            foreach ($request->all() as $request) {
                // delete data at orders table
                $order = $this->orders->destroy($request['id']);

                // delete data at order_detail table
                $orderDetail = $this->orderDetail
                    ->where('order_id', $request['id'])
                    ->delete();

                // for response
                $orderResponse = $this->orders->where('id', $request['id'])->with('data_item.product')->get();

                // logs
                $logs = $this->logs
                    ->create([
                        'log_time' => Carbon::now(),
                        'activity' => "delete order with id : " . $request['id'],
                        'table_id' => $request['id'],
                        'table_name' => 'orders, order_detail',
                        'data_content' => $orderResponse,
                        'from_user' => auth()->user()->id,
                        'to_user' => null,
                        'platform' => 'apps',
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Delete order successfully',
                'data' => $orderResponse,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete order failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            // update status at orders table
            $user = $this->users->find(auth()->user()->id);
            $order = $this->orders->find($id);

            if ($order->status != 10) {
                if ($order->payment_point > 0) {
                    $user->point += $order->payment_point;
                } else {
                    $user->point += 0;
                }
                $user->save();
            }

            $order->status = '10';
            $order->notes = $request->notes;
            $order->save();

            $pointHistory = $this->pointHistory
                ->create([
                    'customer_id' => $user->id,
                    'order_id' => $id,
                    'deposit' => $order->payment_point,
                    'status' => 'point dari cancel order invoice ' . $order->invoice,
                ]);

            // for datacontent
            $dataContent = $this->orders
                ->where('id', $id)
                ->with('data_item.product')
                ->first();

            // logs
            $logs = $this->logs
                ->create([
                    'log_time' => Carbon::now(),
                    'activity' => "order cancel",
                    'table_id' => $id,
                    'table_name' => 'orders, order_detail',
                    'data_content' => $dataContent,
                    'from_user' => auth()->user()->id,
                    'to_user' => null,
                    'platform' => 'apps',
                ]);

            // update data to erp
            Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token' => config('erp.token_api'),
                'invoice' => $order->invoice,
                'kode' => $order->data_user->site_code,
                'status_update_erp' => '10',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cancel order successfully',
                'data' => $order,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancel order failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function complete($id)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            $user = $this->users->find(auth()->user()->id);

            // for datacontent
            $dataContent = $this->orders->where('id', $id)->with(['data_item.product', 'data_user'])->first();

            // start count point
            $array_point = [];
            $point = 0;
            foreach ($dataContent->data_item as $row) {
                if ($row->point || $row->point_principal) {
                    if ($row->point_principal) {;
                        array_push($array_point, $row->point_principal);
                    } else {
                        array_push($array_point, $row->point);
                    }
                }
            }

            if (count($array_point) > 0) {
                $point = array_sum($array_point);
            }
            //end

            // update status at orders table
            $order = $this->orders->find($id);
            $order->status = '4';
            $order->point = $point;
            $order->complete_time = Carbon::now();
            $order->save();
            // end

            // update point at users table
            if ($point > 0) {
                if ($user->point) {
                    $user->point += $point;
                } else {
                    $user->point = $point;
                }

                $user->save();

                $pointHistory = $this->pointHistory
                    ->create([
                        'customer_id' => $user->id,
                        'order_id' => $id,
                        'deposit' => $point,
                        'status' => 'point dari order invoice ' . $order->invoice,
                    ]);

                $logs = $this->logs
                    ->create([
                        'log_time' => Carbon::now(),
                        'activity' => 'successfully sent point',
                        'table_id' => $order->id,
                        'data_content' => $pointHistory,
                        'table_name' => 'users, point_histories',
                        'column_name' => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                        'from_user' => null,
                        'to_user' => $user->id,
                        'platform' => 'apps',
                    ]);
            }
            // end

            // logs
            $logs = $this->logs
                ->create([
                    'log_time' => Carbon::now(),
                    'activity' => "order completed manually",
                    'table_id' => $id,
                    'table_name' => 'orders, order_detail',
                    'data_content' => $dataContent,
                    'from_user' => auth()->user()->id,
                    'to_user' => null,
                    'platform' => 'apps',
                ]);

            // // update data to erp
            Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token' => config('erp.token_api'),
                'invoice' => $order->invoice,
                'kode' => $order->data_user->site_code,
                'total_point' => $point,
                'status_update_erp' => '4',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Completed order successfully',
                'data' => $order,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Completed order failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function notification(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            $notifications = $this->logs->query();

            // check user login
            $id = Auth::user()->id;

            // get new order notifications
            if ($request->status == "1") {
                $notifications = $notifications
                    ->where('activity', 'new order')
                    ->where('table_name', 'orders, order_detail')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get order confirm notifications
            if ($request->status == "2") {
                $notifications = $notifications
                    ->where('activity', 'order confirm')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get delivery process notifications
            if ($request->status == "3") {
                $notifications = $notifications
                    ->where('activity', 'order process')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get order complete notifications
            if ($request->status == "4") {
                $notifications = $notifications
                    ->where('activity', 'order completed')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get order cancel notifications
            if ($request->status == "10") {
                $notifications = $notifications
                    ->where('activity', 'order cancel')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get cod h-2 notifications
            if ($request->status == "cod h-2") {
                $notifications = $notifications
                    ->where('activity', 'cod h-2')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get cod h-1 notifications
            if ($request->status == "cod h-1") {
                $notifications = $notifications
                    ->where('activity', 'cod h-1')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    });

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get cod h notifications
            if ($request->status == "cod h") {
                $notifications = $notifications
                    ->where('activity', 'cod h')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get seen order notifications
            if ($request->seen == "true") {
                $notifications = $notifications
                    ->where('activity', 'order complete')
                    ->where('table_name', 'orders')
                    ->where('user_seen', '1')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get unseen order notifications
            if ($request->unseen == "true") {
                $notifications = $notifications
                    ->where('activity', 'order complete')
                    ->where('table_name', 'orders')
                    ->where('user_seen', null)
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // review reminder notifications
            if ($request->status == "4" && $request->reminder == "true") {
                $notifications = $notifications
                    ->where('activity', 'order completed')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // complete order reminder notifications
            if ($request->type == "order complete" && $request->reminder == "true") {
                $notifications = $notifications
                    ->where('activity', 'reminder complete order')
                    ->where('table_name', 'orders')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC');

                // counting total
                $total = $notifications
                    ->where('user_seen', null)
                    ->count();
            }

            // get all order notifications
            if ($request->status || $request->seen) {
                $notifications = $notifications->paginate(10);
            } else {
                $notifications = $notifications
                    ->select('logs.*')
                    // ->leftJoin('order_detail', function($join)
                    // {
                    //     $join->on('logs.table_id', '=', 'order_detail.order_id');
                    // })

                    ->where('activity', 'not like', '%Delete order with id%')
                    ->where('activity', 'not like', '%new order failed%')
                    ->where('activity', 'not like', '%update status order with%')
                    ->where('activity', 'not like', '%order with invoice%')
                    ->where('activity', 'not like', '%successfully ordered complaint with id%')
                    ->where('table_name', 'like', '%orders%')
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->orderBy('log_time', 'DESC')
                    ->paginate(10);

                // counting total
                $total = $this->logs
                    ->where('activity', 'not like', '%Delete order with id%')
                    ->where('activity', 'not like', '%new order failed%')
                    ->where('activity', 'not like', '%update status order with%')
                    ->where('activity', 'not like', '%order with invoice%')
                    ->where('activity', 'not like', '%successfully ordered complaint with id%')
                    ->where('table_name', 'like', '%orders%')
                    ->where('user_seen', null)
                    ->where(function ($query) use ($id) {
                        $query->where('from_user', $id)
                            ->orWhere('to_user', $id);
                    })
                    ->count();
            }

            return response()->json([
                'success' => true,
                'message' => 'Get orders notification successfully',
                'total' => $total,
                'data' => $notifications,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get orders notification failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function seenNotification($id)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            $notification = $this->logs->find($id);

            $notification->user_seen = '1';

            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Seen order notification successfully',
                'data' => $notification,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Seen order notification failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function upload(Request $request, $id)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'mimes:jpeg,jpg,png,gif|required|max:1024',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data' => null,
            ], 400);
        }

        try {
            // upload payments file
            $order = $this->orders->find($id);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = $file->getClientOriginalExtension();

                $newName = "payment" . date('YmdHis') . "." . $ext;

                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/payment/' . $newName));
                $order->payment_link = '/images/payment/' . $newName;
            }

            $order->save();

            // logs
            $logs = $this->logs
                ->create([
                    'log_time' => Carbon::now(),
                    'activity' => 'Upload payment',
                    'data_content' => $order,
                    'table_name' => 'orders',
                    'column_name' => 'orders.payment_link',
                    'from_user' => auth()->user()->id,
                    'to_user' => null,
                    'platform' => 'apps',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Upload payments file successfully',
                'data' => $order,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload payments file failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus(Request $request)
    {
        try {
            if (!JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'data' => null,
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
                'data' => null,
            ], 400);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid',
                'data' => null,
            ], 400);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent',
                'data' => null,
            ], 400);
        }

        try {
            $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/orders', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token' => config('erp.token_api'),
                'invoice' => $request->invoice,
            ]);

            $order = $response['data'][0];

            $data = $this->orders
                ->where('invoice', $order['invoice'])
                ->first(); // get data order by invoice from erp

            if (!is_null($data)) { // if not null

                $activity = ""; // give activity desc

                if ($order['status_update_erp'] == '1') {
                    $activity = "new order";
                } else if ($order['status_update_erp'] == '2') {
                    $activity = "order confirm";
                } else if ($order['status_update_erp'] == '3') {
                    $activity = "order process";
                } else if ($order['status_update_erp'] == '4') {
                    $activity = "order completed";
                } else if ($order['status_update_erp'] == '10') {
                    $activity = "order cancel";
                }

                if ($data->updated_at != $order['last_updated_erp']) { // check if erp update
                    $dataUpdated = $data->update([
                        'delivery_status' => $order['delivery_status'],
                        'delivery_time' => $order['delivery_time'],
                        'status' => $order['status_update_erp'],
                        'updated_at' => $order['last_updated_erp'],
                    ]); // update status order

                    // if($data->status != "1" || $data->status != "10") {
                    if ($data->status == "2" || $data->status == "3" || $data->status == "4") {
                        if ($dataUpdated) {
                            $this->sendNotification($data->customer_id, $data->status); // call method sendnotification to send notif into user phone
                            // start entry point
                            if ($data->status == 4 && $data->point == null) {
                                $array_point = [];
                                $point = 0;
                                foreach ($data->data_item as $row) {
                                    if ($row->point || $row->point_principal) {
                                        if ($row->point_principal) {;
                                            array_push($array_point, $row->point_principal);
                                        } else {
                                            array_push($array_point, $row->point);
                                        }
                                    }
                                }

                                if (count($array_point) > 0) {
                                    $point = array_sum($array_point);
                                    $data->point = $point;
                                    $data->complete_time = Carbon::now();
                                    $data->save();

                                    $user = $this->users->find($data->customer_id);

                                    if ($user->point) {
                                        $user->point += $point;
                                    } else {
                                        $user->point = $point;
                                    }
                                    $user->save();

                                    $pointHistory = $this->pointHistory
                                        ->create([
                                            'customer_id' => $user->id,
                                            'order_id' => $data->id,
                                            'deposit' => $point,
                                            'status' => 'point dari order invoice ' . $data->invoice,
                                        ]);

                                    $logs = $this->logs
                                        ->create([
                                            'log_time' => Carbon::now(),
                                            'activity' => 'successfully sent point',
                                            'table_id' => $user->id,
                                            'data_content' => $pointHistory,
                                            'table_name' => 'users, point_histories',
                                            'column_name' => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                                            'from_user' => null,
                                            'to_user' => $user->id,
                                            'platform' => 'apps',
                                        ]);

                                    // update data to erp
                                    Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                                        'X-API-KEY' => config('erp.x_api_key'),
                                        'token' => config('erp.token_api'),
                                        'invoice' => $data->invoice,
                                        'kode' => $user->site_code,
                                        'total_point' => $point,
                                    ]);
                                }
                            }
                            //end
                        }
                    }

                    $data_log = $this->orders
                        ->where('invoice', $order['invoice'])
                        ->with('data_item.product')
                        ->first();

                    $this->logs->updateOrCreate( // insert to logs if using cron job table
                        ['table_id' => $data_log->id],
                        [
                            'log_time' => Carbon::now(),
                            'activity' => $activity . ' manually',
                            'table_name' => 'orders',
                            'column_name' => 'orders.id, orders.status, orders.updated_at',
                            'from_user' => auth()->user()->id,
                            'to_user' => null,
                            'data_content' => $data,
                            'platform' => 'web',
                            'created_at' => Carbon::now(),
                        ]
                    );

                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully update order status',
                        'data' => $data_log,
                    ], 200);
                } else {
                    $data_log = $this->orders
                        ->where('invoice', $order['invoice'])
                        ->with('data_item.product')
                        ->first(); // get data from server to input into logs

                    return response()->json([
                        'success' => false,
                        'message' => 'Status order not update yet!',
                        'data' => $data_log,
                    ], 200);
                    // $this->log->updateOrCreate(                                             // insert to logs if using cron job table
                    //     ['table_id'     => $data_log->id],
                    //     ['log_time'     => Carbon::now()]
                    // );
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check order status failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function minTransaction($site_code)
    {
        try {
            try { // check token
                if (!JWTAuth::parseToken()->authenticate()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found',
                        'data' => null,
                    ], 404);
                }
            } catch (TokenExpiredException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token expired',
                    'data' => null,
                ], 400);
            } catch (TokenInvalidException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalid',
                    'data' => null,
                ], 400);
            } catch (JWTException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token absent',
                    'data' => null,
                ], 400);
            }

            $min = $this->mappingSites->where('kode', $site_code)->select('kode', 'min_transaction')->get();
            return response()->json([
                'success' => true,
                'message' => 'Cek Minimum Transaction successfully',
                'data' => $min,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cek Minimum Transaction failed',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendNotification($user_id, $status)
    {
        $activity = "";
        // give status
        if ($status == '1') {
            $activity = 'Pesanan Baru';
        } else if ($status == '2') {
            $activity = 'Pesanan Anda Terkonfirmasi';
        } else if ($status == '3') {
            $activity = 'Pesanan Anda Sedang Terproses';
        } else if ($status == '4') {
            $activity = 'Pesanan Selesai';
        }

        $fcm_token = $this->users
            ->where('id', $user_id)
            ->pluck('fcm_token')
            ->all(); // get fcm_token from user table

        $SERVER_API_KEY = config('firebase.server_api_key'); // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => 'Status Orderan',
                "body" => $activity,
                "sound" => 'default',
            ],
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
    }
}
