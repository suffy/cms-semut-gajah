<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;
use App\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;

class RedeemPointController extends Controller
{
    protected $product, $logs;

    public function __construct(Product $product, Log $logs)
    {
        $this->product = $product;
        $this->logs    = $logs;
    }

    public function index()
    {
        $redeem_products = $this->product->where('status_redeem', '1')->paginate(10);

        return view('admin.pages.redeem-point', compact('redeem_products'));
    }

    public function create()
    {
        return view('admin.pages.redeem-point-new');
    }

    public function store(Request $request)
    {
        try{
            $this->validate($request, [
                'product_by'        => 'required',
                'product_mpm'       => 'required_if:product_by,1',
                'redeem_point_mpm'  => 'required_if:product_by,1',
                'invoice_name'      => 'required_if:product_by,2',
                'name'              => 'required_if:product_by,2',
                'redeem_point'      => 'required_if:product_by,2',
                'satuan_online'     => 'required_if:product_by,2',
                'qty'               => 'required_if:product_by,2',
                'photo_product'     => 'required_if:product_by,2',
                'redeem_desc'       => 'required',
                'redeem_snk'        => 'required',
            ]);

            if($request->product_by == 'mpm') {
                $product = $this->product->find($request->product_mpm);
                $product->status_redeem = '1';
                $product->redeem_point  = $request->redeem_point_mpm;
                $product->redeem_desc   = $request->redeem_desc;
                $product->redeem_snk    = $request->redeem_snk;
                $product->save();
            } else if($request->product_by == 'input') {
                $product = $this->product;
                $product->invoice_name  = $request->invoice_name;
                $product->name          = $request->name;
                $product->redeem_point  = $request->redeem_point;
                $product->satuan_online = $request->satuan_online;
                $product->qty3          = $request->qty;
                $product->status_redeem = '1';
                $product->status        = '0';
                $product->redeem_desc   = $request->redeem_desc;
                $product->redeem_snk    = $request->redeem_snk;
                
                $product->save();

                if ($request->hasFile('photo_product')) {
                    $file = $request->file('photo_product');
                    $ext = $file->getClientOriginalExtension();
                    $relPath = '/images/product/';
                    if (!file_exists(public_path($relPath))) {
                        mkdir(public_path($relPath), 0755, true);
                    }

                    $newName = $product->id . "." . $ext;
        
                    $image_resize = InterImage::make($file->getRealPath());
                    $image_resize->save(('images/product/' . $newName));
                    $product->image = '/images/product/'.$newName;
                }

                $product->kodeprod      = $product->id;
                $product->save();
            }

            $dataContent = $product;

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create product redeem point";
            $logs->data_content = $dataContent;
            $logs->table_id     = $product->id;
            $logs->table_name   = 'product';
            $logs->column_name  = 'status_redeem, redeem_point';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/redeem-point'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch (\Exception $e) {
            // var_dump($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = $this->product->find($id);

        return view('admin.pages.redeem-point-edit', compact('product'));
    }

    public function update(Request $request)
    {
        try {

            $product = $this->product->find($request->id);
    
            if($request->product_by == 'mpm') {
                $product->redeem_point  = $request->redeem_point_mpm;
                $product->redeem_desc   = $request->redeem_desc;
                $product->redeem_snk    = $request->redeem_snk;
                $product->save();
            } else if($request->product_by == 'input') {
                $product->invoice_name  = $request->invoice_name;
                $product->name          = $request->name;
                $product->redeem_point  = $request->redeem_point;
                $product->satuan_online = $request->satuan_online;
                $product->qty3          = $request->qty;
                $product->redeem_desc   = $request->redeem_desc;
                $product->redeem_snk    = $request->redeem_snk;
                
                $product->save();
    
                if ($request->hasFile('photo_product')) {
                    $file = $request->file('photo_product');
                    $ext = $file->getClientOriginalExtension();
                    $relPath = '/images/product/';
                    if (!file_exists(public_path($relPath))) {
                        mkdir(public_path($relPath), 0755, true);
                    }

                    if (file_exists(public_path().$product->image)) {
                        unlink(public_path().$product->image); //menghapus file lama
                    }
    
                    $newName = $product->id . "." . $ext;
        
                    $image_resize = InterImage::make($file->getRealPath());
                    $image_resize->save(('images/product/' . $newName));
                    $product->image = '/images/product/'.$newName;
                }
    
                $product->kodeprod      = $product->id;
                $product->save();
            }
            
            $dataContent = $product;

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update product redeem point";
            $logs->data_content = $dataContent;
            $logs->table_id     = $product->id;
            $logs->table_name   = 'product';
            $logs->column_name  = 'status_redeem, redeem_point';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/redeem-point'))
                                            ->with('status', 1)
                                            ->with('message', "Data Terupdate!");
        } catch (\Exception $e) {
            // var_dump($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try{
            $product = $this->product->find($id);
            $product->status_redeem = '0';
            $product->save();

            $dataContent = $product;

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete product redeem point";
            $logs->data_content = $dataContent;
            $logs->table_id     = $product->id;
            $logs->table_name   = 'product';
            $logs->column_name  = 'status_redeem';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/redeem-point'))
                                            ->with('status', 1)
                                            ->with('message', "Data Terhapus!");
        } catch (\Exception $e) {
            // var_dump($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }   
    }
}
