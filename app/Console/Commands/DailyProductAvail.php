<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ProductAvailability;
use App\Log;
use App\MappingSite;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DailyProductAvail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'productSite:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate insert product availability from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $productAvailability, $mappingSites;

    public function __construct(ProductAvailability $productAvailability, Log $log, MappingSite $mappingSite)
    {
        parent::__construct();
        $this->productAvailability  = $productAvailability;
        $this->log                  = $log;
        $this->mappingSites         = $mappingSite;
    }

    // get products from erp
    public function get()
    {
        // ambil data product_site dari erp
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/product_site', [
            'X-API-KEY' => 123,
            'token'     => config('erp.token_api')
        ])->json();

        $this->store($response['data']);
    }

    // insert into products & product_prices table
    public function store($products)
    {
        foreach($products as $row) {
            // ambil data product dengan beberapa kondisi
            $product = $this->productAvailability->where('site_code', $row['site_code'])->where('product_id', $row['kodeprod'])->first();

            // jika data sudah ada 
            if(isset($product)) {
                // jika ada update data dari erp
                if($product->updated_at != $row['last_updated']) {
                    $this->info('update' . $row['site_code'] . ' - ' . $row['kodeprod']);
                    // update data
                    $product->update([
                        'site_code'     => $row['site_code'],
                        'product_id'    => $row['kodeprod'],
                        'status'        => $row['status_aktif'],
                        'updated_at'    => $row['last_updated']
                    ]); 

                    // simpan data logs
                    $this->log->updateOrCreate(
                        ['table_id'     => $product->id],
                        ['log_time'     => Carbon::now(),
                        'activity'      => 'update product_availability with id : ' . $product->id,
                        'table_name'    => 'product_availability',
                        'column_name'   => 'site_code, product_id, status, updated_at',
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => $product,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()]);
                } else {
                    // hanya simpan di logs
                    $this->log->updateOrCreate(
                        ['table_id'     => $product->id],
                        ['log_time'     => Carbon::now(),
                        'activity'      => 'check product_availability with id : ' . $product->id,
                        'table_name'    => 'product_availability',
                        'column_name'   => 'site_code, product_id, status, updated_at',
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => $product,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()]);
                }
            // jika belum ada data 
            } else {
                $site = $this->mappingSites->where('kode', $row['site_code'])->first();
                $this->info('create ' . $row['site_code'] . ' - ' . $row['kodeprod']);

                if(isset($site)) {
                    // simpan data
                    $product = $this->productAvailability->create([
                        'site_code'     => $row['site_code'],
                        'product_id'    => $row['kodeprod'],
                        'status'        => $row['status_aktif'],
                        'updated_at'    => $row['last_updated']
                    ]); 

                    $this->log->updateOrCreate(
                        ['table_id'     => $product->id],
                        ['log_time'     => Carbon::now(),
                        'activity'      => 'insert new product_availability with id : ' . $product->id,
                        'table_name'    => 'product_availability',
                        'column_name'   => 'site_code, product_id, status, updated_at',
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => $product,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()]);
                } else {
                    $this->info('tidak ada mapping site ' . $row['site_code']);
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

        $this->info('Insert products_site from erp successfully');
        // return 0;
    }
}
