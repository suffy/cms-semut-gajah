<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\OrderDetail;
use App\Product;
use App\ProductOfferItem;
use App\RecentProduct;
use App\Banner;
use App\Promo;
use App\Category;
use App\TopSpender;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LoadAllController extends Controller
{
    protected $products, $orders, $orderDetail, $productsOfferItem, $banners, $promo, $categories, $topSpender;

    public function __construct(Product $product, OrderDetail $orderDetail, ProductOfferItem $productOfferItem, RecentProduct $recentProduct, Banner $banner, Promo $promo, Category $category, TopSpender $topSpender)
    {
        $this->products             = $product;
        $this->orderDetail          = $orderDetail;
        $this->productsOfferItem    = $productOfferItem;
        $this->recentProduct        = $recentProduct;
        $this->banners              = $banner;
        $this->promo                = $promo;
        $this->categories           = $category;
        $this->topSpender          = $topSpender;
    }

    // private function arraySelectProduct()
    // {
    //     return ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name','description', 'image', 'brand_id', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];
    // }

    // // array for select product
    // private function arraySelectProductOld()
    // {
    //     return ['id', 'kodeprod', 'name','description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'status_terbaru', 'created_at', 'updated_at'];
    // }


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

        $userId         = auth()->user()->id;
        $siteCode       = auth()->user()->site_code;
        $date           = Carbon::now();
        $app_version    = Auth::user()->app_version;
        $salurCode      = auth()->user()->salur_code;
        $kode_type      = Auth::user()->kode_type;

        // array for select shoppingcart
        $arrayCart      = ['user_id', 'product_id', 'qty'];
        // array for select promo_sku
        $arrayPromoSku  = ['promo_skus.product_id', 'promo_skus.promo_id', 'promos.title', 'promos.id'];
        // $arrayPrice     = ['product_id', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt'];

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

        if ($app_version == '1.1.1') {
            $array      = ['id', 'kodeprod', 'name', 'description', 'image_backup as image', 'brand_id', 'category_id', 'satuan_online', 'konversi_sedang_ke_kecil', 'status', 'status_herbana', 'status_promosi_coret', 'status_terlaris', 'type_status', 'status_terbaru', 'created_at', 'updated_at'];

            try {
                if (cache()->has('banners-' . $userId)) {
                    $banners = cache()->get('banners-' . $userId);
                } else {
                    $banners = cache()->remember('banners-' . $userId, 60, function () {
                        return $this->banners->where('status', 1)->get();
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get banners failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('promos-' . $userId)) {
                    $promos = cache()->get('promos-' . $userId);
                } else {
                    $promos = cache()->remember('promos-' . $userId, 30, function () {
                        return $this->promo
                            ->where('status', '1')
                            ->with(['sku', 'reward.product'])
                            ->paginate(10);
                    });
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get promo failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('categories-' . $userId)) {
                    $categories = cache()->get('categories-' . $userId);
                } else {
                    $categories = cache()->remember('categories-' . $userId, 50, function () {
                        return $this->categories->get();
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get product category failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('products_newest-' . $userId)) {
                    $products_newest = cache()->get('products_newest-' . $userId);
                } else {
                    $products_newest = cache()->remember('products_newest-' . $userId, 30, function () use ($array) {
                        return $this->products
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->where('status', '1')
                            ->where('status_terbaru', '1')
                            ->orderBy('created_at', 'desc')
                            ->with(['price', 'product_stock', 'review' => function ($query) {
                                $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'));
                                $query->groupBy('product_id');
                            }])
                            ->select($array)
                            ->paginate(10);
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products newest failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('products_popular-' . $userId)) {
                    $products_popular = cache()->get('products_popular-' . $userId);
                } else {
                    $products_popular = cache()->remember('products_popular-' . $userId, 30, function () use ($array) {
                        return $this->products
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->where('status', '1')
                            ->where('status_terlaris', '1')
                            ->orderBy('created_at', 'desc')
                            ->with(['price', 'product_stock', 'review' => function ($query) {
                                $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'));
                                $query->groupBy('product_id');
                            }])
                            ->select($array)
                            ->paginate(10);
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products popular failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                $products       = $this->products->query();
                $recentProducts = $this->recentProduct->query();

                if (cache()->has('products_recomen-' . $userId)) {
                    $merge = cache()->get('products_recomen-' . $userId);
                } else {
                    $category = $recentProducts                                                                 //  get recent view product from user
                        ->where('user_id', $userId)
                        ->where('status', 1)
                        ->join('products', 'products.id', '=', 'recent_products.product_id')
                        ->select('products.category_id as id', 'products.slug', 'products.brand_id', 'recent_products.created_at')
                        ->latest()
                        ->first();
                    if (is_null($category)) {
                        $category = $this->products
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->where('status', 1)
                            ->where('status_terlaris', 1)
                            ->select('category_id as id', 'slug', 'brand_id')
                            ->first();
                    }

                    $productsBycategory = $products                                                             //  get product by category by recent view below
                        ->orwhere(function ($q) use ($kode_type) {
                            if (!is_null($kode_type)) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%')
                                    ->orWhere('type_status', null);
                            }
                        })
                        // ->where('category_id', $category->id)
                        ->where('status', 1)
                        // ->orderBy(DB::raw('RAND()'))
                        ->with(['price', 'product_stock', 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'));
                            $query->groupBy('product_id');
                        }])
                        ->select($array)
                        ->get();
                    // ->toArray();

                    $name  = explode('-', $category->slug);                                                     //  get name product from recent view below

                    $productsByname = $products                                                                 //  get product by similar name from recent view
                        ->orwhere(function ($q) use ($kode_type) {
                            if (!is_null($kode_type)) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%')
                                    ->orWhere('type_status', null);
                            }
                        })
                        ->where('name', 'like', '%' . $name[0] . '%')
                        ->where('status', 1)
                        // ->orderBy(DB::raw('RAND()'))
                        ->with(['price', 'product_stock', 'review' => function ($query) {
                            $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'));
                            $query->groupBy('product_id');
                        }])
                        ->select($array)
                        ->get();
                    // ->toArray();

                    if (is_null($productsByname)) {                                                              //  check if product by similar name null
                        $productsByname = $products                                                                    //  use product by similar brand_id
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->where('brand_id', $category->brand_id)
                            ->where('status', 1)
                            // ->orderBy(DB::raw('RAND()'))
                            ->with(['price', 'product_stock', 'review' => function ($query) {
                                $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'));
                                $query->groupBy('product_id');
                            }])
                            ->select($array)
                            ->get();
                        // ->toArray();
                    }

                    $merge              = cache()->remember('products_recomen-' . $userId, 8, function () use ($productsBycategory, $productsByname) {
                        return $productsBycategory->merge($productsByname)->unique()->shuffle()->toArray();
                    });
                }

                $products_recomen   = $this->paginate_array($merge);
            } catch (\Exception $e) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'get recomen product, failed',
                    'data'      => $e->getMessage()
                ], 500);
            }

            try {
                array_walk($array, function (&$value, $key) {
                    $value = 'products.' . $value;
                });

                if (cache()->has('products_recent-' . $userId)) {
                    $products_recent = cache()->get('products_recent-' . $userId);
                } else {
                    $products_recent   = cache()->remember('products_recent-' . $userId, 10, function () use ($userId, $date, $array, $kode_type) {
                        return $this->products
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->join('recent_products', 'recent_products.product_id', '=', 'products.id')
                            // ->select('products.*')
                            ->where('recent_products.user_id', $userId)
                            ->where('products.status', '1')
                            ->where('recent_products.created_at', '>=', $date->subDays(15))
                            ->orderBy('recent_products.created_at', 'desc')
                            ->with(['price', 'product_stock', 'review' => function ($query) {
                                $query->select('product_id', DB::raw('ROUND(avg(star_review)::numeric, 1) as avg_rating'));
                                $query->groupBy('product_id');
                            }])
                            ->select($array)
                            ->paginate(10);
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products recent failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            $allData = [
                'products_recomen'  => [$products_recomen],
                'banners'           => [$banners],
                'promo'             => [$promos],
                'categories'        => [$categories],
                'products_newest'   => [$products_newest],
                'products_popular'  => [$products_popular],
                'products_recent'   => [$products_recent]
            ];
        } else {
            $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'kodeprod', 'name', 'description', 'image', 'brand_id', 'subgroup', 'category_id', 'satuan_online',  'kecil', 'konversi_sedang_ke_kecil', 'status_promosi_coret', 'status_herbana', 'status_terlaris', 'status_terbaru', 'status_renceng', 'products.created_at'];

            try {
                if ($app_version == '1.1.2') {
                    if (cache()->has('banners-' . $userId)) {
                        $banners = cache()->get('banners-' . $userId);
                    } else {
                        $banners = cache()->remember('banners-' . $userId, 60, function () {
                            return $this->banners->where('status', 1)->get();
                        });
                    }
                } else {
                    if (cache()->has('banners-' . $userId)) {
                        $banners = cache()->get('banners-' . $userId);
                    } else {
                        $banners = cache()->remember('banners-' . $userId, 60, function () {
                            $date       = Carbon::now()->format('Y-m-d');
                            $topSpender = $this->topSpender
                                ->select(DB::raw("id, title, description, banner, 'top_spender' as identity, '1' as status "))
                                ->where('start', '<=', $date)
                                ->where('end', '>=', $date)
                                ->with(['rank_reward' => function ($q) {
                                    $q->select('id', 'top_spender_id', 'pos', 'nominal');
                                }])
                                ->limit(1)
                                ->orderBy('created_at', 'DESC')
                                ->get();

                            $bannerPromo = $this->promo
                                ->select(DB::raw("id, title, banner, status, priority_bottom, priority_bottom_position, 'promo' as identity "))
                                ->where('status', 1)
                                ->where('priority_bottom', 1)
                                ->where('start', '<=', $date)
                                ->where('end', '>=', $date)
                                ->orderBy('priority_bottom_position', 'ASC')
                                ->orderBy('created_at', 'DESC')
                                // ->limit(3)
                                // ->select('id', 'title', 'banner', 'status')
                                ->get();

                            // get id promo from result bannerPromo
                            $idPromo = $bannerPromo->pluck('id');
                            $limit = null;
                            // count promo result
                            $countPromo = $bannerPromo->count();
                            // set limit promo result
                            if ($topSpender->isNotEmpty()) {
                                $limit          = 0;
                            } else {
                                $limit          = 1;
                            }

                            if ($countPromo < 3) {
                                if ($topSpender->isNotEmpty()) {
                                    $limit          = 3 - $bannerPromo->count();
                                } else {
                                    $limit          = 4 - $bannerPromo->count();
                                }
                            } elseif ($countPromo > 3) {
                                if ($topSpender->isNotEmpty()) {
                                    $limit          = 4 - $bannerPromo->count();
                                } else {
                                    $limit          = 5 - $bannerPromo->count();
                                }
                            }

                            // get promo result 2
                            $bannerPromo2   = $this->promo
                                ->select(DB::raw("id, title, banner, status, priority_bottom, priority_bottom_position, 'promo' as identity "))
                                ->where('status', 1)
                                ->orderBy('created_at', 'DESC')
                                ->whereNotIn('id', $idPromo)
                                ->inRandomOrder()
                                ->limit($limit)
                                ->get();
                            // concat banner promo
                            $bannerPromo = $bannerPromo->concat($bannerPromo2);

                            if ($topSpender->isNotEmpty()) {
                                $merged     = $topSpender->concat($bannerPromo);
                            } else {
                                $merged = $bannerPromo;
                            }
                            return $merged;
                        });
                    }
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get banners failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if ($app_version == '1.1.3' || $app_version == '1.1.4') {
                    if (cache()->has('promos-' . $userId)) {
                        $promos = cache()->get('promos-' . $userId);
                    } else {
                        $promos = cache()->remember('promos-' . $userId, 60, function () {
                            return $this->promo
                                ->where('status', '1')
                                ->with(['sku', 'reward.product'])
                                ->paginate(10);
                        });
                    }
                } else {
                    if (cache()->has('promos-' . $userId)) {
                        $promos = cache()->get('promos-' . $userId);
                    } else {
                        $promos = cache()->remember('promos-' . $userId, 60, function () {
                            $date       = Carbon::now()->format('Y-m-d');
                            $topSpender = $this->topSpender
                                ->select(DB::raw("id, title, description, banner, 'top_spender' as identity, '1' as status "))
                                ->where('end', '>', $date)
                                ->with(['rank_reward' => function ($q) {
                                    $q->select('id', 'top_spender_id', 'pos', 'nominal');
                                }])
                                ->get();
                            if ($topSpender) {
                                $banner     = $this->banners
                                    ->select(DB::raw("id, title, status, images as banner, 'banner' as identity "))
                                    ->where('status', 1)
                                    // ->limit(1)
                                    ->where('priority', 1)
                                    ->orderBy('priority_position', 'ASC')
                                    ->get();
                                // $limit = 15 - ((int) $banner->count() + (int) $topSpender->count());
                                $promo      = $this->promo
                                    ->select(DB::raw("*,'promo' as identity "))
                                    ->where('status', '1')
                                    ->with(['sku', 'reward.product'])
                                    ->where('priority_top', 1)
                                    ->orderBy('priority_top_position', 'ASC')
                                    // ->inRandomOrder()
                                    // ->limit($limit)
                                    ->get();
                                $merged     = $topSpender->concat($promo);
                                $promos     = $merged->concat($banner);
                                $data       = ['data' => $promos];
                                return $data;
                            } else {
                                $banner     = $this->banners
                                    ->select(DB::raw("id, title, status, images as banner, 'banner' as identity "))
                                    ->where('status', 1)
                                    // ->limit(1)
                                    ->where('priority', 1)
                                    ->orderBy('priority_position', 'ASC')
                                    ->get();
                                $limit = 15 - ((int) $banner->count());
                                $promo      = $this->promo
                                    ->select(DB::raw("*, 'promo' as identity "))
                                    ->where('status', '1')
                                    ->with(['sku', 'reward.product'])
                                    // ->inRandomOrder()
                                    // ->limit($limit)
                                    ->where('priority_top', 1)
                                    ->orderBy('priority_top_position', 'ASC')
                                    ->get();
                                $promos     = $promo->concat($banner);
                                $data = ['data' => $promos];
                                return $data;
                            }
                        });
                    }
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get promo failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('categories-' . $userId)) {
                    $categories = cache()->get('categories-' . $userId);
                } else {
                    $categories = cache()->remember('categories-' . $userId, 60, function () {
                        return $this->categories->get();
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get product category failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('products_newest-' . $userId)) {
                    $products_newest = cache()->get('products_newest-' . $userId);
                } else {
                    $products_newest = cache()->remember('products_newest-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode, $kode_type) {
                        return  $this->products
                            ->where('product_availability.status', '1')
                            ->where('status_terbaru', '1')
                            ->where('product_availability.site_code', $siteCode)
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            // ->orderBy('created_at', 'desc')
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
                            ->paginate(10);
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products newest failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('products_popular-' . $userId)) {
                    $products_popular = cache()->get('products_popular-' . $userId);
                } else {
                    $products_popular = cache()->remember('products_popular-' . $userId, 60, function () use ($array, $userId, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode, $kode_type) {
                        return $this->products
                            ->where('product_availability.status', '1')
                            ->where('status_terlaris', '1')
                            // ->orderBy('created_at', 'desc')
                            ->where('product_availability.site_code', $siteCode)
                            ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
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
                            ->paginate(10);
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products popular failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                $text = 0;
                $products       = $this->products->query();
                $recentProducts = $this->recentProduct->query();

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
                            ->where(function ($query) use ($kode_type) {
                                if (!is_null($kode_type)) {
                                    $query->where('type_status', 'like', '%' . $kode_type . '%');
                                    $query->orwhere('type_status', null);
                                }
                            })
                            ->where('product_availability.status', 1)
                            ->where('status_terlaris', 1)
                            ->select('category_id as id', 'slug', 'brand_id')
                            ->where('product_availability.site_code', $siteCode)
                            ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                            ->first();
                        // ->toSql();
                    }
                    $productsBycategory = $products                                                             //  get product by category by recent view below
                        ->orwhere(function ($q) use ($kode_type) {
                            if (!is_null($kode_type)) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%')
                                    ->orWhere('type_status', null);
                            }
                        })
                        ->where('product_availability.status', 1)
                        ->where(function ($query) use ($category) {
                            if ($category) {
                                $query->where('category_id', $category->id);
                            }
                        })
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
                    $name  = ($category) ?  explode('-', $category->slug) : "";                                                      //  get name product from recent view below
                    $productsByname = $products                                                                 //  get product by similar name from recent view
                        // ->where('product_availability.status', 1)
                        // ->whereRaw( 
                        //         "MATCH(products.name) AGAINST(?)", 
                        //         array($name[0])
                        // )
                        ->orwhere(function ($q) use ($kode_type) {
                            if (!is_null($kode_type)) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%')
                                    ->orWhere('type_status', null);
                            }
                        })
                        ->where(function ($query) use ($category, $name) {
                            if ($category) {
                                $query->where('products.name', 'like', '%' . $name[0] . '%');
                            } else {
                                $query->where('products.name', 'like', '%' . $name . '%');
                            }
                        })

                        // ->where('product_availability.site_code', $siteCode)
                        // ->join('product_availability', 'product_availability.product_id' ,'=', 'products.id')
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

                    if (is_null($productsByname)) {                                                              //  check if product by similar name null
                        $productsByname = $products                                                                    //  use product by similar brand_id
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
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

                    $merge              = cache()->remember('products_recomen-' . $userId, 60, function () use ($productsBycategory, $productsByname) {
                        return $productsBycategory->merge($productsByname)->unique()->shuffle()->toArray();
                    });
                }

                $products_recomen   = $this->paginate_array($merge);
            } catch (\Exception $e) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'get recomen product, failed',
                    'data'      => $e->getMessage()
                ], 500);
            }

            try {
                $array      = ['products.id', 'product_availability.site_code', 'product_availability.status as status', 'products.kodeprod', 'products.name', 'products.description', 'products.image', 'products.brand_id', 'products.category_id', 'products.satuan_online',  'products.kecil', 'products.konversi_sedang_ke_kecil', 'products.status_promosi_coret', 'products.status_herbana', 'products.status_terlaris', 'products.status_terbaru', 'products.status_renceng', 'products.created_at'];

                if (cache()->has('products_recent-' . $userId)) {
                    $products_recent = cache()->get('products_recent-' . $userId);
                } else {
                    $products_recent = cache()->remember('products_recent-' . $userId, 60, function () use ($userId, $date, $array, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode, $kode_type) {
                        return $this->products
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                            ->join('recent_products', 'recent_products.product_id', '=', 'products.id')
                            ->where('product_availability.site_code', $siteCode)
                            ->where('recent_products.user_id', $userId)
                            ->where('product_availability.status', '1')
                            ->where('recent_products.created_at', '>=', $date->subDays(15))
                            ->orderBy('recent_products.created_at', 'desc')
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
                            ->paginate(10);
                    });
                }
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products recent failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            try {
                if (cache()->has('products_promo-' . $userId)) {
                    $products_promo = cache()->get('products_promo-' . $userId);
                } else {
                    $products_promo = cache()->remember('products_promo-' . $userId, 60, function () use ($userId, $date, $array, $arrayCart, $arrayPromoSku, $arrayPrice, $siteCode, $kode_type) {
                        return $this->products
                            ->where(function ($q) use ($kode_type) {
                                $q->where('type_status', 'like', '%' . $kode_type . '%');
                                $q->orwhere('type_status', null);
                            })
                            ->join('promo_skus', 'promo_skus.product_id', '=', 'products.id')
                            ->join('promos', 'promos.id', '=', 'promo_skus.promo_id')
                            ->join('product_availability', 'product_availability.product_id', '=', 'products.id')
                            ->where('promos.status', 1)
                            ->where('product_availability.status', 1)
                            ->where('product_availability.site_code', $siteCode)
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
                            ->distinct()
                            ->paginate(10);
                    });
                }

                // return response()->json($productsPromo);
            } catch (Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Get products promo failed',
                    'data'    => $e->getMessage()
                ], 500);
            }

            $allData = [
                'products_recomen'  => [$products_recomen],
                'banners'           => [$banners],
                'promo'             => [$promos],
                'categories'        => [$categories],
                'products_newest'   => [$products_newest],
                'products_popular'  => [$products_popular],
                'products_recent'   => [$products_recent],
                'products_promo'    => [$products_promo]
            ];
        }
        // $tes = $this->products->with(['varian.price' => function($query) {
        // $query->select('product_id as id', 'name', 'subgroup', 'harga_ritel_gt', 'harga_grosir_mt', 'harga_semi_grosir_mt', 'harga_promosi_coret_ritel_gt', 'harga_promosi_coret_grosir_mt');
        // }])->limit(10)->get();
        // return response()->json($tes);
        return response()->json([
            'success' => true,
            'message' => 'Load all successfully',
            'data'    => $allData
        ], 200);
    }

    // method to paginate array object
    private function paginate_array($items, $perPage = 12, $page = null, $options = [])
    {
        $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total          = count($items);
        $currentpage    = $page;
        $offset         = ($currentpage * $perPage) - $perPage;
        $itemstoshow    = array_slice($items, $offset, $perPage);

        return new LengthAwarePaginator($itemstoshow, $total, $perPage);
    }
}
