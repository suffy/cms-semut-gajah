<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\Salesman;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $order, $orderDetail, $product, $user, $salesman;

    public function __construct(Order $order, OrderDetail $orderDetail, Product $product, User $user, Salesman $salesman)
    {
        $this->order        = $order;
        $this->orderDetail  = $orderDetail;
        $this->product      = $product;
        $this->user         = $user;
        $this->salesman     = $salesman;
    }

    public function report()
    {
        $topProducts = $this->order
                            ->select('products.name as product', DB::raw('count(order_detail.product_id) as total'))
                            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->groupBy('order_detail.product_id', 'products.id')
                            ->orderBy('total', 'desc')
                            ->limit(10)
                            ->get();

        $totalProducts              = $this->product->count();
        $totalOrders                = $this->order->count();
        $totalPendingOrders         = $this->order->where('status', '1')->count();
        $totalConfirmationOrders    = $this->order->where('status', '2')->count();
        $totalDeliveryOrders        = $this->order->where('status', '3')->count();
        $totalSuccessOrders         = $this->order->where('status', '4')->count();
        $totalFailedOrders          = $this->order->where('status', '5')->count();
        $totalUsers                 = $this->user->count();
        $totalSalesman              = $this->salesman->count();
                    
        return view('admin.pages.report', compact('topProducts', 'totalProducts', 'totalOrders', 'totalPendingOrders', 'totalConfirmationOrders', 'totalDeliveryOrders', 'totalSuccessOrders', 'totalFailedOrders', 'totalUsers', 'totalSalesman'));
    }
}
