<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\TopSpenderWinner;
use App\TopSpenderReward;
use App\OrderDetail;
use App\TopSpender;
use App\Product;
use App\Order;
use App\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Intervention\Image\ImageManagerStatic as InterImage;

class TopSpenderController extends Controller
{
    protected $topSpender, $topSpenderWinner, $topSpenderReward, $product, $order, $orderDetail, $logs;
    public function __construct(TopSpender $topSpender, TopSpenderWinner $topSpenderWinner, TopSpenderReward $topSpenderReward, Product $product, Order $order, OrderDetail $orderDetail, Log $logs)
    {
        $this->topSpender       = $topSpender;
        $this->topSpenderWinner = $topSpenderWinner;
        $this->topSpenderReward = $topSpenderReward;
        $this->product          = $product;
        $this->order            = $order;
        $this->orderDetail      = $orderDetail;
        $this->logs             = $logs;
    }

    public function index(Request $request)
    {
        $topSpender = $this->topSpender;
        if ($request->search != null) {
            $topSpender = $topSpender->where('title', 'like', '%' . $request->search . '%');
        }
        $topSpender = $topSpender->with('rank_reward')->orderBy('id', 'desc')->paginate(10);

        $brand = $this->product->get()
            ->where('brand_id', '!=', 'BSP')
            ->unique('brand_id')
            ->sortBy('brand_id')
            ->pluck('brand_id', 'brand')
            ->except('BSP', NULL);

        return view('admin/pages/top-spender', compact('topSpender', 'brand'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title'         => 'required',
                'description'   => 'required',
                'banner'        => 'required',
                'start'         => 'required',
                'end'           => 'required',
                'reward'        => 'required',
                'limit'         => 'required',
                'pos'           => 'required',
                'nominal'       => 'required',
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withInput()
                    ->with('status', 2)
                    ->with('errors', $validator->errors())
                    ->with('message', $validator->errors()->first());
            }
            $topSpender                 = $this->topSpender;
            $topSpender->title          = $request->title;
            $topSpender->description    = $request->description;
            $topSpender->start          = $request->start;
            $topSpender->end            = $request->end;
            $topSpender->reward         = $request->reward;
            $topSpender->limit          = $request->limit;
            if ($topSpender->site_code != null) {
                $topSpender->site_code = $request->site_code;
            }
            if ($topSpender->brand_id != null) {
                $topSpender->brand_id = $request->brand_id;
            }
            if ($topSpender->product_id != null) {
                $topSpender->product_id = $request->product_id;
            }
            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $ext = $file->getClientOriginalExtension();
                $relPath = '/images/top-spender/';
                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                $newName = "top-spender" . date('YmdHis') . "." . $ext;

                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/top-spender/' . $newName));
                $topSpender->banner = '/images/top-spender/' . $newName;
            }
            $save = $topSpender->save();

            $topSpenderReward = $this->topSpenderReward;
            foreach ($request->pos as $index => $key) {
                $data = array(
                    'top_spender_id' => $topSpender->id,
                    'pos' => $request->pos[$index],
                    'nominal' => $request->nominal[$index],
                );

                $topSpenderReward->create($data);
            }

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new top spender periode";
            $logs->table_id     = $topSpender->id;
            $logs->data_content = $topSpender;
            $logs->table_name   = 'top_spender';
            $logs->column_name  = 'top_spender.*';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role . '/top-spender'))
                ->with('status', 1)
                ->with('message', "Top Spender berhasil ditambahkan");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('status', 2)
                ->with('message', $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        // dd($request);
        try {
            $this->validate($request, [
                'title'         => 'required',
                'description'   => 'required',
                'start'         => 'required',
                'end'           => 'required',
                'reward'        => 'required',
                'limit'         => 'required',
                'pos'           => 'required',
                'nominal'       => 'required',
            ]);
            $topSpender                 = $this->topSpender->find($request->id);
            // dd($request->id);
            $topSpender->title          = $request->title;
            $topSpender->description    = $request->description;
            $topSpender->start          = $request->start;
            $topSpender->end            = $request->end;
            $topSpender->reward         = $request->reward;
            $topSpender->limit          = $request->limit;
            if ($topSpender->site_code != null) {
                $topSpender->site_code = $request->site_code;
            }
            if ($topSpender->brand_id != null) {
                $topSpender->brand_id = $request->brand_id;
            }
            if ($topSpender->product_id != null) {
                $topSpender->product_id = $request->product_id;
            }
            if ($request->hasFile('banner')) {
                $file       = $request->file('banner');
                $ext        = $file->getClientOriginalExtension();
                $relPath    = '/images/top-spender/';

                if (!file_exists(public_path($relPath))) {
                    mkdir(public_path($relPath), 0755, true);
                }

                if (file_exists(public_path() . $topSpender->banner)) {
                    unlink(public_path() . $topSpender->banner); //menghapus file lama
                }
                $newName = "top-spender" . date('YmdHis') . "." . $ext;

                $image_resize   = InterImage::make($file->getRealPath());
                $image_resize->save(('images/top-spender/' . $newName));
                $topSpender->banner  = '/images/top-spender/' . $newName;
            }
            $save = $topSpender->save();

            $this->topSpenderReward->where('top_spender_id', $topSpender->id)->delete();

            $topSpenderReward = $this->topSpenderReward;
            foreach ($request->pos as $index => $key) {
                $data = array(
                    'top_spender_id' => $topSpender->id,
                    'pos' => $request->pos[$index],
                    'nominal' => $request->nominal[$index],
                );

                $topSpenderReward->create($data);
            }

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update top spender periode";
            $logs->table_id     = $topSpender->id;
            $logs->data_content = $topSpender;
            $logs->table_name   = 'top_spender';
            $logs->column_name  = 'top_spender.*';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role . '/top-spender'))
                ->with('status', 1)
                ->with('message', "Top Spender berhasil diubah");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('status', 2)
                ->with('message', $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $topSpender = $this->topSpender->find($request->id);
            $topSpender->delete();
            $topSpender->rank_reward = $this->topSpenderReward->where('top_spender_id', $topSpender->id)->delete();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete top spender periode";
            $logs->table_id     = $id;
            $logs->data_content = null;
            $logs->table_name   = 'top_spender';
            $logs->column_name  = 'top_spender.*';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            return response()->json([
                'status'    => true,
                'message'   => 'data berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ]);
        }
    }

    public function list($id)
    {
        $topSpender = $this->topSpender->find($id);
        $list = $this->order
            ->select('orders.customer_id as id', 'users.name as name', 'users.customer_code as customer_code',DB::raw('SUM(order_detail.total_price) as total'))
            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_detail.product_id')
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->where('orders.status', '=', '4')
            ->where('status_faktur', '=', 'F')
            ->whereBetween('orders.order_time', [$topSpender->start, $topSpender->end]);

        if ($topSpender->site_code != null) {
            $list = $list->where('orders.site_code', '=', $topSpender->site_code);
        }
        if ($topSpender->brand_id != null) {
            $list = $list->where('order_detail.products.brand_id', '=', $topSpender->brand_id);
        }
        if ($topSpender->product != null) {
            $list = $list->where('order_detail.product_id', '=', $topSpender->product_id);
        }

        $list = $list
            ->groupBy('orders.customer_id', 'users.name', 'users.customer_code')
            ->orderBy('total', 'desc')
            ->limit($topSpender->limit)
            ->get();

        return response()->json([
            'status'    => true,
            'data'      => $list
        ]);
    }

    public function topSpenderList($id, $customer_code)
    {
        $topSpender = $this->topSpender->find($id);
        $list = $this->order
            ->select('orders.customer_id as id', 'users.name as name', 'users.customer_code as customer_code', DB::raw('SUM(order_detail.total_price) as total'))
            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_detail.product_id')
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->where('orders.status', '=', '4')
            ->where('status_faktur', '=', 'F')
            ->whereBetween('orders.order_time', [$topSpender->start, $topSpender->end]);
        if ($topSpender->site_code != null) {
            $list = $list->where('orders.site_code', '=', $topSpender->site_code);
        }
        if ($topSpender->brand != null) {
            $list = $list->where('order_detail.products.brand_id', '=', $topSpender->brand_id);
        }
        if ($topSpender->product != null) {
            $list = $list->where('order_detail.product_id', '=', $topSpender->product_id);
        }
        $list = $list->groupBy('orders.customer_id', 'users.name', 'users.customer_code')
            ->orderBy('total', 'desc')
            // ->limit($topSpender->limit)
            ->get()->toArray();

        $data_list = array_slice($list, 0, $topSpender->limit);
        $data_user = null;
        $data_user_index = null;
        foreach ($list as $key => $value) {
            if ($value['customer_code'] == $customer_code) {
                $data_user = $value;
                $data_user_index = $key;
                break;
            }
        }

        return view('admin.pages.top-spender-list', compact('data_list', 'data_user', 'data_user_index', 'topSpender'));
    }

    public function create()
    {
        $brand = $this->product->get()
            ->where('brand_id', '!=', 'BSP')
            ->unique('brand_id')
            ->sortBy('brand_id')
            ->pluck('brand_id', 'brand')
            ->except('BSP', NULL);

        return view('admin/pages/top-spender-create', compact('brand'));
    }

    public function edit($id)
    {
        $topSpender = $this->topSpender->find($id);

        $brand = $this->product->get()
            ->where('brand_id', '!=', 'BSP')
            ->unique('brand_id')
            ->sortBy('brand_id')
            ->pluck('brand_id', 'brand')
            ->except('BSP', NULL);

        return view('admin/pages/top-spender-update', compact('topSpender', 'brand'));
    }
}
