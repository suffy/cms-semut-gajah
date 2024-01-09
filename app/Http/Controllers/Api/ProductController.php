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
    //     return ['id', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online', 'kecil', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'status_renceng', 'created_at'];
    // }

    // // array for select product
    // private function arraySelectProductOld()
    // {
    //     return ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
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
                $array      = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at', 'products.type_status'];
                array_walk($array, function (&$value, $key) {
                    $value = 'products.' . $value;
                });
                $arrayProductPromo = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $array              = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at', 'products.type_status'];
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
                $array      = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
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
                $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
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
            $array      = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            array_walk($array, function (&$value, $key) {
                $value = 'products.' . $value;
            });
            $arrayProductPromo = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
        } else {
            $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
            $arrayProductPromo = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'products.kodeprod', 'products.name', 'products.description', 'products.image', 'products.brand_id', 'products.category_id', 'products.satuan_online',  'products.kecil', 'products.konversi_sedang_ke_kecil', 'products.status_promosi_coret', 'products.status_herbana', 'products.status_terlaris', 'products.status_terbaru', 'products.status_renceng', 'products.created_at'];
        }
        $arrayCart      = $this->arraySelectCart();
        $arrayPromoSku  = $this->arraySelectPromoSku();
        $arrayPrice     = $this->arraySelectPrice();

        try {
            if (cache()->has('herbal-' . $userId)) {
                $herbal = cache()->get('herbal-' . $userId);
            } else {
                $herbal = cache()->remember('herbal-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('supmul-' . $userId)) {
                $supmul = cache()->get('supmul-' . $userId);
            } else {
                $supmul = cache()->remember('supmul-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products suplemen multivitamin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('foodbeverage-' . $userId)) {
                $foodbeverage = cache()->get('foodbeverage-' . $userId);
            } else {
                $foodbeverage = cache()->remember('foodbeverage-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products food beverage failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('minyakbalsem-' . $userId)) {
                $minyakbalsem = cache()->get('minyakbalsem-' . $userId);
            } else {
                $minyakbalsem = cache()->remember('minyakbalsem-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products minyak balsem failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbal_newest-' . $userId)) {
                $herbal_newest = cache()->get('herbal_newest-' . $userId);
            } else {
                $herbal_newest = cache()->remember('herbal_newest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('supmul_newest-' . $userId)) {
                $supmul_newest = cache()->get('supmul_newest-' . $userId);
            } else {
                $supmul_newest = cache()->remember('supmul_newest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products suplemen multivitamin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('foodbeverage_newest-' . $userId)) {
                $foodbeverage_newest = cache()->get('foodbeverage_newest-' . $userId);
            } else {
                $foodbeverage_newest = cache()->remember('foodbeverage_newest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products food beverage failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('minyakbalsem_newest-' . $userId)) {
                $minyakbalsem_newest = cache()->get('minyakbalsem_newest-' . $userId);
            } else {
                $minyakbalsem_newest = cache()->remember('minyakbalsem_newest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products minyak balsem failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('herbal_popular-' . $userId)) {
                $herbal_popular = cache()->get('herbal_popular-' . $userId);
            } else {
                $herbal_popular = cache()->remember('herbal_popular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('supmul_popular-' . $userId)) {
                $supmul_popular = cache()->get('supmul_popular-' . $userId);
            } else {
                $supmul_popular = cache()->remember('supmul_popular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products suplemen multivitamin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('foodbeverage_popular-' . $userId)) {
                $foodbeverage_popular = cache()->get('foodbeverage_popular-' . $userId);
            } else {
                $foodbeverage_popular = cache()->remember('foodbeverage_popular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products food beverage failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('minyabalsem_popular-' . $userId)) {
                $minyakbalsem_popular = cache()->get('minyakbalsem_popular-' . $userId);
            } else {
                $minyakbalsem_popular = cache()->remember('minyakbalsem_popular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products minyak balsem failed',
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

            if (cache()->has('herbal_recent-' . $userId)) {
                $herbal_recent = cache()->get('herbal_recent-' . $userId);
            } else {
                $herbal_recent = cache()->remember('herbal_recent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products herbal failed',
                'data'    => $e->getMessage() . "\n"
            ], 500);
        }

        try {
            if (cache()->has('supmul_recent-' . $userId)) {
                $supmul_recent = cache()->get('supmul_recent-' . $userId);
            } else {
                $supmul_recent = cache()->remember('supmul_recent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products suplemen multivitamin failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('foodbeverage_recent-' . $userId)) {
                $foodbeverage_recent = cache()->get('foodbeverage_recent-' . $userId);
            } else {
                $foodbeverage_recent = cache()->remember('foodbeverage_recent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products food beverage failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        try {
            if (cache()->has('minyakbalsem_recent-' . $userId)) {
                $minyakbalsem_recent = cache()->get('minyakbalsem_recent-' . $userId);
            } else {
                $minyakbalsem_recent = cache()->remember('minyakbalsem_recent-' . $userId, 60, function () use ($arrayId, $userId, $arrayProductPromo, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode) {
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
                'message' => 'Get products minyak balsem failed',
                'data'    => $e->getMessage()
            ], 500);
        }

        $allData = [
            'herbal'                => [$herbal],
            'supmul'                => [$supmul],
            'foodbeverage'          => [$foodbeverage],
            'minyakbalsem'          => [$minyakbalsem],
            'herbal_newest'         => [$herbal_newest],
            'supmul_newest'         => [$supmul_newest],
            'foodbeverage_newest'   => [$foodbeverage_newest],
            'minyakbalsem_newest'   => [$minyakbalsem_newest],
            'herbal_popular'        => [$herbal_popular],
            'supmul_popular'        => [$supmul_popular],
            'foodbeverage_popular'  => [$foodbeverage_popular],
            'minyakbalsem_popular'  => [$minyakbalsem_popular],
            'herbal_recent'         => [$herbal_recent],
            'supmul_recent'         => [$supmul_recent],
            'foodbeverage_recent'   => [$foodbeverage_recent],
            'minyakbalsem_recent'   => [$minyakbalsem_recent]
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
                $array      = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
            } else {
                $array      = ['products.id', 'status', 'kodeprod', 'name', 'description', 'image', 'kecil', 'status_renceng', 'status_redeem', 'redeem_point', 'redeem_desc', 'redeem_snk', 'products.created_at'];
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
            $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'subgroup', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
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
