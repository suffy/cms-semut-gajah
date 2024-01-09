<?php

namespace App\Console\Commands;

use App\Log;
use App\MappingSite;
use App\NotificationVerification;
use App\User;
use App\UserAddress;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationIfVerified;
use Illuminate\Support\Facades\DB;

class DailyCustomer extends Command
{
    protected $users, $userAddress, $mappingSites, $log;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'customer:daily {number}';
    protected $signature = 'customer:daily';

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
        // $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/customer', [
        //     'X-API-KEY' => config('erp.x_api_key'),
        //     'token'     => config('erp.token_api'),
        //     // 'kode'      => $site->kode,
        //     // 'kode'      => 'clp12',
        // ])->json();

        // ambil data tanggal sekarang
        $folder = Carbon::now()->format('Y-m-d');
        // ambil nama directory
        $path   = public_path() . "/json/" . $folder . "/customer.json"; 

        // simpan kedalam variable json
        $json   = json_decode(file_get_contents($path), true);

        $this->store($json);
        // }
    }

    // insert to salesmen table
    public function store($sites)
    {
        try {
            $logs = [];
            // looping data dari json
            foreach ($sites as $site) {
                // ambil data dari user dari platform app
                $user_app      = DB::table('users')->where('phone', $site['phone'])->where('platform', 'app')->first();
                // ambil data dengan condisi customer_code
                $check         = DB::table('users')->where('customer_code' ,$site['kode_lang'])->first();
                // jika ada data dari platform app
                if($user_app) {
                    $this->info('app data');
                    // update data user app
                    $user = $this->users->updateOrCreate(
                        ['phone'            => $site['phone']],
                        ['name'             => $site['nama_lang'],
                        'account_type'      => '4',
                        'account_role'      => 'user',
                        'platform'          => 'erp',
                        'customer_code'     => $site['kode_lang'],
                        'site_code'         => $site['kode'],
                        'salur_code'        => $site['kodesalur'],
                        'code_approval'     => $site['code_approval'], // code _approval from erp
                        'class'             => $site['class'],
                        'kode_type'         => $site['kode_type'],
                        'type_payment'      => $site['status_payment'],
                        'status_blacklist'  => $site['status_blacklist']]
                    );
                    
                    // insert to notification verification table
                    // $this->notification->updateOrCreate(
                    //     ['user_id' => $user->id], 
                    //     ['user_id' => $user->id,
                    //     'checked_at' => Carbon::now()]
                    // );
    
                    // simpan kedalam logs
                    $this->log->updateOrCreate(['table_id'     => $user['id']],
                                ['log_time'     => Carbon::now(),
                                // 'activity'      => 'Insert new customer with id : ' . $user->id,
                                'activity'      => 'Update or check new customer with id : ' . $user->id,
                                'table_name'    => 'users, user_address',
                                'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                                'from_user'     => null,
                                'to_user'       => $user->id,
                                'data_content'  => $user,
                                'platform'      => 'web',
                                'created_at'    => Carbon::now()]);
                } else {
                    // jika data sudah ada dengan kondisi customer code
                    if($check) {
                        // jika user belum registrasi ke dalam apps
                        if($check->otp_verified_at == null) {
                            // if($check->updated_at != $site['erp_last_updated']) {
                                $this->info('updated_at != erpl_last_updated');
                                // update data user
                                $user = $this->users->updateOrCreate(
                                    ['customer_code' => $site['kode_lang']],
                                    ['name'             => $site['nama_lang'],
                                    'account_type'      => '4',
                                    'account_role'      => 'user',
                                    'platform'          => 'erp',
                                    'site_code'         => $site['kode'],
                                    'phone'             => $site['phone'],
                                    'salur_code'        => $site['kodesalur'],
                                    'code_approval'     => $site['code_approval'], // code _approval from erp
                                    'class'             => $site['class'],
                                    'kode_type'         => $site['kode_type'],
                                    'type_payment'      => $site['status_payment'],
                                    'status_blacklist'  => $site['status_blacklist'],
                                    'updated_at'        => $site['erp_last_updated']]
                                );
                
                                // if($user->code_approval == null) {
                                //     // random string
                                //     $characters = '0123456789';
                                //     $charactersLength = strlen($characters);
                                //     $code_approval = '';
                
                                //     for ($i = 0; $i < 6; $i++) {
                                //         $code_approval .= $characters[rand(0, $charactersLength - 1)];
                                //     }
                
                                //     $user->code_approval = $code_approval;
                                //     // $this->info($code_approval);
                                //     $user->save();
                                // }
        
                                // ambil data site_id
                                $mappingSite    = $this->mappingSites
                                                            ->where('kode', $site['kode'])
                                                            ->first();
        
                                // update data alamat user
                                $this->userAddress->updateOrCreate(
                                                        ['user_id'           => $user['id']],
                                                        ['mapping_site_id'   => $mappingSite->id,
                                                        'name'              => $site['nama_lang'],
                                                        'address'           => $site['alamat'],
                                                        'default_address'   => '1']
                                                        );
    
                                // simpan data logs
                                $this->log->updateOrCreate(['table_id'     => $user['id']],
                                            ['log_time'     => Carbon::now(),
                                            // 'activity'      => 'Insert new customer with id : ' . $user->id,
                                            'activity'      => 'Update or check new customer with id : ' . $user->id,
                                            'table_name'    => 'users, user_address',
                                            'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                                            'from_user'     => null,
                                            'to_user'       => $user->id,
                                            'data_content'  => $user,
                                            'platform'      => 'web',
                                            'created_at'    => Carbon::now()]);
                            // }
                            $this->info('otp verified null');
    
                        // jika user sudah registrasi 
                        } else {
                            $user = $check;
                            $this->info('otp verified not null');
                            // insert to logs table
                            // $log = $this->log->updateOrCreate(
                            //     ['table_id'     => $user->id],
                            //     ['log_time'     => Carbon::now(),
                            //     'activity'      => 'Insert new customer-' . $this->argument('number') . ' with id : ' . $user->id,
                            //     'table_name'    => 'users, user_address',
                            //     'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                            //     'from_user'     => null,
                            //     'to_user'       => $user->id,
                            //     'data_content'  => json_encode($user),
                            //     'platform'      => 'web',
                            //     'created_at'    => Carbon::now()]
                            //     );
    
    
                            // get site_id
                            // $mappingSite    = $this->mappingSites
                            //                         ->where('kode', $site['kode'])
                            //                         ->first();
    
                            // // insert into user_address table
                            // $this->userAddress->updateOrCreate(
                            //                     ['user_id'           => $user->id],
                            //                     ['mapping_site_id'   => $mappingSite->id,
                            //                     'name'              => $site['nama_lang'],
                            //                     'address'           => $site['alamat'],
                            //                     'default_address'   => '1']
                            //                     );
    
                            $this->log->updateOrCreate(['table_id'     => $user->id],
                                        ['log_time'     => Carbon::now(),
                                        // 'activity'      => 'Insert new customer with id : ' . $user->id,
                                        'activity'      => 'Update or check new customer with id : ' . $user->id,
                                        'table_name'    => 'users, user_address',
                                        'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                                        'from_user'     => null,
                                        'to_user'       => $user->id,
                                        'data_content'  => json_encode($user),
                                        'platform'      => 'web',
                                        'created_at'    => Carbon::now()]);
                        }
                    // jika data user belum ada di database
                    } else {
                        // $this->info('lewat sini');
                        // $user = $this->users->updateOrCreate(
                        //     ['customer_code' => $site['kode_lang']],
                        //     ['name'         => $site['nama_lang'],
                        //     'account_type'  => '4',
                        //     'account_role'  => 'user',
                        //     'platform'      => 'erp',
                        //     'site_code'     => $site['kode'],
                        //     'phone'         => $site['phone'],
                        //     'salur_code'    => $site['kodesalur'],
                        //     'class'         => $site['class'],
                        //     'type_payment'  => $site['status_payment']]
                        // );
    
                        $this->info('create data');
    
                        // simpan data ke dalam database
                        $user = $this->users->create(
                            ['customer_code' => $site['kode_lang'],
                            'name'              => $site['nama_lang'],
                            'account_type'      => '4',
                            'account_role'      => 'user',
                            'platform'          => 'erp',
                            'site_code'         => $site['kode'],
                            'phone'             => $site['phone'],
                            'salur_code'        => $site['kodesalur'],
                            'code_approval'     => $site['code_approval'], // code _approval from erp
                            'class'             => $site['class'],
                            'kode_type'         => $site['kode_type'],
                            'type_payment'      => $site['status_payment'],
                            'status_blacklist'  => $site['status_blacklist'],
                            'updated_at'        => $site['erp_last_updated']]
                        );
        
                        // if($user->code_approval == null) {
                        //     // random string
                        //     $characters = '0123456789';
                        //     $charactersLength = strlen($characters);
                        //     $code_approval = '';
        
                        //     for ($i = 0; $i < 6; $i++) {
                        //         $code_approval .= $characters[rand(0, $charactersLength - 1)];
                        //     }
        
                        //     $user->code_approval = $code_approval;
                        //     // $this->info($code_approval);
                        //     $user->save();
                        // }
    
                        // get site_id
                        $mappingSite    = $this->mappingSites
                                                    ->where('kode', $site['kode'])
                                                    ->first();
    
                        // insert into user_address table
                        $this->userAddress->updateOrCreate(
                                                        ['user_id'           => $user->id],
                                                        ['mapping_site_id'   => $mappingSite->id,
                                                        'name'              => $site['nama_lang'],
                                                        'address'           => $site['alamat'],
                                                        'default_address'   => '1']
                                                        );
    
                        // insert to logs table
                        // $log = $this->log->updateOrCreate(
                        //                                 ['table_id'     => $user['id']],
                        //                                 ['log_time'     => Carbon::now(),
                        //                                 'activity'      => 'Insert new customer-' . $this->argument('number') . ' with id : ' . $user->id,
                        //                                 'table_name'    => 'users, user_address',
                        //                                 'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                        //                                 'from_user'     => null,
                        //                                 'to_user'       => $user->id,
                        //                                 'data_content'  => $user,
                        //                                 'platform'      => 'web',
                        //                                 'created_at'    => Carbon::now()]
                        //                                 );
    
                        $this->log->updateOrCreate(
                                ['table_id'     => $user['id']],
                                ['log_time'     => Carbon::now(),
                                'activity'      => 'Insert new customer with id : ' . $user->id,
                                'table_name'    => 'users, user_address',
                                'column_name'   => 'users.email, users.name, users.account_type, users.account_role, users.platform, users.site_code, users.customer_code, users.salur_code, users.class, users.status_payment, user_address.user_id, user_address.mapping_site_id, user_address.name, user_address.address, user_addres.default_address',
                                'from_user'     => null,
                                'to_user'       => $user->id,
                                'data_content'  => $user,
                                'platform'      => 'web',
                                'created_at'    => Carbon::now()]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->log->updateOrCreate(
                    ['table_id'     => 1],
                    ['log_time'     => Carbon::now(),
                    'activity'      => 'Error while cron job customer ',
                    'table_name'    => 'logs',
                    'column_name'   => 'logs.*',
                    'from_user'     => null,
                    'to_user'       => 1,
                    'data_content'  => $e->getMessage(),
                    'platform'      => 'web',
                    'created_at'    => Carbon::now()]);
        }

        // $chunks = array_chunk($logs, 5000);
        // foreach($chunks as $chunk) {
        //     $this->log->insert($chunk);
        // }
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
