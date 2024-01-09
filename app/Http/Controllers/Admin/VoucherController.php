<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Log;
use App\Order;
use App\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as InterImage;

class VoucherController extends Controller
{

    protected $voucher;
    protected $order;
    protected $logs;

    public function __construct(Voucher $voucher, Order $order, Log $log){
        $this->voucher = $voucher;
        $this->order = $order;
        $this->logs = $log;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $voucher = $this->voucher::query();
        $valsearch = preg_replace('/[^A-Za-z0-9 ]/', '', $request->input('search'));

        if($request->has('search')){
            $voucher = $voucher->where('code', 'like', '%'.$valsearch.'%');
        }

        if($request->has('ordering')){
            if($request->input('ordering')=="newest"){
                $voucher = $voucher->orderBy('id', 'desc');
            }

            if($request->input('ordering')=="oldest"){
                $voucher = $voucher->orderBy('id', 'asc');
            }

            if($request->input('ordering')=="code"){
                $voucher = $voucher->orderBy('code', 'asc');
            }
        }
        $voucher = $voucher->paginate(10);

        return view('admin/pages/voucher-code')
            ->with("voucher", $voucher);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('admin/pages/voucher-code-new');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if($request->input('type')=='percent'){
            $per = (int)str_replace("%", "", $request->input('percent'));

            if($per>100){
                $per=100;
            }
            $nominal = 0;
            $percent = $per;

        }else{
            $nominal = (int)str_replace(".","",$request->input('nominal'));
            $percent = 0;
        }

        if($request->input('id')!==null)
         {
            $vouc = $this->voucher::find($request->input('id'));
            $vouc->save();

        }else{
            $vouc = new $this->voucher();
            $vouc->save();
        }

        if($vouc){
            $vouc->code = $request->input('code');
            $vouc->type = $request->input('type');
            $vouc->admin_id = Auth::user()->id;
            $vouc->percent = $percent;
            $vouc->nominal = $nominal;
            $vouc->max_nominal = (int)str_replace(".","",$request->input('max_nominal'));
            $vouc->max_use = (int)$request->input('max_use');
            $vouc->max_use_user = (int)$request->input('max_use_user');
            $vouc->daily_use = (int)$request->input('daily_use');
            $vouc->min_transaction = (int)str_replace(".","",$request->input('min_transaction'));
            $vouc->max_transaction = (int)str_replace(".","",$request->input('max_transaction'));
            $vouc->category = $request->input('category');
            $vouc->description = $request->input('description');
            $vouc->termandcondition = $request->input('termandcondition');
            $vouc->start_at = $request->input('start_at')." ".$request->input('time_start_at');
            $vouc->end_at = $request->input('end_at')." ".$request->input('time_end_at');
            $vouc->icon = $request->input('icon');
            $vouc->available = $request->input('available');
            $vouc->status = $request->input('status');

            if ($request->hasFile('file')) {
                $relPathImage = '/images/voucher/';
                if (!file_exists(public_path($relPathImage))) {
                    mkdir(public_path($relPathImage), 0755, true);
                }
                $file = $request->file('file');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "voucher" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/voucher/' . $newName));
                $vouc->file = '/images/voucher/'.$newName;
            }

            $vouc->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new voucher";
            $logs->data_content = $vouc;
            $logs->table_name   = 'coupon';
            $logs->column_name  = 'code, type, admin_id, percent, nominal, max_nominal, max_use, max_use_user, daily_use, min_transaction, max_transaction, category, description, termandcondition, start_at, end_at, icon, available, status';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            if (auth()->user()->account_role == 'manager') {
                return redirect('manager/vouchers/'.$vouc->id)
                    ->with('status', 1)
                    ->with('message', 'Voucher created successfully!');
            } else {
                return redirect('superadmin/vouchers/'.$vouc->id)
                    ->with('status', 1)
                    ->with('message', 'Voucher created successfully!');
            }
        }else{
            if (auth()->user()->account_role == 'manager') {
                return redirect('manager/vouchers/create')
                    ->with('status', 2)
                    ->with('message', 'Failed!');
            } else {
                return redirect('superadmin/vouchers/create')
                    ->with('status', 2)
                    ->with('message', 'Failed!');
            }
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
        //
        $voucher = $this->voucher::find($id);
        $order = $this->order::where('coupon_id', $id)
                ->whereIn('status', [1,2,3,4,6])
                ->orderBy('order_time', 'desc')
                ->paginate(15);
        return view('admin/pages/voucher-code-detail')
            ->with('order', $order)
            ->with("voucher", $voucher);
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

        $vouc = $this->voucher::find($request->input('id'));

        if($request->input('type')=='percent'){
            $per = (int)str_replace("%", "", $request->input('percent'));

            if($per>100){
                $per=100;
            }
            $nominal = 0;
            $percent = $per;

        }else{
            $nominal = (int)str_replace(".","",$request->input('nominal'));
            $percent = 0;
        }

        if($request->input('id')!==null)
         {
            $vouc = $this->voucher::find($request->input('id'));
            $vouc->save();

        }else{
            $vouc = new $this->voucher();
            $vouc->save();
        }

        if($vouc){
            $vouc->code = $request->input('code');
            $vouc->type = $request->input('type');
            $vouc->admin_id = Auth::user()->id;
            $vouc->percent = $percent;
            $vouc->nominal = $nominal;
            $vouc->max_nominal = (int)str_replace(".","",$request->input('max_nominal'));
            $vouc->max_use = (int)$request->input('max_use');
            $vouc->max_use_user = (int)$request->input('max_use_user');
            $vouc->daily_use = (int)$request->input('daily_use');
            $vouc->min_transaction = (int)str_replace(".","",$request->input('min_transaction'));
            $vouc->max_transaction = (int)str_replace(".","",$request->input('max_transaction'));
            $vouc->category = $request->input('category');
            $vouc->description = $request->input('description');
            $vouc->termandcondition = $request->input('termandcondition');
            $vouc->start_at = $request->input('start_at')." ".$request->input('time_start_at');
            $vouc->end_at = $request->input('end_at')." ".$request->input('time_end_at');
            $vouc->icon = $request->input('icon');
            $vouc->available = $request->input('available');
            $vouc->status = $request->input('status');

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = $file->getClientOriginalExtension();
    
                $newName = "voucher" . date('YmdHis') . "." . $ext;
    
                $image_resize = InterImage::make($file->getRealPath());
                $image_resize->save(('images/voucher/' . $newName));
                $vouc->file = '/images/voucher/'.$newName;
            }

            $vouc->save();

            // logs
            $logs = $this->logs;

            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update voucher with id : " . $id;
            $logs->data_content = $vouc;
            $logs->table_name   = 'coupon';
            $logs->column_name  = 'code, type, admin_id, percent, nominal, max_nominal, max_use, max_use_user, daily_use, min_transaction, max_transaction, category, description, termandcondition, start_at, end_at, icon, available, status';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";

            $logs->save();

            if (auth()->user()->account_role == 'manager') {
                return redirect('manager/vouchers/'.$vouc->id)
                    ->with('status', 1)
                    ->with('message', 'Voucher edit successfully!');
            } else {
                return redirect('superadmin/vouchers/'.$vouc->id)
                    ->with('status', 1)
                    ->with('message', 'Voucher edit successfully!');
            }
        }else{
            if (auth()->user()->account_role == 'manager') {
                return redirect('manager/vouchers/'.$vouc->id)
                    ->with('status', 2)
                    ->with('message', 'Voucher edit failed!');
            } else {
                return redirect('superadmin/vouchers/'.$vouc->id)
                    ->with('status', 2)
                    ->with('message', 'Voucher edit failed!');
            }
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
        //
    }
}
