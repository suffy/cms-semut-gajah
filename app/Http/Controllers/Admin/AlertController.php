<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Alert;
use App\AlertStatus;
use App\Promo;
use App\TopSpender;
use App\Log;
use Exception;
use Illuminate\Support\Carbon;
// import Storage class
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AlertController extends Controller
{
    protected $alert, $promo, $topSpender, $log, $alertStatus;
    public function __construct(Alert $alert, Promo $promo, TopSpender $topSpender, Log $log, AlertStatus $alertStatus)
    {
        $this->alert       = $alert;
        $this->promo       = $promo;
        $this->topSpender  = $topSpender;
        $this->log         = $log;
        $this->alertStatus = $alertStatus;
    }

    public function index()
    {
        $alert = $this->alert->first();
        return view('admin/pages/alert', compact('alert'));
    }

    public function ajaxPromo(Request $request)
    {
        $data = [];
        if ($request->has('q')) {
            $search = $request->q;
            $data = $this->promo->where('status', 1)
                ->where('title', 'LIKE', "%" . $search . "%")
                ->orderBy('title')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function ajaxTopSpender(Request $request)
    {
        $data = [];
        if ($request->has('q')) {
            $search = $request->q;
            $now = Carbon::now()->format('Y-m-d');
            $data = $this->topSpender
                ->where('start', '<=', $now)
                ->where('end', '>=', $now)
                // ->where('status', 1)
                ->where('title', 'LIKE', "%" . $search . "%")
                ->orderBy('title')
                // ->limit(20)
                ->get();
        }
        return response()->json($data);
    }

    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'type'          => 'required',
                'title'         => 'required_if:type,promo,top_spender|string',
                'description'   => 'required_if:type,promo,top_spender|string',
                'start'         => 'required|date',
                'end'           => 'required|date',
                'parameter'     => 'required_if:type,promo,top_spender,link',
                'image'         => 'required_if:type,image',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withInput()
                    ->with('status', 2)
                    ->with('errors', $validator->errors())
                    ->with('message', $validator->errors()->first());
            }

            $alert = $this->alert->first();
            if ($request->image) {
                if (file_exists(public_path($alert->parameter)) && $alert->parameter != null) {
                    unlink(public_path($alert->parameter)); //menghapus file lama
                }
                $Newname = "Alert-Image" . "-" . time() . '.' . $request->image->extension();
                $parameter = 'images/alertImage/' . $Newname;
                $request->image->move(public_path('images/alertImage/'), $Newname);
            } else {
                rmdir(public_path('images/alertImage'));
                $parameter = $request->parameter;
            }

            $activity = null;
            if ($alert != null) {
                $alert->title       = $request->title;
                $alert->description = $request->description;
                $alert->type        = $request->type;
                $alert->start       = $request->start;
                $alert->end         = $request->end;
                $alert->parameter   = $parameter;
                $alert->save();

                $activity = "Update alert notification";
            } else {
                $alert = $this->alert;
                $alert->title       = $request->title;
                $alert->description = $request->description;
                $alert->type        = $request->type;
                $alert->start       = $request->start;
                $alert->end         = $request->end;
                $alert->parameter   = $parameter;
                $alert->created_at  = Carbon::now();
                $alert->updated_at  = Carbon::now();
                $alert->save();

                $activity = "Create alert notification";
            }

            $alertStatus = $this->alertStatus->truncate();

            $log = $this->log;
            $log->log_time      = Carbon::now();
            $log->activity      = $activity;
            $log->data_content  = $alert;
            $log->table_id      = $alert->id;
            $log->table_name    = 'alerts';
            $log->column_name   = 'title, description, type, start, end, parameter';
            $log->from_user     = auth()->user()->id;
            $log->to_user       = null;
            $log->platform      = 'web';
            $log->save();

            $role = auth()->user()->account_role;

            return redirect(url($role . '/alert'))
                ->with('status', 1)
                ->with('message', "Data Tersimpan!");
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('status', 2)
                ->with('message', $e->getMessage());
        }
    }
}
