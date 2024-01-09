<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\OrderDetail;
use App\Product;

class RecapCustomerController extends Controller
{
    protected $orders, $products;
    public function __construct(Order $orders, OrderDetail $orderDetail, Product $products)
    {
        $this->orders       = $orders;
        $this->orderDetail  = $orderDetail;
        $this->products     = $products;
    }

    public function index(Request $request)
    {
        return view('admin.pages.customer-recaps');
    }

    public function detail(Request $request, $customer_id)
    {
        // nama product, kodeprod, brand_id, 
        $products = $this->orderDetail
                                ->whereNotNull('product_id')
                                ->select('product_id', DB::raw('sum(total_price) as price_total, sum(qty) as qty_total'))
                                ->join('orders', 'orders.id', '=', 'order_detail.order_id');

        if($request->product_id) {
            $products = $products->where('product_id', $request->product_id);
        }

        if($request->brand_id) {
            $products = $products
                            ->whereHas('product', function($q) {
                                    $q->where('brand_id', $request->brand_id);
                                });
        }

        if($request->start_date && $request->end_date) {
            $products = $products
                            ->whereBetween('order_time', [$request->start_date, $request->end_date]);
            
        }
                        
        $products = $products
                            ->where('orders.customer_id', $customer_id)
                            ->where('orders.status', 4)
                            ->orderBy('qty_total', 'DESC')
                            ->orderBy('price_total', 'DESC')
                            ->groupBy('product_id')
                            ->with(['product' => function($q) {
                                $q->select('id', 'name', 'kodeprod', 'brand_id');
                            }])
                            ->limit(10)
                            ->get();
        if($request->ajax()){
            return view('admin.pages.pagination_data_recap_customer_detail', compact('products'))->render();
        } else {
            dd($products);
            // return view('admin.pages.customer-recap-detail', compact('products'));
        }
    }

    public function fetchDataRecap(Request $request)
    {
        $site_code = auth()->user()->site_code;
        
        if($site_code) {
            $customers = $this->orders
                                ->where('status', 4)
                                ->whereHas('user', function($q) use ($site_code) {
                                    $q
                                        // ->select('orders.customer_id', 'users.id', 'users.site_')
                                        ->where('site_code', $site_code);
                                })
                                
                                ->with(['user' => function ($q) {
                                    $q->select('id', 'name', 'site_code', 'customer_code');
                                }]);
        } else {
            $customers = $this->orders
                                ->where('status', 4)
                                ->with(['user' => function ($q) {
                                    $q->select('id', 'name', 'site_code', 'customer_code');
                                }]);
        }

        if($request->product_id) {
            $customers = $customers->whereHas('data_item', function($q) use ($request) {
                                    $q
                                        ->select('order_detail.id', 'order_id', 'product_id')
                                        ->where('product_id', $request->product_id);
                                });
        } 
        
        if($request->brand_id) {
            $customers = $customers->whereHas('data_item', function($q) use ($request) {
                                        $q
                                            ->select('order_detail.id', 'order_id', 'product_id', 'brand_id')
                                            ->join('products', 'products.id' ,'=', 'order_detail.product_id')
                                            ->where('brand_id', $request->brand_id);
                                    });
        } 
        
        if($request->start_date && $request->end_date) {
            $customers = $customers->whereHas('data_item', function($q) use ($request) {
                                        $q
                                            ->select('order_detail.id', 'order_id', 'product_id', 'brand_id')
                                            ->join('products', 'products.id' ,'=', 'order_detail.product_id')
                                            ->whereBetween('order_time', [$request->start_date, $request->end_date]);
                                    });
        } 
        
        if($request->search) {
            $customers = $customers->whereHas('user', function($q) use ($request) {
                                                    $q
                                                        ->where('name', 'like', '%'.$request->search.'%');
                                                })
                                        ->with(['data_item' => function ($q) {
                                            $q
                                                ->select('order_detail.id', 'order_id', 'product_id', 'brand_id')
                                                ->join('products', 'products.id' ,'=', 'order_detail.product_id');
                                        }]);
        } 
        
        if($request->site_code) {
            $customers = $customers->whereHas('user', function($q) use ($request) {
                                                    $q
                                                        ->where('site_code', $request->site_code);
                                                })
                                                ->with(['data_item' => function ($q) {
                                                    $q
                                                        ->select('order_detail.id', 'order_id', 'product_id', 'brand_id')
                                                        ->join('products', 'products.id' ,'=', 'order_detail.product_id');
                                                }]);
        }

        $customers = $customers
                            // ->select('id', 'customer_id', DB::raw('SUM(payment_final) AS payment_final, count(id) as order_count'))
                            ->select('customer_id', DB::raw('SUM(payment_final) AS payment_final, count(customer_id) as order_count'))
                            ->orderBy('order_count', 'DESC')
                            ->orderBy('payment_final', 'DESC')
                            ->groupBy('customer_id')
                            ->paginate(10);
    
        if($request->ajax()){
            return view('admin.pages.pagination_data_recap_customer', compact('customers'))->render();
        } else {
            dd($customers);
        }
    }

    public function ajaxProduct(Request $request)
    {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data = $this->products
                ->where('name', 'LIKE', "%".ucwords($search)."%")
                ->select('id', 'name')
                // ->orderBy('branch_name')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function ajaxBrand(Request $request)
    {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data = $this->products
                ->where('brand', 'LIKE', "%".ucwords($search)."%")
                ->select('brand_id', 'brand')
                ->groupBy('brand_id', 'brand')
                // ->orderBy('branch_name')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function ajaxGroup(Request $request)
    {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data   = $this->products
                                ->where('nama_group', 'LIKE', "%" .strtoupper($search) . "%")
                                ->select('nama_group')
                                ->groupBy('nama_group')
                                ->get();
        }
        return response()->json($data);
    }


}
