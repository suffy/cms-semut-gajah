<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'manager',
                'email' => 'manager@cyberolympus.com',
                'password' => bcrypt('manager'),
                'phone' => null,
                'account_type' => 1,
                'account_role' => 'manager',
                'photo' => '/images/profile/profile20210702083501.jpeg',
                'platform' => null,
                'site_code' => null,
                'otp_verified_at' => Carbon::now(),
                'customer_code' => null,
                'salur_code' => null,
                'class' => null,
                'type_payment' => null
            ],
            [
                'name' => 'superadmin',
                'email' => 'superadmin@cyberolympus.com',
                'password' => bcrypt('superadmin'),
                'phone' => null,
                'account_type' => 1,
                'account_role' => 'superadmin',
                'photo' => '/images/profile/profile20210702083501.jpeg',
                'platform' => null,
                'site_code' => null,
                'otp_verified_at' => Carbon::now(),
                'customer_code' => null,
                'salur_code' => null,
                'class' => null,
                'type_payment' => null
            ],
            [
                'name' => 'admin',
                'email' => 'admin@cyberolympus.com',
                'password' => bcrypt('admin'),
                'phone' => null,
                'account_type' => 1,
                'account_role' => 'admin',
                'photo' => '/images/profile/profile20210702083501.jpeg',
                'platform' => null,
                'site_code' => null,
                'otp_verified_at' => Carbon::now(),
                'customer_code' => null,
                'salur_code' => null,
                'class' => null,
                'type_payment' => null
            ],
            [
                'name' => 'user',
                'email' => 'user@email.com',
                'password' => bcrypt('qweasd123'),
                'phone' => '081111111111',
                'account_type' => 4,
                'account_role' => 'user','photo' => '/images/profile/profile20210702083501.jpeg',
                'platform' => 'app',
                'site_code' => 'JK101',
                'otp_verified_at' => Carbon::now(),
                'customer_code' => 'JK1100000',
                'salur_code' => 'RT',
                'class' => 'RITEL',
                'type_payment' => 'T'
            ]

        ];

        User::insert($users);
    }
}
