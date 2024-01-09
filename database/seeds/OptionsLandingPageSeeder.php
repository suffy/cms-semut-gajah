<?php

use Illuminate\Database\Seeder;

class OptionsLandingPageSeeder extends Seeder
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
                'slug'          => "name",
                'option_type'   => 'pwa',
                'option_name'   => 'Name',
                'option_value'  => 'Semut Gajah',
            ],
            [
                'slug'          => "description",
                'option_type'   => 'pwa',
                'option_name'   => 'Description',
                'option_value'  => 'Ini Hanya Sebuah Description',
            ],
            [
                'slug'          => "link_fb",
                'option_type'   => 'pwa',
                'option_name'   => 'Link Facebook',
                'option_value'  => 'https://www.facebook.com',
            ],
            [
                'slug'          => "link_ig",
                'option_type'   => 'pwa',
                'option_name'   => 'Link Instagram',
                'option_value'  => 'https://www.instagram.com',
            ],
            [
                'slug'          => "link_twit",
                'option_type'   => 'pwa',
                'option_name'   => 'Link Twitter',
                'option_value'  => 'https://www.twitter.com',
            ],
            [
                'slug'          => "link_yb",
                'option_type'   => 'pwa',
                'option_name'   => 'Link Youtube',
                'option_value'  => 'https://www.youtube.com',
            ],
            [
                'slug'          => "email",
                'option_type'   => 'pwa',
                'option_name'   => 'Email',
                'option_value'  => 'semutgajahofficial@gmail.com',
            ],
            [
                'slug'          => "url_landing_page",
                'option_type'   => 'pwa',
                'option_name'   => 'Url Landing Page',
                'option_value'  => 'https://www.semutgajah.com',
            ],
            [
                'slug'          => "url_pwa",
                'option_type'   => 'pwa',
                'option_name'   => 'Url PWA',
                'option_value'  => 'https://www.m.semutgajah.com',
            ]

        ];

        \DB::table('options')->insert($data);
    }
}
