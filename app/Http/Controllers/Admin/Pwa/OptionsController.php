<?php

namespace App\Http\Controllers\Admin\Pwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataOption;
use App\Log;
use Illuminate\Support\Carbon;

class OptionsController extends Controller
{
    protected $options, $logs;
    public function __construct(DataOption $options, Log $logs)
    {
        $this->options  = $options;
        $this->logs     = $logs;
    }

    public function index()
    {
        return view('admin.pages.options');
    }

    public function fetch_data(Request $request)
    {
        $options = $this->options->where('option_type', 'pwa')->orderBy('id', 'asc')->paginate(10);
        if($request->ajax()){
            return view('admin.pages.pagination_data_option', compact('options'))->render();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $options = $this->options->find($id);

            $options->update([
                'option_name'    => $request->option_name,
                'option_value'   => $request->option_value,
            ]);

            $logs = $this->logs;
            $logs->log_time     = Carbon::now();
            $logs->activity     = "update options id " . $options->id;
            $logs->table_id     = $options->id;
            $logs->table_name   = 'options';
            $logs->from_user    = auth()->user()->id;
            $logs->to_user      = null;
            $logs->platform     = "web";
            $logs->save();
    
            return response()->json(['success' => true], 200);
        } catch(\Exception $e) {
            return response()->json(['data' => $e->getMessage()], 400);
        }
    }
}
