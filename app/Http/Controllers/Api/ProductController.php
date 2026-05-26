<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\OrderDetail;
use App\Product;
use App\ProductOfferItem;
use App\RecentProduct;
use App\Category;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductController extends Controller
{
    protected $products, $orders, $orderDetail, $categories, $productsOfferItem;

    public function __construct(Product $product, OrderDetail $orderDetail, ProductOfferItem $productOfferItem, RecentProduct $recentProduct, Category $categories)
    {
        $this->products             = $product;
        $this->orderDetail          = $orderDetail;
        $this->productsOfferItem    = $productOfferItem;
        $this->recentProduct        = $recentProduct;
        $this->categories           = $categories;
    }

    // // array for select product
    // private function arraySelectProduct()
    // {
    //     return ['id', 'kodeprod', 'name','description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'kecil', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'status_renceng', 'created_at'];
    // }

    // // array for select product
    // private function arraySelectProductOld()
    // {
    //     return ['id', 'kodeprod', 'name','description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    // }

    // array for select product!
    private function arraySelectCart()
    {
        return ['user_id', 'product_id', 'qty'];
    }

    // array for select product
    private function arraySelectPromoSku()
    {
        return ['promo_skus.product_id', 'promo_skus.promo_id', 'promos.title', 'promos.id'];
    }

    // array for select product
    private function arraySelectPrice()
    {
        $salurCode      = auth()->user()->salur_code;
        // return ['product_id', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt'];
        if ($salurCode == 'SW' || $salurCode == 'WS' || $salurCode == 'SO') {
            return DB::raw("
                                    product_prices.id,
                                    product_id,
                                    harga_grosir_mt,
                                    harga_promosi_coret_ritel_gt,
                                    harga_promosi_coret_grosir_mt,
                                    products.brand_id,
                                    harga_ritel_gt as ritel_gt,
                                    (CASE
                                        WHEN products.brand_id::integer=005 THEN harga_ritel_gt
                                        WHEN products.brand_id::integer=001 THEN harga_ritel_gt
                                        ELSE harga_grosir_mt
                                        END) as harga_ritel_gt
                                ");
        } else {
            return DB::raw("
                                    product_prices.id,
                                    product_id,
                                    harga_ritel_gt,
                                    harga_grosir_mt,
                                    harga_promosi_coret_ritel_gt,
                                    harga_promosi_coret_grosir_mt,
                                    products.brand_id,
                                    harga_ritel_gt as rt_backup
                                ");
        };
    }

    public function get(Request $request)                                    // get data mpm api product promo
    {
        try {                                                                   // check token
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
            $userId         = auth()->user()->id;                                       // get user id
            $products       = $this->products->query();
            $app_version    = auth()->user()->app_version;
            $siteCode       = auth()->user()->site_code;
            if ($app_version == '1.1.1') {
                $array      = ['id', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at', 'products.type_status'];
                array_walk($array, function (&$value, $key) {
                    $value = 'products.' . $value;
                });
                $arrayProductPromo = ['id', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $array              = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at', 'products.type_status'];
                $arrayProductPromo = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'products.kodeprod', 'products.name', 'products.description', 'products.image', 'products.brand_id', 'products.category_id', 'products.satuan_online',  'products.kecil', 'products.konversi_sedang_ke_kecil', 'products.status_promosi_coret', 'products.status_herbana', 'products.status_terlaris', 'products.status_terbaru', 'products.status_renceng', 'products.created_at'];
            }
            // $arrayPrice     = ['product_id', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt'];
            $arrayPrice     = $this->arraySelectPrice();

            // Kondisi untuk harga biar sesuai dengan user yang login
            // if($salurCode == 'SW' || $salurCode == 'WS' || $salurCode == 'SO') {
            //     $arrayPrice = DB::raw("
            //                             product_prices.id,
            //                             product_id,
            //                             harga_grosir_mt,
            //                             harga_promosi_coret_ritel_gt,
            //                             harga_promosi_coret_grosir_mt,
            //                             products.brand_id,
            //                             harga_ritel_gt as ritel_gt,
            //                             (CASE
            //                                 WHEN products.brand_id=005 THEN harga_ritel_gt
            //                                 WHEN products.brand_id=0001 THEN harga_ritel_gt
            //                                 ELSE harga_grosir_mt
            //                                 END) as harga_ritel_gt
            //                         ");
            // } else {
            //     $arrayPrice = DB::raw("
            //                             product_prices.id,
            //                             product_id,
            //                             harga_ritel_gt,
            //                             harga_grosir_mt,
            //                             harga_promosi_coret_ritel_gt,
            //                             harga_promosi_coret_grosir_mt,
            //                             products.brand_id,
            //                             harga_ritel_gt as rt_backup
            //                         ");
            // };

            $kode_type = Auth::user()->kode_type;
            if (!is_null($kode_type)) {
                $products = $products->where(function ($q) use ($kode_type) {
                    $q->where('type_status', 'like', '%' . $kode_type . '%');
                    $q->orwhere('type_status', null);
                });
            }

            // get newest products all category
            if (!$request->category_id && $request->category == 'newest') {     // check if using param newest
                $products   = $products
                    ->where('status_terbaru', '1')
                    ->where('product_availability.status', '1')
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    // ->orderBy('created_at', 'desc')
                    ->select($array)
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('products.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('products.created_at', 'desc');
                }
            }

            // get newest products specific category'b
            if ($request->category_id != '' && $request->category == 'newest') {
                $products   = $products
                    ->where('product_availability.status', '1')
                    ->where('products.category_id', $request->category_id)
                    ->where('status_terbaru', '1')
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select($array)
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('products.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products = $products->orderBy('products.created_at', 'desc');
                }
            }

            // get popular products all category
            if (!$request->category_id && $request->category == 'popular') {    // check if using param popular
                $products   = $products
                    ->where('product_availability.status', '1')
                    ->where('status_terlaris', '1')
                    ->orderBy('status_terlaris', 'Desc')
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select($array)
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('products.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('products.created_at', 'desc');
                }
            }

            // get popular products specific category
            if ($request->category_id != '' && $request->category == 'popular') {
                $products   = $products
                    ->where('product_availability.status', '1')
                    ->where('category_id', $request->category_id)
                    ->where('status_terlaris', '1')
                    ->orderBy('status_terlaris', 'Desc')
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select($array)
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('created_at', 'desc');
                }
            }

            // get promo products all category
            if (!$request->category_id && $request->category == 'promo') {      // check if using param promo
                // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

                $products   = $this->products
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->join('promo_skus', 'promo_skus.product_id', '=', 'products.id')
                    ->join('promos', 'promos.id', '=', 'promo_skus.promo_id')
                    ->where('product_availability.site_code', $siteCode)
                    ->where('promos.status', 1)
                    ->where('product_availability.status', 1)
                    ->select($arrayProductPromo)
                    ->distinct();

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('products.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('products.created_at', 'desc');
                }
            }

            // get promo products specific category
            if ($request->category_id != '' && $request->category == 'promo') {
                // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

                $products   = $this->products
                    ->join('promo_skus', 'promo_skus.product_id', '=', 'products.id')
                    ->join('promos', 'promos.id', '=', 'promo_skus.promo_id')
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->where('promos.status', 1)
                    ->where('product_availability.status', 1)
                    ->where('products.category_id', $request->category_id)
                    ->where('product_availability.site_code', $siteCode)
                    ->select($arrayProductPromo)
                    ->distinct();

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('products.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('products.created_at', 'desc');
                }
            }

            // search products
            if ($request->search) {
                $products = $products
                    ->where('products.name', 'like', '%' . ucwords($request->search) . '%')
                    // ->whereRaw(
                    //         "MATCH(name) AGAINST(?)",
                    //         array($request->search)
                    // )
                    ->where('product_availability.status', '1')
                    // ->where('product_availability.site_code', $siteCode)
                    // ->join('product_availability', 'product_availability.product_id' ,'=', 'products.id')
                    ->select($array);
            }

            // if product with categori_id
            if ($request->category_id != '' && !$request->category) {
                $products = $products
                    ->where('product_availability.status', '1')
                    ->where('category_id', $request->category_id)
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select($array)
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('products.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('products.created_at', 'desc');
                }
            }

            // get all products
            if (!$request->category_id && !$request->category) {
                $products = $products
                    ->where('product_availability.status', '1')
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select($array)
                    ->limit(10);
            }

            // get recent transaction products
            if (!$request->category_id && $request->category == 'recent') {
                $arrayId = $this->orderDetail
                    ->select('id')
                    ->whereNotNull('product_id')
                    ->whereRaw('id in (select max(id) from order_detail group by product_id)')
                    ->orderBy('id', 'desc')
                    ->pluck('id');

                // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

                $products   = $products
                    ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                    ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->whereIn('order_detail.id', $arrayId)
                    ->where('status_faktur', 'F')
                    ->where('product_availability.status', '1')
                    ->where('orders.customer_id', $userId)
                    ->where('product_availability.site_code', $siteCode)
                    ->groupBy('products.id')
                    ->select($arrayProductPromo)
                    ->distinct('order_detail.product_id')
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('orders.order_time', 'asc');
                } else if ($request->order == 'desc') {
                    $products = $products->orderBy('orders.order_time', 'desc');
                }
            }

            // get recent transaction products with category id
            if ($request->category_id != '' && $request->category == 'recent') {
                $arrayId    = $this->orderDetail
                    ->select('id')
                    ->whereNotNull('product_id')
                    ->whereRaw('id in (select max(id) from order_detail group by product_id)')
                    ->orderBy('id', 'desc')
                    ->pluck('id');

                // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

                $products   = $products
                    ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                    ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->where('product_availability.site_code', $siteCode)
                    ->whereIn('order_detail.id', $arrayId)
                    ->where('status_faktur', 'F')
                    ->where('product_availability.status', '1')
                    ->where('products.category_id', $request->category_id)
                    ->where('orders.customer_id', $userId)
                    ->select($arrayProductPromo)
                    ->distinct('order_detail.product_id')
                    ->limit(10);

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('orders.order_time', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('orders.order_time', 'desc');
                }
            }

            // category products promo
            if (!$request->category_id && $request->category == 'products_promo') {
                // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

                $products   = $this->products
                    ->join('promo_skus', 'promo_skus.product_id', '=', 'products.id')
                    ->join('promos', 'promos.id', '=', 'promo_skus.promo_id')
                    ->where('promos.status', 1)
                    ->where('product_availability.status', 1)
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select($arrayProductPromo)
                    ->distinct();

                if ($request->order == 'asc') {
                    $products   = $products->orderBy('promos.created_at', 'asc');
                } else if ($request->order == 'desc') {
                    $products   = $products->orderBy('promos.created_at', 'desc');
                }
            }
            // base query product with param
            $products = $products
                ->with([
                    'price' => function ($query) use ($arrayPrice) {
                        $query->select($arrayPrice)
                            ->join('products', 'products.id', '=', 'product_prices.product_id');
                    },
                    'cart' => function ($query) use ($userId) {
                        $query->where('user_id', $userId)
                            ->select('id', 'user_id', 'product_id', 'qty');
                    }, 'promo_sku' => function ($query) {
                        $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                            ->select('promo_skus.product_id', 'promo_skus.promo_id', 'promos.id')
                            ->where('promos.status', 1)
                            ->limit(1);
                    }
                ])
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get products successfully',
                'data'    => $products
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    // get data product recomendation for user
    public function recomen()
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
            $userId         = auth()->user()->id;
            $products       = $this->products->query();
            $recentProducts = $this->recentProduct->query();
            $app_version    = Auth::user()->app_version;
            $siteCode       = auth()->user()->site_code;
            if ($app_version == '1.1.1') {
                $array      = ['id', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
            }
            $arrayCart      = $this->arraySelectCart();
            $arrayPromoSku  = $this->arraySelectPromoSku();
            $arrayPrice     = $this->arraySelectPrice();

            if (cache()->has('products_recomen-' . $userId)) {
                $merge = cache()->get('products_recomen-' . $userId);
            } else {
                $category = $recentProducts                                                                 //  get recent view product from user
                    ->where('user_id', $userId)
                    ->where('product_availability.status', 1)
                    ->where('product_availability.site_code', $siteCode)
                    ->join('products', 'products.id', '=', 'recent_products.product_id')
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->select('products.category_id as id', 'products.slug', 'products.brand_id', 'recent_products.created_at')
                    ->latest()
                    ->first();

                if (is_null($category)) {
                    $category = $this->products
                        ->where('product_availability.status', 1)
                        ->where('status_terlaris', 1)
                        ->select('category_id as id', 'slug', 'brand_id')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->first();
                }

                $productsBycategory = $products                                                             //  get product by category by recent view below
                    ->where('product_availability.status', 1)
                    ->where('category_id', $category->id)
                    ->where('product_availability.site_code', $siteCode)
                    ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                    ->with(['price' => function ($query) use ($arrayPrice) {
                        $query->select($arrayPrice)
                            ->join('products', 'products.id', '=', 'product_prices.product_id');
                    }, 'review' => function ($query) {
                        $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                            ->groupBy('product_id', 'product_review.product_id');
                    }, 'cart' => function ($query) use ($userId, $arrayCart) {
                        $query->where('user_id', $userId)
                            ->select($arrayCart);
                    }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                        $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                            ->select($arrayPromoSku)
                            ->where('promos.status', 1);
                    }])
                    ->select($array)
                    ->get();
                // ->toArray();

                $name  = explode('-', $category->slug);                                                     //  get name product from recent view below

                $productsByname = $products                                                                 //  get product by similar name from recent view
                    // ->whereRaw(
                    //     "MATCH(products.name) AGAINST(?)",
                    //     array($name[0])
                    // )
                    ->where('products.name', 'like', '%' . ucwords($name[0]) . '%')
                    // ->where('products.search_name', 'like', '%' . $name[0] . '%')
                    // ->where('product_availability.site_code', $siteCode)
                    // ->join('product_availability', 'product_availability.product_id' ,'=', 'products.id')
                    ->with(['price' => function ($query) use ($arrayPrice) {
                        $query->select($arrayPrice)
                            ->join('products', 'products.id', '=', 'product_prices.product_id');
                    }, 'review' => function ($query) {
                        $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                            ->groupBy('product_id', 'product_review.product_id');
                    }, 'cart' => function ($query) use ($userId, $arrayCart) {
                        $query->where('user_id', $userId)
                            ->select($arrayCart);
                    }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                        $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                            ->select($arrayPromoSku)
                            ->where('promos.status', 1);
                    }])
                    ->select($array)
                    ->get();

                if (is_null($productsByname)) {                                                              //  check if product by similar name null
                    $productsByname = $products                                                                    //  use product by similar brand_id
                        ->where('product.avialability.status', 1)
                        ->where('brand_id', $category->brand_id)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1);
                        }])
                        ->select($array)
                        ->get();
                    // ->toArray();
                }

                $merge              = cache()->remember('products_recomen-' . $userId, 30, function () use ($productsBycategory, $productsByname) {
                    return $productsBycategory->merge($productsByname)->unique()->shuffle()->toArray();
                });
            }

            $products   = $this->paginate_array($merge);

            return response()->json([
                'success' => true,
                'message' => 'get recomen product, success',
                'data'    => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success'   => false,
                'message'   => 'get recomen product, failed',
                'data'      => $e->getMessage()
            ], 500);
        }
    }

    // method to paginate array object
    private function paginate_array($items, $perPage = 10, $page = null, $options = [])
    {
        $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total          = count($items);
        $currentpage    = $page;
        $offset         = ($currentpage * $perPage) - $perPage;
        $itemstoshow    = array_slice($items, $offset, $perPage);

        return new LengthAwarePaginator($itemstoshow, $total, $perPage);
    }

    public function storeRecent(Request $request)
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
            $productId  = $request->product_id;
            $userId     = Auth()->user()->id;

            // insert or update data if viewed by user
            $recent = $this->recentProduct::updateOrCreate(
                ['product_id'   => $productId],
                [
                    'user_id'      => $userId,
                    'created_at'    => Carbon::now()->format('Y-m-d H:i:s')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Store product recent view, success',
                'data'    => $recent
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Store product recent view, failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function getRecent(Request $request)
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
            $userId     = Auth()->user()->id;
            $siteCode   = auth()->user()->site_code;
            $products   = $this->products->query();
            $date       = Carbon::now();

            $app_version = Auth::user()->app_version;
            if ($app_version == '1.1.1') {
                $array      = ['products.id', 'products.kodeprod', 'products.name', 'products.description', 'products.image_backup as image', 'products.brand_id', 'products.category_id', 'products.satuan_online', 'products.konversi_sedang_ke_kecil', 'products.status', 'products.status_herbana', 'products.status_promosi_coret', 'products.status_terlaris', 'products.status_terbaru', 'products.created_at', 'products.updated_at'];
            } else {
                $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
            }
            $arrayPrice     = $this->arraySelectPrice();

            // get recent product where user login and before 15 days
            $recentProducts = $products
                ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                ->join('recent_products', 'recent_products.product_id', '=', 'products.id')
                ->where('product_availability.site_code', $siteCode)
                ->where('recent_products.user_id', $userId)
                ->where('product_availability.status', 1)
                ->where('recent_products.created_at', '>=', $date->subDays(15))
                ->limit(10);

            if ($request->order == 'asc') {
                $recentProducts = $recentProducts->orderBy('recent_products.created_at', 'asc');
            } elseif ($request->order == 'desc') {
                $recentProducts = $recentProducts->orderBy('recent_products.created_at', 'desc');
            } else {
                $recentProducts = $recentProducts->orderBy('recent_products.created_at', 'desc');
            }

            // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

            $recentProducts = $recentProducts
                ->select($array)
                ->with(['price' => function ($query) use ($arrayPrice) {
                    $query->select($arrayPrice)
                        ->join('products', 'products.id', '=', 'product_prices.product_id');
                }, 'review', 'promo_sku' => function ($query) {
                    $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                        ->select('promo_skus.product_id', 'promo_skus.promo_id', 'promos.id')
                        ->where('promos.status', 1);
                }])
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get product recent view, success',
                'data'    => $recentProducts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get product recent view, failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request, $id)
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

        $siteCode       = auth()->user()->site_code;
        $userId         = auth()->user()->id;
        $products       = $this->products->query();
        $arrayPrice     = $this->arraySelectPrice();
        $arrayCart      = $this->arraySelectCart();

        if ($request->star) {
            // filter review by rating star
            $products   = $products
                ->join('product_review', 'products.id', '=', 'product_review.product_id')
                ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                ->where('product_availability.site_code', $siteCode)
                ->select('products.*', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                ->groupBy('products.id')
                ->with(['price'  => function ($query) use ($arrayPrice) {
                    $query->select($arrayPrice)
                        ->join('products', 'products.id', '=', 'product_prices.product_id');
                }])
                ->with(['review' => function ($query) use ($request) {
                    $query->where('star_review', $request->star)
                        ->with('user');
                }]);
        } else {
            // get all review
            $products   = $products
                ->leftJoin('product_review', 'products.id', '=', 'product_review.product_id')
                ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                ->where('product_availability.site_code', $siteCode)
                ->select('products.*', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                ->groupBy('products.id')
                ->with(['price'  => function ($query) use ($arrayPrice) {
                    $query->select($arrayPrice)
                        ->join('products', 'products.id', '=', 'product_prices.product_id');
                },  'cart' => function ($query) use ($userId, $arrayCart) {
                    $query->where('user_id', $userId)
                        ->select($arrayCart);
                }, 'review.user']);
        }

        $products = $products->find($id);

        return response()->json([
            'message' => $products,
        ]);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Get detail product successfully',
                'data'    => $products
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get detail product failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function rating($id)
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

        $review     = "SELECT product_id, star_review FROM product_review WHERE product_id = " . $id;
        $star   = "" .
            "SELECT
            product_id,
            SUM(CASE WHEN star_review  = 5 THEN 1 ELSE 0 END) as total_five_star,
            SUM(CASE WHEN star_review  = 4 THEN 1 ELSE 0 END) as total_four_star,
            SUM(CASE WHEN star_review  = 3 THEN 1 ELSE 0 END) as total_three_star,
            SUM(CASE WHEN star_review  = 2 THEN 1 ELSE 0 END) as total_two_star,
            SUM(CASE WHEN star_review  = 1 THEN 1 ELSE 0 END) as total_one_star
            FROM product_review
            WHERE product_id = " . $id .
            "GROUP BY product_id";

        $percent = "" .
            "SELECT
            product_id,
            CONCAT(ROUND(SUM(CASE WHEN star_review  = 5 THEN 1 ELSE 0 END) / ROUND(COUNT(*),2) * 100), '%') AS percent_five_star,
            CONCAT(ROUND(SUM(CASE WHEN star_review  = 4 THEN 1 ELSE 0 END) / ROUND(COUNT(*),2) * 100), '%') AS percent_four_star,
            CONCAT(ROUND(SUM(CASE WHEN star_review  = 3 THEN 1 ELSE 0 END) / ROUND(COUNT(*),2) * 100), '%') AS percent_three_star,
            CONCAT(ROUND(SUM(CASE WHEN star_review  = 2 THEN 1 ELSE 0 END) / ROUND(COUNT(*),2) * 100), '%') AS percent_two_star,
            CONCAT(ROUND(SUM(CASE WHEN star_review  = 1 THEN 1 ELSE 0 END) / ROUND(COUNT(*),2) * 100), '%') AS percent_one_star,
            ROUND(AVG(star_review)::numeric, 1) AS avg_rating
            FROM product_review  WHERE product_id = " . $id .
            "GROUP BY product_id";

        $response = DB::select(DB::raw("
            SELECT
            review.product_id,
            ROUND(avg(star_review)::numeric, 1) as avg_star,
            star.*,
            percent.*
            from (" . $review . ") as review
            left join (" . $star . ") as star
            on review.product_id = star.product_id
            left join (" . $percent . ") as percent
            on review.product_id = percent.product_id
            group by
                review.product_id,
                star.product_id, star.total_five_star, star.total_four_star, star.total_three_star, star.total_two_star, star.total_one_star,
                percent.product_id, percent.percent_five_star, percent.percent_four_star, percent.percent_three_star, percent.percent_two_star, percent.percent_one_star,
                percent.avg_rating
        "));

        try {
            return response()->json([
                'success' => true,
                'message' => 'Get rating product successfully',
                'data'    => $response
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get rating product failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function allProduct()
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

        $userId         = auth()->user()->id;
        $date           = Carbon::now();
        $siteCode       = auth()->user()->site_code;
        $app_version = Auth::user()->app_version;
        if ($app_version == '1.1.1') {
            $array      = ['id', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            array_walk($array, function (&$value, $key) {
                $value = 'products.' . $value;
            });
            $arrayProductPromo = ['id', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
        } else {
            $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
            $arrayProductPromo = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'products.kodeprod', 'products.name', 'products.description', 'products.image', 'products.brand_id', 'products.category_id', 'products.satuan_online',  'products.kecil', 'products.konversi_sedang_ke_kecil', 'products.status_promosi_coret', 'products.status_herbana', 'products.status_terlaris', 'products.status_terbaru', 'products.status_renceng', 'products.created_at'];
        }
        $arrayCart      = $this->arraySelectCart();
        $arrayPromoSku  = $this->arraySelectPromoSku();
        $arrayPrice     = $this->arraySelectPrice();

        try {
            if (cache()->has('masukAngin-' . $userId)) {
                $masukAngin = cache()->get('masukAngin-' . $userId);
            } else {
                $masukAngin = cache()->remember('masukAngin-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '1')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id', 'product_review.product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products masuk angin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('obatBatuk-' . $userId)) {
                $obatBatuk = cache()->get('obatBatuk-' . $userId);
            } else {
                $obatBatuk = cache()->remember('obatBatuk-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '2')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id', 'product_review.product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products obat batuk failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('sariawanPanasDalam-' . $userId)) {
                $sariawanPanasDalam = cache()->get('sariawanPanasDalam-' . $userId);
            } else {
                $sariawanPanasDalam = cache()->remember('sariawanPanasDalam-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '3')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products sariawan dan panas dalam failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('pegalLinuStamina-' . $userId)) {
                $pegalLinuStamina = cache()->get('pegalLinuStamina-' . $userId);
            } else {
                $pegalLinuStamina = cache()->remember('pegalLinuStamina-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '4')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products pegal linu dan stamina failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('produkWanita-' . $userId)) {
                $produkWanita = cache()->get('produkWanita-' . $userId);
            } else {
                $produkWanita = cache()->remember('produkWanita-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '5')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products produk wanita failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('permen-' . $userId)) {
                $permen = cache()->get('permen-' . $userId);
            } else {
                $permen = cache()->remember('permen-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '6')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products permen failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbaMojo-' . $userId)) {
                $herbaMojo = cache()->get('herbaMojo-' . $userId);
            } else {
                $herbaMojo = cache()->remember('herbaMojo-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '7')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herba mojo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbana-' . $userId)) {
                $herbana = cache()->get('herbana-' . $userId);
            } else {
                $herbana = cache()->remember('herbana-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '8')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbana failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('madu-' . $userId)) {
                $madu = cache()->get('madu-' . $userId);
            } else {
                $madu = cache()->remember('madu-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '9')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products madu failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshcare-' . $userId)) {
                $freshcare = cache()->get('freshcare-' . $userId);
            } else {
                $freshcare = cache()->remember('freshcare-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '10')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshcare failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('hotin-' . $userId)) {
                $hotin = cache()->get('hotin-' . $userId);
            } else {
                $hotin = cache()->remember('hotin-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '11')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products hotin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduTj-' . $userId)) {
                $maduTj = cache()->get('maduTj-' . $userId);
            } else {
                $maduTj = cache()->remember('maduTj-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '12')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products maduTj failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('tresnojoyo-' . $userId)) {
                $tresnojoyo = cache()->get('tresnojoyo-' . $userId);
            } else {
                $tresnojoyo = cache()->remember('tresnojoyo-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '13')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products tresnojoyo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('bebio-' . $userId)) {
                $bebio = cache()->get('bebio-' . $userId);
            } else {
                $bebio = cache()->remember('bebio-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '14')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products bebio failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshliving-' . $userId)) {
                $freshliving = cache()->get('freshliving-' . $userId);
            } else {
                $freshliving = cache()->remember('freshliving-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '15')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshliving failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('mywell-' . $userId)) {
                $mywell = cache()->get('mywell-' . $userId);
            } else {
                $mywell = cache()->remember('mywell-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '16')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products mywell failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbal-' . $userId)) {
                $herbal = cache()->get('herbal-' . $userId);
            } else {
                $herbal = cache()->remember('herbal-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('category_id', '17')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('masukAnginNewest-' . $userId)) {
                $masukAnginNewest = cache()->get('masukAnginNewest-' . $userId);
            } else {
                $masukAnginNewest = cache()->remember('masukAnginNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '1')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id', 'product_review.product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products masuk angin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('obatBatukNewest-' . $userId)) {
                $obatBatukNewest = cache()->get('obatBatukNewest-' . $userId);
            } else {
                $obatBatukNewest = cache()->remember('obatBatukNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '2')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products obat batuk failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('sariawanPanasDalamNewest-' . $userId)) {
                $sariawanPanasDalamNewest = cache()->get('sariawanPanasDalamNewest-' . $userId);
            } else {
                $sariawanPanasDalamNewest = cache()->remember('sariawanPanasDalamNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '3')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products sariawan dan panas dalam failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('pegalLinuStaminaNewest-' . $userId)) {
                $pegalLinuStaminaNewest = cache()->get('pegalLinuStaminaNewest-' . $userId);
            } else {
                $pegalLinuStaminaNewest = cache()->remember('pegalLinuStaminaNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '4')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products pegal linu dan panas dalam failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('produkWanitaNewest-' . $userId)) {
                $produkWanitaNewest = cache()->get('produkWanitaNewest-' . $userId);
            } else {
                $produkWanitaNewest = cache()->remember('produkWanitaNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '5')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products peroduk wanita failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('permenNewest-' . $userId)) {
                $permenNewest = cache()->get('permenNewest-' . $userId);
            } else {
                $permenNewest = cache()->remember('permenNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '6')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products permen failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbaMojoNewest-' . $userId)) {
                $herbaMojoNewest = cache()->get('herbaMojoNewest-' . $userId);
            } else {
                $herbaMojoNewest = cache()->remember('herbaMojoNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '7')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herba mojo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbanaNewest-' . $userId)) {
                $herbanaNewest = cache()->get('herbanaNewest-' . $userId);
            } else {
                $herbanaNewest = cache()->remember('herbanaNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '8')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbana failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduNewest-' . $userId)) {
                $maduNewest = cache()->get('maduNewest-' . $userId);
            } else {
                $maduNewest = cache()->remember('maduNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '9')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products madu failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshcareNewest-' . $userId)) {
                $freshcareNewest = cache()->get('freshcareNewest-' . $userId);
            } else {
                $freshcareNewest = cache()->remember('freshcareNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '10')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshcare failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('hotinNewest-' . $userId)) {
                $hotinNewest = cache()->get('hotinNewest-' . $userId);
            } else {
                $hotinNewest = cache()->remember('hotinNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '11')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products hotin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduTjNewest-' . $userId)) {
                $maduTjNewest = cache()->get('maduTjNewest-' . $userId);
            } else {
                $maduTjNewest = cache()->remember('maduTjNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '12')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products maduTj failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('tresnojoyoNewest-' . $userId)) {
                $tresnojoyoNewest = cache()->get('tresnojoyoNewest-' . $userId);
            } else {
                $tresnojoyoNewest = cache()->remember('tresnojoyoNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '13')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products tresnojoyo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('bebioNewest-' . $userId)) {
                $bebioNewest = cache()->get('bebioNewest-' . $userId);
            } else {
                $bebioNewest = cache()->remember('bebioNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '14')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products bebio failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshlivingNewest-' . $userId)) {
                $freshlivingNewest = cache()->get('freshlivingNewest-' . $userId);
            } else {
                $freshlivingNewest = cache()->remember('freshlivingNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '15')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshliving failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('mywellNewest-' . $userId)) {
                $mywellNewest = cache()->get('mywellNewest-' . $userId);
            } else {
                $mywellNewest = cache()->remember('mywellNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '16')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products mywell failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbalNewest-' . $userId)) {
                $herbalNewest = cache()->get('herbalNewest-' . $userId);
            } else {
                $herbalNewest = cache()->remember('herbalNewest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terbaru', '1')
                        ->where('category_id', '17')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('masukAnginPopular-' . $userId)) {
                $masukAnginPopular = cache()->get('masukAnginPopular-' . $userId);
            } else {
                $masukAnginPopular = cache()->remember('masukAnginPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '1')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id', 'product_review.product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products masuk angin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('obatBatukPopular-' . $userId)) {
                $obatBatukPopular = cache()->get('obatBatukPopular-' . $userId);
            } else {
                $obatBatukPopular = cache()->remember('obatBatukPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '2')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products obat batuk failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('sariawanPanasDalamPopular-' . $userId)) {
                $sariawanPanasDalamPopular = cache()->get('sariawanPanasDalamPopular-' . $userId);
            } else {
                $sariawanPanasDalamPopular = cache()->remember('sariawanPanasDalamPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '3')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products sariawan dan panas dalam failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('pegalLinuStaminaPopular-' . $userId)) {
                $pegalLinuStaminaPopular = cache()->get('pegalLinuStaminaPopular-' . $userId);
            } else {
                $pegalLinuStaminaPopular = cache()->remember('pegalLinuStaminaPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '4')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products pegal linu dan stamina failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('produkWanitaPopular-' . $userId)) {
                $produkWanitaPopular = cache()->get('produkWanitaPopular-' . $userId);
            } else {
                $produkWanitaPopular = cache()->remember('produkWanitaPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '5')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products produk wanita failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('permenPopular-' . $userId)) {
                $permenPopular = cache()->get('permenPopular-' . $userId);
            } else {
                $permenPopular = cache()->remember('permenPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '6')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products permen failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbaMojoPopular-' . $userId)) {
                $herbaMojoPopular = cache()->get('herbaMojoPopular-' . $userId);
            } else {
                $herbaMojoPopular = cache()->remember('herbaMojoPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '7')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herba mojo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbanaPopular-' . $userId)) {
                $herbanaPopular = cache()->get('herbanaPopular-' . $userId);
            } else {
                $herbanaPopular = cache()->remember('herbanaPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '8')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbana failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduPopular-' . $userId)) {
                $maduPopular = cache()->get('maduPopular-' . $userId);
            } else {
                $maduPopular = cache()->remember('maduPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '9')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products madu failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshcarePopular-' . $userId)) {
                $freshcarePopular = cache()->get('freshcarePopular-' . $userId);
            } else {
                $freshcarePopular = cache()->remember('freshcarePopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '10')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshcare failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('hotinPopular-' . $userId)) {
                $hotinPopular = cache()->get('hotinPopular-' . $userId);
            } else {
                $hotinPopular = cache()->remember('hotinPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '11')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products hotin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduTjPopular-' . $userId)) {
                $maduTjPopular = cache()->get('maduTjPopular-' . $userId);
            } else {
                $maduTjPopular = cache()->remember('maduTjPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '12')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products maduTj failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('tresnojoyoPopular-' . $userId)) {
                $tresnojoyoPopular = cache()->get('tresnojoyoPopular-' . $userId);
            } else {
                $tresnojoyoPopular = cache()->remember('tresnojoyoPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '13')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products tresnojoyo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('bebioPopular-' . $userId)) {
                $bebioPopular = cache()->get('bebioPopular-' . $userId);
            } else {
                $bebioPopular = cache()->remember('bebioPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '14')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products bebio failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshlivingPopular-' . $userId)) {
                $freshlivingPopular = cache()->get('freshlivingPopular-' . $userId);
            } else {
                $freshlivingPopular = cache()->remember('freshlivingPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '15')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshliving failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('mywellPopular-' . $userId)) {
                $mywellPopular = cache()->get('mywellPopular-' . $userId);
            } else {
                $mywellPopular = cache()->remember('mywellPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '16')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products mywell failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbalPopular-' . $userId)) {
                $herbalPopular = cache()->get('herbalPopular-' . $userId);
            } else {
                $herbalPopular = cache()->remember('herbalPopular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->where('product_availability.status', '1')
                        ->where('status_terlaris', '1')
                        ->where('category_id', '17')
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($array)
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            $arrayId = $this->orderDetail
                ->select('id')
                ->whereNotNull('product_id')
                ->whereRaw('id in (select max(id) from order_detail group by product_id)')
                ->orderBy('id', 'desc')
                ->pluck('id');
            // return response()->json($arrayId);

            // array_walk($array, function(&$value, $key) { $value = 'products.' . $value; } );

            if (cache()->has('masukAnginRecent-' . $userId)) {
                $masukAnginRecent = cache()->get('masukAnginRecent-' . $userId);
            } else {
                $masukAnginRecent = cache()->remember('masukAnginRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '1')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id', 'product_review.product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products masuk angin failed',
                'data'    => $e->getMessage() . "\n"
            ], 500);
        }

        try {
            if (cache()->has('obatBatukRecent-' . $userId)) {
                $obatBatukRecent = cache()->get('obatBatukRecent-' . $userId);
            } else {
                $obatBatukRecent = cache()->remember('obatBatukRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '2')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products obat batuk failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('sariawanPanasDalamRecent-' . $userId)) {
                $sariawanPanasDalamRecent = cache()->get('sariawanPanasDalamRecent-' . $userId);
            } else {
                $sariawanPanasDalamRecent = cache()->remember('sariawanPanasDalamRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '3')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products sariawan dan panas dalam failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('pegalLinuStaminaRecent-' . $userId)) {
                $pegalLinuStaminaRecent = cache()->get('pegalLinuStaminaRecent-' . $userId);
            } else {
                $pegalLinuStaminaRecent = cache()->remember('pegalLinuStaminaRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '4')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products pegal linu dan stamina failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('produkWanitaRecent-' . $userId)) {
                $produkWanitaRecent = cache()->get('produkWanitaRecent-' . $userId);
            } else {
                $produkWanitaRecent = cache()->remember('produkWanitaRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '5')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products produk wanita failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('permenRecent-' . $userId)) {
                $permenRecent = cache()->get('permenRecent-' . $userId);
            } else {
                $permenRecent = cache()->remember('permenRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '6')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products permen failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbaMojoRecent-' . $userId)) {
                $herbaMojoRecent = cache()->get('herbaMojoRecent-' . $userId);
            } else {
                $herbaMojoRecent = cache()->remember('herbaMojoRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '7')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herba mojo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbanaRecent-' . $userId)) {
                $herbanaRecent = cache()->get('herbanaRecent-' . $userId);
            } else {
                $herbanaRecent = cache()->remember('herbanaRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '8')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbana failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduRecent-' . $userId)) {
                $maduRecent = cache()->get('maduRecent-' . $userId);
            } else {
                $maduRecent = cache()->remember('maduRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '9')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products madu failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshcareRecent-' . $userId)) {
                $freshcareRecent = cache()->get('freshcareRecent-' . $userId);
            } else {
                $freshcareRecent = cache()->remember('freshcareRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '10')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshcare failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('hotinRecent-' . $userId)) {
                $hotinRecent = cache()->get('hotinRecent-' . $userId);
            } else {
                $hotinRecent = cache()->remember('hotinRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '11')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products hotin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('maduTjRecent-' . $userId)) {
                $maduTjRecent = cache()->get('maduTjRecent-' . $userId);
            } else {
                $maduTjRecent = cache()->remember('maduTjRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '12')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products maduTj failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('tresnojoyoRecent-' . $userId)) {
                $tresnojoyoRecent = cache()->get('tresnojoyoRecent-' . $userId);
            } else {
                $tresnojoyoRecent = cache()->remember('tresnojoyoRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '13')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products tresnojoyo failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('bebioRecent-' . $userId)) {
                $bebioRecent = cache()->get('bebioRecent-' . $userId);
            } else {
                $bebioRecent = cache()->remember('bebioRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '14')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products bebio failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('freshlivingRecent-' . $userId)) {
                $freshlivingRecent = cache()->get('freshlivingRecent-' . $userId);
            } else {
                $freshlivingRecent = cache()->remember('freshlivingRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '15')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products freshliving failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('mywellRecent-' . $userId)) {
                $mywellRecent = cache()->get('mywellRecent-' . $userId);
            } else {
                $mywellRecent = cache()->remember('mywellRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '16')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products mywell failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbalRecent-' . $userId)) {
                $herbalRecent = cache()->get('herbalRecent-' . $userId);
            } else {
                $herbalRecent = cache()->remember('herbalRecent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
                    return $this->products
                        ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
                        ->whereIn('order_detail.id', $arrayId)
                        ->where('status_faktur', 'F')
                        ->where('product_availability.status', '1')
                        ->where('products.category_id', '17')
                        ->where('orders.customer_id', $userId)
                        ->where('product_availability.site_code', $siteCode)
                        ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                        ->with(['price' => function ($query) use ($arrayPrice) {
                            $query->select($arrayPrice)
                                ->join('products', 'products.id', '=', 'product_prices.product_id');
                        }, 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'))
                                ->groupBy('product_id');
                        }, 'cart' => function ($query) use ($userId, $arrayCart) {
                            $query->where('user_id', $userId)
                                ->select($arrayCart);
                        }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                            $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                                ->select($arrayPromoSku)
                                ->where('promos.status', 1)
                                ->limit(1);
                        }])
                        ->select($arrayProductPromo)
                        ->distinct('order_detail.product_id')
                        ->paginate(10);
                });
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        $allData = [
            'masukAngin'                => [$masukAngin],
            'obatBatuk'                => [$obatBatuk],
            'sariawanPanasDalam'          => [$sariawanPanasDalam],
            'pegalLinuStamina'          => [$pegalLinuStamina],
            'produkWanita'          => [$produkWanita],
            'permen'          => [$permen],
            'herbaMojo'          => [$herbaMojo],
            'herbana'          => [$herbana],
            'madu'          => [$madu],
            'freshcare'          => [$freshcare],
            'hotin'          => [$hotin],
            'maduTj'          => [$maduTj],
            'tresnojoyo'          => [$tresnojoyo],
            'bebio'          => [$bebio],
            'freshliving'          => [$freshliving],
            'mywell'          => [$mywell],
            'herbal'          => [$herbal],
            'masukAnginNewest'                => [$masukAnginNewest],
            'obatBatukNewest'                => [$obatBatukNewest],
            'sariawanPanasDalamNewest'          => [$sariawanPanasDalamNewest],
            'pegalLinuStaminaNewest'          => [$pegalLinuStaminaNewest],
            'produkWanitaNewest'          => [$produkWanitaNewest],
            'permenNewest'          => [$permenNewest],
            'herbaMojoNewest'          => [$herbaMojoNewest],
            'herbanaNewest'          => [$herbanaNewest],
            'maduNewest'          => [$maduNewest],
            'freshcareNewest'          => [$freshcareNewest],
            'hotinNewest'          => [$hotinNewest],
            'maduTjNewest'          => [$maduTjNewest],
            'tresnojoyoNewest'          => [$tresnojoyoNewest],
            'bebioNewest'          => [$bebioNewest],
            'freshlivingNewest'          => [$freshlivingNewest],
            'mywellNewest'          => [$mywellNewest],
            'herbalNewest'          => [$herbalNewest],
            'masukAnginPopular'                => [$masukAnginPopular],
            'obatBatukPopular'                => [$obatBatukPopular],
            'sariawanPanasDalamPopular'          => [$sariawanPanasDalamPopular],
            'pegalLinuStaminaPopular'          => [$pegalLinuStaminaPopular],
            'produkWanitaPopular'          => [$produkWanitaPopular],
            'permenPopular'          => [$permenPopular],
            'herbaMojoPopular'          => [$herbaMojoPopular],
            'herbanaPopular'          => [$herbanaPopular],
            'maduPopular'          => [$maduPopular],
            'freshcarePopular'          => [$freshcarePopular],
            'hotinPopular'          => [$hotinPopular],
            'maduTjPopular'          => [$maduTjPopular],
            'tresnojoyoPopular'          => [$tresnojoyoPopular],
            'bebioPopular'          => [$bebioPopular],
            'freshlivingPopular'          => [$freshlivingPopular],
            'mywellPopular'          => [$mywellPopular],
            'herbalPopular'          => [$herbalPopular],
            'masukAnginRecent'                => [$masukAnginRecent],
            'obatBatukRecent'                => [$obatBatukRecent],
            'sariawanPanasDalamRecent'          => [$sariawanPanasDalamRecent],
            'pegalLinuStaminaRecent'          => [$pegalLinuStaminaRecent],
            'produkWanitaRecent'          => [$produkWanitaRecent],
            'permenRecent'          => [$permenRecent],
            'herbaMojoRecent'          => [$herbaMojoRecent],
            'herbanaRecent'          => [$herbanaRecent],
            'maduRecent'          => [$maduRecent],
            'freshcareRecent'          => [$freshcareRecent],
            'hotinRecent'          => [$hotinRecent],
            'maduTjRecent'          => [$maduTjRecent],
            'tresnojoyoRecent'          => [$tresnojoyoRecent],
            'bebioRecent'          => [$bebioRecent],
            'freshlivingRecent'          => [$freshlivingRecent],
            'mywellRecent'          => [$mywellRecent],
            'herbalRecent'          => [$herbalRecent],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Load all successfully',
            'data'    => $allData
        ], 200);
    }

    public function redeem(Request $request)
    {
        try {                                                                   // check token
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
            $userId         = auth()->user()->id;                                       // get user id
            $products       = $this->products->query();
            $app_version    = auth()->user()->app_version;

            if ($app_version == '1.1.1') {
                $array      = ['id', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $array      = ['products.id', 'status', 'kodeprod', 'name', 'description', 'image','image_backup', 'kecil', 'status_renceng', 'status_redeem', 'redeem_point', 'redeem_desc', 'redeem_snk', 'products.created_at'];
            }
            // $arrayPrice     = ['product_id', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt'];

            $products       = $products
                // ->where('status', '1')
                ->where('status_redeem', '1')
                // ->where('product_availability.site_code', $siteCode)
                // ->join('product_availability', 'product_availability.product_id' ,'=', 'products.id')
                ->select($array)
                ->addSelect(DB::raw("'1' as qty, 'redeem product' as notes"))
                ->limit(10);

            if ($request->order == 'asc') {
                $products   = $products->orderBy('products.created_at', 'asc');
            } else if ($request->order == 'desc') {
                $products   = $products->orderBy('products.created_at', 'desc');
            }

            $products       = $products->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Get products redeem point successfully',
                'data'    => $products
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get products redeem point failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }

    public function varian(Request $request, $id)
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
            $salurCode = Auth::user()->salur_code;
            // Kondisi untuk harga biar sesuai dengan user yang login
            if ($salurCode == 'SW' || $salurCode == 'WS' || $salurCode == 'SO') {
                $arrayPrice = DB::raw("
                                        product_prices.id,
                                        product_id,
                                        harga_grosir_mt,
                                        harga_promosi_coret_ritel_gt,
                                        harga_promosi_coret_grosir_mt,
                                        products.brand_id,
                                        harga_ritel_gt as ritel_gt,
                                        (CASE
                                            WHEN products.brand_id::integer=005 THEN harga_ritel_gt
                                            WHEN products.brand_id::integer=001 THEN harga_ritel_gt
                                            ELSE harga_grosir_mt
                                            END) as harga_ritel_gt
                                    ");
            } else {
                $arrayPrice = DB::raw("
                                        product_prices.id,
                                        product_id,
                                        harga_ritel_gt,
                                        harga_grosir_mt,
                                        harga_promosi_coret_ritel_gt,
                                        harga_promosi_coret_grosir_mt,
                                        products.brand_id,
                                        harga_ritel_gt as rt_backup
                                    ");
            };
            // array for select promo_sku
            $arrayPromoSku  = ['promo_skus.product_id', 'promo_skus.promo_id', 'promos.title', 'promos.id'];

            $siteCode   = Auth::user()->site_code;
            $subgroup   = $this->products->select('subgroup')->where('id', $id)->first()->subgroup;
            $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image','image_backup', 'brand_id', 'subgroup', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
            $varian     = $this->products
                ->where('product_availability.status', '1')
                ->where('product_availability.site_code', $siteCode)
                ->where('subgroup', $subgroup)
                // ->orderBy('created_at', 'desc')
                ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                ->with(['price' => function ($query) use ($arrayPrice) {
                    $query->select($arrayPrice)
                        ->join('products', 'products.id', '=', 'product_prices.product_id');
                }, 'promo_sku' => function ($query) use ($arrayPromoSku) {
                    $query->leftJoin('promos', 'promos.id', '=', 'promo_id')
                        ->select($arrayPromoSku)
                        ->where('promos.status', 1);
                }])
                ->select($array)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Get varian product successfully',
                'data'    => $varian
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Get varian product failed',
                'data'    => $e->getMessage()
            ], 500);
        }
    }
}
