<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Promo;
use App\PromoSku;
use App\PromoReward;
use App\ShoppingCart;
use App\Log;
use Carbon\Carbon;
use Dotenv\Loader\Value;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as InterImage;

class PromoController extends Controller
{
    protected $product, $promo, $promo_sku, $promo_reward, $logs, $shoppingCart;
    public function __construct(Product $product, Promo $promo, PromoSku $promo_sku, PromoReward $promo_reward, Log $log, ShoppingCart $shoppingCart)
    {
        $this->product          = $product;
        $this->promo            = $promo;
        $this->promo_reward     = $promo_reward;
        $this->promo_sku        = $promo_sku;
        $this->logs             = $log;
        $this->shoppingCarts    = $shoppingCart;
    }

    public function index(Request $request)
    {
        if($request->status != null) {
            $promos  = $this->promo
                                ->where('status', $request->status);
        } else {
            $promos  = $this->promo;
        } 
        if($request->start != null && $request->end != null) {
            $promos  = $this->promo
                                ->where('start', '>=', $request->start)
                                ->where('end', '<=', $request->end);
        } else if($request->start != null && $request->end == null) {
            $promos  = $this->promo
                                ->where('start', '>=', $request->start);
        } else if($request->start == null && $request->end != null) {
            $promos  = $this->promo
                                ->where('end', '<=', $request->end);
        }
        if($request->search != null) {
            $promos  = $this->promo
                                ->where('title', 'LIKE', "%".$request->search."%");
        }
            // dd($promos);
            $promos = $promos
                        ->with(['sku', 'reward.product'])
                        ->whereNull('special')
                        ->orderBy('id', 'desc')
                        ->paginate(10);

        return view('admin/pages/promo', compact('promos'));
    }

    public function edit($id)
    {
        $promos  = $this->promo
                            ->where('id', $id)
                            ->with(['sku', 'reward.product'])
                            ->first();
        $brand = $this->product->get()
                    ->where('brand_id' ,'!=', 'BSP')
                    ->unique('brand_id')
                    ->sortBy('brand_id')
                    ->pluck('brand_id', 'brand')
                    ->except('BSP');

        $groups = $this->product->get()
                    ->unique('group_id')
                    ->sortBy('group_id')
                    ->pluck('group_id', 'nama_group')
                    ->except(NULL);

        return view('admin/pages/promo-edit', compact('promos', 'brand', 'groups'));
    }

    public function detail($id)
    {
        $promo = $this->promo
                            ->with('reward.product')
                            ->find($id);
        
        $sku   = $this->promo_sku
                            ->with('product')
                            ->where('promo_id', $promo->id)
                            ->paginate(10);

        return view('admin/pages/promo-detail', compact('promo', 'sku'));
    }

    public function create()
    {
        $brand = $this->product->get()
                                ->where('brand_id' ,'!=', 'BSP')
                                ->unique('brand_id')
                                ->sortBy('brand_id')
                                ->pluck('brand_id', 'brand')
                                ->except('BSP');

        $groups = $this->product->get()
                                    ->unique('group_id')
                                    ->sortBy('group_id')
                                    ->pluck('group_id', 'nama_group')
                                    ->except(NULL);

        return view('admin/pages/promo-new', compact('brand', 'groups'));
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'promo_title'           => 'required|string',
                'highlight'             => 'required|string',
                'description'           => 'required|string',
                'termcondition'         => 'required',
                'detail_termcondition'  => 'required_if:termcondition,1,3',
                'min_transaction'       => 'required_if:termcondition,2,3',
                'all_min_qty'           => 'required_if:detail_termcondition,1',
                'product_min_qty'       => 'required_if:detail_termcondition,2|array',
                'product_min_qty.*'     => 'required_if:detail_termcondition,2',
                'min_sku'               => 'required_if:detail_termcondition,3',
                'class_type'            => 'required',
                'banner'                => 'required',
                'category'              => 'required',
                'detail_category'       => 'required_if:category,1,3',
                'point'                 => 'required_if:detail_category,1',
                'discount'              => 'required_if:detail_category,2',
                'nominal'               => 'required_if:detail_category,3',
                'product_reward_id'     => 'required_if:category,2,3|array',
                'product_reward_id.*'   => 'required_if:category,2,3',
                'start'                 => 'required|date',  
                'end'                   => 'required|date',
                'product_id'            => 'required_if:all_transaction,0|array',
                'product_id.*'          => 'required_if:all_transaction,0',
            ]);

            if($validator->fails()) {
                return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('errors', $validator->errors())
                        ->with('message', $validator->errors()->first());
            }
            
            $promo                  = $this->promo;
            
            $promo->title                   = $request->promo_title;
            $promo->highlight               = $request->highlight;
            $promo->description             = $request->description;
            $promo->termcondition           = $request->termcondition;
            $promo->detail_termcondition    = $request->detail_termcondition;
            $promo->category                = $request->category;
            $promo->detail_category         = $request->detail_category;
            $promo->contact                 = $request->contact;
            $promo->multiple                = $request->multiple;
            $promo->reward_choose           = $request->reward_choose;
            $promo->status                  = 1;
            $promo->min_qty                 = $request->all_min_qty;
            $promo->min_sku                 = $request->min_sku;
            $promo->min_transaction         = $request->min_transaction;
            $promo->all_transaction         = $request->all_transaction;
            $promo->start                   = $request->start;
            $promo->end                     = $request->end;
            $promo->created_at              = Carbon::now();

            if($request->class_type == '1') {
                $promo_class = $request->ct_class;
                $promo_type  = $request->ct_type;
            } else if($request->class_type == '2') {
                $promo_class = $request->ct_class;
                $promo_type  = null;
            } else if($request->class_type == '3') {
                $promo_class = null;
                $promo_type  = $request->ct_type;
            } else {
                $promo_class = null;
                $promo_type  = null;
            }

            $promo->class_cust = $promo_class;
            $promo->type_cust  = $promo_type;

            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $ext = $file->getClientOriginalExtension();
                $relPath = '/images/promo/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                $newName = "banner" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/promo/' . $newName));
                $promo->banner = '/images/promo/'.$newName;
            }
    
            $save = $promo->save();
        
            $promo_reward                       = $this->promo_reward;
            if($request->product_reward_id) {
                if($request->discount || $request->point || $request->nominal) {
                    $promo_reward->promo_id             = $promo->id;
                    $promo_reward->reward_disc          = $request->discount;
                    $promo_reward->reward_point         = $request->point;
                    $promo_reward->reward_nominal       = $request->nominal;
                    if($request->max) {
                        $promo_reward->max              = $request->max;
                    }
                    $promo_reward->save();
                }
                foreach($request->product_reward_id as $index => $key) {
                    $data_reward = array(
                        'promo_id'      => $promo->id,
                        'reward_product_id'    => $request->product_reward_id[$index],
                        'reward_qty'           => $request->product_reward_qty[$index],
                        'satuan'               => $request->satuan_qty[$index],
                        'created_at'           => Carbon::now()
                    );
                    $promo_reward->create($data_reward);
                }
            } else { 
                if($request->product_reward_form && $request->product_reward_qty_form) {
                    $promo_reward->promo_id             = $promo->id;
                    $promo_reward->reward_product_id    = $request->product_reward_form;
                    $promo_reward->reward_qty           = $request->product_reward_qty_form;
                    $promo_reward->satuan               = $request->satuan_qty;
                } else if ($request->product_reward_form && $request->product_reward_qty_form == null) {
                    return redirect()->back()
                                        ->with('status', 2)
                                        ->with('message', 'Reward Qty Product Masih Kosong');
                } else {
                    $promo_reward->promo_id             = $promo->id;
                    $promo_reward->reward_disc          = $request->discount;
                    $promo_reward->reward_point         = $request->point;
                    $promo_reward->reward_nominal       = $request->nominal;
                    if($request->max) {
                        $promo_reward->max              = $request->max;
                    }
                }

                $promo_reward->save();
            }

            if($request->all_transaction == '0') {
                $promo_sku  = $this->promo_sku;
                foreach($request->product_id as $index => $key) {
                    $data = array(
                        'promo_id'      => $promo->id,
                        'product_id'    => $request->product_id[$index],
                        'min_qty'       => isset($request->product_min_qty[$index]) ? $request->product_min_qty[$index] : null,
                        'satuan'        => isset($request->satuan[$index]) ? $request->satuan[$index] : null,
                        'created_at'    => Carbon::now()
                    );
    
                    $promo_sku->create($data);
                }
            }

            $dataContent = $this->promo->where('id',$promo->id)->with(['sku', 'reward'])->first();

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new promo";
            $logs->data_content = $dataContent;
            $logs->table_id     = $promo->id;
            $logs->table_name   = 'promos';
            $logs->column_name  = 'title, description, highlight, point, termcondition, detail_termcondition, category, detail_category, contact, report, banner, multiple, reward_choose, status, min_qty, min_sku, min_transaction, start, end';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/promo'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch(\Exception $e) {
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'promo_title'           => 'required|string',
                'highlight'             => 'required|string',
                'description'           => 'required|string',
                'termcondition'         => 'required',
                'detail_termcondition'  => 'required_if:termcondition,1,3',
                'min_transaction'       => 'required_if:termcondition,2,3',
                'all_min_qty'           => 'required_if:detail_termcondition,1',
                'product_min_qty'       => 'required_if:detail_termcondition,2|array',
                'product_min_qty.*'     => 'required_if:detail_termcondition,2',
                'min_sku'               => 'required_if:detail_termcondition,3',
                // 'banner'                => 'required',
                'category'              => 'required',
                'detail_category'       => 'required_if:category,1,3',
                'point'                 => 'required_if:detail_category,1',
                'discount'              => 'required_if:detail_category,2',
                'nominal'               => 'required_if:detail_category,3',
                'product_reward_id'     => 'required_if:category,2,3|array',
                'product_reward_id.*'   => 'required_if:category,2,3',
                'start'                 => 'required|date',  
                'end'                   => 'required|date',
                'product_id'            => 'required_if:all_transaction,0|array',
                'product_id.*'          => 'required_if:all_transaction,0',
            ]);

            if($validator->fails()) {
                return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('errors', $validator->errors())
                        ->with('message', $validator->errors()->first());
            }

            $promo                          = $this->promo->find($id);
            
            $promo->title                   = $request->promo_title;
            $promo->highlight               = $request->highlight;
            $promo->description             = $request->description;
            $promo->termcondition           = $request->termcondition;
            $promo->detail_termcondition    = $request->detail_termcondition;
            $promo->category                = $request->category;
            $promo->detail_category         = $request->detail_category;
            $promo->contact                 = $request->contact;
            $promo->multiple                = $request->multiple;
            $promo->reward_choose           = $request->reward_choose;
            $promo->min_qty                 = $request->all_min_qty;
            $promo->min_sku                 = $request->min_sku;
            $promo->min_transaction         = $request->min_transaction;
            $promo->all_transaction         = $request->all_transaction;
            $promo->start                   = $request->start;
            $promo->end                     = $request->end;
            $promo->updated_at              = Carbon::now();

            if($request->class_type == '1') {
                $promo_class = $request->ct_class;
                $promo_type  = $request->ct_type;
            } else if($request->class_type == '2') {
                $promo_class = $request->ct_class;
                $promo_type  = null;
            } else if($request->class_type == '3') {
                $promo_class = null;
                $promo_type  = $request->ct_type;
            } else {
                $promo_class = null;
                $promo_type  = null;
            }

            $promo->class_cust = $promo_class;
            $promo->type_cust  = $promo_type;

            if ($request->hasFile('banner')) {
                $file       = $request->file('banner');
                $ext        = $file->getClientOriginalExtension();
                $relPath    = '/images/promo/';

                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                if (file_exists(public_path().$promo->banner)) {
                    unlink(public_path().$promo->banner); //menghapus file lama
                }
                $newName = "banner" . date('YmdHis') . "." . $ext;
    
                $image_resize   = InterImage::make($file->getRealPath());
                $image_resize->save(('images/promo/' . $newName));
                $promo->banner  = '/images/promo/'.$newName;
            }
            $save = $promo->save();

            $this->promo_reward->where('promo_id', $promo->id)->delete();
            $promo_reward = $this->promo_reward;
            if($request->product_reward_id) {
                if($request->discount || $request->point || $request->nominal) {
                    $promo_reward->promo_id             = $promo->id;
                    $promo_reward->reward_disc          = $request->discount;
                    $promo_reward->reward_point         = $request->point;
                    $promo_reward->reward_nominal       = $request->nominal;
                    if($request->max) {
                        $promo_reward->max              = $request->max;
                    }
                    $promo_reward->save();
                }
                foreach($request->product_reward_id as $index => $key) {
                    $data_reward = array(
                        'promo_id'              => $promo->id,
                        'reward_product_id'    => $request->product_reward_id[$index],
                        'reward_qty'           => $request->product_reward_qty[$index],
                        'satuan'               => $request->satuan_qty[$index],
                        'created_at'            => Carbon::now()
                    );
                    $promo_reward->create($data_reward);
                }
            } else { 
                $promo_reward->promo_id             = $promo->id;
                $promo_reward->reward_disc          = $request->discount;
                $promo_reward->reward_point         = $request->point;
                $promo_reward->reward_nominal       = $request->nominal;
                if($request->max) {
                    $promo_reward->max              = $request->max;
                }
                $promo_reward->save();
            }
            
            if($request->all_transaction == '0') {
                $this->promo_sku->where('promo_id', $promo->id)->delete();
                $promo_sku  = $this->promo_sku;
                foreach($request->product_id as $index => $key) {
                    $data = array(
                        'promo_id'      => $promo->id,
                        'product_id'    => $request->product_id[$index],
                        'min_qty'       => isset($request->product_min_qty[$index]) ? $request->product_min_qty[$index] : null,
                        'satuan'        => isset($request->satuan[$index]) ? $request->satuan[$index] : null,
                        'created_at'    => Carbon::now()
                    );

                    $promo_sku->create($data);
                }
            } else if ($request->all_transaction == '1') {
                $this->promo_sku->where('promo_id', $promo->id)->delete();
            }

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update promo with id : " . $id;
            $logs->table_id     = $id;
            $logs->data_content = $save;
            $logs->table_name   = 'promos';
            $logs->column_name  = 'title, description, highlight, point, termcondition, detail_termcondition, category, detail_category, contact, report, banner, multiple, reward_choose, status, min_qty, min_sku, min_transaction, start, end';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();
            
            $role = auth()->user()->account_role;

            return redirect(url($role .'/promo'))
                                            ->with('status', 1)
                                            ->with('message', "Data Terupdate!");
        } catch(\Exception $e) {
            return redirect()->back()
                                    ->with('status', 2)
                                    ->with('message', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $promo = $this->promo->find($id);
            
            if($promo->banner) {
                unlink(public_path().$promo->banner);
            } 

            $promo->delete();

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete promo with id : " . $id;
            $logs->table_id     = $id;
            $logs->table_name   = 'promos';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            return redirect()
                            ->back()
                            ->with('status', 1)
                            ->with('message', "Data Berhasil Dihapus!");
        } catch(\Exception $e) {

            return redirect(url('manager/promo'))
                            ->with('status', 2)
                            ->with('message', $e->getMessage());
        }
    }

    public function updateStatus($id)
    {
        $promo = $this->promo::find($id);

        if ($promo) {
            if ($promo->status == "1") {
                $promo->status = "0";
                $this->shoppingCarts->where('promo_id', $id)->update(['promo_id' => NULL]);

                $logs = $this->logs;
                $logs->log_time     = Carbon::now();
                $logs->activity     = "change status promo to nonaktif";
                $logs->table_id     = $id;
                $logs->table_name   = 'promos';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = "web";
                $logs->save();
            } else {
                $promo->status = "1";

                $logs = $this->logs;
                $logs->log_time     = Carbon::now();
                $logs->activity     = "change status promo to aktif";
                $logs->table_id     = $id;
                $logs->table_name   = 'promos';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = "web";
                $logs->save();
            }
            $promo->save();

            $status = 1;
            $msg = 'Update sukses ' . $promo->status;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }

    public function ajaxBrand(Request $request)
    {
        $brand_id = $request->input('brand_id');
        $data = $this->product->where('status', '1')
            ->where('brand_id', $brand_id)
            ->orderBy('name')
            // ->limit(20)
            ->get();

        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function ajaxGroup(Request $request)
    {
        $group_id = $request->input('group_id');
        $data = $this->product
            ->where('status', '1')
            ->where('group_id', $group_id)
            ->select('subgroup', 'nama_sub_group')
            ->get()
            ->sortBy('nama_sub_group')
            ->unique('nama_sub_group');
            // ->limit(20)

        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function ajaxSubGroupProduct(Request $request)
    {
        $subgroup = $request->input('subgroup');
        $data = $this->product
            ->where('status', '1')
            ->where('subgroup', $subgroup)
            ->get()
            ->sortBy('name');
            // ->limit(20)

        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function ajaxProduct(Request $request)
    {
        $data = [];

        if($request->has('q')){
            $search = $request->q;
            $data = $this->product->where('status', '1')
                ->where('name', 'LIKE', "%".ucwords($search)."%")
                ->orWhere('kodeprod', 'LIKE', "%".$search."%")
                ->orderBy('name')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function productSatuan(Request $request)
    {
        $data['satuan'] = $this->product
                                    ->where('id', $request->id)
                                    ->get(["satuan_online"]);
        return response()->json($data);
    }

    public function ajaxSetListAll(Request $request)
    {
        $group_id = $request->input('group_id');
        $data = $this->product
            ->where('status', '1')
            ->where('group_id', $group_id)
            ->get()
            ->sortBy('nama_sub_group');
            // ->limit(20)

        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function ajaxSetListSubGroup(Request $request)
    {
        $subgroup = $request->input('subgroup');
        $data = $this->product
            ->where('status', '1')
            ->where('subgroup', $subgroup)
            ->get()
            ->sortBy('name');
            // ->limit(20)
        $status = 1;
        $msg = 'get data success';
        $data = $data;
        return response()->json(compact('status', 'msg', 'data'), 200);
    }

    public function specialIndex()
    {
        $promos = $this->promo
                    ->with(['reward'])
                    ->whereNotNull('special')
                    ->orderBy('id', 'desc')
                    ->paginate(10);
// dd($promos);
        return view('admin/pages/special-promo', compact('promos'));
    }

    public function updateStatusSpecial($id)
    {
        $promo = $this->promo::find($id);

        if ($promo) {
            if ($promo->status == "1") {
                $promo->status = "0";

                $logs = $this->logs;
                $logs->log_time     = Carbon::now();
                $logs->activity     = "change status promo special to nonaktif";
                $logs->table_id     = $id;
                $logs->table_name   = 'promos';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = "web";
                $logs->save();
            } else {
                $promo->status = "1";

                $logs = $this->logs;
                $logs->log_time     = Carbon::now();
                $logs->activity     = "change status promo special to aktif";
                $logs->table_id     = $id;
                $logs->table_name   = 'promos';
                $logs->from_user    = auth()->user()->id;
                $logs->to_user      = null;
                $logs->platform     = "web";
                $logs->save();
            }
            $promo->save();

            $status = 1;
            $msg = 'Update sukses ' . $promo->status;
            return response()->json(compact('status', 'msg'), 200);
        } else {
            $status = 0;
            $msg = 'Update Gagal';
            return response()->json(compact('status', 'msg'), 200);
        }
    }
   
    public function specialStore(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
               'promo_title'    => 'required|string',
               'banner'         => 'required',
               'special'        => 'required',
               'reward_disc'    => 'required',
               'max'            => 'required',
               'highlight'      => 'required|string',
               'description'    => 'required|string'
            ]);

            if($validator->fails()) {
               return redirect()->back()
                       ->withInput()
                       ->with('status', 2)
                       ->with('errors', $validator->errors())
                       ->with('message', $validator->errors()->first());
            }
           
            $promo                  = $this->promo;
            $promo->title           = $request->promo_title;
            $promo->highlight       = $request->highlight;
            $promo->description     = $request->description;
            $promo->all_transaction = '1';
            $promo->status          = '1';
            $promo->special         = $request->special;
            $promo->created_at      = Carbon::now();
            $promo->updated_at      = Carbon::now();

            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $ext = $file->getClientOriginalExtension();
                $relPath = '/images/promo/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                $newName = "banner" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/promo/' . $newName));
                $promo->banner = '/images/promo/'.$newName;
            }

            $save = $promo->save();
       
            $promo_reward                   = $this->promo_reward;
            $promo_reward->promo_id         = $promo->id;
            $promo_reward->reward_disc      = $request->reward_disc;
            $promo_reward->max              = $request->max;
            $promo_reward->save();

            $dataContent = $this->promo->where('id', $promo->id)->with(['reward'])->first();

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new special promo";
            $logs->data_content = $dataContent;
            $logs->table_id     = $promo->id;
            $logs->table_name   = 'promos';
            $logs->column_name  = 'title, banner, highlight, description, all_transaction, status, special';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/special-promo'))
                        ->with('status', 1)
                        ->with('message', "Data Tersimpan!");
        } catch(\Exception $e) {
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }

    public function specialUpdate(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'promo_title'    => 'required|string',
                'reward_disc'    => 'required',
                'max'            => 'required',
                'highlight'      => 'required|string',
                'description'    => 'required|string'
            ]);

            if($validator->fails()) {
                return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('errors', $validator->errors())
                        ->with('message', $validator->errors()->first());
            }
           
            $promo                  = $this->promo->find($request->id);
            $promo->title           = $request->promo_title;
            $promo->highlight       = $request->highlight;
            $promo->description     = $request->description;
            $promo->updated_at      = Carbon::now();

            if ($request->hasFile('banner')) {
                $file       = $request->file('banner');
                $ext        = $file->getClientOriginalExtension();
                $relPath    = '/images/promo/';

                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                if (file_exists(public_path().$promo->banner)) {
                    unlink(public_path().$promo->banner); //menghapus file lama
                }
                $newName = "banner" . date('YmdHis') . "." . $ext;
    
                $image_resize   = InterImage::make($file->getRealPath());
                $image_resize->save(('images/promo/' . $newName));
                $promo->banner  = '/images/promo/'.$newName;
            }
            $save = $promo->save();
       
            $promo_reward                   = $this->promo_reward->where('promo_id', $promo->id)->first();
            $promo_reward->reward_disc      = $request->reward_disc;
            $promo_reward->max              = $request->max;
            $promo_reward->save();

            $dataContent = $this->promo->where('id', $promo->id)->with(['reward'])->first();

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update promo with id : " . $promo->id;
            $logs->table_id     = $promo->id;
            $logs->data_content = $save;
            $logs->table_name   = 'promos';
            $logs->column_name  = 'title, banner, highlight, description';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();
            
            $role = auth()->user()->account_role;

            return redirect(url($role .'/special-promo'))
                                    ->with('status', 1)
                                    ->with('message', "Data Terupdate!");
        } catch(\Exception $e) {
            return redirect()->back()
                                    ->with('status', 2)
                                    ->with('message', $e->getMessage());
        }
    }
    
    public function specialDelete($id)
    {
        try {
            $promo = $this->promo->find($id);

            if($promo->banner) {
                unlink(public_path().$promo->banner);
            } 
            $promo->delete();

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete promo with id : " . $id;
            $logs->table_id     = $id;
            $logs->table_name   = 'promos';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            return response()->json([
                'status'    => true,
                'message'   => 'data berhasil dihapus!',
                'data'      => $logs
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage(),
                'data'      => $logs
            ]);
        }
    }

    function updateBanner(Request $request) 
    {
        try {
            $promo = $this->promo->find($request->id);
            if ($request->hasFile('banner')) {
                $file       = $request->file('banner');
                $ext        = $file->getClientOriginalExtension();
                $relPath    = '/images/promo/';

                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                if (file_exists(public_path().$promo->banner)) {
                    @unlink(public_path().$promo->banner); //menghapus file lama
                }
                $newName = "banner" . date('YmdHis') . "." . $ext;

                $image_resize   = InterImage::make($file->getRealPath());
                $image_resize->save(('images/promo/' . $newName));
                $promo->banner  = '/images/promo/'.$newName;
            }
            $save = $promo->save();
            
            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update banner promo with id : " . $request->id;
            $logs->table_id     = $request->id;
            $logs->data_content = $save;
            $logs->table_name   = 'promos';
            $logs->column_name  = 'banner';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();
            
            $role = auth()->user()->account_role;

            return redirect(url($role .'/promo'))
            ->with('status', 1)
            ->with('message', "Data Terupdate!");
        } catch(\Exception $e) {
            return redirect()->back()
            ->with('status', 2)
            ->with('message', $e->getMessage());
        }
    }

    public function priorityTop()
    {
        $banners = $this->promo->select('id', 'title', 'banner', 'priority_top', 'priority_top_position')->where('status', 1)->whereNull('special')->orderBy('id', 'desc')->get();
        
        return view('admin/pages/promo-priority-top', compact('banners'));
    }

    public function priorityBottom()
    {
        $banners = $this->promo->select('id', 'title', 'banner', 'priority_bottom', 'priority_bottom_position')->where('status', 1)->whereNull('special')->orderBy('id', 'desc')->get();
        
        return view('admin/pages/promo-priority-bottom', compact('banners'));
    }

    public function priorityTopStore(Request $request)
    {
        try {
            // $banners
            $promos = $this->promo->where('status', 1)->whereNull('special')->get();
            $pos = array_filter($request->pos);
            $posArray = [];
            foreach($pos as $p) {
                array_push($posArray, $p);
            }
            foreach($promos as $key => $promo) {
                if(in_array($promo->id, $request->top)) {
                    $promo->priority_top = '1';
                    $promo->priority_top_position = $posArray[array_search($promo->id, $request->top)];
                } else {
                    $promo->priority_top = '0';
                    $promo->priority_top_position = null;
                }
                $promo->save();
            }

            $role = auth()->user()->account_role;

            return redirect(url($role .'/promo'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch(\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }

    public function priorityBottomStore(Request $request)
    {
        try {
            // $banners
            $promos = $this->promo->where('status', 1)->whereNull('special')->get();
            $pos = array_filter($request->pos);
            $posArray = [];
            foreach($pos as $p) {
                array_push($posArray, $p);
            }
            foreach($promos as $key => $promo) {
                if(in_array($promo->id, $request->bottom)) {
                    $promo->priority_bottom = '1';
                    $promo->priority_bottom_position = $posArray[array_search($promo->id, $request->bottom)];
                } else {
                    $promo->priority_bottom = '0';
                    $promo->priority_bottom_position = null;
                }
                $promo->save();
            }
            
            $role = auth()->user()->account_role;

            return redirect(url($role .'/promo'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch(\Exception $e) {
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }
}
