<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Banner;
use App\Order;
use App\Page;
use App\PartnerLogo;
use App\Post;
use App\PostCategory;
use App\Product;
use App\ProductOffer;
use App\ShoppingCart;
use App\User;
use App\Voucher;
use App\OrderDetail;
use App\ProductReview;
use Illuminate\Support\Facades\Auth;
use PDF;

class PageController extends Controller
{
    //

    protected $category;
    protected $banner;
    protected $product;
    protected $product_offers;
    protected $shopping_cart;
    protected $partnerlogo;
    protected $post_category;
    protected $voucher;
    protected $user;

    public function __construct(User $user, Category $category, Banner $banner, Product $product, ShoppingCart $shopping_cart, PartnerLogo $partnerlogo, ProductOffer $product_offers, PostCategory $post_category, Voucher $voucher)
    {
        $this->category = $category;
        $this->banner = $banner;
        $this->product = $product;
        $this->product_offers = $product_offers;
        $this->shopping_cart = $shopping_cart;
        $this->partnerlogo = $partnerlogo;
        $this->post_category     = $post_category;
        $this->voucher     = $voucher;
        $this->user     = $user;
    }

    function index(){
        $category   = $this->category->where('status', '1')->get();
        $banner     = $this->banner->where('page', '/')
                    ->where('status', '1')->get();
        $flash      = $this->product_offers->where('status', '1')
                        ->with('offer_item')
                        ->orderBy('id', 'desc')
                        ->get();
        $new        = $this->product->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->where('status', '1')->get();

        $featured   = $this->product->orderBy('created_at', 'desc')
                    ->where('featured', '1')
                    ->limit(10)
                    ->where('status', '1')->get();

        return view('public/pages/index', compact('category', 'flash', 'new', 'banner', 'featured'));
    }

    function allProducts(){
        $product_category   = $this->product->where('status', '1')->get();
        $category           = '';
        $category_list      = $this->category->where('status', '1')->get();
        $partnerlogo        = $this->partnerlogo->where('status', '1')->get();
        $newest_product     = $this->product->limit(5)->where('status', '1')->orderBy('created_at','DESC')->get();
        $allproducts        = $this->product->where('status', '1')->count();
        return view('public/pages/products', compact('product_category', 'category', 'category_list', 'newest_product', 'partnerlogo', 'allproducts'));
    }

    function productFilter(Request $request){
        
        $input_category = $request->input('category');
        $input_start_price = $request->input('start_price');
        $input_end_price = $request->input('end_price');
        $input_vendor = $request->input('vendor');
        $input_tags = $request->input('tags');
        $input_search = $request->input('search');
        
        $products = $this->product->query();
        $products = $products->where('status', '1');

        $category = "";
        if($input_category!=""){
            $products = $products->where('category_id', $input_category);
            $cat = Category::find($input_category);
            $category = $cat->name ?? "";
        }

        if($input_start_price!=""){
            $products = $products->where('price', '>=', $input_start_price);
        }

        if($input_end_price!=""){
            $products = $products->where('price', '<=', $input_end_price);
        }

        if($input_vendor!=""){
            if($input_vendor!="0"){
                $products = $products->where('brand', 'like', '%'.$input_vendor.'%');
            }
        }

        if($input_search!=""){
            $products = $products->where('name', 'like', '%'.$input_search.'%');
        }

        $product2 = $products;
        $products = $products->paginate(18);

        $product_all = $product2->count();

        return view('public/pages/includes/product-grid', compact('products', 'product_all', 'category'));
    }

    function products($slug_category){
        $category_list      = $this->category->where('status', '1')->get();
        $product            = $this->product->withCount('category')->where('status', '1')->get();
        $category           = $this->category->where('slug', $slug_category)->first();
        $product_category   = $this->product->where('category_id', $category->id)
                            ->where('status', '1')->get();
        $best_seller        = OrderDetail::leftJoin('products', 'products.id', '=', 'order_detail.product_id')
                            ->groupBy('order_detail.product_id', 'products.name', 'products.slug', 'products.price_promo', 'products.price', 'products.image')
                            ->selectRaw('sum(order_detail.qty) as total_qty, products.slug, products.name, products.price_promo, products.price, products.image, order_detail.product_id')
                            ->orderBy('total_qty', 'DESC')->limit(10)
                            ->where('products.status', '1')
                            ->get();
        $top_rated          = ProductReview::leftJoin('products', 'products.id', '=', 'product_review.product_id')
                            ->groupBy('product_review.product_id', 'products.name', 'products.slug', 'products.price', 'products.image')
                            ->selectRaw('sum(product_review.star_review) as total_review, products.slug, products.name, products.price, product_review.product_id, products.image')
                            ->orderBy('total_review', 'DESC')->limit(5)
                            ->where('products.status', '1')
                            ->get();
        $partnerlogo        = $this->partnerlogo->where('status', '1')->get();
        $new                = $this->product->orderBy('created_at', 'desc')
                            ->where('status', '1')
                            ->limit(10)
                            ->get();
        $productTags        = Product::where('status', '1')
                            ->where('category_id', $category->id)
                            ->whereNotNull('tags')                            
                            ->pluck('tags');
                            
        return view('public/pages/product-category', compact('slug_category', 'product_category', 'category', 'best_seller', 'category_list', 'product', 'top_rated', 'partnerlogo', 'new', 'productTags'));
    }

    function productCategoryFilter(Request $request){
        
        $input_category = $request->input('category');
        $input_start_price = $request->input('start_price');
        $input_end_price = $request->input('end_price');
        $input_vendor = $request->input('vendor');
        $input_tags = $request->input('tags');
        
        $products = $this->product->query();
        $products = $products->where('status', '1');

        $category = "";
        if($input_category!=""){
            $cat = Category::where('slug', $input_category)->first();
            $products = $products->where('category_id', $cat->id);
            $category = $cat->name ?? "";
        }

        if($input_start_price!=""){
            $products = $products->where('price', '>=', $input_start_price);
        }

        if($input_end_price!=""){
            $products = $products->where('price', '<=', $input_end_price);
        }

        if($input_vendor!=""){
            if($input_vendor!="0"){
                $products = $products->where('brand', 'like', '%'.$input_vendor.'%');
            }
        }

        if($input_tags!=""){
            if($input_tags!="0"){
                $products = $products->where('tags', 'like', '%'.$input_tags.'%');
            }
        }

        $product2 = $products;
        $products = $products->paginate(9);

        $product_all = $product2->count();

        return view('public/pages/includes/product-category-grid', compact('products', 'product_all', 'category'));
    }

    function productDetail($slug){

        $product        = $this->product->where('slug', $slug)
                        ->first();
                    
        if($product){
            $product->view_counts = $product->view_counts+1;
            $product->save();

            $best_seller    = OrderDetail::leftJoin('products', 'products.id', '=', 'order_detail.product_id')
                            ->groupBy('order_detail.product_id', 'products.name', 'products.slug', 'products.price_promo', 'products.price', 'products.image')
                            ->selectRaw('sum(order_detail.qty) as total_qty, products.name, products.slug, products.price_promo, products.price, products.image, order_detail.product_id')
                            ->orderBy('total_qty', 'DESC')->limit(10)
                            ->where('products.status', '1')
                            ->get();
            $ratingsAverage = ProductReview::where('product_id', $product->id)
                            ->whereNotNull('star_review')
                            ->avg('star_review');
            $ratingsRound   = round($ratingsAverage, 2);
            $sumRatings     = ProductReview::where('product_id', $product->id)
                            ->whereNotNull('star_review')
                            ->count();
            $countRatings   = ProductReview::groupBy('star_review')
                            ->selectRaw('star_review, (ROUND((COUNT(star_review)/' . $sumRatings . '), 2) * 100) AS persen')
                            ->where('product_id', $product->id)
                            ->whereNotNull('star_review')
                            ->orderBy('star_review', 'DESC')
                            ->get();
                            
            return view('public/pages/product-detail', compact('product', 'best_seller', 'ratingsAverage', 'sumRatings', 'ratingsRound', 'countRatings'));
        }else{
            return redirect(url('/'));
        }
    }

    function cart(){
        return view('public/pages/cart');
    }

    function contact(){
        return view('public/pages/contact');
    }

    function checkout(){

        $user = Auth::user();

        if($user){
            $cart = $this->shopping_cart->where('user_id', $user->id)
            ->get();
            return view('public/pages/checkout')
                ->with('cart', $cart);
        }else{
            return redirect('cart');
        }
    }

    function pagePostDetail(Request $request){
        return view('public/pages/posts');
    }

    public function blog(Request $request){
        //logic pencarian judul
        // $valsearch = $request->input('search');
        $valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));
        if($valsearch==""||$valsearch=="0"){
            $q_search = "";
        }else{
            $q_search = " AND (title like '%".$valsearch."%' OR content like '%".$valsearch."%' OR tags like '%".$valsearch."%')";
        }

        // logic pencarian category
        $valcategory = $request->input('category');
        if($valcategory =="" || $valcategory =="0"){
            $q_category = "";
        }else{
            $q_category = " AND post_category_id = '".$valcategory."'";
        }

        if($request->has('orderby')){

            if($request->input('orderby')=='oldest'){
                $post = Post::whereRaw("status=1 ".$q_category." ".$q_search)
                ->orderBy('id', 'asc')
                ->paginate(9);
            }else if($request->input('orderby')=='newest'){
                $post = Post::whereRaw("status=1 ".$q_category." ".$q_search)
                ->orderBy('id', 'desc')
                ->paginate(9);
            }else{
                $post = Post::whereRaw("status=1 ".$q_category." ".$q_search)
                ->orderBy('id', 'desc')
                ->paginate(9);
            }

        }else{
            $post = Post::whereRaw("status=1 ".$q_category." ".$q_search)
                ->orderBy('id', 'desc')
                ->paginate(9);
        }

        $post_category = $this->post_category->all();
        return view('public/pages/blog', compact('post', 'post_category'));
    }

    function blogDetail($slug){
        $post = Post::where('slug', $slug)->first();

        if($post){
            return view('public/pages/blog-detail')
                    ->with('post', $post);
        }else{
            return redirect(url(''));
        }
    }

    public function page(){
        $pages          = Page::all();
        return view('public/pages/page', compact('pages'));
    }

    function pageDetail($slug){
        $page = Page::where('slug', $slug)->where('status', '1')->first();

        if($page){
            return view('public/pages/page-detail')
                    ->with('page', $page);
        }else{
            return redirect(url(''));
        }
    }

    function checkOngkir(Request $request){

        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $weight = $request->input('weight');
        $courier = $request->input('courier');

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "origin=".$origin."&destination=".$destination."&weight=".$weight."&courier=".$courier."",
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
            "key: c9e4f7323839dc52441a53f4e41af8d9"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }

    public function findVoucher(Request $request)
    {
        // Request data
        $code = $request->input('code');
        $user_id = $request->input('customer_id');
        $payment_total = $request->input('payment_total');

        // Find coupon by code
        $data = $this->voucher->where('code', $code)
            ->where('start_at', '<', date('Y-m-d H:i:s'))
            ->where('end_at', '>', date('Y-m-d H:i:s'))
            ->where('status', '=', '1')
            ->first();

        if ($data) {


            // Find user
            $user = $this->user->find($user_id);
            


            // Coupon used and max user
            if ($data->used != $data->max_use) {

                $min_trans = true;

                // Min trans
                if($data->min_transaction==0 || $data->min_transaction==null){
                    $min_trans = true;
                }else{

                    if($data->min_transaction>=($payment_total+1)){
                        $min_trans = false;

                        $status = 404;
                        $msg = 'Minimal order '.$data->min_transaction.' untuk dapat memakai voucher ini';
                        return response()->json(compact('status', 'msg'), 200);

                    }else{
                        $min_trans = true;
                    }
                }


                    // find existict order by user
                    $order = Order::where('payment_discount_code', $code)
                                    ->where('customer_id', $user_id)
                                    ->where('coupon_id', $data->id)
                                    ->count();

                    if($data->max_use_user==0||$data->max_use_user==null){

                            $status = 200;
                            $msg = 'Selamat, kupon tersedia';
                            return response()->json(compact('status', 'msg', 'data'), 200);
                    }else{

                        //find existing order by user and same day
                        if($order >= $data->max_use_user){
                            $status = 404;
                            $msg = 'Kupon sudah melebihi pemakaian per user';
                            return response()->json(compact('status', 'msg'), 200);
                        }else{

                        	$order_daily = Order::where('payment_discount_code', $code)
                                    ->where('customer_id', $user_id)
                                    ->where('coupon_id', $data->id)
                                    ->where('order_time', 'like', "%".date('Y-m-d')."%")
                                    ->count();

                                    if($order_daily>= $data->daily_use){
                                    	$status = 404;
			                            $msg = 'Kupon sudah melebihi pemakaian per user per hari';
			                            return response()->json(compact('status', 'msg'), 200);
                                    }

                            // coupon type
                            if($data->type=="nominal") {
                                $status = 200;
                                $msg = 'Selamat, kupon tersedia';
                                return response()->json(compact('status', 'msg', 'data'), 200);
                            }else{

                                $perc = $data->percent;
                                $nominal = $payment_total*($perc/100);

                                // check nominal
                                if($nominal>$data->max_nominal){
                                    $no = $data->max_nominal;
                                }else{
                                    $no = $nominal;
                                }

                                $data['nominal'] = $no;

                                $status = 200;
                                $msg = 'Selamat, kupon tersedia';
                                return response()->json(compact('status', 'msg', 'data'), 200);
                            }
                        }

                    }

            } else {
                $status = 404;
                $msg = 'Maaf, penggunaan kupon telah melebihi kuota.';
                return response()->json(compact('status', 'msg'), 200);
            }

        } else {
            $status = 404;
            $msg = 'Maaf, kode yang anda masukkan tidak tersedia.';
            return response()->json(compact('status', 'msg'), 200);
        }
    }
}
