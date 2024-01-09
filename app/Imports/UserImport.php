<?php

namespace App\Imports;

use App\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class UserImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        User::create([
            'name'                  => @$row['name'],
            'email'                 => @$row['email'],
            'account_type'          => '1',
            'account_role'          => @$row['account_role'],
            'password'              => @$row['password'],
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }
}
