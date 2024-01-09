<?php

use App\Menu;
use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Stores',
                'slug' => 'page/lokasi-toko',
                'menu_order' => '1',
                'status' => '1',
            ],
            [
                'name' => 'Contact',
                'slug' => 'contact',
                'menu_order' => '2',
                'status' => '1',
            ]

        ];

        Menu::insert($data);
    }
}
