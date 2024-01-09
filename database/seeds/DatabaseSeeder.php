<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        Eloquent::unguard();

        $this->call(UsersTableSeeder::class);

        $this->call(MenusTableSeeder::class);
        $this->command->info('All table seeded!');

        $users = database_path('sql/users.sql');
        DB::unprepared(file_get_contents($users));
        $this->command->info('users table seeded!');

        $mapping_site = database_path('sql/mapping_site.sql');
        DB::unprepared(file_get_contents($mapping_site));
        $this->command->info('mapping_site table seeded!');

        $user_address = database_path('sql/user_address.sql');
        DB::unprepared(file_get_contents($user_address));
        $this->command->info('user_address table seeded!');

        $salesmen = database_path('sql/salesmen.sql');
        DB::unprepared(file_get_contents($salesmen));
        $this->command->info('salesmen table seeded!');

        $user_meta = database_path('sql/user_meta.sql');
        DB::unprepared(file_get_contents($user_meta));
        $this->command->info('user_meta table seeded!');
        
        $db_rajaongkir = database_path('sql/db_rajaongkir.sql');
        DB::unprepared(file_get_contents($db_rajaongkir));
        $this->command->info('db_rajaongkir table seeded!');

        $post_categories = database_path('sql/post_categories.sql');
        DB::unprepared(file_get_contents($post_categories));
        $this->command->info('Post categories table seeded!');

        $basic_data = database_path('sql/basic_data.sql');
        DB::unprepared(file_get_contents($basic_data));
        $this->command->info('Basic table seeded!');

        $categories = database_path('sql/categories.sql');
        DB::unprepared(file_get_contents($categories));
        $this->command->info('categories table seeded!');

        $product = database_path('sql/products.sql');
        DB::unprepared(file_get_contents($product));
        $this->command->info('products table seeded!');

        $product_prices = database_path('sql/product_prices.sql');
        DB::unprepared(file_get_contents($product_prices));
        $this->command->info('product price table seeded!');

        $order = database_path('sql/orders.sql');
        DB::unprepared(file_get_contents($order));
        $this->command->info('orders table seeded!');

        $order = database_path('sql/offers.sql');
        DB::unprepared(file_get_contents($order));
        $this->command->info('offers table seeded!');

        $order = database_path('sql/offers_item.sql');
        DB::unprepared(file_get_contents($order));
        $this->command->info('offers_item table seeded!');

        $order = database_path('sql/db_rajaongkir.sql');
        DB::unprepared(file_get_contents($order));
        $this->command->info('db raja ongkir table seeded!');

        $order = database_path('sql/help_categories.sql');
        DB::unprepared(file_get_contents($order));
        $this->command->info('db help categories table seeded!');

        $order = database_path('sql/helps.sql');
        DB::unprepared(file_get_contents($order));
        $this->command->info('db helps table seeded!');

        $credit_limit = database_path('sql/credit_limits.sql');
        DB::unprepared(file_get_contents($credit_limit));
        $this->command->info('db credit_limits table seeded!');

        $promo = database_path('sql/promos.sql');
        DB::unprepared(file_get_contents($promo));
        $this->command->info('db promos table seeded!');
    }
}
