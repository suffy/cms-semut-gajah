<?php

namespace App\Console\Commands;

use App\Log;
use App\Product;
use App\ProductStrata;
use App\ProductPrice;
use Carbon\Carbon;
use Exception;
use Intervention\Image\ImageManagerStatic as InterImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DailyProduct extends Command
{
    protected $products, $productPrices, $productStrata, $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate insert products from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Product $product, ProductPrice $productPrice, ProductStrata $productStrata, Log $log)
    {
        parent::__construct();
        $this->products         = $product;
        $this->productPrices    = $productPrice;
        $this->productStrata      = $productStrata;
        $this->log              = $log;
    }

    // get products from erp
    public function get()
    {
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/product', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api')
        ])->json();

        $this->store($response['data']);
    }

    // insert into products & product_prices table
    public function store($products)
    {
        foreach ($products as $product) {
            $this->info($product['kodeprod']);
            // generate slug
            $slug = strtolower(str_replace(" ", "-", preg_replace('/[^A-Za-z0-9 !@#$%^&*()-.]/u', '', strip_tags(strtolower(str_replace(" ", "-", $product['namaprod'])))))); // namaprod

            // check category
            $category_id = null; // apps_kategori_online
            $brand = null; // supp

            if ($product['apps_kategori_online'] == 'HERBAL') {
                $category_id = '1';
            }

            if ($product['apps_kategori_online'] == 'SUPPLEMEN & MULTIVITAMIN') {
                $category_id = '2';
            }

            if ($product['apps_kategori_online'] == 'FOOD & BEV') {
                $category_id = '3';
            }

            if ($product['apps_kategori_online'] == 'MINYAK ANGIN & BALSAM') {
                $category_id = '4';
            }

            if ($product['apps_kategori_online'] == '') {
                $category_id = null;
            }

            if ($product['supp']        == '001') {
                $brand = 'deltomed';
            } else if ($product['supp'] == '002') {
                $brand = 'marguna';
            } else if ($product['supp'] == '003') {
                $brand = 'jamu jago';
            } else if ($product['supp'] == '004') {
                $brand = 'jaya agung';
            } else if ($product['supp'] == '005') {
                $brand = 'ultra sakti';
            } else if ($product['supp'] == '007') {
                $brand = 'vonix latexindo';
            } else if ($product['supp'] == '008') {
                $brand = 'bintang kupu kupu';
            } else if ($product['supp'] == '009') {
                $brand = 'unilever';
            } else if ($product['supp'] == '010') {
                $brand = 'natura vita utama';
            } else if ($product['supp'] == '011') {
                $brand = 'herbana';
            } else if ($product['supp'] == '012') {
                $brand = 'intrafood singabera';
            } else if ($product['supp'] == '013') {
                $brand = 'nutrisi harapan bangsa';
            } else if ($product['supp'] == '014') {
                $brand = 'heavenly nutrition indonesia';
            }

            $data = DB::table('products')->where('kodeprod', $product['kodeprod'])->select('image', 'updated_at')->first();

            // postgres
            if ($product['apps_last_updated'] == '0000-00-00 00:00:00') {
                $apps_last_updated = Carbon::now();
            } else {
                $apps_last_updated = $product['apps_last_updated'];
            }
            // end

            if (is_null($data)) {
                // get image and store at folder
                $product_image = NULL;
                if ($product['apps_images']) {
                    try {
                        $url             = $product['apps_images'];
                        $info            = pathinfo($url);
                        $context         = stream_context_create(['http' => ['ignore_errors' => true]]);
                        $contents        = file_get_contents($url);
                        if (!is_array($http_response_header)) {
                            if (strpos($http_response_header, "did not properly respond") !== false) {
                                // insert to logs table
                                $this->log->create(
                                    [
                                        'table_id'     => $product['kodeprod'],
                                        'log_time'     => Carbon::now(),
                                        'activity'      => 'failed download image product from erp with id : ' . $product['kodeprod'],
                                        'table_name'    => 'products',
                                        'column_name'   => 'products.id, products.name, products.image',
                                        'from_user'     => null,
                                        'to_user'       => null,
                                        'data_content'  => null,
                                        'platform'      => 'web',
                                        'created_at'    => Carbon::now()
                                    ]
                                );
                                $contents = file_get_contents($url);
                            }
                        }
                        $rel_path        = '/images/product/';
                        if (!file_exists(public_path($rel_path))) {
                            mkdir(public_path($rel_path), 777, true);
                        }
                        $new_name        = $product['kodeprod'] . "." . $info['extension'];
                        $product_image   = $rel_path . $new_name;
                        // if (file_exists(public_path() . $product_image)) {
                        //     unlink(public_path() . $product_image); //menghapus file lama
                        // }

                        $image_resize = InterImage::make($contents);
                        $image_resize->save(('public/images/product/' . $new_name));
                    } catch (\Exception $e) {
                    }
                }
                // end

                $this->products->Create(
                    [
                        'id'                       => $product['kodeprod'], //kodeprod
                        'kodeprod'                  => $product['kodeprod'],
                        'brand_id'                  => $product['supp'], //supp
                        'brand'                     => $brand,
                        'status_herbana'            => $product['apps_status_herbana'], //apps_status_herbana
                        'invoice_name'              => $product['namaprod'],  // namaprod
                        'name'                      => $product['apps_namaprod'], // apps_namaprod
                        // 'search_name'               => strtolower($product['apps_namaprod']), // apps_namaprod_search
                        // 'large_unit'        => $product['besar'],  //--
                        // 'medium_unit'       => $product['sedang'], //  | -> apps_satuan_online
                        // 'small_unit'        => $product['kecil'],  //--
                        'satuan_online'             => $product['apps_satuan_online'],
                        // 'large_qty'         => $product['qty1'], // --
                        // 'medium_qty'        => $product['qty2'], //   | -> apps_konversi_sedang_ke_kecil
                        // 'small_qty'         => $product['qty3'], // --
                        'group_id'                  => $product['group_id'],
                        'nama_group'                => $product['nama_group'],
                        'subgroup'                  => $product['subgroup'],
                        'nama_sub_group'            => $product['nama_sub_group'],
                        'besar'                     => $product['besar'],
                        'sedang'                    => $product['sedang'],
                        'kecil'                     => $product['kecil'],
                        'qty1'                      => $product['qty1'],
                        'qty2'                      => $product['qty2'],
                        'qty3'                      => $product['qty3'],
                        'konversi_sedang_ke_kecil'  => $product['apps_konversi_sedang_ke_kecil'],
                        'slug'                      => $slug,
                        'category_id'               => $category_id,
                        'description'               => $product['apps_deskripsi'],
                        // 'image'                     => $product['apps_images'], // apps_image
                        'image_backup'              => $product['apps_images'], // image_temporary
                        'image'                     => $product_image, // apps_image update 13 - 01 - 2022
                        'spent'                     => $product['apps_spent'], // apps_spent
                        'point'                     => $product['apps_point'], // apps_point
                        'ratio'                     => $product['apps_ratio'], // apps_ratio
                        'status'                    => $product['apps_status_aktif'], // apps_status_aktif
                        'min_pembelian'             => $product['apps_min_pembelian'], // apps_min_pembelian
                        'status_promosi_coret'      => $product['apps_status_promosi_coret'], // apps_status_promosi_coret
                        'status_terlaris'           => $product['apps_status_terlaris'], //
                        'status_terbaru'            => $product['apps_status_terbaru'], //
                        'status_renceng'            => $product['apps_status_renceng'], //
                        'type_status'               => $product['apps_status_type'], //
                        'updated_at'                => $apps_last_updated
                    ]
                );

                // insert into product_detail table
                if (count($product['master_harga']) > 0) {
                    $this->productPrices->updateOrCreate(
                        ['product_id'                       => $product['kodeprod']], //kodeprod
                        [
                            'harga_ritel_gt'                   => $product['master_harga'][0]['apps_harga_ritel_gt'] != "" ? $product['master_harga'][0]['apps_harga_ritel_gt'] : NULL, // medium_retil
                            'harga_grosir_mt'                   => $product['master_harga'][0]['apps_harga_grosir_mt'] != "" ? $product['master_harga'][0]['apps_harga_grosir_mt'] : NULL, // medium_grosir
                            'harga_semi_grosir'                 => $product['master_harga'][0]['apps_harga_semi_grosir'] != "" ? $product['master_harga'][0]['apps_harga_semi_grosir'] : NULL, // apps_harga_grosir_mt
                            //harga promo
                            'harga_promosi_coret_ritel_gt'      =>  $product['master_harga'][0]['apps_harga_promosi_coret_ritel_gt'],  // apps_harga_promosi_coret_ritel_gt
                            'harga_promosi_coret_grosir_mt'     =>  $product['master_harga'][0]['apps_harga_promosi_coret_grosir_mt'], // apps_harga_promosi_coret_grosir_mt
                            'harga_promosi_coret_semi_grosir'   =>  $product['master_harga'][0]['apps_harga_promosi_coret_semi_grosir']
                        ], // apps_harga_promosi_coret_semi_grosir
                    );
                }

                // type disc class or min transaction
                if ($product['apps_discount_class'] == '0') {
                    $this->productStrata->Create([
                        'product_id' => $product['kodeprod'],
                        'disc_percent' => $product['apps_discount_class_persen'],
                        'min_transaction' => $product['apps_discount_class_minimum_transaksi'],
                    ]);
                }

                // insert to logs table
                $this->log->create(
                    [
                        'table_id'     => $product['kodeprod'],
                        'log_time'     => Carbon::now(),
                        'activity'      => 'Insert/update product from erp with id : ' . $product['kodeprod'],
                        'table_name'    => 'products',
                        'column_name'   => 'products.id, products.brand_id, products.status_herbana, products.invoice_name, products.name, products.search_name, products.satuan_online, products.konversi_sedang_ke_kecil, products.slug, products.category_id, products.description, products.image, products.weight, products.status_promosi_coret',
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => null,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()
                    ]
                );
            } else {
                if ($data->updated_at != $product['apps_last_updated']) {
                    // get image and store at folder
                    $old_image       = $data->image;
                    $product_image   = $old_image;
                    if ($product['apps_images']) {
                        try {
                            $url             = $product['apps_images'];
                            $info            = pathinfo($url);
                            $context         = stream_context_create(['http' => ['ignore_errors' => true]]);
                            $contents        = file_get_contents($url);
                            if (!is_array($http_response_header)) {
                                if (strpos($http_response_header, "did not properly respond") !== false) {
                                    // insert to logs table
                                    $this->log->create(
                                        [
                                            'table_id'     => $product['kodeprod'],
                                            'log_time'     => Carbon::now(),
                                            'activity'      => 'failed download image product from erp with id : ' . $product['kodeprod'],
                                            'table_name'    => 'products',
                                            'column_name'   => 'products.id, products.name, products.image',
                                            'from_user'     => null,
                                            'to_user'       => null,
                                            'data_content'  => null,
                                            'platform'      => 'web',
                                            'created_at'    => Carbon::now()
                                        ]
                                    );
                                    $contents = file_get_contents($url);
                                }
                            }
                            $new_name        = $product['kodeprod'] . "." . $info['extension'];
                            $rel_path        = '/images/product/';
                            $product_image   = $rel_path . $new_name;
                            if (file_exists(public_path() . $old_image)) {
                                unlink(public_path() . $old_image); //menghapus file lama
                            }
                            $image_resize = InterImage::make($contents);
                            $image_resize->save(('public/images/product/' . $new_name));
                        } catch (\Exception $e) {
                            $this->info($e->getMessage());
                        }
                    }

                    // end

                    // insert into products table
                    $this->products->updateOrCreate(
                        ['id'                       => $product['kodeprod']], //kodeprod
                        [
                            'kodeprod'                 => $product['kodeprod'],
                            'brand_id'                  => $product['supp'], //supp
                            'brand'                     => $brand,
                            'status_herbana'            => $product['apps_status_herbana'], //apps_status_herbana
                            'invoice_name'              => $product['namaprod'],  // namaprod
                            'name'                      => $product['apps_namaprod'], // apps_namaprod
                            // 'search_name'               => strtolower($product['apps_namaprod']), // apps_namaprod_search
                            // 'large_unit'             => $product['besar'],  //--
                            // 'medium_unit'            => $product['sedang'], //  | -> apps_satuan_online
                            // 'small_unit'             => $product['kecil'],  //--
                            'satuan_online'             => $product['apps_satuan_online'],
                            // 'large_qty'              => $product['qty1'], // --
                            // 'medium_qty'             => $product['qty2'], //   | -> apps_konversi_sedang_ke_kecil
                            // 'small_qty'              => $product['qty3'], // --
                            'group_id'                  => $product['group_id'],
                            'nama_group'                => $product['nama_group'],
                            'subgroup'                  => $product['subgroup'],
                            'nama_sub_group'            => $product['nama_sub_group'],
                            'besar'                     => $product['besar'],
                            'sedang'                    => $product['sedang'],
                            'kecil'                     => $product['kecil'],
                            'qty1'                      => $product['qty1'],
                            'qty2'                      => $product['qty2'],
                            'qty3'                      => $product['qty3'],
                            'konversi_sedang_ke_kecil'  => $product['apps_konversi_sedang_ke_kecil'],
                            'slug'                      => $slug,
                            'category_id'               => $category_id,
                            'description'               => $product['apps_deskripsi'],
                            // 'image'                     => $product['apps_images'], // apps_image
                            'image_backup'              => $product['apps_images'], // image_temporary
                            'image'                     => $product_image, // apps_image update 13 - 01 - 2022
                            'spent'                     => $product['apps_spent'], // apps_spent
                            'point'                     => $product['apps_point'], // apps_point
                            'ratio'                     => $product['apps_ratio'], // apps_ratio
                            'status'                    => $product['apps_status_aktif'], // apps_status_aktif
                            'min_pembelian'             => $product['apps_min_pembelian'], // apps_min_pembelian
                            'status_promosi_coret'      => $product['apps_status_promosi_coret'], // apps_status_promosi_coret
                            'status_terlaris'           => $product['apps_status_terlaris'], //
                            'status_terbaru'            => $product['apps_status_terbaru'], //
                            'status_renceng'            => $product['apps_status_renceng'], //
                            'type_status'               => $product['apps_status_type'], //
                            'updated_at'                => $apps_last_updated
                        ]
                    );

                    // insert into product_detail table
                    if (count($product['master_harga']) > 0) {
                        $this->productPrices->updateOrCreate(
                            ['product_id'                       => $product['kodeprod']], //kodeprod
                            [
                                'harga_ritel_gt'                   => $product['master_harga'][0]['apps_harga_ritel_gt'] != "" ? $product['master_harga'][0]['apps_harga_ritel_gt'] : NULL, // medium_retil
                                'harga_grosir_mt'                   => $product['master_harga'][0]['apps_harga_grosir_mt'] != "" ? $product['master_harga'][0]['apps_harga_grosir_mt'] : NULL, // medium_grosir
                                'harga_semi_grosir'                 => $product['master_harga'][0]['apps_harga_semi_grosir'] != "" ? $product['master_harga'][0]['apps_harga_semi_grosir'] : NULL, // apps_harga_grosir_mt
                                //harga promo
                                'harga_promosi_coret_ritel_gt'      =>  $product['master_harga'][0]['apps_harga_promosi_coret_ritel_gt'],  // apps_harga_promosi_coret_ritel_gt
                                'harga_promosi_coret_grosir_mt'     =>  $product['master_harga'][0]['apps_harga_promosi_coret_grosir_mt'], // apps_harga_promosi_coret_grosir_mt
                                'harga_promosi_coret_semi_grosir'   =>  $product['master_harga'][0]['apps_harga_promosi_coret_semi_grosir']
                            ], // apps_harga_promosi_coret_semi_grosir
                        );
                    }

                    // type disc class or min transaction
                    if ($product['apps_discount_class'] == '0') {
                        $this->productStrata->updateOrCreate(
                            ['product_id'     => $product['kodeprod']], //kodeprod
                            [
                                'disc_percent' => $product['apps_discount_class_persen'],
                                'min_transaction' => $product['apps_discount_class_minimum_transaksi'],
                            ]
                        );
                    }

                    $products = $this->products
                        ->where('kodeprod', $product['kodeprod'])
                        ->first();

                    // insert to logs table
                    $this->log->updateOrCreate(
                        ['table_id'     => $product['kodeprod']],
                        [
                            'log_time'     => Carbon::now(),
                            'activity'      => 'Insert/update product from erp with id : ' . $product['kodeprod'],
                            'table_name'    => 'products',
                            'column_name'   => 'products.id, products.brand_id, products.status_herbana, products.invoice_name, products.name, products.search_name, products.satuan_online, products.konversi_sedang_ke_kecil, products.slug, products.category_id, products.description, products.image, products.weight, products.status_promosi_coret',
                            'from_user'     => null,
                            'to_user'       => null,
                            'data_content'  => $products,
                            'platform'      => 'web',
                            'created_at'    => Carbon::now()
                        ]
                    );
                } else {
                    // insert to logs table
                    $this->log->updateOrCreate(
                        ['table_id'     => $product['kodeprod']],
                        [
                            'log_time'     => Carbon::now(),
                            'activity'      => 'Already check product from erp with id : ' . $product['kodeprod'],
                            'table_name'    => 'products',
                            'column_name'   => 'products.id, products.brand_id, products.status_herbana, products.invoice_name, products.name, products.search_name, products.satuan_online, products.konversi_sedang_ke_kecil, products.slug, products.category_id, products.description, products.image, products.weight, products.status_promosi_coret',
                            'from_user'     => null,
                            'to_user'       => null,
                            'data_content'  => null,
                            'platform'      => 'web',
                            'created_at'    => Carbon::now()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get();

        $this->info('Insert products from erp successfully');
    }
}
