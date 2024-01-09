<?php

namespace App\Console\Commands;

use App\Log;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\ProductPrice;
use App\ProductStrata;
use App\Subscribe;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

class DailySubscribe extends Command
{
    protected $subscribe, $productPrice, $order, $user, $orderDetail, $product, $logs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscribe:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running auto order for product subscribed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Subscribe $subscribe, ProductPrice $productPrice, Order $order, User $user, OrderDetail $orderDetail, Product $product, Log $log)
    {
        parent::__construct();
        $this->subscribe    = $subscribe;
        $this->productPrice = $productPrice;
        $this->order        = $order;
        $this->user         = $user;
        $this->orderDetail  = $orderDetail;
        $this->product      = $product;
        $this->logs         = $log;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // get subscribes data
        $subscribes = $this->subscribe->with('product.price')->get();

        foreach ($subscribes as $subscribe) {
            // get default address user
            $user = $this->user->find($subscribe->user_id);
            // per day
            if ($subscribe->time == 'day') {
                // handling price
                $cc = $this->handlingPrice($subscribe, $user, $subscribe->product);
            }

            // per week
            if ($subscribe->time == 'week') {
                if ($subscribe->created_at->format('d-m-Y') == Carbon::now()->subDays(7)->format('d-m-Y')) {
                    // handling price
                    $this->handlingPrice($subscribe, $user, $subscribe->product);
                }
            }

            // per 2 week
            if ($subscribe->time == '2_week') {
                $end_at = date('Y-m-d', strtotime('+14 day', strtotime($subscribe->start_at)));
                if ($subscribe->start_at == Carbon::now()->subDays(14)->format('Y-m-d')) {
                    // handling price
                    $this->handlingPrice($subscribe, $user, $subscribe->product);
                    // update start_at 
                    $subscribe->start_at = Carbon::now();
                    $subscribe->save();

                    $activity     = 'Pesanan langgananmu  ' . $subscribe->product->name . ' sudah diproses';
                    // send notif
                    $this->sendNotification($user->id, $activity);
                }
            }

            // per month
            if ($subscribe->time == 'month') {
                // if (Carbon::now()->format('d') == '1') {
                $end_at = date('Y-m-d', strtotime('+1 month', strtotime($subscribe->start_at)));
                if (Carbon::now()->format('Y-m-d') == date('Y-m-d', strtotime('+1 month', strtotime($subscribe->start_at)))) {
                    // handling price
                    $this->handlingPrice($subscribe, $user, $subscribe->product);
                    // update start_at 
                    $subscribe->start_at = Carbon::now();
                    $subscribe->save();

                    $activity     = 'Pesanan langgananmu  ' . $subscribe->product->name . ' sudah diproses';
                    // send notif
                    $this->sendNotification($user->id, $activity);
                }
            }

            // per year
            if ($subscribe->time == 'year') {
                if (Carbon::now()->format('d-m') == '01-01') {
                    // handling price
                    $this->handlingPrice($subscribe, $user, $subscribe->product);
                }
            }

            // h-2 notification
            if (Carbon::now()->format('Y-m-d') == date('Y-m-d', strtotime('-2 day', strtotime($end_at)))) {

                $status     = '2 hari lagi pesanan langgananmu ' . $subscribe->product->name . ' akan diproses';

                // send notif
                $this->sendNotification($user->id, $status);

                // log
                $logs   = $this->logs
                    ->create([
                        'log_time'      => Carbon::now(),
                        'activity'      => 'subscribe h-2',
                        'table_id'      => $subscribe->id,
                        'data_content'  => $subscribe,
                        'table_name'    => 'subscribes',
                        'column_name'   => 'subscribes.id, subscribes.user_id, subscribes.qty, subscribes.notes, subscribes.notes, subscribes.status, subscribes.created_at, subscribes.updated_at',
                        'from_user'     => null,
                        'to_user'       => $subscribe->user_id,
                        'platform'      => 'web',
                    ]);
            }

            // h-1 notification
            if (Carbon::now()->format('Y-m-d') == date('Y-m-d', strtotime('-1 day', strtotime($end_at)))) {

                $status     = '1 hari lagi pesanan langgananmu ' . $subscribe->product->name . ' akan diproses';
                $this->sendNotification($user->id, $status);

                // log
                $logs   = $this->logs
                    ->create([
                        'log_time'      => Carbon::now(),
                        'activity'      => 'subscribe h-1',
                        'table_id'      => $subscribe->id,
                        'data_content'  => $subscribe,
                        'table_name'    => 'subscribes',
                        'column_name'   => 'subscribes.id, subscribes.user_id, subscribes.qty, subscribes.notes, subscribes.notes, subscribes.status, subscribes.created_at, subscribes.updated_at',
                        'from_user'     => null,
                        'to_user'       => $subscribe->user_id,
                        'platform'      => 'web',
                    ]);
            }
        }

        $this->info('Successfully create auto order from subscribe');
    }

    public function sendNotification($user_id, $activity)
    {
        $fcm_token = $this->user->where('id', $user_id)->pluck('fcm_token')->all(); // get fcm_token from user table

        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => 'Status Langganan',
                "body" => $activity,
            ]
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

    // handling price
    public function handlingPrice($subscribe, $user, $product)
    {
        // get user login
        // $userLogin      = $this->user->find($subscribe->user_id);

        // get herbana status
        // $herbana        = $this->product
        //                         ->where('id', $subscribe->product_id)
        //                         ->first();

        // get product price
        // $productPrice   = $this->productPrice
        //                             ->where('product_id', $subscribe->product_id)
        //                             ->first();

        // ambil konversi sedang kecil
        $konversi = $product->konversi_sedang_ke_kecil;
        $half     = NULL;
        // jika kondisi half
        if ($subscribe->half == '1') {
            // jika product adad status_renceng
            if ($product->status_renceng) {
                // ambil setengah konversi
                $konversi = $konversi / 2;
                // status half
                $half     = 1;
            }
        }

        $discClass = ProductStrata::where('product_id', $product->id)->first();

        // disc by class
        if ($discClass) {
            $priceApps      = $product->price->harga_ritel_gt;
            $totalPrice     =  $product->price->harga_ritel_gt * $subscribe->qty;
            $finalPrice     =  $product->price->harga_ritel_gt * $subscribe->qty;
            $disc_cabang    = 0;

            // if min transaction true
            if (round($totalPrice) >= round($discClass->min_transaction)) {
                $priceApps      = $product->price->harga_ritel_gt;
                $totalPrice     = $product->price->harga_ritel_gt * $subscribe->qty; // total price before promo principal
                $finalPrice     = $totalPrice - ($totalPrice * ($discClass->disc_percent / 100)); // final price after promo principal
                $disc_cabang    = $discClass->disc_percent;
            }
        }

        // disc by strata
        if (!$discClass) {
            $priceApps      = 0;
            if ($product->status_promosi_coret) {
                $harga_ritel_gt         = $product->price->harga_ritel_gt * $subscribe->qty;
                $harga_grosir_mt        = $product->price->harga_grosir_mt * $subscribe->qty;
                // $harga_semi_grosir      = $product->price->harga_promosi_coret_semi_grosir * $request->qty;  
                if ($half == 1) {
                    $harga_ritel_gt  = $harga_ritel_gt / 2;
                    $harga_grosir_mt = $harga_grosir_mt / 2;
                }
            } else {
                $harga_ritel_gt         = $product->price->harga_ritel_gt * $subscribe->qty;
                $harga_grosir_mt        = $product->price->harga_grosir_mt * $subscribe->qty;
                // $harga_semi_grosir      = $product->price->harga_semi_grosir * $request->qty;  
                // check if halc
                if ($half == 1) {
                    $harga_ritel_gt  = $harga_ritel_gt / 2;
                    $harga_grosir_mt = $harga_grosir_mt / 2;
                }
            }

            // jika salud code ws atau so atau sw
            if ($user->salur_code == 'WS' || $user->salur_code == 'SO' || $user->salur_code == 'SW') {
                // jika class grosit atau star outlet
                if ($user->class == 'GROSIR' || $user->class == 'STAR OUTLET') {
                    // jika bukan status herbana
                    if ($product->status_herbana == null || $product->status_herbana == 0) {
                        if ($product->brand_id == '005') {
                            $priceApps      = $product->price->harga_ritel_gt;
                            $finalPrice     =  $harga_ritel_gt; // final price after promo principal
                            $totalPrice     =  $harga_ritel_gt; // total price before promo principal
                            $disc_cabang    = 0;
                        } else if ($product->brand_id == '001') {
                            $priceApps      = $product->price->harga_ritel_gt;
                            $finalPrice     = $harga_ritel_gt - $harga_ritel_gt * (4.5 / 100);
                            $totalPrice     =  $harga_ritel_gt;
                            $disc_cabang    = 4.5;
                        } else if ($product->brand_id == '002' || $product->brand_id == '004' || $product->brand_id == '012' || $product->brand_id == '013' || $product->brand_id == '014') {
                            $priceApps      = $product->price->harga_grosir_mt;
                            $finalPrice     = $harga_grosir_mt;
                            $totalPrice     = $harga_grosir_mt;
                            $disc_cabang    = 0;
                        } else {
                            $priceApps      = $product->price->harga_grosir_mt;
                            $finalPrice     = $harga_grosir_mt;
                            $totalPrice     = $harga_grosir_mt;
                            $disc_cabang    = 0;
                        }
                    } else {
                        $priceApps          = $product->price->harga_grosir_mt;
                        $finalPrice         = $harga_grosir_mt;
                        $totalPrice         = $harga_grosir_mt;
                        $disc_cabang        = 0;
                    }
                } elseif ($user->class == 'SEMI GROSIR') {
                    if ($product->status_herbana == null || $product->status_herbana == 0) {
                        if ($product->brand_id == '005') {
                            $priceApps      = $product->price->harga_ritel_gt;
                            $finalPrice     = $harga_ritel_gt;
                            $totalPrice     = $harga_ritel_gt;
                            $disc_cabang    = 0;
                        } else if ($product->brand_id == '001') {
                            $priceApps      = $product->price->harga_ritel_gt;
                            $finalPrice     = $harga_ritel_gt - $harga_ritel_gt * (3 / 100);
                            $totalPrice     = $harga_ritel_gt;
                            $disc_cabang    = 3;
                        } else if ($product->brand_id == '002' || $product->brand_id == '004' || $product->brand_id == '012' || $product->brand_id == '013' || $product->brand_id == '014') {
                            $priceApps      = $product->price->harga_grosir_mt;
                            $finalPrice     = $harga_grosir_mt;
                            $totalPrice     = $harga_grosir_mt;
                            $disc_cabang    = 0;
                        } else {
                            $priceApps      = $product->price->harga_grosir_mt;
                            $finalPrice     = $harga_grosir_mt;
                            $totalPrice     = $harga_grosir_mt;
                            $disc_cabang    = 0;
                        }
                    } else {
                        $priceApps      = $product->price->harga_grosir_mt;
                        $finalPrice     = $harga_grosir_mt;
                        $totalPrice     = $harga_grosir_mt;
                        $disc_cabang    = 0;
                    }
                } else {
                    $priceApps      = $product->price->harga_grosir_mt;
                    $finalPrice     = $harga_grosir_mt;
                    $totalPrice     = $harga_grosir_mt;
                    $disc_cabang    = 0;
                }
            } elseif ($user->salur_code == 'RT') {
                if ($user->class == 'RITEL') {
                    $priceApps      = $product->price->harga_ritel_gt;
                    $finalPrice     = $harga_ritel_gt;
                    $totalPrice     = $harga_ritel_gt;
                    $disc_cabang    = 0;
                } else {
                    $priceApps      = $product->price->harga_ritel_gt;
                    $finalPrice     = $harga_ritel_gt;
                    $totalPrice     = $harga_ritel_gt;
                    $disc_cabang    = 0;
                }
            } else {
                $priceApps      = $product->price->harga_ritel_gt;
                $finalPrice     = $harga_ritel_gt;
                $totalPrice     = $harga_ritel_gt;
                $disc_cabang    = 0;
            }
        }
        // insert to orders table
        $order = $this->order;

        // $order->invoice                 = $user->customer_code . strtotime($subscribe->created_at);
        $order->invoice                 = $user->customer_code . strtotime(Carbon::now());
        $order->customer_id             = $subscribe->user_id;
        $order->subscribe_id            = $subscribe->id;
        $order->name                    = $user->name;
        $order->phone                   = $user->phone;
        $order->address                 = $user->user_default_address[0]->address;
        $order->kelurahan               = $user->user_default_address[0]->kelurahan;
        $order->kecamatan               = $user->user_default_address[0]->kecamatan;
        $order->kota                    = $user->user_default_address[0]->kota;
        $order->provinsi                = $user->user_default_address[0]->provinsi;
        $order->kode_pos                = $user->user_default_address[0]->kode_pos;
        $order->latitude                = $user->user_default_address[0]->latitude;
        $order->longitude               = $user->user_default_address[0]->longitude;
        $order->payment_method          = 'cod';
        $order->payment_link            = null;
        $order->payment_date            = null;
        $order->payment_total           = $totalPrice;
        $order->coupon_id               = null;
        $order->payment_discount_code   = null;
        $order->payment_discount        = null;
        $order->payment_code            = null;
        $order->order_weight            = null;
        $order->order_distance          = null;
        $order->delivery_status         = null;
        $order->delivery_fee            = null;
        $order->delivery_track          = null;
        $order->delivery_time           = null;
        $order->delivery_date           = null;
        $order->order_time              = Carbon::now();
        $order->site_code               = $user->site_code;
        $order->confirmation_time       = null;
        $order->notes                   = $subscribe->notes;
        $order->status                  = '1';
        $order->status_faktur           = 'F';
        $order->created_at              = Carbon::now();
        $order->updated_at              = null;
        $order->deleted_at              = null;
        $order->payment_final           = $finalPrice;
        $order->photo                   = null;
        $order->courier                 = null;
        $order->delivery_service        = null;

        $order->save();

        // get orders id
        // $order = $this->order->where('subscribe_id', $subscribe->id)->first();

        $qty_konversi = $subscribe->qty * $konversi;
        // $item_disc    = $totalPrice - ($priceApps * $subscribe->disc);

        if ($disc_cabang > 0) {
            $rp_cabang = round(($disc_cabang / 100) * $totalPrice, 1);
        } else {
            $rp_cabang = 0;
        }

        // insert to order_detail table
        $orderDetail = $this->orderDetail;

        $orderDetail->product_id                = $subscribe->product_id;
        $orderDetail->order_id                  = $order->id;
        // $orderDetail->large_price               = null;
        // $orderDetail->medium_price              = null;
        // $orderDetail->small_price               = null;
        $orderDetail->konversi_sedang_ke_kecil  = $product->konversi_sedang_ke_kecil;
        $orderDetail->qty                       = $subscribe->qty;
        $orderDetail->qty_konversi              = $qty_konversi;
        $orderDetail->price_apps                = $priceApps;
        $orderDetail->total_price               = $finalPrice;
        $orderDetail->half                      = $half;
        $orderDetail->disc_cabang               = $disc_cabang;
        $orderDetail->rp_cabang                 = $rp_cabang;
        $orderDetail->small_unit                = $product->kecil;
        $orderDetail->created_at                = Carbon::now();
        $orderDetail->updated_at                = null;
        $orderDetail->deleted_at                = null;
        $orderDetail->location_id               = null;
        $orderDetail->description               = null;
        $orderDetail->status                    = null;

        $orderDetail->save();

        $orderResponse = $this->order->where('id', $order->id)->with('data_item.product')->first();

        $logs   = $this->logs
            ->create([
                'log_time'      => Carbon::now(),
                // 'activity'      => 'new order from subscribe id ' . $subscribe->id,
                'activity'      => 'new order from subscribe',
                'table_id'      => $order->id,
                'data_content'  => $orderResponse,
                'table_name'    => 'orders, order_detail',
                'column_name'   => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders.delivery_service, order_detail.product_id, order_detail.order_id, order_detail.price_apps, order_detail.konversi_sedang_ke_kecil, order_detail.qty_konversi, order_detail.qty, order_detail.total_price',
                'from_user'     => $subscribe->user_id,
                'to_user'       => null,
                'platform'      => 'apps',
            ]);

        // insert order into erp
        Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders', [
            'X-API-KEY'             => config('erp.x_api_key'),
            'token'                 => config('erp.token_api'),
            'invoice'               => $order->invoice,
            // 'customer_id'           => $subscribe->user_id,
            'customer_id'           => $user->customer_code,
            'subscribe_id'          => $subscribe->id,
            'name'                  => $user->name,
            'phone'                 => $user->phone,
            'address'               => $user->address,
            'location'              => null,
            'id_provinsi'           => null,
            'provinsi'              => $user->provinsi,
            'id_kota'               => null,
            'kota'                  => $user->kota,
            'id_kelurahan'          => null,
            'kelurahan'             => $user->kelurahan,
            'id_kecamatan'          => null,
            'kecamatan'             => $user->kecamatan,
            'kode_pos'              => $user->kode_pos,
            'latitude'              => null,
            'longitude'             => null,
            'payment_method'        => 'cod',
            'payment_link'          => null,
            'payment_date'          => null,
            'payment_total'         => $finalPrice,
            'coupon_id'             => null,
            'payment_discount_code' => null,
            'payment_discount'      => null,
            'payment_code'          => null,
            'order_weight'          => null,
            'order_distance'        => null,
            'delivery_status'       => null,
            'delivery_fee'          => null,
            'delivery_track'        => null,
            'delivery_time'         => null,
            'delivery_date'         => null,
            'order_time'            => Carbon::now(),
            'confirmation_time'     => null,
            'notes'                 => null,
            'status'                => '1',
            'status_faktur'         => 'F',
            'site_code'             => $user->site_code,
            'created_at'            => Carbon::now(),
            'updated_at'            => Carbon::now(),
            'deleted_at'            => null,
            'payment_final'         => $finalPrice,
            'photo'                 => null,
            'courier'               => null,
            'delivery_service'      => null,
            'status_update_erp'     => null,
            'server'                => config('server.server')
        ]);

        // insert order into erp
        Http::post('http://site.muliaputramandiri.com/restapi/api/master_data/orders_detail', [
            'X-API-KEY'                 => config('erp.x_api_key'),
            'token'                     => config('erp.token_api'),
            // 'product_id'        => $request['product_id'],
            'product_id'                => $product->kodeprod, // update 21-09-21
            'order_id'                  => $order->id,
            'invoice'                   => $order->invoice,
            'harga_product'             => $product->price->harga_ritel_gt,
            'harga_product_konversi'    => ($product->price->harga_ritel_gt / $konversi),
            'large_price'               => null,
            'large_qty'                 => null,
            'large_unit'                => null,
            'medium_price'              => null,
            'medium_qty'                => null,
            'medium_unit'               => null,
            'small_price'               => null,
            'small_qty'                 => $subscribe->qty,
            'small_unit'                => null,
            'harga_product'             => $priceApps, // update 21 - 09 -21
            'qty_konversi'              => $qty_konversi, // update 21 - 09 - 21
            // 'item_disc'                 => $item_disc, // update 08 - 10 - 21
            'item_disc'                 => $rp_cabang,
            'total_price'               => $finalPrice,
            'notes'                     => $subscribe->notes,
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
            'deleted_at'                => null,
            'product_review_id'         => null,
            'location_id'               => null,
            'description'               => null,
            'status'                    => null,
            'status_update_erp'         => null,
            'last_updated_erp'          => null,
            'disc_cabang'               => $disc_cabang,
            'rp_cabang'                 => $rp_cabang
        ]);
    }
}
