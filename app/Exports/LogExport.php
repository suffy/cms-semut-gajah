<?php

namespace App\Exports;

use App\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LogExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date;

    function __construct($start_date, $end_date) 
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if($this->start_date) {
            $logs   = Log::select('log_time', 'activity', 'data_content', 'table_name', 'column_name', 'from_user', 'to_user', 'platform')
                    ->where('activity', 'not like', '%Insert/update credit limit%')
                    ->where('activity', 'not like', '%Insert new customer%')
                    ->where('activity', 'not like', '%Insert/update salesman from erp%')
                    ->where('activity', 'not like', '%Insert/update product from erp%')
                    ->where('activity', 'not like', '%Already check product from erp%')
                    ->where('activity', 'not like', '%Inser/update stock from erp%')
                    ->where('activity', 'not like', '%Already give notification%')
                    ->whereBetween('log_time', [$this->start_date, $this->end_date])
                    ->get();
        } else {
            $logs   = Log::select('log_time', 'activity', 'data_content', 'table_name', 'column_name', 'from_user', 'to_user', 'platform')
                    ->where('activity', 'not like', '%Insert/update credit limit%')
                    ->where('activity', 'not like', '%Insert new customer%')
                    ->where('activity', 'not like', '%Insert/update salesman from erp%')
                    ->where('activity', 'not like', '%Insert/update product from erp%')
                    ->where('activity', 'not like', '%Already check product from erp%')
                    ->where('activity', 'not like', '%Inser/update stock from erp%')
                    ->where('activity', 'not like', '%Already give notification%')
                    ->get();
        }

        return $logs;
    }

    public function headings(): array
    {
        return ["Log Time", "Activity", "Content", "Table Name", "Column", "From User", "To User", "Platform"];
    }
}
