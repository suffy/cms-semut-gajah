<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Log;
use App\Order;
use App\OrderDetail;
use App\ProductReview;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    protected $productReviews, $orderDetails, $orders, $logs;

    public function __construct(ProductReview $productReview, OrderDetail $orderDetail, Order $order, Log $log)
    {
        $this->productReviews   = $productReview;
        $this->orderDetails     = $orderDetail;
        $this->orders           = $order;
        $this->logs             = $log;
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

        // check user login
        $id          = Auth::user()->id;
        $app_version = Auth::user()->app_version;

        try {
            $productReviews = $this->orders->query();
            if ($app_version == '1.1.1') {
                $arrayProduct      = $this->arraySelectProductOld();
            } else {
                $arrayProduct      = $this->arraySelectProduct();
            }
            $arrayOrderDetail   = ['id', 'product_id', 'order_id', 'small_unit', 'konversi_sedang_ke_kecil', 'half', 'qty_konversi', 'qty', 'price_apps', 'total_price', 'product_review_id', 'promo_id', 'disc_cabang', 'rp_cabang', 'disc_principal', 'rp_principal', 'point_principal', 'bonus', 'bonus_qty', 'bonus_name', 'bonus_konversi', 'point'];

            if ($app_version == '1.1') {
                // check reviewed product
                if ($request->review == 'true') {
                    $productReviews = $productReviews
                        ->with('data_review.product.review')
                        ->whereHas('data_review', function ($query) {
                            $query->where('product_review_id', '!=', null);
                        })
                        ->where('customer_id', $id)
                        ->where('status', '4');
                }

                // check unreviewed product
                if ($request->review == 'false') {
                    $productReviews = $productReviews
                        ->with('data_unreview.product')
                        ->whereHas('data_unreview', function ($query) {
                            $query->where('product_review_id', null);
                        })
                        ->where('customer_id', $id)
                        ->where('status', '4');
                }

                // search reviewed product
                if ($request->review == 'true' && $request->search) {
                    $productReviews = $productReviews
                        ->with(['data_review.product' => function ($query) use ($request) {
                            $query->where('name', 'like', '%' . $request->search . '%');
                            $query->with('review');
                        }])
                        ->whereHas('data_review', function ($query) {
                            $query->where('product_review_id', '!=', null);
                        })
                        ->where('customer_id', $id)
                        ->where('status', '4');
                }

                $productReviews = $productReviews->paginate(10);

                return response()->json([
                    'success' => true,
                    'message' => 'Create review product successfully',
                    'data'    => $productReviews
                ], 200);
            } else {
                $selectProductReviews = ['product_review.id', 'products.name', 'products.image', 'product_review.product_id', 'product_review.user_id', 'product_review.order_id', 'product_review.star_review', 'product_review.detail_review', 'product_review.created_at'];
                $selectOrders         = ['id', 'customer_id', 'status', 'payment_final', 'status_faktur', 'order_time', 'review_at', 'created_at', 'updated_at'];
                // check reviewed product

                if ($request->review == 'true') {
                    $productReviews = $productReviews
                        ->with(['data_review' => function ($query) use ($arrayOrderDetail, $selectProductReviews) {
                            $query->select($arrayOrderDetail)
                                ->with(['review' => function ($q) use ($selectProductReviews) {
                                    $q->leftJoin('products', 'products.id', '=', 'product_id')
                                        ->select($selectProductReviews);
                                }]);
                        }])
                        ->whereHas('data_review', function ($query) {
                            $query->where('product_review_id', '!=', null);
                        })
                        ->where('customer_id', $id)
                        ->where('status', '4');
                }

                // check unreviewed product
                if ($request->review == 'false') {
                    $productReviews = $productReviews
                        ->with(['data_unreview' => function ($query) use ($arrayOrderDetail, $arrayProduct) {
                            $query->select($arrayOrderDetail)
                                ->with(['product' => function ($q) use ($arrayProduct) {
                                    $q->select($arrayProduct);
                                }]);
                        }])
                        ->whereHas('data_unreview', function ($query) {
                            $query->where('product_review_id', null);
                        })
                        ->where('customer_id', $id)
                        ->whereNull('status_review')
                        ->where('status', '4');
                }

                // search reviewed product
                if ($request->review == 'true' && $request->search) {
                    $productReviews = $productReviews
                        ->with(['data_review' => function ($query) use ($arrayOrderDetail, $request, $selectProductReviews) {
                            $query->select($arrayOrderDetail)
                                ->with(['review' => function ($q) use ($request, $selectProductReviews) {
                                    $q->leftJoin('products', 'products.id', '=', 'product_id')
                                        ->select($selectProductReviews)
                                        ->where('name', 'like', '%' . $request->search . '%');
                                }]);
                        }])
                        // ->where(function($query) {
                        //     $query->whereHas('data_review.review', function($q){$q;}, '!=', null);
                        // })
                        // cek kondisi 
                        ->where('customer_id', $id)
                        ->where('status', '4');
                }

                $productReviews = $productReviews
                    ->select($selectOrders)
                    ->orderBy('review_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate(10);

                return response()->json([
                    'success' => true,
                    'message' => 'Get review product successfully',
                    'data'    => $productReviews
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get review product failed',
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

        // validation
        $validator = Validator::make(
            $request->json()->all(),
            [
                'order_id'                  => 'required',
                'products'                  => 'required',
                'products.*.star_review'    => 'required|not_in:0',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first(),
                'data'      => null
            ], 201);
        }

        // check user login
        $id = Auth::user()->id;

        try {
            $requests = $request->json()->all();

            foreach ($requests['products'] as $request) {
                // insert into product_review table
                $productReview  = $this->productReviews
                    ->create([
                        'product_id'    => $request['product_id'],
                        'user_id'       => $id,
                        'order_id'      => $requests['order_id'],
                        'star_review'   => $request['star_review'],
                        'detail_review' => $request['detail_review']
                    ]);

                // edit order_detail
                $orderDetail    = $this->orderDetails
                    ->where('order_id', $requests['order_id'])
                    ->where('product_id', $request['product_id'])
                    ->update([
                        'product_review_id' => $productReview->id
                    ]);
            }

            $order                  = $this->orders->find($requests['order_id']);
            $order->status_review   = 1;
            $order->review_at       = Carbon::now();
            $order->save();

            // log
            $logs   = $this->logs
                ->create([
                    'log_time'      => Carbon::now(),
                    'activity'      => "new review from user with id : " . $id . " and product with id: " . $request['product_id'],
                    'table_name'    => 'order_detail, product_review',
                    'column_name'   => 'product_detail.product_review_id, product_review.product_id, product_review.user_id, product_review.order_id, product_review.star_review, product_review.detail_review.product_review.created_at, product_review.updated_at',
                    'data_content'  => $productReview,
                    'from_user'     => auth()->user()->id,
                    'to_user'       => null,
                    'platform'      => 'apps'
                ]);

            // for response
            $productReviewResponse  = $this->orders
                ->with('data_review.product.review')
                ->whereHas('data_item', function ($query) {
                    $query->where('product_review_id', '!=', null);
                })
                ->where('customer_id', $id)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Create review product successfully',
                'data'    => $productReviewResponse
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Create review product failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
