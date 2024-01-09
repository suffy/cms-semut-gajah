<?php

namespace App\Http\Controllers;


use App\Mail\Invoice;
use App\Order;
use App\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{

    function emailOrder($id){

        $order = Order::find($id);
        $order_detail = OrderDetail::join('product', 'product.id','=','order_detail.product_id')
            ->where('order_id', $id)
            ->select('order_detail.*', 'product_name as product_name')
            ->get();

        $item = 0;
        $total = 0;

        foreach ($order_detail as $key => $value) {
            $item = $item + $value->qty;
            $total = $total + $value->total_price;
        }

        $order_details = [$order_detail, $item, $total];

        $order['order_details'] = $order_details;

        try{
            Mail::to('melati.sekaringtyas@gmail.com')->send(new Invoice($order));
            echo "send";
        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }
}