<?php

namespace App\Console\Commands;

use App\Log;
use App\MappingSite;
use App\ProductStock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DailyStock extends Command
{
    protected $productStocks, $mappingSites, $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate update stock from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductStock $productStock, MappingSite $mappingSite, Log $log)
    {
        parent::__construct();
        $this->productStocks    = $productStock;
        $this->mappingSites     = $mappingSite;
        $this->log              = $log;
    }

    // get stock from erp
    public function get()
    {
        // looping request by site_id
        $sites = $this->mappingSites->get();
        foreach ($sites as $site) {
            $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/stock', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token'     => config('erp.token_api'),
                'kode'      => $site->kode,
            ])->json();

            // $this->info($response['data']);
            
            if (array_key_exists('data', $response)) {
                $this->store($response['data']);
            }
        }
    }

    // insert to product_stocks table
    public function store($stocks)
    {
        foreach ($stocks as $stock) {
            $stock = $this->productStocks->updateOrCreate(
                        ['product_id'    => $stock['kodeprod']],
                        ['site_code'     => $stock['kode'],
                        'month'         => $stock['bulan'],
                        'stock'         => $stock['stock_akhir'],
                        'last_updated'  => $stock['last_updated']]
                    );

            // insert to logs table
            $this->log->updateOrCreate(
                ['table_id'     => $stock->id],
                ['log_time'     => Carbon::now(),
                'activity'      => 'Insert/update stock from erp with id : ' . $stock->id,
                'table_name'    => 'product_stocks',
                'column_name'   => 'product_stock.id, product_stock.product_id, product_stock.site_code, product_stock.month, product_stock.stock, product_stock.last_updated',
                'from_user'     => null,
                'to_user'       => null,
                'data_content'  => $stock,
                'platform'      => 'web',
                'created_at'    => Carbon::now()]
            );

            $this->info($stock);
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

        $this->info('Insert stock from erp successfully');
    }
}
