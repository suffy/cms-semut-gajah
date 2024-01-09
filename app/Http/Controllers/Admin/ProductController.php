<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Location;
use App\Log;
use App\Order;
use App\PartnerLogo;
use App\Product;
use App\ProductImage;
use App\ProductLocation;
use App\ProductReview;
use App\MappingSite;
use App\ProductAvailability;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ProductController extends Controller
{

    protected $product;
    protected $category;
    protected $brand;
    protected $location;
    protected $product_loc;
    protected $order;
    protected $logs;
    protected $mappingSites;
    protected $productAvailability;

    public function __construct(Product $product, Category $category, PartnerLogo $brand, Location $location, ProductLocation $product_loc, Order $order, Log $log, MappingSite $mappingSites, ProductAvailability $productAvailability)
    {
        $this->product = $product;
        $this->category = $category;
        $this->brand = $brand;
        $this->location = $location;
        $this->product_loc = $product_loc;
        $this->order = $order;
        $this->logs = $log;
        $this->mappingSites = $mappingSites;
        $this->productAvailability = $productAvailability;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //

        $product = $this->product->query();

        if ($request->has('search')) {
            $product = $product->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('kodeprod', 'like', '%' . $request->search . '%');
        }

        if ($request->has('ordering')) {
            if ($request->input('ordering') == "desc") {

                if ($request->input('params') != "") {
                    $product = $product->orderBy($request->input('params'), 'desc');
                } else {
                    $product = $product->orderBy('id', 'desc');
                }
            }

            if ($request->input('ordering') == "asc") {

                if ($request->input('params') != "") {
                    $product = $product->orderBy($request->input('params'), 'asc');
                } else {
                    $product = $product->orderBy('id', 'asc');
                }
            }
        } else {
            $product = $product->orderBy('id', 'desc');
            $product = $product->orderBy('status', 'desc');
        }

        if ($request->has('status')) {
            if ($request->status != "") {
                $product = $product->where('status', $request->status);
            }
        }

        if ($request->has('category_id')) {
            if ($request->category_id != "") {
                $product = $product->where('category_id', $request->category_id);
            }
        }

        try {
            //code...
            $product = $product->with('price')->paginate(10);
            $category = $this->category->where('status', '1')->get();

            return view('admin.pages.products')
                ->with('category', $category)
                ->with('product', $product);
        } catch (\Throwable $th) {
            //throw $th;
            return redirect(url('admin/products'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = $this->category->where('status', '1')->get();
        $brand = $this->brand->get();
        return view('admin.pages.product-new', compact('category', 'brand'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $prod = new Product;

        $prod->name             = $request->input('name');
        $prod->slug             = strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u', '', strip_tags(strtolower(str_replace(" ", "-", $request->input('name')))))));
        $prod->category_id      = (int) $request->input('category_id');
        $prod->description      = $request->input('description');

        $prod->brand            = $request->input('brand');
        $prod->price_buy        = (float) str_replace(".", "", $request->input('price_buy'));
        $prod->price_sell       = (float) str_replace(".", "", $request->input('price_sell'));
        $prod->price_promo      = (float) str_replace(".", "", $request->input('price_promo'));
        $prod->weight           = (float) $request->input('weight');
        $prod->stock            = (float) $request->input('stock');
        $prod->type             = $request->input('type');
        $prod->featured         = $request->input('featured');
        $prod->tags             = $request->input('tags');
        $prod->menu_order       = $request->input('menu_order');
        $prod->status           = $request->input('status');
        $prod->sku              = $request->input('sku');
        $prod->cashback_1       = $request->input('cashback_1');
        $prod->cashback_2       = $request->input('cashback_2');
        $prod->large_unit       = $request->input('large_unit');
        $prod->large_qty        = $request->input('large_qty');
        $prod->medium_unit      = $request->input('medium_unit');
        $prod->medium_qty       = $request->input('medium_qty');
        $prod->small_unit       = $request->input('small_unit');
        $prod->small_qty        = $request->input('small_qty');

        if ($request->hasFile('image')) {
            // image's folder
            $folder = 'product';
            // image's filename
            $newName = "product-" . date('Ymd-His');
            // image's form field name
            $form_name = 'image';

            $prod->image = '/' . \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $save = $prod->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "create new product";
        $logs->data_content = $save;
        $logs->table_name   = 'products';
        $logs->table_id     = $save->id;
        $logs->column_name  = 'name, slug, category_id, description, brand, price_buy, price_sell, price_promo, weight, stock, type, featured, tags, menu_order, status, sku, cashback_1, cashback_2, large_unit, large_qty, medium_unit, medium_qty, small_unit, small_qty, image';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($save) {

            if ($request->hasFile('image_multi')) {

                foreach ($request->file('image_multi') as $i => $row) {

                    $file = $request->file('image_multi');

                    $photo = $file[$i];
                    $name = $i . strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u', '', strip_tags(strtolower(str_replace(" ", "-", $prod->name))))));
                    $resize_name = $name . '.' . $photo->getClientOriginalExtension();

                    Image::make($photo)
                        ->resize(650, null, function ($constraints) {
                            $constraints->aspectRatio();
                        })
                        ->save(('images' . '/product/' . $resize_name));

                    ProductImage::create([
                        'product_id' => $prod->id,
                        'path' => '/images/product/' . $resize_name,
                    ]);
                }
            }
        }

        if ($prod) {

            return redirect(url('admin/products'))
                ->with('status', 1)
                ->with('message', "Data Tersimpan!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 2)
                ->with('message', "Error while save data!");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // count product
        $salesToday     = $this->order
            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
            ->whereDay('order_time', '=', Carbon::now()->day)
            ->where('order_detail.product_id', $id)
            ->count();
        $salesLastMonth = $this->order
            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
            ->whereMonth('order_time', '=', Carbon::now()->month)
            ->where('order_detail.product_id', $id)
            ->count();
        $salesLastYear = $this->order
            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
            ->whereYear('order_time', '=', Carbon::now()->year)
            ->where('order_detail.product_id', $id)
            ->count();
        $salesTotal    = $this->order
            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
            ->where('order_detail.product_id', $id)
            ->count();

        $prod = $this->product->where('id', $id)->first();
        // $prod   = $this->category
        //         ->join('products', 'products.category_id', '=', 'categories.id')
        //         ->where('products.id', $id)
        //         ->first();
        $brand = $this->brand->get();
        $location = $this->location->get();
        if ($prod) {
            $category = $this->category->where('status', '1')->get();
            $product_loc = $this->product_loc->where('product_id', $prod->id)->get();
            return view('admin.pages.product-detail')
                ->with('salesToday', $salesToday)
                ->with('salesLastMonth', $salesLastMonth)
                ->with('salesLastYear', $salesLastYear)
                ->with('salesTotal', $salesTotal)
                ->with('category', $category)
                ->with('brand', $brand)
                ->with('product_loc', $product_loc)
                ->with('location', $location)
                ->with('product', $prod);
        } else {
            return redirect(url('admin/products'));
        }
    }

    public function storeProductStock(Request $request)
    {
        $find_warehouse = $this->product_loc->where('product_id', $request->product_id)
            ->where('location_id', $request->location_id)
            ->get();
        if (count($find_warehouse) >= 1) {
            return redirect($request->url)
                ->with('status', 2)
                ->with('message', "Gudang sudah tersedia!");
        } else {
            $product_loc = new $this->product_loc;

            $product_loc->product_id = $request->product_id;
            $product_loc->location_id = $request->location_id;
            $product_loc->price_sell = $request->price_sell;
            $product_loc->price_promo = $request->price_promo;
            $product_loc->stock = $request->stock;

            $product_loc->save();
            return redirect($request->url)
                ->with('status', 1)
                ->with('message', "Data Tersimpan!");
        }
    }

    public function editProductStock(Request $request, $id)
    {
        $product_loc = $this->product_loc->find($id);
        $product_loc->stock = $request->stock;

        $product_loc->save();
        return redirect($request->url)
            ->with('status', 1)
            ->with('message', "Data Tersimpan!");
    }

    public function deleteProductStock($id)
    {
        $product_loc = $this->product_loc->find($id);
        $product_id = $product_loc->product_id;
        if (isset($product_loc)) {
            $product_loc->delete();
        }

        return redirect('admin/products/' . $product_id)
            ->with('status', 2)
            ->with('message', "Data Dihapus!");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $prod = $this->product->find($id);

        $prod->name = $request->input('name');
        $prod->slug = strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u', '', strip_tags(strtolower(str_replace(" ", "-", $request->input('name')))))));
        $prod->category_id = (int) $request->input('category_id');
        $prod->description = $request->input('description');

        $prod->brand = $request->input('brand');
        $prod->price_buy = (float) str_replace(".", "", $request->input('price_buy'));
        $prod->price_sell = (float) str_replace(".", "", $request->input('price_sell'));
        $prod->price_promo = (float) str_replace(".", "", $request->input('price_promo'));
        $prod->weight = (float) $request->input('weight');
        $prod->stock = (float) $request->input('stock');
        $prod->featured = $request->input('featured');
        $prod->menu_order = $request->input('menu_order');
        $prod->status = $request->input('status');
        $prod->tags = $request->input('tags');
        $prod->sku = $request->input('sku');
        $prod->cashback_1 = $request->input('cashback_1');
        $prod->cashback_2 = $request->input('cashback_2');
        $prod->large_unit = $request->input('large_unit');
        $prod->large_qty = $request->input('large_qty');
        $prod->medium_unit = $request->input('medium_unit');
        $prod->medium_qty = $request->input('medium_qty');
        $prod->small_unit = $request->input('small_unit');
        $prod->small_qty = $request->input('small_qty');

        if ($request->hasFile('image')) {
            // image's folder
            $folder = 'product';
            // image's filename
            $newName = "product-" . date('Ymd-His');
            // image's form field name
            $form_name = 'image';

            $prod->image = '/' . \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
        }

        $prod->save();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "update product with id : " . $id;
        $logs->data_content = $prod;
        $logs->table_name   = 'products';
        $logs->table_id     = $prod->id;
        $logs->column_name  = 'name, slug, category_id, description, brand, price_buy, price_sell, price_promo, weight, stock, type, featured, tags, menu_order, status, sku, cashback_1, cashback_2, large_unit, large_qty, medium_unit, medium_qty, small_unit, small_qty, image';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        if ($prod) {

            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Tersimpan!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 2)
                ->with('message', "Error while save data!");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = $this->product->find($id)->delete();

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "delete product with id : " . $id;
        $logs->table_name   = 'products';
        $logs->table_id     = $id;
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        return redirect(url('admin/products'))
            ->with('status', 1)
            ->with('message', "Data Berhasil Dihapus!");
    }

    public function multipleUpload(Request $request)
    {
        $photos = $request->file('file');

        if (!is_array($photos)) {
            $photos = [$photos];
        }

        $prod           = $this->product->find($request->input('product_id'));
        $todayDate      = Carbon::now()->format('Y-m-d His');

        for ($i = 0; $i < count($photos); $i++) {
            $photo = $photos[$i];
            $name = $i . $todayDate . strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-]/u', '', strip_tags(strtolower(str_replace(" ", "-", $prod->name))))));
            $save_name = $name . '.' . $photo->getClientOriginalExtension();
            $resize_name = $name . '.' . $photo->getClientOriginalExtension();

            Image::make($photo)
                ->resize(650, null, function ($constraints) {
                    $constraints->aspectRatio();
                })
                ->save(('images' . '/product/' . $resize_name));

            ProductImage::create([
                'product_id' => $request->input('product_id'),
                'path' => '/images/product/' . $resize_name,
            ]);
        }

        $resp = array(
            'message' => 'Image saved Successfully',
        );
        return $resp;
    }

    public function updateProductRecommendation(Request $request)
    {

        $id = $request->input('id');
        $status = $request->input('status');
        $user = Product::find($id);

        if ($user) {
            $user->featured = $status;
            $user->save();
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Data Updated!");
        } else {
            return redirect($request->input('url'))
                ->with('status', 1)
                ->with('message', "Error while Saved!");
        }
    }

    public function imageDestroy(Request $request, $id)
    {
        $post_images = ProductImage::find($id);
        if (isset($post_images)) {

            if (file_exists($post_images->path)) {
                unlink($post_images->path); //menghapus file lama
            }

            $post_images->forceDelete();
        }

        return redirect($request->input('url'))
            ->with('status', 1)
            ->with('message', "Data Deleted!");
    }

    public function searchListProduct(Request $request)
    {

        $keyword = $request->input('keyword');
        $data = $this->product->where('status', '1')
            ->whereRaw('search_name like "%' . $keyword . '%"')
            ->limit(20)
            ->get();

        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function updateStatus($id)
    {
        $product = Product::find($id);
        if ($product) {
            if ($product->status == "1") {
                $product->status = "0";
            } else {
                $product->status = "1";
            }

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "change status product to " . $product->status;
            $logs->table_id     = $id;
            $logs->table_name   = 'product';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $product->save();

            $status = 1;
            $msg = 'Update sukses ' . $product->status;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function updateAvailStatus($id)
    {
        $product = $this->productAvailability->find($id);
        if ($product) {
            if ($product->status == "1") {
                $product->status = "0";
            } else {
                $product->status = "1";
            }

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "change status product availability to " . $product->status;
            $logs->table_id     = $id;
            $logs->table_name   = 'product_availability';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $product->save();

            $status = 1;
            $msg = 'Update sukses ' . $product->status;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function updateProductPriceBuy($id, Request $request)
    {
        $product = Product::find($id);

        if ($product) {

            $product->price_buy = $request->input('price_buy');
            $product->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update product price buy with id : " . $id;
            $logs->table_name   = 'products';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            $status = 1;
            $data = $product;
            $msg = 'Update sukses ' . $request->input('price');
            return response()->json(compact('status', 'msg', 'data'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function updateProductPriceSell($id, Request $request)
    {
        $product = Product::find($id);

        if ($product) {

            $product->price_sell = $request->input('price_sell');
            $product->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update product price sell with id : " . $id;
            $logs->table_name   = 'products';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            $status = 1;
            $data = $product;
            $msg = 'Update sukses ' . $request->input('price');
            return response()->json(compact('status', 'msg', 'data'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function updateProductStock($id, Request $request)
    {
        $product = Product::find($id);

        if ($product) {

            $product->stock = $request->input('stock');
            $product->save();

            $status = 1;
            $data = $product;
            $msg = 'Update sukses ' . $request->input('stock');
            return response()->json(compact('status', 'msg', 'data'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function duplicateProduct($id, Request $request)
    {
        $product = Product::find($id);

        if ($product) {

            $slug_count = (Product::where('slug', 'like', '%' . $product->slug . '%')->count() + 1);
            $new_product = new Product();

            $new_product->name = $product->name . "-" . $slug_count;
            $new_product->slug = $product->slug . "-" . $slug_count;
            $new_product->category_id = $product->category_id;
            $new_product->description = $product->description;

            $new_product->brand = $product->brand;
            $new_product->price_buy = $product->price_buy;
            $new_product->price_sell = $product->price_sell;
            $new_product->price_promo = $product->price_promo;
            $new_product->weight = $product->weight;
            $new_product->stock = $product->stock;
            $new_product->menu_order = $product->menu_order;
            $new_product->status = $product->status;

            if (isset($product->image)) {
                // $new_prod_name = "images/product/copy-".str_replace("images/product/", "", $product->image);

                $file = pathinfo($product->image);

                $name = $file['filename'] ?? "image";
                $ext = $file['extension'] ?? "png";

                $new_prod_name = "images/product/" . $name . "-copy-" . date('His') . "." . $ext;

                $success = \File::copy($product->image, $new_prod_name);
                $new_product->image = $new_prod_name;
            }

            $new_product->save();

            $product_image = ProductImage::where('product_id', $product->id)->get();

            foreach ($product_image as $row) :

                // $new_prod_name = "images/product/copy-".str_replace("images/product/", "", $row->path);
                $file = pathinfo($row->path);
                $name = $file['filename'] ?? "image";
                $ext = $file['extension'] ?? "png";
                $new_prod_name = "images/product/" . $name . "-copy-" . date('His') . "." . $ext;

                $success = \File::copy($row->path, $new_prod_name);
                $new_product_image = new ProductImage();
                $new_product_image->product_id = $new_product->id;
                $new_product_image->path = $new_prod_name;
                $new_product_image->save();

            endforeach;

            return redirect('admin/products/')
                ->with('status', 1)
                ->with('message', "Duplicate Sukses!");
        } else {
            return redirect('admin/products/')
                ->with('status', 2)
                ->with('message', "Duplicate failed!");
        }
    }

    public function pageImportProduct()
    {
        return view('admin/pages/product-import');
    }

    public function uploadExcelOld(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file');

            $prod_import = Excel::toArray([], $path);

            $no_saved = 0;
            $no_unsaved = 0;

            foreach ($prod_import[0] as $no => $row) {

                $data = Product::where('sku', $row[1])->first();

                try {

                    if ($data) {
                    } else {
                        $data = new Product();
                    }

                    $data->name = $row[2];
                    $data->slug = strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u', '', strip_tags(strtolower(str_replace(" ", "-", $row[2]))))));
                    $data->qty = $row[3];
                    $data->price = $row[4];

                    if ($data->status == "1") {
                    } else {
                        $data->status = '0';
                    }

                    $data->save();
                    $no_saved++;
                } catch (\Throwable $th) {
                    $no_unsaved++;
                }
            }
        }

        return redirect($request->input('url'))
            ->with('no_saved', $no_saved)
            ->with('no_unsaved', $no_unsaved)
            ->with('data', $prod_import[0])
            ->with('status', 1)
            ->with('message', "Data Saved!");
    }

    public function uploadExcel()
    {
        Excel::import(new ProductsImport, request()->file('file'));

        // logs
        $logs = $this->logs;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Import product";
        $logs->table_name   = 'products';
        $logs->from_user    = auth()->user()->id;
        $logs->to_user      = null;
        $logs->platform     = "web";

        $logs->save();

        return back();
    }

    public function productRating(Request $request)
    {
        $rating = ProductReview::paginate(15);
        return view('admin/pages/product-rating')
            ->with('rating', $rating);
    }

    public function availability()
    {
        $mappingSites = $this->mappingSites->paginate(10);
        return view('admin/pages/product-availability', compact('mappingSites'));
    }

    public function siteCode($site_code)
    {
        $products = $this->productAvailability
            ->where('site_code', $site_code)
            ->with(['product'])
            ->paginate(10);
        return view('admin/pages/product-availability-detail', compact('products', 'site_code'));
    }

    public function getImageName($id)
    {
        try {
            $product            = $this->product->find($id);
            $url                = $product->image_backup;
            $info               = pathinfo($url);
            $rel_path           = '/images/product/';
            $new_name           = $product->kodeprod . "." . $info['extension'];
            $product_image      = $rel_path . $new_name;
            $product->image     = $product_image;
            $product->save();

            return response()->json([
                'status'    => 'success',
                'data'      => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'failed',
                'data'      => $e->getMEssage()
            ], 500);
        }
    }

    public function uploadImage(Request $request, $id)
    {
        try {
            $product = null;
            if ($request->hasFile('image')) {
                $product = $this->product->find($id);
                // image's folder
                $folder = 'product';
                // image's filename
                // $newName = $product->kodeprod . $request->file('image')->getClientOriginalExtension();
                $newName = $product->kodeprod;
                // image's form field name
                $form_name = 'image';

                $image = '/' . \App\Helpers\StoreImage::saveImage($request, $folder, $newName, $form_name);
                $product->image = $image;
                $product->save();
            }

            return response()->json([
                'status'    => 'success',
                'data'      => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'failed',
                'data'      => $e->getMEssage()
            ], 500);
        }
    }
}
