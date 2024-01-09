<?php

namespace App\Console\Commands;

use App\CreditLimit;
use App\Log;
use App\MappingSite;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MonthlyCreditLimit extends Command
{
    protected $mappingSite, $creditLimit, $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creditlimit:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate update credit limit from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MappingSite $mappingSite, CreditLimit $creditLimit, Log $log)
    {
        parent::__construct();
        $this->mappingSite  = $mappingSite;
        $this->creditLimit  = $creditLimit;
        $this->log          = $log;
    }

    // get credit limit from erp
    public function get()
    {
        // looping request by site_id
        $sites = $this->mappingSite->get();
        // foreach ($sites as $site) {
            $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/credit_limit', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token'     => config('erp.token_api'),
                // 'kode'      => $site->kode,
                'kode'      => 'kbmk4',
            ])->json();

            // $this->info($response['data']);
            
            if (array_key_exists('data', $response)) {
                $this->store($response['data']);
            }
        // }
    }

    // insert to product_stocks table
    public function store($creditLimits)
    {
        foreach ($creditLimits as $creditLimit) {
            foreach ($creditLimit['credit_limit_principal'] as $data) {
                // insert or update credit limit
                $creditLimit    = $this->creditLimit->updateOrCreate([
                                    'customer_code'    => $data['kode_lang'],
                                    'brand_id'         => $data['brand_id'],
                                    'credit_limit'      => $data['credit_limit']
                                ]);
    
                // insert to logs table
                $this->log->updateOrCreate(
                    ['table_id'     => $creditLimit->id],
                    ['log_time'     => Carbon::now(),
                    'activity'      => 'Insert/update credit limit from erp with id : ' . $creditLimit->id,
                    'table_name'    => 'credit_limits',
                    'column_name'   => 'credit_limits.id, credit_limits.customer_code, credit_limits.brand_id, credit_limits.credit_limit',
                    'from_user'     => null,
                    'to_user'       => null,
                    'data_content'  => $creditLimit,
                    'platform'      => 'web',
                    'created_at'    => Carbon::now()]
                );
                
                $this->info($creditLimit);
            }
        }

        $this->info('Successfully get credit limit');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return $this->get();
    }
}
