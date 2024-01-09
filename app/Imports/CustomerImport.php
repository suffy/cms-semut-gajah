<?php

namespace App\Imports;

use App\MappingSite;
use App\User;
use App\UserAddress;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;


class CustomerImport implements ToModel, WithHeadingRow
{   
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $email = @$row['email'];
        User::updateOrCreate(
            ['email'                 => $email],
            ['name'                  => @$row['name'],
            'account_type'          => '4',
            'platform'              => 'app',
            'account_role'          => @$row['account_role'],
            'password'              => bcrypt(@$row['password']),
            'phone'                 => @$row['phone']]
        );

        Mappingsite::updateOrCreate([
            'branch_name'   => @$row['name_pt'],
            'kode'   => @$row['site_id'],
            'nama_comp'      => @$row['site_name'],
        ]);

        // get user id
        $user = User::where('email', @$row['email'])->first();
        
        // get mapping_site id
        $mapping_site = MappingSite::where('kode', @$row['site_id'])->first();

        UserAddress::updateOrCreate([
            'user_id'           => $user->id,
            'mapping_site_id'   => $mapping_site->id,
            'name'              => @$row['name'],
            'provinsi'          => @$row['province'],
            'kota'              => @$row['city'],
            'kecamatan'         => @$row['district'],
            'kelurahan'         => @$row['subdistrict'],
            'kode_pos'          => @$row['postal_code'],
            'address'           => @$row['address'],
            'default_address'   => '1',
        ]);
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            ['0' => Rule::unique(['mapping_site', 'site_id'])], // Table name, field in your db
            ['1' => Rule::unique(['users', 'email'])], // Table name, field in your db
            ['2' => Rule::unique(['users', 'phone'])], // Table name, field in your db
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.unique' => 'Site ID Duplicate',
            '1.unique' => 'Email Duplicate',
            '2.unique' => 'Phone Duplicate',
        ];
    }
}
