<?php

namespace App\Console\Commands;

use App\MappingSite;
use App\User;
use App\MetaUser;
use App\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DailyCustomerBinaan extends Command
{
    protected $users, $mappingSites, $metaUser, $logs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customerbinaan:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add salesman code to customer from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $user, MappingSite $mappingSite, MetaUser $metaUser, Log $logs)
    {
        parent::__construct();
        $this->users        = $user;
        $this->mappingSites = $mappingSite;
        $this->metaUser     = $metaUser;
        $this->logs         = $logs;
    }

    // get data from erp
    public function get()
    {
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/site', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
        ])->json();

        $sites = $response['data'];

        foreach ($sites as $site) {
            $this->info($site['site_code']);
            $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/customer_binaan', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token'     => config('erp.token_api'),
                'kode'      => $site['site_code'],
            ])->json();

            if (array_key_exists('data', $response)) {
                $this->store($response['data']);
            }
        }
    }

    // update customers at users table
    public function store($sites)
    {
        foreach ($sites as $site) {
            // insert customerbinaan at meta_user table
            $metaUser = $this->metaUser->updateOrCreate(
                ['customer_code'     => strtoupper($site['kode_lang'])],
                [
                    'site_code' => strtoupper($site['kode']),
                    'salesman_code' => $site['kodesales'],
                    'salesman_erp_code' => $site['kodesales_erp']
                ]
            );

            // insert to logs table
            $log = $this->logs->updateOrCreate(
                ['table_id'     => $metaUser->id],
                [
                    'log_time'     => Carbon::now(),
                    'activity'      => 'Insert new customer binaan with id : ' . $metaUser->id,
                    'table_name'    => 'user_meta',
                    'column_name'   => 'user_meta.site_code, user_meta.customer_code, user_meta.salesman_code, user_meta.salesman_erp_code, user_meta.created_at, user_meta.deleted_at',
                    'from_user'     => null,
                    'to_user'       => null,
                    'data_content'  => $metaUser,
                    'platform'      => 'web',
                    'created_at'    => Carbon::now()
                ]
            );

            $this->info($metaUser);
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

        $this->info('Update salesman_code all customer from erp successfully');
    }
}
