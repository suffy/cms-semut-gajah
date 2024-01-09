<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Location;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Log;
use App\Mail\Invoice;
use App\User;
use App\OrderDetail;
use App\Product;
use App\ProductReview;
use App\ProductReviewImage;
use App\ShoppingCart;
use App\UserAddress;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Auth;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Illuminate\Support\Carbon;
use PDF;

class OrderController extends Controller
{

    
    protected $order;
    protected $user;
    protected $location;
    protected $order_detail;

    public function __construct(Order $order, User $user, Location $location, OrderDetail $order_detail)
    {
        $this->order = $order;
        $this->user = $user;
        $this->location = $location;
        $this->order_detail = $order_detail;
    }

	public function index(Request $request)
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
        ])->where('customer_id', Auth::user()->id)->count();

        $this_month = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfMonth(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfMonth(),
        ])->where('customer_id', Auth::user()->id)->count();

        $this_weeks =  Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfWeek(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfWeek(),
        ])->where('customer_id', Auth::user()->id)->count();

        $today = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay(),
        ])->where('customer_id', Auth::user()->id)->count();

        $transaction_all = Order::count();
        $transaction_new = Order::whereIn('status', [1,2,3])->where('customer_id', Auth::user()->id)->count();

        try {
            //code...
                        if($params!="" && $ordering!=""){
                            $transaction = Order::whereRaw("1 AND customer_id = '".Auth::user()->id."' ".$q_search.$q_waktu.$q_status)
                            ->orderBy($params,$ordering)
                            ->paginate(30);
                        }else{
                            $transaction = Order::whereRaw("1 AND customer_id = '".Auth::user()->id."' ".$q_search.$q_waktu.$q_status)
                            ->orderBy('id','desc')
                            ->paginate(30);
                        }

                        return view('public.member.order')
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
                        ->where('customer_id', Auth::user()->id)
                        ->paginate(30);

                        return view('public.member.order')
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
    
	public function history(Request $request)
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
        ])->where('customer_id', Auth::user()->id)->count();

        $this_month = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfMonth(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfMonth(),
        ])->where('customer_id', Auth::user()->id)->count();

        $this_weeks =  Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfWeek(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfWeek(),
        ])->where('customer_id', Auth::user()->id)->count();

        $today = Order::whereBetween('created_at', [
            Carbon::now()->setTimezone('Asia/Jakarta')->startOfDay(),
            Carbon::now()->setTimezone('Asia/Jakarta')->endOfDay(),
        ])->where('customer_id', Auth::user()->id)->count();

        $transaction_all = Order::count();
        $transaction_new = Order::whereIn('status', [1,2,3])->where('customer_id', Auth::user()->id)->count();

        try {
            //code...
                        if($params!="" && $ordering!=""){
                            $transaction = Order::whereRaw("1 AND order_time <= DATE_SUB(NOW(), INTERVAL order_time MONTH) AND customer_id = '".Auth::user()->id."' ".$q_search.$q_waktu.$q_status)
                            ->orderBy($params,$ordering)
                            ->paginate(30);
                        }else{
                            $transaction = Order::whereRaw("1 AND order_time <= DATE_SUB(NOW(), INTERVAL order_time MONTH) AND customer_id = '".Auth::user()->id."' ".$q_search.$q_waktu.$q_status)
                            ->orderBy('id','desc')
                            ->paginate(30);
                        }

                        return view('public.member.order')
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
                        ->where('customer_id', Auth::user()->id)
                        ->paginate(30);

                        return view('public.member.order')
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


	public function detail($id) {
            $order = Order::where('id', $id)->with('data_item')->first();
			return view('public.member.order-detail', compact('order'));
    }

	public function invoice($order_id)
	{
        $order = Order::where('id', $order_id)->first();
        $pdf = PDF::loadview('public/member/invoice-order',['order'=>$order]);
        return $pdf->stream();
    }


	public function payment($id)
	{
		$order = Order::where('id', $id)->with('data_item')->first();
        return view('public.member.payment', compact('order'));
    }

    public function upload_images(Request $request, $id){
       $trans = OrderPayment::find($id);
       $trans_id = $trans->order_id;

        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $ext  = $file->getClientOriginalExtension();

            $newName = "upload-".date('Y-m-d-His') . "." . $ext;

            $image_resize = Image::make($file->getRealPath());
            $image_resize->save(('uploads/' .$newName));

            $trans->upload = $newName;
            $trans->status = 0;

            $order = Order::find($trans_id);
            $order->notification = " Berhasil upload pembayaran, tunggu konfirmasi dari kami untuk proses selanjutnya";
            \Mail::to(Auth::user()->email)->send(new OrderStatus($order));
        }

    $trans->save();
    return redirect('/member/order-detail/'.$trans_id)
        ->with('status', 1)
        ->with('message', "Bukti pembayaran telah diupload, tunggu informasi dari kami!");

}


	public function store(Request $request)
    {


            $user = User::find(Auth::user()->id);

            DB::beginTransaction();

            $user_address = UserAddress::find($request->input('address-id'));

            $order = Order::create([
            
                "invoice" => "",
                "customer_id" => $user->id,
                "name" => $user_address->name,
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
                "payment_final" => (int)$request->input('payment-final'),
                "coupon_id" => $request->input('voucher-id'),
                "payment_discount_code" => $request->input('voucher-code'),
                "payment_discount" => $request->input('payment-discount'),
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

        if ($order){

                $shopping_cart = ShoppingCart::where('user_id', $user->id)->get();

                $success = false;
                foreach($shopping_cart as $row){

                    $prod = Product::find($row->product_id);
                    $trans_detail = OrderDetail::create([
                        'order_id'           => $order->id,
                        'product_id'           => $row->product_id,
                        'price'           => $row->price,
                        'qty'           => $row->qty,
                        'total_price'           => $row->total_price
                    ]);

                    if($trans_detail){

                        if($prod){
                            $prod->stock = $prod->stock-$row->qty;
                            $prod->save();
                        }

                        $row->forceDelete();
                        $success = true;
                    }else{
                        $success = false;
                    }
                }

                if($success){
                    DB::commit();

                    try {
                        //code...
                        \Mail::to(Auth::user()->email)->send(new Invoice($order));
                        return redirect(url('member/order-detail/'.$order->id))
                        ->with('status', 1)
                        ->with('message', "Order Berhasil, tunggu konfirmasi dari kami!");
                    } catch (\Throwable $th) {
                        //throw $th;

                        return redirect(url('member/order-detail/'.$order->id))
                        ->with('status', 1)
                        ->with('message', "Order Berhasil, tetapi email gagal terkirim!");
                    }
                    
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

    public function resendEmailInvoice($id){

        $order = $this->order->find($id);
        \Mail::to(Auth::user()->email)->send(new Invoice($order));
    }


    public function submitReview($id, Request $request){

        DB::beginTransaction();
        $product = Order::find($id);

        $id_review = $request->id;

        $success = false;

        try {
            //code...
            for($i=0; $i<count($id_review); $i++){
                $product_review = ProductReview::find($request->review_id[$i]);
                if($product_review){
                    
                }else{
                    $product_review = new ProductReview();
                    
                }
    
                $product_review->user_id = Auth::user()->id;
                $product_review->order_id = $id;
                $product_review->product_id = $request->id[$i];
                $product_review->star_review = $request->selected_rating[$i];
                $product_review->detail_review = $request->review[$i];
                $product_review->save();
    
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;

            // return $th;
            $success  = false;
            DB::rollBack();
        }
        
        return back()->with('status', 1)
        ->with('message', "Review telah diupdate");
    }

}
