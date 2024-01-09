<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exports\ReportItemExport;
use App\Exports\ReportTransaksiExport;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Log;
use App\User;
use App\Location;
use App\MappingSite;
use App\OrderDetail;
use App\Product;
use App\ShoppingCart;
use App\UserAddress;
use PDF;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Auth;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Illuminate\Support\Facades\Http;
use Excel;

class OrderController extends Controller
{

    protected $order;
    protected $user;
    protected $location;
    protected $order_detail;
    protected $category;
    protected $product;
    protected $logs;
    protected $mappingSites;

    public function __construct(Order $order, User $user, Location $location, OrderDetail $order_detail, Category $category, Product $product, Log $log, MappingSite $mappingSite)
    {
        $this->order = $order;
        $this->user = $user;
        $this->location = $location;
        $this->order_detail = $order_detail;
        $this->category = $category;
        $this->product = $product;
        $this->logs = $log;
        $this->mappingSites = $mappingSite;
    }

    public function index(Request $request)
    {
        // check user login
        $user = $this->user->find(Auth::user()->id)->first();
        // count order
        $ordersToday        = $this->order
                                        ->whereBetween('order_time', [Carbon::now()->startOfDay(), Carbon::today()->endOfDay()])
                                        ->count();
        $ordersLastWeek     = $this->order
                                        ->whereBetween('order_time', [Carbon::now()->startOfWeek()->subWeek(), Carbon::now()->endOfWeek()->subWeek()])
                                        ->count();
        $ordersLastMonth    = $this->order
                                        ->whereBetween('order_time', [Carbon::now()->startOfMonth()->subMonth(), Carbon::now()->endOfMonth()->subMonth()])
                                        ->count();
        $ordersLastYear     = $this->order  
                                        ->whereBetween('order_time', [Carbon::now()->startOfYear()->subYear(), Carbon::now()->endOfYear()->subYear()])
                                        ->count();

        $categories = $this->category->get();
        $orders     = $this->category->query();
        
        if ($request->has('end_date')) {
            $orders = $orders->whereBetween('orders.order_time', [$request->start_date, $request->end_date]);
        }

        if ($request->has('category_id')) {
            $orders = $orders->where('category_id', $request->category_id);
        }

        if ($request->has('qty')) {
            $orders = $orders->whereBetween('order_detail.qty', [$request->qty, $request->max]);
        }

        if ($request->has('search')) {
            $orders = $orders->where('orders.invoice', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status_faktur')) {
            $orders = $orders->where('orders.status_faktur', $request->status_faktur);
        }

        $orders = $orders
                ->select('orders.id', 'orders.order_time', 'orders.invoice', 'orders.name','orders.payment_point', 'mapping_site.branch_name', 'orders.payment_total', 'orders.payment_final', 'orders.status', 'orders.point', 'orders.status_faktur')
                ->join('products', 'products.category_id', '=', 'categories.id')
                ->join('order_detail', 'order_detail.product_id', '=', 'products.id')
                ->join('orders', 'orders.id', '=', 'order_detail.order_id')
                ->leftJoin('mapping_site', function($join){
                    $join->on('orders.site_code', '=', 'mapping_site.kode');
                })
                ->orderBy('orders.id', 'DESC')
                ->groupBy('order_detail.order_id', 'orders.id', 'mapping_site.id')
                ->whereNull('orders.deleted_at')
                ->paginate(10);
        return view('admin.pages.orders', compact('ordersToday', 'ordersLastWeek', 'ordersLastMonth', 'ordersLastYear', 'categories', 'orders'));
    }

	public function indexOld(Request $request)
	{
		$valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));
        if($valsearch==""||$valsearch=="0"){
            $q_search = "";
        }else{
            $q_search = " AND name like '%".$valsearch."%'";
        }

        $status = $request->input('status');
        if($status==""){
            $q_status = "";
        }else{
            $q_status = " AND status = '".$status."'";
        }

        $ordering = $request->input('ordering');
        $params = $request->input('params');
        

        $mulai = $request->input('mulai');
        $sampai = $request->input('sampai');

        if($mulai=="" || $mulai==null){
            $mulai = date('Y-m-01');
        }

        if($sampai=="" || $sampai==null){
            $sampai = date('Y-m-d');
        }

        if($mulai==""||$mulai=="0"){
            $q_waktu = "";

        }else{

             $q_waktu = " AND created_at > '".$mulai."'";

            if($sampai==""||$sampai=="0"){
                $q_sampai = "";
            }else{
                $q_waktu = " AND created_at between '".$mulai." 00:00:01' AND '".$sampai." 23:23:59' ";
            }

        }

         $this_year = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfYear(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfYear(),
        ])->count();

        $this_month = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfMonth(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfMonth(),
        ])->count();

        $this_weeks =  Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfWeek(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfWeek(),
        ])->count();

        $today = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay(),
        ])->count();

        $transaction_all = Order::count();
        $transaction_new = Order::whereIn('status', [1,2,3])->count();

        try {
            //code...
                        if($params!="" && $ordering!=""){
                            $transaction = Order::whereRaw('1 '.$q_search.$q_waktu.$q_status)
                            ->orderBy($params,$ordering)
                            ->paginate(30);
                        }else{
                            $transaction = Order::whereRaw('1 '.$q_search.$q_waktu.$q_status)
                            ->orderBy('id','desc')
                            ->paginate(30);
                        }

                        return view('admin.pages.orders')
                        ->with('mulai', $mulai)
                        ->with('sampai', $sampai)
                        ->with('ordering', $ordering)
                        ->with('params', $params)
                        ->with('search', $valsearch)
                        ->with('status', $status)
                        ->with('transaction_all', $transaction_all)
                        ->with('transaction_new', $transaction_new)
                        ->with('this_year', $this_year)
                        ->with('this_weeks', $this_weeks)
                        ->with('this_month', $this_month)
                        ->with('today', $today)
                        ->with('orders', $transaction);

        } catch (\Throwable $th) {
            //throw $th;
            $transaction = Order::whereRaw('0')
                        ->paginate(30);

                        return view('admin.pages.orders-old')
                        ->with('mulai', $mulai)
                        ->with('sampai', $sampai)
                        ->with('ordering', $ordering)
                        ->with('params', $params)
                        ->with('search', $valsearch)
                        ->with('status', $status)
                        ->with('transaction_all', $transaction_all)
                        ->with('transaction_new', $transaction_new)
                        ->with('this_year', $this_year)
                        ->with('this_weeks', $this_weeks)
                        ->with('this_month', $this_month)
                        ->with('today', $today)
                        ->with('orders', $transaction);
        }

	}


	public function orderDetail($id) {
            $order = Order::where('id', $id)->with('data_item')->first();
            $orderDetails = OrderDetail::whereNotNull('product_id')->where('order_id', $id)->with('product')->get();
            $orderPromos = OrderDetail::whereNull('product_id')->where('order_id', $id)->with('promo')->get();
            $location = $this->location->get();
            $totalPrice = OrderDetail::whereNotNull('product_id')->where('order_id', $id)->sum('total_price');
			return view('admin.pages.order-detail', compact('order', 'orderDetails', 'orderPromos', 'location', 'totalPrice'));
    }

	public function updateStatus(Request $request) {

        DB::beginTransaction();

            $order = Order::where('id', $request->input('order-id'))->with('data_user')->first();
            if($order){
                $status = $request->input('status');
                $order->status = $status;

                // update confirmation_time
                if ($status == '2') {
                    $order->confirmation_time = Carbon::now();
                }

                // update delivery_time
                if ($status == '3') {
                    $order->delivery_time = Carbon::now();
                }

                $order->save();

                $activity = "";
                if($status=='1'){
                    $activity = 'new order';
                }else if($status=='2'){
                    $activity = 'order confirm';
                }else if($status=='3'){
                    $activity = 'order process';
                    $order->delivery_time = Carbon::now();
                    $order->save();
                }else if($status=='4'){
                    $activity = 'order completed';
                    $order->complete_time = Carbon::now();
                    $order->save();

                    // add point
                    $point  = round($order->payment_total / 10000);
                    $user   = $this->user->find($order->customer_id);
                    $this->user->where('id', $order->customer_id)
                                ->update([
                                    'point' => $user->point + $point
                                ]);
                    // log
                    // $log = Log::create([
                    //     'log_time' => date('Y-m-d H:i:s'),
                    //     'activity' => 'add ' . $point . ' point to user with id : ' . $order->customer_id,
                    //     'table_name' => 'orders',
                    //     'table_id' => $order->id,
                    //     'from_user' => auth()->user()->id,
                    //     'to_user' => $order->customer_id,
                    //     'platform' => "web",
                    //     'user_seen' => null,
                    //     'admin_seen' => null,
                    //     'status' => null
                    // ]);

                    // notification reminder review product
                    // $this->orderNotification('reminder review', $order->customer_id, Carbon::now());
                    $this->sendNotification($order->customer_id, $order->status);
                }else if($status=='10'){
                    $activity = 'order cancel';
                    $order->complete_time = Carbon::now();
                    $order->save();

                    foreach($order->data_item as $row):
                        $p = Product::find($row->product_id);
                        if($p){
                            $p->stock = $p->stock+$row->qty;
                            $p->save();
                        }
                    endforeach;
                    
                }

                // notification
                // $this->orderNotification($activity, $order->customer_id, Carbon::now());
                $this->sendNotification($order->customer_id, $order->status);

                // for data_content log
                $dataContent = $this->order->where('id', $request->input('order-id'))->with('data_item.product')->first();

                // log
                $log = Log::create([
                    'log_time'      => date('Y-m-d H:i:s'),
                    'activity'      => $activity,
                    'table_name'    => 'orders',
                    'table_id'      => $order->id,
                    'from_user'     => auth()->user()->id,
                    'to_user'       => $order->customer_id,
                    'data_content'  => $dataContent,
                    'platform'      => "web",
                    'user_seen'     => null,
                    'admin_seen'    => null,
                    'status'        => null
                ]);

                // update data to erp
                if ($status == 4 || $status == 10) {
                    Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                        'X-API-KEY'         => config('erp.x_api_key'),
                        'token'             => config('erp.token_api'),
                        'invoice'           => $order->invoice,
                        'kode'              => $order->data_user->site_code,
                        'status_update_erp' => $status
                    ]);
                }

                DB::commit();

                return redirect('/admin/order-detail/'.$order->id)
                    ->with('status', 1)
                    ->with('message', "Pesanan telah diupdate!");
                
            }else{

                DB::rollBack();
                return redirect(url('admin/orders'));
            }
    }

    public function updateResi(Request $request) {
        $trans = Order::find($request->id);
 
        $trans->delivery_track =  $request->resi;
         if ($request->hasFile('file')) {
             $file = $request->file('file');
             $ext  = $file->getClientOriginalExtension();
 
             $newName = "resi-".date('Y-m-d-His') . "." . $ext;
 
             $image_resize = Image::make($file->getRealPath());
             $image_resize->save(('images/' .$newName));
 
             $trans->photo = $newName;
 
         }
 
     $trans->save();
     return redirect('/admin/order-detail/'.$trans->id)
         ->with('status', 1)
         ->with('message', "Bukti resi telah diupload!");
    }

	public function invoice($order_id)
	{
		$order = Order::where('id', $order_id)->first();
        $orderPromos = OrderDetail::whereNull('product_id')->where('order_id', $order_id)->with('promo')->get();
        $pdf = PDF::loadview('public/member/invoice-order',[
                                                            'order'         => $order, 
                                                            'orderPromos'   => $orderPromos
                                                        ]);
        return $pdf->stream();
    }

	public function delivery($order_id)
	{
		$order = Order::where('id', $order_id)->first();
        $pdf = PDF::loadview('admin/pages/delivery',['order'=>$order]);
        return $pdf->stream();
    }


    // public function upload_images(Request $request, $id){
    //    $trans = OrderPayment::find($id);
    //    $trans_id = $trans->order_id;

    //     if ($request->hasFile('upload')) {
    //         $file = $request->file('upload');
    //         $ext  = $file->getClientOriginalExtension();

    //         $newName = "upload-".date('Y-m-d-His') . "." . $ext;

    //         $image_resize = Image::make($file->getRealPath());
    //         $image_resize->save(('uploads/' .$newName));

    //         $trans->upload = $newName;
    //         $trans->status = 0;

    //         $order = Order::find($trans_id);
    //         $order->notification = " Berhasil upload pembayaran, tunggu konfirmasi dari kami untuk proses selanjutnya";
    //         \Mail::to('admin@iklanqu.com')->send(new OrderStatus($order));
    //         \Mail::to(Auth::user()->email)->send(new OrderStatus($order));
    //     }

    //     $trans->save();
    //     return redirect('/member/order-detail/'.$trans_id)
    //         ->with('status', 1)
    //         ->with('message', "Bukti pembayaran telah diupload, tunggu informasi dari kami!");
    // }

    public function warehouseAssign(Request $request, $order_id)
    {
        $order_detail = $this->order_detail->where('order_id', $order_id)->get();
        foreach($order_detail as $item){
            foreach($request->product_id as $i => $value){
                if($item->product_id == $value){
                    $item->location_id = $request->location_id[$i];
                    $item->save();
                }
           }
        }

        return redirect($request->url)
            ->with('status', 1)
            ->with('message', "Data Tersimpan!");
    }


	public function store(Request $request)
    {
            $user = User::find(Auth::user()->id);

            DB::beginTransaction();

            $user_address = UserAddress::find($request->input('address-id'));

            $order = Order::create([
            
                "invoice" => "",
                "customer_id" => $user->id,
                "name" => $user_address->address_name,
                "phone" => $user_address->address_phone,
                "address" => $user_address->address,
                "location" => "",
                "kelurahan" => $user_address->kelurahan,
                "kecamatan" => $user_address->kecamatan,
                "kota" => $user_address->kota,
                "provinsi" => $user_address->provinsi,
                "kode_pos" => $user_address->kodepos,
                "latitude" => null,
                "longitude" => null,
                "payment_method" => null,
                "payment_link" => null,
                "payment_date" => null,
                "payment_total" => (int)$request->input('payment-total'),
                "coupon_id" => null,
                "payment_discount_code" => null,
                "payment_discount" => null,
                "payment_code" => null,
                "order_weight" => (double)$request->input('weight'),
                "order_distance" => null,
                "delivery_status" => null,
                "delivery_fee" => (int)$request->input('delivery-fee'),
                "delivery_track" => "",
                "courier" => $request->input('courier'),
                "delivery_service" => $request->input('ongkir'),
                "delivery_time" => null,
                "delivery_date" => null,
                "order_time" => date('Y-m-d H:i:s'),
                "confirmation_time" => null,
                "notes" => $request->input('notes'),
                "status" => 1
            
            ]);

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "new order";
        $logs->data_content = "customer_id : " . $user_address . ", name : " . $user_address->address_name . ", phone : " . $user_address->address_phone . ", address : " . $user_address->address . ", kelurahan : " . $user_address->kelurahan . ", kecamatan : " . $user_address->kecamatan . ", kota : " . $user_address->kota . ", provinsi : " . $user_address->provinsi . ", kode_pos : " . $user_address->kodepost . ", payment_total : " . (int)$request->input('payment-total') . ", order_weight : " . (double)$request->input('weight') . ", delivery_fee : " . (int)$request->input('delivery_fee') . ", courier : " . $request->input('courier') . ", delivery_service : " . $request->input('ongkir') . ", notes : " . $request->input('notes');
        $logs->table_name   = 'orders';
        $logs->column_name  = 'customer_id, name, phone, address, provinsi, kode_pos, payment_total, order_weight, delivery_fee, courier, delivery_service, notes';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($order){

                $shopping_cart = ShoppingCart::where('user_id', $user->id)->get();

                $success = false;
                foreach($shopping_cart as $row){
                    $trans_detail = OrderDetail::create([
                        'order_id'           => $order->id,
                        'product_id'           => $row->product_id,
                        'price'           => $row->price,
                        'qty'           => $row->qty,
                        'total_price'           => $row->total_price
                    ]);

                    if($trans_detail){
                        $row->forceDelete();
                        $success = true;
                    }else{
                        $success = false;
                    }
                }

                if($success){
                    DB::commit();

                    return redirect(url('member/order-detail/'.$order->id))
                    ->with('status', 1)
                    ->with('message', "Order Berhasil, tunggu konfirmasi dari kami!");
                }else{
                    DB::rollBack();

                    return redirect(url('checkout'))
                    ->with('status', 2)
                    ->with('message', "Order Gagal, coba lagi beberapa saat!");
                }
                
        }else{

            DB::rollBack();

                return redirect(url('checkout'))
                ->with('status', 2)
                ->with('message', "Order Gagal, coba lagi beberapa saat!");
        }

    }


    public function paymentUpload(Request $request){

        $order=  $this->order->find($request->input('id'));

        if($order){

            if($request->hasFile('image'))
            {
                // image's folder
                $folder = 'payment';
                // image's filename
                $newName = "payment-" .$order->id."-".date('Ymd-His');
                // image's form field name
                $form_name = 'image';
                $order->payment_link = \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
                $order->payment_date= date('Y-m-d H:i:s');
                $order->save();
            }
            

            return redirect(url('member/order-detail/'.$order->id));
        }

    }

    // public function reportSales(Request $request)
	// {
	// 	$valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));
    //     if($valsearch==""||$valsearch=="0"){
    //         $q_search = "";
    //     }else{
    //         $q_search = " AND name like '%".$valsearch."%'";
    //     }

    //     $status = $request->input('status');
    //     if($status==""){
    //         $q_status = " AND status = 4";
    //         $status= 4;
    //     }else{
    //         $q_status = " AND status = '".$status."'";
    //     }

    //     $ordering = $request->input('ordering');
    //     $params = $request->input('params');
        

    //     $mulai = $request->input('mulai');
    //     $sampai = $request->input('sampai');

    //     if($mulai=="" || $mulai==null){
    //         $mulai = date('Y-m-01');
    //     }

    //     if($sampai=="" || $sampai==null){
    //         $sampai = date('Y-m-d');
    //     }

    //     if($mulai==""||$mulai=="0"){
    //         $q_waktu = "";

    //     }else{

    //          $q_waktu = " AND created_at > '".$mulai."'";

    //         if($sampai==""||$sampai=="0"){
    //             $q_sampai = "";
    //         }else{
    //             $q_waktu = " AND created_at between '".$mulai." 00:00:01' AND '".$sampai." 23:23:59' ";
    //         }

    //     }

    //      $this_year = Order::whereBetween('created_at', [
    //         Carbon::now()->setTimezone('Asia/Jakarta')->startOfYear(),
    //         Carbon::now()->setTimezone('Asia/Jakarta')->endOfYear(),
    //     ])->where('status', 4)->count();

    //     $this_month = Order::whereBetween('created_at', [
    //         Carbon::now()->setTimezone('Asia/Jakarta')->startOfMonth(),
    //         Carbon::now()->setTimezone('Asia/Jakarta')->endOfMonth(),
    //     ])->where('status', 4)->count();

    //     $this_weeks =  Order::whereBetween('created_at', [
    //         Carbon::now()->setTimezone('Asia/Jakarta')->startOfWeek(),
    //         Carbon::now()->setTimezone('Asia/Jakarta')->endOfWeek(),
    //     ])->where('status', 4)->count();

    //     $today = Order::whereBetween('created_at', [
    //         Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay(),
    //         Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay(),
    //     ])->where('status', 4)->count();

    //     $transaction_all = Order::where('status', 4)->count();

    //     try {
    //         //code...
    //                     if($params!="" && $ordering!=""){
    //                         $transaction = Order::whereRaw('1 '.$q_search.$q_waktu.$q_status)
    //                         ->orderBy($params,$ordering)
    //                         ->get();
    //                     }else{
    //                         $transaction = Order::whereRaw('1 '.$q_search.$q_waktu.$q_status)
    //                         ->orderBy('id','desc')
    //                         ->get();
    //                     }

    //                     return view('admin.pages.report-sales')
    //                     ->with('mulai', $mulai)
    //                     ->with('sampai', $sampai)
    //                     ->with('ordering', $ordering)
    //                     ->with('params', $params)
    //                     ->with('search', $valsearch)
    //                     ->with('status', $status)
    //                     ->with('transaction_all', $transaction_all)
    //                     ->with('this_year', $this_year)
    //                     ->with('this_weeks', $this_weeks)
    //                     ->with('this_month', $this_month)
    //                     ->with('today', $today)
    //                     ->with('orders', $transaction);

    //     } catch (\Throwable $th) {
    //         //throw $th;
    //                     $transaction = Order::whereRaw('0')
    //                     ->paginate(30);

    //                     return view('admin.pages.report-sales')
    //                     ->with('mulai', $mulai)
    //                     ->with('sampai', $sampai)
    //                     ->with('ordering', $ordering)
    //                     ->with('params', $params)
    //                     ->with('search', $valsearch)
    //                     ->with('status', $status)
    //                     ->with('transaction_all', $transaction_all)
    //                     ->with('this_year', $this_year)
    //                     ->with('this_weeks', $this_weeks)
    //                     ->with('this_month', $this_month)
    //                     ->with('today', $today)
    //                     ->with('orders', $transaction);
    //     }

    // }

    public function reportSales(Request $request)
	{
        $order = $this->order->query();
        if($request->has('status_faktur')) {
            $status_faktur = $request->status_faktur;
        } else {
            $status_faktur = 'F';
        }

        if ($request->start_date && $request->end_date) {
            $startDate = strtotime($request->start_date);
            $endDate = strtotime($request->end_date);

            $orders  = $order
                            ->with(['data_item.product', 'data_user.user_address', 'order_details'])
                            ->where('status_faktur', $status_faktur)
                            ->whereBetween('order_time', [date('Y-m-d H:i:s', $startDate), date('Y-m-d H:i:s', $endDate)])
                            ->where(function($query) {
                                $query
                                    ->where('status', '2')
                                    ->orWhere('status', '3')
                                    ->orWhere('status', '4');
                            })
                            // ->where('status', '2')
                            // ->orWhere('status', '3')
                            // ->orWhere('status', '4')
                            ->get();
        } else {
            $orders   = $order
                            ->with(['data_item.product', 'data_user.user_address', 'order_details'])
                            ->whereDate('order_time', Carbon::today())
                            ->where('status_faktur', $status_faktur)
                            ->where(function($query) {
                                $query
                                    ->where('status', '2')
                                    ->orWhere('status', '3')
                                    ->orWhere('status', '4');
                            })
                            // ->where('status', '2')
                            // ->orWhere('status', '3')
                            // ->orWhere('status', '4')
                            ->get();
        }
        $total = 0;
        if($orders) {
            $total = $orders->sum('payment_final');
            // foreach($orders as $order){
            //     // foreach($order->order_details as $order_detail){
            //         $total += $order->payment_final;
            //     // }
            // }
        }
        
                
        return view('admin.pages.report-sales', compact('orders', 'total'));
    }

    public function reportSalesItem(Request $request)
	{
        $order = $this->order->query();
        if($request->has('status_faktur')) {
            $status_faktur = $request->status_faktur;
        } else {
            $status_faktur = 'F';
        }
        if ($request->start_date && $request->end_date) {
            $startDate = strtotime($request->start_date);
            $endDate = strtotime($request->end_date);

            $orders  = $order
                    ->with(['data_item.product', 'data_user.user_address', 'order_details'])
                    ->where('status_faktur', $status_faktur)
                    ->whereBetween('order_time', [date('Y-m-d H:i:s', $startDate), date('Y-m-d H:i:s', $endDate)])
                    ->where(function($query) {
                        $query
                            ->where('status', '2')
                            ->orWhere('status', '3')
                            ->orWhere('status', '4');
                    })
                    // ->where('status', '2')
                    // ->orWhere('status', '3')
                    // ->orWhere('status', '4')
                    ->get();
        } else {
            $orders   = $order
                    ->with(['data_item.product', 'data_user.user_address', 'order_details'])
                    ->whereDate('order_time', Carbon::today())
                    ->where('status_faktur', $status_faktur)
                    ->where(function($query) {
                        $query
                            ->where('status', '2')
                            ->orWhere('status', '3')
                            ->orWhere('status', '4');
                    })
                    // ->where('status', '2')
                    // ->orWhere('status', '3')
                    // ->orWhere('status', '4')
                    ->get();
        }

        $total = 0;
        if($orders) {
            $total = $orders->sum('payment_final');
            // foreach($orders as $order){
            //     // foreach($order->order_details as $order_detail){
            //         $total += $order->payment_final;
            //     // }
            // }
        }
                
        return view('admin.pages.report-sales', compact('orders', 'total'));
    }

    public function reportSalesTransaksi(Request $request)
	{
        $order = $this->order->query();
        if($request->has('status_faktur')) {
            $status_faktur = $request->status_faktur;
        } else {
            $status_faktur = 'F';
        }

        if ($request->start_date && $request->end_date) {
            $startDate = strtotime($request->start_date);
            $endDate = strtotime($request->end_date);

            $orders  = $order
                    ->with(['data_item.product', 'data_user.user_address'])
                    ->where('status_faktur', $status_faktur)
                    ->whereBetween('order_time', [date('Y-m-d H:i:s', $startDate), date('Y-m-d H:i:s', $endDate)])
                    ->where(function($query) {
                        $query
                            ->where('status', '2')
                            ->orWhere('status', '3')
                            ->orWhere('status', '4');
                    })
                    // ->where('status', '2')
                    // ->orWhere('status', '3')
                    // ->orWhere('status', '4')
                    ->get();
        } else {
            $orders   = $order
                    ->with(['data_item.product', 'data_user.user_address', 'order_details'])
                    ->whereDate('order_time', Carbon::today())
                    ->where('status_faktur', $status_faktur)
                    ->where(function($query) {
                        $query
                            ->where('status', '2')
                            ->orWhere('status', '3')
                            ->orWhere('status', '4');
                    })
                    // ->where('status', '2')
                    // ->orWhere('status', '3')
                    // ->orWhere('status', '4')
                    ->get();                    
        }

        if($orders) {
            $total = $orders->sum('payment_final');
        }
                
        return view('admin.pages.report-sales-transaksi', compact('orders', 'total'));
    }
    
    public function reportStatistik(Request $request)
	{
		$valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));
        if($valsearch==""||$valsearch=="0"){
            $q_search = "";
        }else{
            $q_search = " AND name like '%".$valsearch."%'";
        }

        $status = $request->input('status');
        if($status==""){
            $q_status = " AND status = 4";
            $status= 4;
        }else{
            $q_status = " AND status = '".$status."'";
        }

        $ordering = $request->input('ordering');
        $params = $request->input('params');
        

        $mulai = $request->input('mulai');
        $sampai = $request->input('sampai');

        if($mulai=="" || $mulai==null){
            $mulai = date('Y-m-01');
        }

        if($sampai=="" || $sampai==null){
            $sampai = date('Y-m-d');
        }

        if($mulai==""||$mulai=="0"){
            $q_waktu = "";

        }else{

            $q_waktu = " AND created_at > '".$mulai."'";

            if($sampai==""||$sampai=="0"){
                $q_sampai = "";
            }else{
                $q_waktu = " AND created_at between '".$mulai." 00:00:01' AND '".$sampai." 23:23:59' ";
            }

        }

        $status_total = $request->input('status');

        if($status_total != null){
            $this_year = Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfYear(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfYear(),
            ])->where('status', $status_total)->count();
    
            $this_month = Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfMonth(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfMonth(),
            ])->where('status', $status_total)->count();
    
            $this_weeks =  Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfWeek(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfWeek(),
            ])->where('status', $status_total)->count();
    
            $today = Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay(),
            ])->where('status', $status_total)->count();
        }else {
            $this_year = Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfYear(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfYear(),
            ])->count();
    
            $this_month = Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfMonth(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfMonth(),
            ])->count();
    
            $this_weeks =  Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfWeek(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfWeek(),
            ])->count();
    
            $today = Order::whereBetween('created_at', [
                Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay(),
                Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay(),
            ])->count();
        }

        $transaction_all = Order::where('status', 4)->count();


        try {
            //code...
                        if($params!="" && $ordering!=""){
                            $transaction = Order::whereRaw('1 '.$q_search.$q_waktu.$q_status)
                            ->orderBy($params,$ordering)
                            ->get();
                        }else{
                            $transaction = Order::whereRaw('1 '.$q_search.$q_waktu.$q_status)
                            ->orderBy('id','desc')
                            ->get();
                        }

                        return view('admin.pages.report-statistik')
                        ->with('mulai', $mulai)
                        ->with('sampai', $sampai)
                        ->with('ordering', $ordering)
                        ->with('params', $params)
                        ->with('search', $valsearch)
                        ->with('status', $status)
                        ->with('transaction_all', $transaction_all)
                        ->with('this_year', $this_year)
                        ->with('this_weeks', $this_weeks)
                        ->with('this_month', $this_month)
                        ->with('today', $today)
                        ->with('orders', $transaction);

        } catch (\Throwable $th) {
            //throw $th;
                        // $transaction = Order::whereRaw('0')
                        $transaction = Order::paginate(30);

                        return view('admin.pages.report-statistik')
                        ->with('mulai', $mulai)
                        ->with('sampai', $sampai)
                        ->with('ordering', $ordering)
                        ->with('params', $params)
                        ->with('search', $valsearch)
                        ->with('status', $status)
                        ->with('transaction_all', $transaction_all)
                        ->with('this_year', $this_year)
                        ->with('this_weeks', $this_weeks)
                        ->with('this_month', $this_month)
                        ->with('today', $today)
                        ->with('orders', $transaction);
        }

	}


    public function sendNotification($user_id, $status)
    {
        $activity = ""; 
                                                                    // give status 
        if ($status         ==  '1') {
            $activity = 'Pesanan Baru';
        } else if ($status  ==  '2') {
            $activity = 'Pesanan Anda Terkonfirmasi';
        } else if ($status  ==  '3') {
            $activity = 'Pesanan Anda Sedang Terproses';
        } else if ($status  ==  '4') {
            $activity = 'Pesanan Complete';
        }

        $fcm_token = $this->user
                            ->where('id', $user_id)
                            ->pluck('fcm_token')
                            ->all(); // get fcm_token from user table

        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification"  => [
                "title" => 'Status Orderan',
                "body"  => $activity,  
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
    // send order notification
    // public function orderNotification($title, $userID, $sendedAt)
    // {
    //     $optionBuilder = new OptionsBuilder();
    //     $optionBuilder->setTimeToLive(60 * 20);

    //     $notificationBuilder = new PayloadNotificationBuilder('New Order Notification');
    //     $notificationBuilder->setBody($title)
    //         ->setSound('default');
    //         // ->setClickAction('http://localhost:8000/admin/chats');

    //     $dataBuilder = new PayloadDataBuilder();
    //     $dataBuilder->addData([
    //         'title'         => $title,
    //         'user_id'       => $userID,
    //         'sended_at'     => $sendedAt,
    //     ]);

    //     $option = $optionBuilder->build();
    //     $notification = $notificationBuilder->build();
    //     $data = $dataBuilder->build();

    //     // to multiple device
    //     // $tokens = User::all()->pluck('fcm_token')->toArray();

    //     // to multiple device
    //     $tokens = $this->user->where('id', $userID)->pluck('fcm_token')->toArray();

    //     $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

    //     return $downstreamResponse->numberSuccess();
    // }

    public function reportSalesExcel(Request $request) {
        return Excel::download(new ReportItemExport($request->start_date, $request->end_date), 'Report-sales-item.xlsx');
    }

    public function reportSalesTransaksiExcel(Request $request) {
        return Excel::download(new ReportTransaksiExport($request->start_date, $request->end_date), 'Report-sales-transaksi.xlsx');
    }
}
