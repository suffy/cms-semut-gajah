<?php

namespace App\Http\Controllers;

use App\Exports\LogExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LogController extends Controller
{    
    public function __construct()
    {
        ini_set('memory_limit', '-1');
    }

    public function export(Request $request)
    {
        $start_date = '';
        $end_date   = '';
        if($request->start_date != null && $request->end_date != null) {
            $start_date = $request->start_date;
            $end_date   = $request->end_date;
        //     $filename   = 'logs-'.$start_date.'-'.$end_date.'.xlsx';
        } 
        // else {
        //     $filename   = 'logs.xlsx';
        // }
        $filename = 'logs-' . Carbon::now()->format('Y-m-d-H_i_s') . '.xlsx';
        return Excel::download(new LogExport($start_date, $end_date), $filename);
    }
}
