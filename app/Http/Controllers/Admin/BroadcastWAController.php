<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\BroadcastWA;
use App\BroadcastWADetail;
use App\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class BroadcastWAController extends Controller
{
    protected $broadcastWA, $broadcastWADetail, $logs;

    public function __construct(BroadcastWA $broadcastWA, BroadcastWADetail $broadcastWADetail, Log $logs)
    {
        $this->broadcastWA          = $broadcastWA;
        $this->broadcastWADetail    = $broadcastWADetail;
        $this->logs                 = $logs;
    }

    public function index()
    {
        $broadcastWA = $this->broadcastWA->with('broadcast_wa_detail')->paginate(10);
        
        return view('admin.pages.broadcast-wa', compact('broadcastWA'));
    }

    public function create()
    {
        return view('admin.pages.broadcast-wa-new');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title'             => 'required',
                'schedule'          => 'required',
                'classification'    => 'required',
                'message'           => 'required',
                'type'              => 'required',
            ]);

            if($validator->fails()) {
                return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('errors', $validator->errors())
                        ->with('message', $validator->errors()->first());
            }

            $schedule = substr($request->schedule, 0, 13);

            $broadcastWA                    = $this->broadcastWA;
            $broadcastWA->title             = $request->title;
            $broadcastWA->schedule          = $schedule . ":00";
            $broadcastWA->classification    = $request->classification;
            $broadcastWA->message           = $request->message;
            $broadcastWA->type              = $request->type;
            // dd($broadcastWA);
            $broadcastWA->save();

            $broadcastWADetail = $this->broadcastWADetail;
            if($broadcastWA->classification == 'distributor') {
                foreach($request->site_code as $index => $key) {
                    $data_detail = array(
                        'id_broadcast_wa'   => $broadcastWA->id,
                        'send_to'           => $request->site_code[$index]
                    );
                    $broadcastWADetail->create($data_detail);
                }
            } else if($broadcastWA->classification == 'user') {
                foreach($request->user_id as $index => $key) {
                    $data_detail = array(
                        'id_broadcast_wa'   => $broadcastWA->id,
                        'send_to'           => $request->user_id[$index]
                    );
                    $broadcastWADetail->create($data_detail);
                }
            }

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Create new Broadcast WA";
            $logs->data_content = $broadcastWA;
            $logs->table_id     = $broadcastWA->id;
            $logs->table_name   = 'broadcast_wa';
            $logs->column_name  = 'title, schedule, classification, message, type';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/broadcast'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch (\Exception $e) {
            dd($e->getMessage());
            exit;
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $broadcastWA = $this->broadcastWA->where('id', $id)->with('broadcast_wa_detail.distributor', 'broadcast_wa_detail.user')->get();
        $broadcastWA = $broadcastWA[0];
        // dd($broadcastWA);
        return view('admin.pages.broadcast-wa-edit', compact('broadcastWA'));
    }

    public function update(Request $request)
    {
        try {
            $schedule = substr($request->schedule, 0, 13);

            $broadcastWA                    = $this->broadcastWA->find($request->id);
            $broadcastWA->title             = $request->title;
            $broadcastWA->schedule          = $schedule . ":00";
            $broadcastWA->classification    = $request->classification;
            $broadcastWA->message           = $request->message;
            $broadcastWA->type              = $request->type;
            $broadcastWA->save();

            $broadcastWADetail = $this->broadcastWADetail;
            $broadcastWADetail->where('id_broadcast_wa', $request->id)->delete();
            if($broadcastWA->classification == 'distributor') {
                foreach($request->site_code as $index => $key) {
                    $data_detail = array(
                        'id_broadcast_wa'   => $broadcastWA->id,
                        'send_to'           => $request->site_code[$index]
                    );
                    $broadcastWADetail->create($data_detail);
                }
            } else if($broadcastWA->classification == 'user') {
                foreach($request->user_id as $index => $key) {
                    $data_detail = array(
                        'id_broadcast_wa'   => $broadcastWA->id,
                        'send_to'           => $request->user_id[$index]
                    );
                    $broadcastWADetail->create($data_detail);
                }
            }

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Update Broadcast WA";
            $logs->data_content = $broadcastWA;
            $logs->table_id     = $broadcastWA->id;
            $logs->table_name   = 'broadcast_wa';
            $logs->column_name  = 'title, schedule, classification, message, type';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/broadcast'))
                                            ->with('status', 1)
                                            ->with('message', "Data Tersimpan!");
        } catch (\Exception $e) {
            dd($e->getMessage());
            exit;
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $broadcastWADetail = $this->broadcastWADetail->where('id_broadcast_wa', $id)->delete();

            $broadcastWA = $this->broadcastWA->find($id);
            $broadcastWA->delete();

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "Delete Broadcast WA";
            $logs->data_content = $broadcastWA;
            $logs->table_id     = $broadcastWA->id;
            $logs->table_name   = 'broadcast_wa';
            $logs->column_name  = 'title, schedule, classification, message';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();

            $role = auth()->user()->account_role;

            return redirect(url($role .'/broadcast'))
                                            ->with('status', 1)
                                            ->with('message', "Data Terhapus!");
        } catch (\Exception $e) {
            return redirect()->back()
                        ->withInput()
                        ->with('status', 2)
                        ->with('message', $e->getMessage());
        }
    }

    public function detail($id)
    {
        $broadcastWA = $this->broadcastWA->where('id', $id)->with('broadcast_wa_detail.distributor', 'broadcast_wa_detail.user')->get();
        $broadcastWA = $broadcastWA[0];
        // dd($broadcastWA);
        return response()->json($broadcastWA);
    }
}
