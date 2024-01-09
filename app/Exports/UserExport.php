<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        if(auth()->user()->account_role == 'distributor') {
            $logs   = User::select('name', 'email', 'code_approval', 'site_code', 'customer_code', 'salur_code', 'class', 'type_payment')
                    ->where('account_type','4')
                    ->where('site_code', auth()->user()->site_code)
                    ->get();
        } else {
            $logs   = User::select('name', 'email', 'code_approval', 'site_code', 'customer_code', 'salur_code', 'class', 'type_payment')
                    ->where('account_type','4')
                    ->get();
        }


        return $logs;
    }

    public function headings(): array
    {
        return ["Name", "Email", "Code Approval", "Site Code", "Customer Code", "Class", "Class Name", "Type"];
    }
}
