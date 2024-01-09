<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\OrderDetail;
use App\UserAddress;
use App\PointHistory;
use App\User;
use App\Log;
use Carbon\Carbon;

class RedeemPointController extends Controller
{
    protected $order, $orderDetail, $userAddress, $pointHistory, $user, $logs;

    public function __construct(Order $order, OrderDetail $orderDetail, UserAddress $userAddress, PointHistory $pointHistory, User $user, Log $logs)
    {
        $this->order        = $order;
        $this->orderDetail  = $orderDetail;
        $this->userAddress  = $userAddress;
        $this->pointHistory = $pointHistory;
        $this->user         = $user;
        $this->logs         = $logs;
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

        DB::beginTransaction();

        try {
            $id         = auth()->user()->id;
            $appVersion = auth()->user()->app_version;
            $user       = $this->userAddress
                                        ->join('users', 'users.id', '=', 'user_address.user_id')
                                        ->where('users.id', $id)
                                        ->where('user_address.default_address', '1')
                                        ->first();

            if($appVersion == '1.1.1') {
                $arrayProduct      = ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $arrayProduct      = ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];            
            }

            // get all json post data
            $requests   = $request->json()->all();

            if($user->point < $requests['data']['payment_total']) {
                $point = $requests['data']['payment_total'] - $user->point;
                return response()->json([
                    'success' => false,
                    'message' => 'Point tidak mencukupi, kurang ' . $point,
                    'data'    => NULL
                ], 201);
            }

            // cek if mobile post plarform from pwa
            if(isset($request['data']['platform'])) {
                $platform   = $request['data']['platform'];
            } else {
                $platform   = 'app';
            }

            $orders = $this->order
                                ->create([
                                    'invoice'               => $user->customer_code . strtotime(Carbon::now()),
                                    'customer_id'           => $id,
                                    'name'                  => $user->name,
                                    'phone'                 => $user->phone,
                                    'app_version'           => $user->app_version,
                                    'provinsi'              => $user->provinsi,
                                    'kota'                  => $user->kota,
                                    'kecamatan'             => $user->kecamatan,
                                    'kelurahan'             => $user->kelurahan,
                                    'kode_pos'              => $user->kode_pos,
                                    'address'               => $user->address,
                                    'site_code'             => $user->site_code,
                                    'payment_method'        => $requests['data']['payment_method'],
                                    'payment_total'         => $requests['data']['payment_total'],
                                    'payment_final'         => $requests['data']['payment_final'],
                                    'order_time'            => Carbon::now()->format('Y-m-d H:i:s'),
                                    'status'                => '1',
                                    'status_faktur'         => 'Redeem',
                                    'platform'              => $platform,
                                    'delivery_service'      => $requests['data']['delivery_service']
                                ]);

            $point  =   $requests['data']['payment_total'];

            foreach($requests['products'] as $request) {
                // insert into order_detail table
                    $orderDetail    = $this->orderDetail
                                                    ->create([
                                                        'product_id'                => $request['id'],
                                                        'order_id'                  => $orders->id,
                                                        // 'konversi_sedang_ke_kecil'  => $request['konversi_sedang_ke_kecil'],
                                                        // 'qty_konversi'              => $qtyKonversi,
                                                        // 'half'                      => $half,
                                                        // 'large_unit'                => isset($request['large_unit']) ? $request['large_unit'] : null,
                                                        // 'medium_unit'               => isset($request['medium_unit']) ? $request['medium_unit'] : null,
                                                        // 'small_unit'                => isset($request['small_unit']) ? $request['small_unit'] : null,
                                                        // 'small_unit'                => $kecil,
                                                        'qty'                       => $request['qty'],
                                                        // 'point'                     => $point,
                                                        'notes'                     => $request['notes'],
                                                        'price_apps'                => $request['redeem_point'],
                                                        'total_price'               => $request['redeem_point'],
                                                        // 'disc_cabang'               => $disc_cabang,
                                                        // 'rp_cabang'                 => $rp_cabang,
                                                        // 'promo_id'                  => $promo_id
                                                    ]);
            }

            // create pointhistory
            $pointHistory   = $this->pointHistory
                                        ->create([
                                            'customer_id'   =>  $user->id,
                                            'order_id'      =>  $orders->id,
                                            'kredit'        =>  $point,
                                            'status'        =>  'point dari order redeem invoice ' . $orders->invoice
                                        ]);

            // get point and update to user
            $point = $user->point - $point;
            $this->user->find($user->id)->update(['point' => $point]);

            // for response
            $orderResponse = $this->order
                                    ->where('id', $orders->id)
                                    ->with(['data_item.product' => function($query) use ($arrayProduct) {
                                        $query->select($arrayProduct);
                                    }])
                                    ->first();

            // logs
            $logs   = $this->logs
                            ->create([
                                'log_time'      => Carbon::now(),
                                'activity'      => "create order redeem product with invoice " . $orders->invoice,
                                'table_id'      => $orders->id,
                                'table_name'    => 'orders, order_detail, point_history',
                                'data_content'  => $orderResponse,
                                'from_user'     => auth()->user()->id,
                                'to_user'       => null,
                                'platform'      => 'apps'
                            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Create order successfully',
                'data'    => $orderResponse
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Create order redeem failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
