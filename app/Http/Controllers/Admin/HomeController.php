<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use App\MappingSite;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\ProductReview;
use App\Salesman;
use App\Subscribe;
use App\User;
use App\MetaUser;
use App\Voucher;
use App\Promo;
use App\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    protected $users, $mappingSite, $salesman, $productCategories, $products, $productReview, $orders, $orderDetail, $vouchers, $subscribes, $promos, $logs, $metas;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(User $users, MappingSite $mappingSite, Salesman $salesman, Category $productCategories, Product $products, ProductReview $productReview, Order $orders, OrderDetail $orderDetail, Voucher $vouchers, Subscribe $subscribes, Promo $promos, Log $logs, MetaUser $metas)
    {
        //        $this->middleware('auth');
        $this->users                = $users;
        $this->mappingSite          = $mappingSite;
        $this->salesman             = $salesman;
        $this->productCategories    = $productCategories;
        $this->products             = $products;
        $this->productReview        = $productReview;
        $this->orders               = $orders;
        $this->orderDetail          = $orderDetail;
        $this->vouchers             = $vouchers;
        $this->subscribes           = $subscribes;
        $this->promos               = $promos;
        $this->logs                 = $logs;
        $this->metas                = $metas;
        ini_set('memory_limit', '-1');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (Auth::user()->account_role != 'distributor' && Auth::user()->account_role != 'distributor_luar' && Auth::user()->account_role != 'distributor_ho') {
            if ($request->start_date && $request->end_date != '') {
                if ($request->site_id) {
                    $start_date         = $request->start_date;
                    $end_date           = $request->end_date;
                    $base_customers     = $this->users->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })->whereBetween('created_at', [$request->start_date, $request->end_date])->where('account_type', 4)->where('site_code', $request->site_id);
                    $customers          = $base_customers->count();
                    $customer_registered = $this->users->whereNotNull('otp_verified_at')->whereBetween('otp_verified_at', [$request->start_date, $request->end_date])->where('account_type', 4)->where('site_code', $request->site_id)->count();
                    $mappingSites       = $this->mappingSite->get();
                    // $salesmen           = $this->salesman->where('kode', $request->site_id)->count();
                    $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->where('site_code', $request->site_id)->groupBy('salesman_code')->get());
                    $productCategories  = $this->productCategories->where('status', '1')->get();
                    $products           = $this->products->where('status', '1')->count();
                    $orders             = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', $request->site_id)->count();
                    $promos             = $this->promos->whereBetween('start', [$request->start_date, $request->end_date])->count();
                    // $point              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', $request->site_id)->sum('point');
                    $point              = $this->users->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', $request->site_id)->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                    $subscribes         = $this->subscribes
                        ->with('user')
                        ->whereHas('user', function ($query) use ($request) {
                            return $query->where('site_code', $request->site_id);
                        })
                        ->whereBetween('created_at', [$request->start_date, $request->end_date])
                        ->count();
                    // transactions
                    $totalOrders            = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', $request->site_id)->count();
                    $newOrders              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->where('site_code', $request->site_id)->count();
                    $newOrdersTotal         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->where('site_code', $request->site_id)->sum('payment_final');
                    $confirmedOrders        = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->where('site_code', $request->site_id)->count();
                    $confirmedOrdersTotal   = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->where('site_code', $request->site_id)->sum('payment_final');
                    $deliveryOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->where('site_code', $request->site_id)->count();
                    $deliveryOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->where('site_code', $request->site_id)->sum('payment_final');
                    $completeOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->where('site_code', $request->site_id)->count();
                    $completeOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->where('site_code', $request->site_id)->sum('payment_final');
                    $cancelOrders           = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->where('site_code', $request->site_id)->count();
                    $cancelOrdersTotal      = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->where('site_code', $request->site_id)->sum('payment_final');
                    // leaderboards
                    $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                        ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                        ->whereBetween('orders.created_at', [$request->start_date, $request->end_date])
                        ->where('orders.site_code', $request->site_id)
                        ->groupBy('users.customer_code', 'users.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topDistributor     = $this->orders
                        ->join('mapping_site', 'mapping_site.kode', '=', 'orders.site_code')
                        ->where('mapping_site.kode', $request->site_id)
                        ->groupBy('orders.site_code', 'mapping_site.kode', 'mapping_site.branch_name', 'mapping_site.nama_comp')
                        ->selectRaw('mapping_site.kode as kode, mapping_site.branch_name as branch, mapping_site.nama_comp as nama, sum(orders.payment_final) as total')
                        ->orderBy('total', 'desc')
                        // ->whereNull('status_partner')
                        // ->whereNull('partner_id')
                        ->whereBetween('orders.created_at', [$request->start_date, $request->end_date])
                        ->limit(5)
                        ->get();

                    $topProducts        = $this->orderDetail
                        ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->where('orders.site_code', $request->site_id)
                        ->groupBy('order_detail.product_id', 'products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topRatings         = $this->productReview
                        ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    $productTop = array();

                    // charts
                    $topProductsChart    = null;
                    $topRatingsChart     = null;

                    foreach ($topProducts as $topProduct) {
                        $topProductsChart['id'][]  = $topProduct->id;
                        $topProductsChart['product'][]  = $topProduct->product;
                        $topProductsChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsChart = json_encode($topProductsChart);

                    foreach ($topRatings as $topRating) {
                        $topRatingsChart['id'][]       = $topRating->id;
                        $topRatingsChart['product'][]       = $topRating->product;
                        $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsChart = json_encode($topRatingsChart);

                    array_push($productTop, $topProductsChart);
                    array_push($productTop, $topRatingsChart);

                    foreach ($productCategories as $cat) {
                        $topProductsCategory[$cat->id]      = $this->orderDetail
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('orders.site_code', $request->site_id)
                            ->where('products.category_id', $cat->id)
                            ->groupBy('order_detail.product_id', 'categories.name', 'products.id', 'products.name')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();


                        $topRatingsCategory[$cat->id]       = $this->productReview
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                            ->join('products', 'products.id', '=', 'product_review.product_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'categories.name', 'products.name')
                            ->orderBy('star_review', 'desc')
                            ->limit(10)
                            ->get();

                        // charts category
                        $topProductsCategoryChart    = null;
                        $topRatingsCategoryChart     = null;
                        $topProductsCategoryChart['category'][] = $cat->name;
                        $topRatingsCategoryChart['category'][]  = $cat->name;

                        foreach ($topProductsCategory[$cat->id] as $topProduct) {
                            $topProductsCategoryChart['id'][]  = $topProduct->id;
                            $topProductsCategoryChart['product'][]  = $topProduct->product;
                            $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                        }

                        $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                        foreach ($topRatingsCategory[$cat->id] as $topRating) {
                            $topRatingsCategoryChart['id'][]       = $topRating->id;
                            $topRatingsCategoryChart['product'][]       = $topRating->product;
                            $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                        }

                        $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                        array_push($productTop, $topProductsCategoryChart[$cat->id]);
                        array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                    }

                    $userApps = $this->users
                        ->where(function ($query) {
                            $query
                                ->where('status_blacklist', '0')
                                ->orWhere('status_blacklist', null);
                        })
                        ->where('account_type', '4')
                        ->where('account_role', 'user')
                        ->whereNotNull('otp_verified_at')
                        ->whereNotNull('site_code')
                        ->whereBetween('created_at', [$request->start_date, $request->end_date])
                        ->select('site_code', DB::raw('count(*) as total'))
                        ->groupBy('site_code')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    foreach ($userApps as $uapps) {
                        $userErp = $this->users
                            ->where(function ($query) {
                                $query
                                    ->where('status_blacklist', '0')
                                    ->orWhere('status_blacklist', null);
                            })
                            ->where('site_code', $uapps->site_code)
                            ->where('account_type', '4')
                            ->where('account_role', 'user')
                            ->whereNull('otp_verified_at')
                            ->whereBetween('created_at', [$request->start_date, $request->end_date])
                            ->select('site_code', DB::raw('count(*) as total'))
                            ->groupBy('site_code')
                            ->first();
                        $uapps->userErp = $userErp->total;
                    }

                    return view('admin/pages/dashboard', compact(
                        'customers',
                        'mappingSites',
                        'salesmen',
                        'productCategories',
                        'products',
                        'orders',
                        'subscribes',
                        'newOrders',
                        'confirmedOrders',
                        'deliveryOrders',
                        'completeOrders',
                        'cancelOrders',
                        'topProducts',
                        'topRatings',
                        'productTop',
                        'topDistributor',
                        'customer_registered',
                        'userApps',
                        'start_date',
                        'end_date',
                        'newOrdersTotal',
                        'confirmedOrdersTotal',
                        'deliveryOrdersTotal',
                        'completeOrdersTotal',
                        'cancelOrdersTotal',
                        'point',
                        'totalOrders',
                        'topUser',
                        'promos'
                    ));
                }
                $start_date         = $request->start_date;
                $end_date           = $request->end_date;
                $base_customers     = $this->users->where(function ($query) {
                    $query
                        ->where('status_blacklist', '0')
                        ->orWhere('status_blacklist', null);
                })->whereBetween('created_at', [$request->start_date, $request->end_date])->where('account_type', 4);
                $customers          = $base_customers->count();
                $customer_registered = $this->users->whereNotNull('otp_verified_at')->whereBetween('otp_verified_at', [$request->start_date, $request->end_date])->where('account_type', 4)->count();
                $mappingSites       = $this->mappingSite->get();
                // $salesmen           = $this->salesman->count();
                $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->groupBy('salesman_code')->get());
                $productCategories  = $this->productCategories->where('status', '1')->get();
                $products           = $this->products->where('status', '1')->count();
                $orders             = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->count();
                $promos             = $this->promos->whereBetween('start', [$request->start_date, $request->end_date])->count();
                // $point              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->sum('point');
                $point              = $this->users->whereBetween('created_at', [$request->start_date, $request->end_date])->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                $subscribes         = $this->subscribes->whereBetween('created_at', [$request->start_date, $request->end_date])->count();
                // transactions
                $totalOrders            = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->count();
                $newOrders              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->count();
                $newOrdersTotal         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->sum('payment_final');
                $confirmedOrders        = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->count();
                $confirmedOrdersTotal   = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->sum('payment_final');
                $deliveryOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->count();
                $deliveryOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->sum('payment_final');
                $completeOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->count();
                $completeOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->sum('payment_final');
                $cancelOrders           = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->count();
                $cancelOrdersTotal      = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->sum('payment_final');
                // leaderboards
                $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                    ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                    ->whereBetween('orders.created_at', [$request->start_date, $request->end_date])
                    ->groupBy('users.customer_code', 'users.name')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get();
                $topDistributor     = $this->orders
                    ->join('mapping_site', 'mapping_site.kode', '=', 'orders.site_code')
                    ->groupBy('orders.site_code', 'mapping_site.kode', 'mapping_site.branch_name', 'mapping_site.nama_comp')
                    ->selectRaw('mapping_site.kode as kode, mapping_site.branch_name as branch, mapping_site.nama_comp as nama, sum(orders.payment_final) as total')
                    ->orderBy('total', 'desc')
                    // ->whereNull('status_partner')
                    // ->whereNull('partner_id')
                    ->whereBetween('orders.created_at', [$request->start_date, $request->end_date])
                    ->limit(5)
                    ->get();
                $topProducts        = $this->orderDetail
                    ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                    ->join('products', 'products.id', '=', 'order_detail.product_id')
                    ->groupBy('order_detail.product_id', 'products.id', 'products.name')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get();
                $topRatings         = $this->productReview
                    ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                    ->join('products', 'products.id', '=', 'product_review.product_id')
                    ->groupBy('products.id', 'products.name')
                    ->orderBy('star_review', 'desc')
                    ->limit(10)
                    ->get();

                $productTop = array();

                // charts
                $topProductsChart    = null;
                $topRatingsChart     = null;

                foreach ($topProducts as $topProduct) {
                    $topProductsChart['id'][]  = $topProduct->id;
                    $topProductsChart['product'][]  = $topProduct->product;
                    $topProductsChart['total'][]    = (int) $topProduct->total;
                }

                $topProductsChart = json_encode($topProductsChart);

                foreach ($topRatings as $topRating) {
                    $topRatingsChart['id'][]       = $topRating->id;
                    $topRatingsChart['product'][]       = $topRating->product;
                    $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                }

                $topRatingsChart = json_encode($topRatingsChart);

                array_push($productTop, $topProductsChart);
                array_push($productTop, $topRatingsChart);

                foreach ($productCategories as $cat) {
                    $topProductsCategory[$cat->id]      = $this->orderDetail
                        ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->join('categories', 'categories.id', '=', 'products.category_id')
                        ->where('products.category_id', $cat->id)
                        ->groupBy('order_detail.product_id', 'categories.name', 'products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();


                    $topRatingsCategory[$cat->id]       = $this->productReview
                        ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->join('categories', 'categories.id', '=', 'products.category_id')
                        ->where('products.category_id', $cat->id)
                        ->groupBy('products.id', 'categories.name', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    // charts category
                    $topProductsCategoryChart    = null;
                    $topRatingsCategoryChart     = null;
                    $topProductsCategoryChart['category'][] = $cat->name;
                    $topRatingsCategoryChart['category'][]  = $cat->name;

                    foreach ($topProductsCategory[$cat->id] as $topProduct) {
                        $topProductsCategoryChart['id'][]       = $topProduct->id;
                        $topProductsCategoryChart['product'][]  = $topProduct->product;
                        $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                    foreach ($topRatingsCategory[$cat->id] as $topRating) {
                        $topRatingsCategoryChart['id'][]       = $topRating->id;
                        $topRatingsCategoryChart['product'][]       = $topRating->product;
                        $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                    array_push($productTop, $topProductsCategoryChart[$cat->id]);
                    array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                }

                $userApps = $this->users
                    ->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })
                    ->where('account_type', '4')
                    ->where('account_role', 'user')
                    ->whereNotNull('otp_verified_at')
                    ->whereNotNull('site_code')
                    ->whereBetween('created_at', [$request->start_date, $request->end_date])
                    ->select('site_code', DB::raw('count(*) as total'))
                    ->groupBy('site_code')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get();
                foreach ($userApps as $uapps) {
                    $userErp = $this->users
                        ->where(function ($query) {
                            $query
                                ->where('status_blacklist', '0')
                                ->orWhere('status_blacklist', null);
                        })
                        ->where('site_code', $uapps->site_code)
                        ->where('account_type', '4')
                        ->where('account_role', 'user')
                        ->whereNull('otp_verified_at')
                        ->whereBetween('created_at', [$request->start_date, $request->end_date])
                        ->select('site_code', DB::raw('count(*) as total'))
                        ->groupBy('site_code')
                        ->first();
                    $uapps->userErp = $userErp->total;
                }

                return view('admin/pages/dashboard', compact(
                    'customers',
                    'mappingSites',
                    'salesmen',
                    'productCategories',
                    'products',
                    'orders',
                    'subscribes',
                    'newOrders',
                    'confirmedOrders',
                    'deliveryOrders',
                    'completeOrders',
                    'cancelOrders',
                    'topProducts',
                    'topRatings',
                    'promos',
                    'productTop',
                    'topDistributor',
                    'customer_registered',
                    'userApps',
                    'start_date',
                    'end_date',
                    'newOrdersTotal',
                    'confirmedOrdersTotal',
                    'deliveryOrdersTotal',
                    'completeOrdersTotal',
                    'cancelOrdersTotal',
                    'point',
                    'totalOrders',
                    'topUser'
                ));
            } else {
                if ($request->site_id) {
                    $start_date         = $this->logs->select('log_time')->orderBy('log_time', 'asc')->first();
                    $start_date         = explode(' ', $start_date->log_time)[0];
                    $end_date           = $this->logs->select('log_time')->orderBy('log_time', 'desc')->first();
                    $end_date           = explode(' ', $end_date->log_time)[0];
                    $base_customers     = $this->users->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })->where('account_type', 4)->where('site_code', $request->site_id);
                    $customers          = $base_customers->count();
                    $customer_registered = $base_customers->whereNotNull('otp_verified_at')->count();
                    $mappingSites       = $this->mappingSite->get();
                    // $salesmen           = $this->salesman->where('kode', $request->site_id)->count();
                    $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->where('site_code', $request->site_id)->groupBy('salesman_code')->get());
                    $productCategories  = $this->productCategories->where('status', '1')->get();
                    $products           = $this->products->where('status', '1')->count();
                    $orders             = $this->orders->where('site_code', $request->site_id)->count();
                    $promos             = $this->promos->where('start', '>=', Carbon::now())->where('end', '<=', Carbon::now())->count();
                    // $point              = $this->users->where('site_code', $request->site_id)->sum('point');
                    $point              = $this->users->where('site_code', $request->site_id)->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                    $subscribes         = $this->subscribes
                        ->with('user')
                        ->whereHas('user', function ($query) use ($request) {
                            return $query->where('site_code', $request->site_id);
                        })
                        ->count();
                    // transactions
                    $totalOrders            = $this->orders->where('site_code', $request->site_id)->count();
                    $newOrders              = $this->orders->where('site_code', $request->site_id)->where('status', '1')->count();
                    $newOrdersTotal         = $this->orders->where('site_code', $request->site_id)->where('status', '1')->sum('payment_final');
                    $confirmedOrders        = $this->orders->where('site_code', $request->site_id)->where('status', '2')->count();
                    $confirmedOrdersTotal   = $this->orders->where('site_code', $request->site_id)->where('status', '2')->sum('payment_final');
                    $deliveryOrders         = $this->orders->where('site_code', $request->site_id)->where('status', '3')->count();
                    $deliveryOrdersTotal    = $this->orders->where('site_code', $request->site_id)->where('status', '3')->sum('payment_final');
                    $completeOrders         = $this->orders->where('site_code', $request->site_id)->where('status', '4')->count();
                    $completeOrdersTotal    = $this->orders->where('site_code', $request->site_id)->where('status', '4')->sum('payment_final');
                    $cancelOrders           = $this->orders->where('site_code', $request->site_id)->where('status', '10')->count();
                    $cancelOrdersTotal      = $this->orders->where('site_code', $request->site_id)->where('status', '10')->sum('payment_final');
                    // leaderboards
                    $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                        ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                        ->where('orders.site_code', $request->site_id)
                        ->groupBy('users.customer_code', 'users.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topDistributor     = $this->orders
                        ->join('mapping_site', 'mapping_site.kode', '=', 'orders.site_code')
                        ->where('mapping_site.kode', $request->site_id)
                        ->groupBy('orders.site_code', 'mapping_site.kode', 'mapping_site.branch_name', 'mapping_site.nama_comp')
                        ->selectRaw('mapping_site.kode as kode, mapping_site.branch_name as branch, mapping_site.nama_comp as nama, sum(orders.payment_final) as total')
                        ->orderBy('total', 'desc')
                        // ->whereNull('status_partner')
                        // ->whereNull('partner_id')
                        ->limit(5)
                        ->get();
                    $topProducts        = $this->orderDetail
                        ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->where('orders.site_code', $request->site_id)
                        ->groupBy('order_detail.product_id', 'products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topRatings         = $this->productReview
                        ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    $productTop = array();

                    // charts
                    $topProductsChart    = null;
                    $topRatingsChart     = null;

                    foreach ($topProducts as $topProduct) {
                        $topProductsChart['id'][]       = $topProduct->product_id;
                        $topProductsChart['product'][]  = $topProduct->product;
                        $topProductsChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsChart = json_encode($topProductsChart);

                    foreach ($topRatings as $topRating) {
                        $topRatingsChart['id'][]            = $topRating->product_id;
                        $topRatingsChart['product'][]       = $topRating->product;
                        $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsChart = json_encode($topRatingsChart);

                    array_push($productTop, $topProductsChart);
                    array_push($productTop, $topRatingsChart);

                    foreach ($productCategories as $cat) {
                        $topProductsCategory[$cat->id]      = $this->orderDetail
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('orders.site_code', $request->site_id)
                            ->where('products.category_id', $cat->id)
                            ->groupBy('order_detail.product_id', 'categories.name', 'products.id', 'products.name')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();


                        $topRatingsCategory[$cat->id]       = $this->productReview
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                            ->join('products', 'products.id', '=', 'product_review.product_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'categories.name', 'products.name')
                            ->orderBy('star_review', 'desc')
                            ->limit(10)
                            ->get();

                        // charts category
                        $topProductsCategoryChart    = null;
                        $topRatingsCategoryChart     = null;
                        $topProductsCategoryChart['category'][] = $cat->name;
                        $topRatingsCategoryChart['category'][]  = $cat->name;

                        foreach ($topProductsCategory[$cat->id] as $topProduct) {
                            $topProductsCategoryChart['id'][]  = $topProduct->product_id;
                            $topProductsCategoryChart['product'][]  = $topProduct->product;
                            $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                        }

                        $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                        foreach ($topRatingsCategory[$cat->id] as $topRating) {
                            $topRatingsCategoryChart['id'][]       = $topRating->product_id;
                            $topRatingsCategoryChart['product'][]       = $topRating->product;
                            $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                        }

                        $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                        array_push($productTop, $topProductsCategoryChart[$cat->id]);
                        array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                    }

                    $userApps = $this->users
                        ->where(function ($query) {
                            $query
                                ->where('status_blacklist', '0')
                                ->orWhere('status_blacklist', null);
                        })
                        ->where('account_type', '4')
                        ->where('account_role', 'user')
                        ->whereNotNull('otp_verified_at')
                        ->whereNotNull('site_code')
                        ->select('site_code', DB::raw('count(*) as total'))
                        ->groupBy('site_code')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    foreach ($userApps as $uapps) {
                        $userErp = $this->users
                            ->where(function ($query) {
                                $query
                                    ->where('status_blacklist', '0')
                                    ->orWhere('status_blacklist', null);
                            })
                            ->where('site_code', $uapps->site_code)
                            ->where('account_type', '4')
                            ->where('account_role', 'user')
                            ->whereNull('otp_verified_at')
                            ->select('site_code', DB::raw('count(*) as total'))
                            ->groupBy('site_code')
                            ->first();
                        $uapps->userErp = $userErp->total;
                    }

                    return view('admin/pages/dashboard', compact(
                        'customers',
                        'mappingSites',
                        'salesmen',
                        'productCategories',
                        'products',
                        'orders',
                        'subscribes',
                        'newOrders',
                        'confirmedOrders',
                        'deliveryOrders',
                        'completeOrders',
                        'cancelOrders',
                        'topProducts',
                        'topRatings',
                        'promos',
                        'productTop',
                        'topDistributor',
                        'customer_registered',
                        'userApps',
                        'start_date',
                        'end_date',
                        'newOrdersTotal',
                        'confirmedOrdersTotal',
                        'deliveryOrdersTotal',
                        'completeOrdersTotal',
                        'cancelOrdersTotal',
                        'point',
                        'totalOrders',
                        'topUser'
                    ));
                }
                $start_date         = $this->logs->select('log_time')->orderBy('log_time', 'asc')->first();
                $start_date         = explode(' ', $start_date->log_time)[0];
                $end_date           = $this->logs->select('log_time')->orderBy('log_time', 'desc')->first();
                $end_date           = explode(' ', $end_date->log_time)[0];
                $base_customers     = $this->users->where(function ($query) {
                    $query
                        ->where('status_blacklist', '0')
                        ->orWhere('status_blacklist', null);
                })->where('account_type', 4);
                $customers          = $base_customers->count();
                $customer_registered = $base_customers->whereNotNull('otp_verified_at')->count();
                $mappingSites       = $this->mappingSite->get();
                // $salesmen           = $this->salesman->count();
                $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->groupBy('salesman_code')->get());
                $productCategories  = $this->productCategories->where('status', '1')->get();
                $products           = $this->products->where('status', '1')->count();
                $orders             = $this->orders->count();
                $promos             = $this->promos->where('start', '<=', Carbon::now()->format('Y-m-d'))->where('end', '>=', Carbon::now()->format('Y-m-d'))->count();
                // $point              = $this->users->sum('point');
                $point              = $this->users->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                $subscribes         = $this->subscribes->count();
                // transactions
                $totalOrders            = $this->orders->count();
                $newOrders              = $this->orders->where('status', '1')->count();
                $newOrdersTotal         = $this->orders->where('status', '1')->sum('payment_final');
                $confirmedOrders        = $this->orders->where('status', '2')->count();
                $confirmedOrdersTotal   = $this->orders->where('status', '2')->sum('payment_final');
                $deliveryOrders         = $this->orders->where('status', '3')->count();
                $deliveryOrdersTotal    = $this->orders->where('status', '3')->sum('payment_final');
                $completeOrders         = $this->orders->where('status', '4')->count();
                $completeOrdersTotal    = $this->orders->where('status', '4')->sum('payment_final');
                $cancelOrders           = $this->orders->where('status', '10')->count();
                $cancelOrdersTotal      = $this->orders->where('status', '10')->sum('payment_final');
                // leaderboards
                $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                    ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                    ->groupBy('users.customer_code', 'users.name')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get();
                $topDistributor     = $this->orders
                    ->join('mapping_site', 'mapping_site.kode', '=', 'orders.site_code')
                    ->groupBy('orders.site_code', 'mapping_site.kode', 'mapping_site.branch_name', 'mapping_site.nama_comp')
                    ->selectRaw('mapping_site.kode as kode, mapping_site.branch_name as branch, mapping_site.nama_comp as nama, sum(orders.payment_final) as total')
                    ->orderBy('total', 'desc')
                    // ->whereNull('status_partner')
                    // ->whereNull('partner_id')
                    ->limit(5)
                    ->get();
                $topProducts        = $this->orderDetail
                    ->join('products', 'products.id', '=', 'order_detail.product_id')
                    ->groupBy('order_detail.product_id', 'products.id', 'products.name')
                    ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get();
                $topRatings         = $this->productReview
                    ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                    ->join('products', 'products.id', '=', 'product_review.product_id')
                    ->groupBy('products.id', 'products.name')
                    ->orderBy('star_review', 'desc')
                    ->limit(10)
                    ->get();

                $productTop = array();

                // charts
                $topProductsChart    = null;
                $topRatingsChart     = null;

                foreach ($topProducts as $topProduct) {
                    $topProductsChart['id'][]       = $topProduct->product_id;
                    $topProductsChart['product'][]  = $topProduct->product;
                    $topProductsChart['total'][]    = (int) $topProduct->total;
                }

                $topProductsChart = json_encode($topProductsChart);

                foreach ($topRatings as $topRating) {
                    $topRatingsChart['id'][]            = $topRating->product_id;
                    $topRatingsChart['product'][]       = $topRating->product;
                    $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                }

                $topRatingsChart = json_encode($topRatingsChart);

                array_push($productTop, $topProductsChart);
                array_push($productTop, $topRatingsChart);

                foreach ($productCategories as $cat) {
                    $topProductsCategory[$cat->id]      = $this->orderDetail
                        ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->join('categories', 'categories.id', '=', 'products.category_id')
                        ->where('products.category_id', $cat->id)
                        ->groupBy('order_detail.product_id', 'products.id', 'products.name', 'categories.id', 'categories.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();


                    $topRatingsCategory[$cat->id]       = $this->productReview
                        ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->join('categories', 'categories.id', '=', 'products.category_id')
                        ->where('products.category_id', $cat->id)
                        ->groupBy('products.id', 'products.name', 'categories.id', 'categories.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    // charts category
                    $topProductsCategoryChart    = null;
                    $topRatingsCategoryChart     = null;
                    $topProductsCategoryChart['category'][] = $cat->name;
                    $topRatingsCategoryChart['category'][]  = $cat->name;

                    foreach ($topProductsCategory[$cat->id] as $topProduct) {
                        $topProductsCategoryChart['id'][]  = $topProduct->product_id;
                        $topProductsCategoryChart['product'][]  = $topProduct->product;
                        $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                    foreach ($topRatingsCategory[$cat->id] as $topRating) {
                        $topRatingsCategoryChart['id'][]       = $topRating->product_id;
                        $topRatingsCategoryChart['product'][]       = $topRating->product;
                        $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                    array_push($productTop, $topProductsCategoryChart[$cat->id]);
                    array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                }

                $userApps = $this->users
                    ->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })
                    ->where('account_type', '4')
                    ->where('account_role', 'user')
                    ->whereNotNull('otp_verified_at')
                    ->whereNotNull('site_code')
                    ->select('site_code', DB::raw('count(*) as total'))
                    ->groupBy('site_code')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get();
                foreach ($userApps as $uapps) {
                    $userErp = $this->users
                        ->where(function ($query) {
                            $query
                                ->where('status_blacklist', '0')
                                ->orWhere('status_blacklist', null);
                        })
                        ->where('site_code', $uapps->site_code)
                        ->where('account_type', '4')
                        ->where('account_role', 'user')
                        ->whereNull('otp_verified_at')
                        ->select('site_code', DB::raw('count(*) as total'))
                        ->groupBy('site_code')
                        ->first();
                    $uapps->userErp = $userErp->total ?? 1;
                }

                // dd($userErp, $userApps);



                // dd($newOrdersTotal , $newOrders);
                return view('admin/pages/dashboard', compact(
                    'customers',
                    'mappingSites',
                    'salesmen',
                    'productCategories',
                    'products',
                    'orders',
                    'subscribes',
                    'newOrders',
                    'confirmedOrders',
                    'deliveryOrders',
                    'completeOrders',
                    'cancelOrders',
                    'topProducts',
                    'topRatings',
                    'promos',
                    'productTop',
                    'topDistributor',
                    'customer_registered',
                    'userApps',
                    'start_date',
                    'end_date',
                    'newOrdersTotal',
                    'confirmedOrdersTotal',
                    'deliveryOrdersTotal',
                    'completeOrdersTotal',
                    'cancelOrdersTotal',
                    'point',
                    'totalOrders',
                    'topUser'
                ));
            }
        } else {
            if (Auth::user()->account_role == 'distributor') {
                if ($request->start_date && $request->end_date != '') {
                    $start_date         = $request->start_date;
                    $end_date           = $request->end_date;
                    $base_customers     = $this->users->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })->whereBetween('created_at', [$request->start_date, $request->end_date])->where('account_type', 4)->where('site_code', Auth::user()->site_code);
                    $customers          = $base_customers->count();
                    $customer_registered = $this->users->whereNotNull('otp_verified_at')->whereBetween('otp_verified_at', [$request->start_date, $request->end_date])->where('account_type', 4)->where('site_code', Auth::user()->site_code)->count();
                    $mappingSites       = $this->mappingSite->get();
                    // $salesmen           = $this->salesman->where('kode', Auth::user()->site_code)->count();
                    $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->where('site_code', Auth::user()->site_code)->groupBy('salesman_code')->get());
                    $productCategories  = $this->productCategories->where('status', '1')->get();
                    $products           = $this->products->where('status', '1')->count();
                    $orders             = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', Auth::user()->site_code)->count();
                    $promos             = $this->promos->whereBetween('start', [$request->start_date, $request->end_date])->count();
                    // $point              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', Auth::user()->site_code)->sum('point');
                    $point              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', Auth::user()->site_code)->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                    $subscribes         = $this->subscribes
                        ->whereBetween('created_at', [$request->start_date, $request->end_date])
                        ->with('user')
                        ->whereHas('user', function ($query) use ($request) {
                            return $query->where('site_code', Auth::user()->site_code);
                        })
                        ->count();
                    // transactions
                    $totalOrders            = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', Auth::user()->site_code)->count();
                    $newOrders              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->where('site_code', Auth::user()->site_code)->count();
                    $newOrdersTotal         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->where('site_code', Auth::user()->site_code)->sum('payment_final');
                    $confirmedOrders        = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->where('site_code', Auth::user()->site_code)->count();
                    $confirmedOrdersTotal   = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->where('site_code', Auth::user()->site_code)->sum('payment_final');
                    $deliveryOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->where('site_code', Auth::user()->site_code)->count();
                    $deliveryOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->where('site_code', Auth::user()->site_code)->sum('payment_final');
                    $completeOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->where('site_code', Auth::user()->site_code)->count();
                    $completeOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->where('site_code', Auth::user()->site_code)->sum('payment_final');
                    $cancelOrders           = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->where('site_code', Auth::user()->site_code)->count();
                    $cancelOrdersTotal      = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->where('site_code', Auth::user()->site_code)->sum('payment_final');
                    // leaderboards
                    $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                        ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                        ->whereBetween('orders.created_at', [$request->start_date, $request->end_date])
                        ->where('orders.site_code', Auth::user()->site_code)
                        ->groupBy('users.customer_code', 'users.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topProducts        = $this->orderDetail
                        ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->where('orders.site_code', Auth::user()->site_code)
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();

                    $topRatings         = $this->productReview
                        ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    $productTop = array();

                    // charts
                    $topProductsChart    = null;
                    $topRatingsChart     = null;

                    foreach ($topProducts as $topProduct) {
                        $topProductsChart['id'][]  = $topProduct->id;
                        $topProductsChart['product'][]  = $topProduct->product;
                        $topProductsChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsChart = json_encode($topProductsChart);

                    foreach ($topRatings as $topRating) {
                        $topRatingsChart['id'][]       = $topRating->id;
                        $topRatingsChart['product'][]       = $topRating->product;
                        $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsChart = json_encode($topRatingsChart);

                    array_push($productTop, $topProductsChart);
                    array_push($productTop, $topRatingsChart);

                    foreach ($productCategories as $cat) {
                        $topProductsCategory[$cat->id]      = $this->orderDetail
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('orders.site_code', Auth::user()->site_code)
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();


                        $topRatingsCategory[$cat->id]       = $this->productReview
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                            ->join('products', 'products.id', '=', 'product_review.product_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('star_review', 'desc')
                            ->limit(10)
                            ->get();

                        // charts category
                        $topProductsCategoryChart    = null;
                        $topRatingsCategoryChart     = null;
                        $topProductsCategoryChart['category'][] = $cat->name;
                        $topRatingsCategoryChart['category'][]  = $cat->name;

                        foreach ($topProductsCategory[$cat->id] as $topProduct) {
                            $topProductsCategoryChart['id'][]  = $topProduct->id;
                            $topProductsCategoryChart['product'][]  = $topProduct->product;
                            $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                        }

                        $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                        foreach ($topRatingsCategory[$cat->id] as $topRating) {
                            $topRatingsCategoryChart['id'][]       = $topRating->id;
                            $topRatingsCategoryChart['product'][]       = $topRating->product;
                            $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                        }

                        $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                        array_push($productTop, $topProductsCategoryChart[$cat->id]);
                        array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                    }

                    return view('admin/pages/dashboard', compact(
                        'customers',
                        'mappingSites',
                        'salesmen',
                        'productCategories',
                        'products',
                        'orders',
                        'subscribes',
                        'newOrders',
                        'confirmedOrders',
                        'deliveryOrders',
                        'completeOrders',
                        'cancelOrders',
                        'topProducts',
                        'topRatings',
                        'promos',
                        'productTop',
                        'customer_registered',
                        'start_date',
                        'end_date',
                        'newOrdersTotal',
                        'confirmedOrdersTotal',
                        'deliveryOrdersTotal',
                        'completeOrdersTotal',
                        'cancelOrdersTotal',
                        'point',
                        'totalOrders',
                        'topUser'
                    ));
                } else {
                    $start_date         = $this->logs->select('log_time')->orderBy('log_time', 'asc')->first();
                    $start_date         = explode(' ', $start_date->log_time)[0];
                    $end_date           = $this->logs->select('log_time')->orderBy('log_time', 'desc')->first();
                    $end_date           = explode(' ', $end_date->log_time)[0];
                    $base_customers     = $this->users->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })->where('account_type', 4)->where('site_code', Auth::user()->site_code);
                    $customers          = $base_customers->count();
                    $customer_registered = $base_customers->whereNotNull('otp_verified_at')->count();
                    $mappingSites       = $this->mappingSite->get();
                    // $salesmen           = $this->salesman->where('kode', Auth::user()->site_code)->count();
                    $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->where('site_code', Auth::user()->site_code)->groupBy('salesman_code')->get());
                    $productCategories  = $this->productCategories->where('status', '1')->get();
                    $products           = $this->products->where('status', '1')->count();
                    $orders             = $this->orders->where('site_code', Auth::user()->site_code)->count();
                    $promos             = $this->promos->where('start', '<=', Carbon::now()->format('Y-m-d'))->where('end', '>=', Carbon::now()->format('Y-m-d'))->count();
                    // $point              = $this->users->where('site_code', Auth::user()->site_code)->sum('point');
                    $point              = $this->users->where('site_code', Auth::user()->site_code)->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                    $subscribes         = $this->subscribes
                        ->with('user')
                        ->whereHas('user', function ($query) {
                            $query->where('site_code', Auth::user()->site_code);
                        })
                        ->get();
                    // transactions
                    $totalOrders            = $this->orders->where('site_code', Auth::user()->site_code)->count();
                    $newOrders              = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '1')->count();
                    $newOrdersTotal         = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '1')->sum('payment_final');
                    $confirmedOrders        = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '2')->count();
                    $confirmedOrdersTotal   = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '2')->sum('payment_final');
                    $deliveryOrders         = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '3')->count();
                    $deliveryOrdersTotal    = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '3')->sum('payment_final');
                    $completeOrders         = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '4')->count();
                    $completeOrdersTotal    = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '4')->sum('payment_final');
                    $cancelOrders           = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '10')->count();
                    $cancelOrdersTotal      = $this->orders->where('site_code', Auth::user()->site_code)->where('status', '10')->sum('payment_final');
                    // leaderboards
                    $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                        ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                        ->where('orders.site_code', Auth::user()->site_code)
                        ->groupBy('users.customer_code', 'users.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topProducts        = $this->orderDetail
                        ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->where('orders.site_code', Auth::user()->site_code)
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topRatings         = $this->productReview
                        ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    $productTop = array();

                    // charts
                    $topProductsChart    = null;
                    $topRatingsChart     = null;

                    foreach ($topProducts as $topProduct) {
                        $topProductsChart['id'][]  = $topProduct->id;
                        $topProductsChart['product'][]  = $topProduct->product;
                        $topProductsChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsChart = json_encode($topProductsChart);

                    foreach ($topRatings as $topRating) {
                        $topRatingsChart['id'][]       = $topRating->id;
                        $topRatingsChart['product'][]       = $topRating->product;
                        $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsChart = json_encode($topRatingsChart);

                    array_push($productTop, $topProductsChart);
                    array_push($productTop, $topRatingsChart);

                    foreach ($productCategories as $cat) {
                        $topProductsCategory[$cat->id]      = $this->orderDetail
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('orders.site_code', Auth::user()->site_code)
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();

                        $topRatingsCategory[$cat->id]       = $this->productReview
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                            ->join('products', 'products.id', '=', 'product_review.product_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('star_review', 'desc')
                            ->limit(10)
                            ->get();

                        // charts category
                        $topProductsCategoryChart    = null;
                        $topRatingsCategoryChart     = null;
                        $topProductsCategoryChart['category'][] = $cat->name;
                        $topRatingsCategoryChart['category'][]  = $cat->name;

                        foreach ($topProductsCategory[$cat->id] as $topProduct) {
                            $topProductsCategoryChart['id'][]  = $topProduct->id;
                            $topProductsCategoryChart['product'][]  = $topProduct->product;
                            $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                        }

                        $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                        foreach ($topRatingsCategory[$cat->id] as $topRating) {
                            $topRatingsCategoryChart['id'][]            = $topRating->id;
                            $topRatingsCategoryChart['product'][]       = $topRating->product;
                            $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                        }

                        $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                        array_push($productTop, $topProductsCategoryChart[$cat->id]);
                        array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                    }

                    return view('admin/pages/dashboard', compact(
                        'customers',
                        'mappingSites',
                        'salesmen',
                        'productCategories',
                        'products',
                        'orders',
                        'subscribes',
                        'newOrders',
                        'confirmedOrders',
                        'deliveryOrders',
                        'completeOrders',
                        'cancelOrders',
                        'topProducts',
                        'topRatings',
                        'promos',
                        'productTop',
                        'customer_registered',
                        'start_date',
                        'end_date',
                        'newOrdersTotal',
                        'confirmedOrdersTotal',
                        'deliveryOrdersTotal',
                        'completeOrdersTotal',
                        'cancelOrdersTotal',
                        'point',
                        'totalOrders',
                        'topUser'
                    ));
                }
            } else if (Auth::user()->account_role == 'distributor_ho') {
                $sites = $this->mappingSite->where('kode', Auth::user()->site_code)->with(['ho_child' => function ($q) {
                    $q->select('kode', 'sub');
                }])->first();

                $array_child = [];
                foreach ($sites->ho_child as $child) {
                    array_push($array_child, $child->kode);
                }

                if ($request->start_date && $request->end_date != '') {
                    $start_date         = $request->start_date;
                    $end_date           = $request->end_date;
                    $base_customers     = $this->users->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })->whereBetween('created_at', [$request->start_date, $request->end_date])->where('account_type', 4)->whereIn('site_code', $array_child);
                    $customers          = $base_customers->count();
                    $customer_registered = $this->users->whereNotNull('otp_verified_at')->whereBetween('otp_verified_at', [$request->start_date, $request->end_date])->where('account_type', 4)->whereIn('site_code', $array_child)->count();
                    $mappingSites       = $this->mappingSite->get();
                    // $salesmen           = $this->salesman->where('kode', Auth::user()->site_code)->count();
                    $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->whereIn('site_code', $array_child)->groupBy('salesman_code')->get());
                    $productCategories  = $this->productCategories->where('status', '1')->get();
                    $products           = $this->products->where('status', '1')->count();
                    $orders             = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->whereIn('site_code', $array_child)->count();
                    $promos             = $this->promos->whereBetween('start', [$request->start_date, $request->end_date])->count();
                    // $point              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('site_code', Auth::user()->site_code)->sum('point');
                    $point              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->whereIn('site_code', $array_child)->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                    $subscribes         = $this->subscribes
                        ->whereBetween('created_at', [$request->start_date, $request->end_date])
                        ->with('user')
                        ->whereHas('user', function ($query) use ($array_child) {
                            return $query->whereIn('site_code', $array_child);
                        })
                        ->count();
                    // transactions
                    $totalOrders            = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->whereIn('site_code', $array_child)->count();
                    $newOrders              = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->whereIn('site_code', $array_child)->count();
                    $newOrdersTotal         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '1')->whereIn('site_code', $array_child)->sum('payment_final');
                    $confirmedOrders        = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->whereIn('site_code', $array_child)->count();
                    $confirmedOrdersTotal   = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '2')->whereIn('site_code', $array_child)->sum('payment_final');
                    $deliveryOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->whereIn('site_code', $array_child)->count();
                    $deliveryOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '3')->whereIn('site_code', $array_child)->sum('payment_final');
                    $completeOrders         = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->whereIn('site_code', $array_child)->count();
                    $completeOrdersTotal    = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '4')->whereIn('site_code', $array_child)->sum('payment_final');
                    $cancelOrders           = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->whereIn('site_code', $array_child)->count();
                    $cancelOrdersTotal      = $this->orders->whereBetween('created_at', [$request->start_date, $request->end_date])->where('status', '10')->whereIn('site_code', $array_child)->sum('payment_final');
                    // leaderboards
                    $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                        ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                        ->whereBetween('orders.created_at', [$request->start_date, $request->end_date])
                        ->whereIn('orders.site_code', $array_child)
                        ->groupBy('users.customer_code', 'users.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topProducts        = $this->orderDetail
                        ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->whereIn('orders.site_code', $array_child)
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();

                    $topRatings         = $this->productReview
                        ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    $productTop = array();

                    // charts
                    $topProductsChart    = null;
                    $topRatingsChart     = null;

                    foreach ($topProducts as $topProduct) {
                        $topProductsChart['id'][]  = $topProduct->id;
                        $topProductsChart['product'][]  = $topProduct->product;
                        $topProductsChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsChart = json_encode($topProductsChart);

                    foreach ($topRatings as $topRating) {
                        $topRatingsChart['id'][]       = $topRating->id;
                        $topRatingsChart['product'][]       = $topRating->product;
                        $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsChart = json_encode($topRatingsChart);

                    array_push($productTop, $topProductsChart);
                    array_push($productTop, $topRatingsChart);

                    foreach ($productCategories as $cat) {
                        $topProductsCategory[$cat->id]      = $this->orderDetail
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->whereIn('orders.site_code', $array_child)
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();


                        $topRatingsCategory[$cat->id]       = $this->productReview
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                            ->join('products', 'products.id', '=', 'product_review.product_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('star_review', 'desc')
                            ->limit(10)
                            ->get();

                        // charts category
                        $topProductsCategoryChart    = null;
                        $topRatingsCategoryChart     = null;
                        $topProductsCategoryChart['category'][] = $cat->name;
                        $topRatingsCategoryChart['category'][]  = $cat->name;

                        foreach ($topProductsCategory[$cat->id] as $topProduct) {
                            $topProductsCategoryChart['id'][]  = $topProduct->id;
                            $topProductsCategoryChart['product'][]  = $topProduct->product;
                            $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                        }

                        $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                        foreach ($topRatingsCategory[$cat->id] as $topRating) {
                            $topRatingsCategoryChart['id'][]       = $topRating->id;
                            $topRatingsCategoryChart['product'][]       = $topRating->product;
                            $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                        }

                        $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                        array_push($productTop, $topProductsCategoryChart[$cat->id]);
                        array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                    }

                    return view('admin/pages/dashboard', compact(
                        'customers',
                        'mappingSites',
                        'salesmen',
                        'productCategories',
                        'products',
                        'orders',
                        'subscribes',
                        'newOrders',
                        'confirmedOrders',
                        'deliveryOrders',
                        'completeOrders',
                        'cancelOrders',
                        'topProducts',
                        'topRatings',
                        'promos',
                        'productTop',
                        'customer_registered',
                        'start_date',
                        'end_date',
                        'newOrdersTotal',
                        'confirmedOrdersTotal',
                        'deliveryOrdersTotal',
                        'completeOrdersTotal',
                        'cancelOrdersTotal',
                        'point',
                        'totalOrders',
                        'topUser'
                    ));
                } else {
                    $start_date         = $this->logs->select('log_time')->orderBy('log_time', 'asc')->first();
                    $start_date         = explode(' ', $start_date->log_time)[0];
                    $end_date           = $this->logs->select('log_time')->orderBy('log_time', 'desc')->first();
                    $end_date           = explode(' ', $end_date->log_time)[0];
                    $base_customers     = $this->users->where(function ($query) {
                        $query
                            ->where('status_blacklist', '0')
                            ->orWhere('status_blacklist', null);
                    })->where('account_type', 4)->whereIn('site_code', $array_child);
                    $customers          = $base_customers->count();
                    $customer_registered = $base_customers->whereNotNull('otp_verified_at')->count();
                    $mappingSites       = $this->mappingSite->get();
                    // $salesmen           = $this->salesman->where('kode', Auth::user()->site_code)->count();
                    $salesmen           = count($this->metas->select(DB::raw('count(salesman_code) as total'))->whereIn('site_code', $array_child)->groupBy('salesman_code')->get());
                    $productCategories  = $this->productCategories->where('status', '1')->get();
                    $products           = $this->products->where('status', '1')->count();
                    $orders             = $this->orders->whereIn('site_code', $array_child)->count();
                    $promos             = $this->promos->where('start', '<=', Carbon::now()->format('Y-m-d'))->where('end', '>=', Carbon::now()->format('Y-m-d'))->count();
                    // $point              = $this->users->where('site_code', Auth::user()->site_code)->sum('point');
                    $point              = $this->users->whereIn('site_code', $array_child)->select(DB::raw('sum(cast(point as double precision))'))->get()[0]->sum;
                    $subscribes         = $this->subscribes
                        ->with('user')
                        ->whereHas('user', function ($query) use ($array_child) {
                            $query->whereIn('site_code', $array_child);
                        })
                        ->get();
                    // transactions
                    $totalOrders            = $this->orders->whereIn('site_code', $array_child)->count();
                    $newOrders              = $this->orders->whereIn('site_code', $array_child)->where('status', '1')->count();
                    $newOrdersTotal         = $this->orders->whereIn('site_code', $array_child)->where('status', '1')->sum('payment_final');
                    $confirmedOrders        = $this->orders->whereIn('site_code', $array_child)->where('status', '2')->count();
                    $confirmedOrdersTotal   = $this->orders->whereIn('site_code', $array_child)->where('status', '2')->sum('payment_final');
                    $deliveryOrders         = $this->orders->whereIn('site_code', $array_child)->where('status', '3')->count();
                    $deliveryOrdersTotal    = $this->orders->whereIn('site_code', $array_child)->where('status', '3')->sum('payment_final');
                    $completeOrders         = $this->orders->whereIn('site_code', $array_child)->where('status', '4')->count();
                    $completeOrdersTotal    = $this->orders->whereIn('site_code', $array_child)->where('status', '4')->sum('payment_final');
                    $cancelOrders           = $this->orders->whereIn('site_code', $array_child)->where('status', '10')->count();
                    $cancelOrdersTotal      = $this->orders->whereIn('site_code', $array_child)->where('status', '10')->sum('payment_final');
                    // leaderboards
                    $topUser            = $this->orders->join('users', 'users.id', '=', 'orders.customer_id')
                        ->select('users.customer_code', 'users.name', DB::raw('sum(orders.payment_final) as total'))
                        ->whereIn('orders.site_code', $array_child)
                        ->groupBy('users.customer_code', 'users.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topProducts        = $this->orderDetail
                        ->select('products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                        ->whereIn('orders.site_code', $array_child)
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('total', 'desc')
                        ->limit(10)
                        ->get();
                    $topRatings         = $this->productReview
                        ->select('products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                        ->join('products', 'products.id', '=', 'product_review.product_id')
                        ->groupBy('products.id', 'products.name')
                        ->orderBy('star_review', 'desc')
                        ->limit(10)
                        ->get();

                    $productTop = array();

                    // charts
                    $topProductsChart    = null;
                    $topRatingsChart     = null;

                    foreach ($topProducts as $topProduct) {
                        $topProductsChart['id'][]  = $topProduct->id;
                        $topProductsChart['product'][]  = $topProduct->product;
                        $topProductsChart['total'][]    = (int) $topProduct->total;
                    }

                    $topProductsChart = json_encode($topProductsChart);

                    foreach ($topRatings as $topRating) {
                        $topRatingsChart['id'][]       = $topRating->id;
                        $topRatingsChart['product'][]       = $topRating->product;
                        $topRatingsChart['star_review'][]   = (int) $topRating->star_review;
                    }

                    $topRatingsChart = json_encode($topRatingsChart);

                    array_push($productTop, $topProductsChart);
                    array_push($productTop, $topRatingsChart);

                    foreach ($productCategories as $cat) {
                        $topProductsCategory[$cat->id]      = $this->orderDetail
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->whereIn('orders.site_code', $array_child)
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();

                        $topRatingsCategory[$cat->id]       = $this->productReview
                            ->select('categories.name as category', 'products.id as product_id', 'products.name as product', DB::raw('ROUND(avg(star_review)::numeric, 1) as star_review'))
                            ->join('products', 'products.id', '=', 'product_review.product_id')
                            ->join('categories', 'categories.id', '=', 'products.category_id')
                            ->where('products.category_id', $cat->id)
                            ->groupBy('products.id', 'products.name', 'categories.name')
                            ->orderBy('star_review', 'desc')
                            ->limit(10)
                            ->get();

                        // charts category
                        $topProductsCategoryChart    = null;
                        $topRatingsCategoryChart     = null;
                        $topProductsCategoryChart['category'][] = $cat->name;
                        $topRatingsCategoryChart['category'][]  = $cat->name;

                        foreach ($topProductsCategory[$cat->id] as $topProduct) {
                            $topProductsCategoryChart['id'][]  = $topProduct->id;
                            $topProductsCategoryChart['product'][]  = $topProduct->product;
                            $topProductsCategoryChart['total'][]    = (int) $topProduct->total;
                        }

                        $topProductsCategoryChart[$cat->id] = json_encode($topProductsCategoryChart);

                        foreach ($topRatingsCategory[$cat->id] as $topRating) {
                            $topRatingsCategoryChart['id'][]            = $topRating->id;
                            $topRatingsCategoryChart['product'][]       = $topRating->product;
                            $topRatingsCategoryChart['star_review'][]   = (int) $topRating->star_review;
                        }

                        $topRatingsCategoryChart[$cat->id] = json_encode($topRatingsCategoryChart);

                        array_push($productTop, $topProductsCategoryChart[$cat->id]);
                        array_push($productTop, $topRatingsCategoryChart[$cat->id]);
                    }

                    return view('admin/pages/dashboard', compact(
                        'customers',
                        'mappingSites',
                        'salesmen',
                        'productCategories',
                        'products',
                        'orders',
                        'subscribes',
                        'newOrders',
                        'confirmedOrders',
                        'deliveryOrders',
                        'completeOrders',
                        'cancelOrders',
                        'topProducts',
                        'topRatings',
                        'promos',
                        'productTop',
                        'customer_registered',
                        'start_date',
                        'end_date',
                        'newOrdersTotal',
                        'confirmedOrdersTotal',
                        'deliveryOrdersTotal',
                        'completeOrdersTotal',
                        'cancelOrdersTotal',
                        'point',
                        'totalOrders',
                        'topUser'
                    ));
                }
            }
        }
    }

    public function ajaxMappingSite(Request $request)
    {
        $data = [];
        if ($request->has('q')) {
            $search = $request->q;
            $data = $this->mappingSite->where('kode', 'LIKE', "%" . $search . "%")
                ->orderBy('kode')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function ajaxCustomer(Request $request)
    {
        $data = [];
        if ($request->has('q')) {
            $search = $request->q;
            $data = $this->users->where('customer_code', 'LIKE', "%" . $search . "%")
                ->orderBy('name')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function ajaxProduct(Request $request)
    {
        $data = [];

        if ($request->has('q')) {
            $search = $request->q;
            $data = $this->products->whereNotNull('name')
                ->where('name', '!=', '0')
                ->where('name', 'LIKE', "%" . ucwords($search) . "%")
                ->orWhere('kodeprod', 'LIKE', "%" . $search . "%")
                ->orderBy('name')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }
}
