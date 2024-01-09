<?php

namespace App\Console\Commands;

use App\Log;
use App\MappingSite;
use App\User;
use App\UserAddress;
use Carbon\Carbon;
use App\Enums\CronJobEnums;
use App\Mail\NotificationIfVerified;
use App\NotificationVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class CustomCustomer extends Command
{
    protected $users, $userAddress, $mappingSites, $log, $notification;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:custom {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate insert salesman from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $user, UserAddress $userAddress, MappingSite $mappingSite, Log $log, NotificationVerification $notification)
    {
        parent::__construct();
        $this->users        = $user;
        $this->userAddress  = $userAddress;
        $this->mappingSites = $mappingSite;
        $this->notification = $notification;
        $this->log          = $log;

        set_time_limit(8000000);
    }

    // get data from erp
    public function get()
    {
        // looping request by site_id
        // $sites = $this->mappingSites->get();
        $this->info('Insert customer from code: ' . $this->argument('code'));
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/customer', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
            'kode'      => $this->argument('code'),
        ])->json();

        $this->store($response['data']);
    }

    // insert to salesmen table
    public function store($sites)
    {
        foreach ($sites as $site) {
            // insert into users table
            $user = $this->users->updateOrCreate(
                        ['phone'        => $site['phone']],
                        ['name'         => $site['nama_lang'],
                        'account_type'  => '4',
                        'account_role'  => 'user',
                        'platform'      => 'erp',
                        'site_code'     => $site['kode'],
                        'customer_code' => $site['kode_lang'],
                        'salur_code'    => $site['kodesalur'],
                        'class'         => $site['class'],
                        'type_payment'  => $site['status_payment']]
                    );

            // get site_id
            $mappingSite    = $this->mappingSites
                            ->where('kode', $site['kode'])
                            ->first();

            // // insert into user_address table
            $this->userAddress->updateOrCreate(
                ['user_id'           => $user['id']],
                ['mapping_site_id'   => $mappingSite['id'],
                'name'              => $site['nama_lang'],
                'address'           => $site['alamat'],
                'default_address'   => '1']
            );

            $this->notification->updateOrCreate(
                ['user_id' => $user['id']], 
                ['user_id' => $user['id'],
                'checked_at' => Carbon::now()]
            );

            // insert to logs table
            $log = $this->log->updateOrCreate(
                ['table_id'     => $user['id']],
                ['log_time'     => Carbon::now(),
                'activity'      => 'Insert new customer with id : ' . $user['id'],
                'table_name'    => 'users, user_address',
                'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                'from_user'     => null,
                'to_user'       => $user['id'],
                'data_content'  => $user,
                'platform'      => 'web',
                'created_at'    => Carbon::now()]
            );
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

        $this->info('Insert customer from erp successfully');
    }
}
