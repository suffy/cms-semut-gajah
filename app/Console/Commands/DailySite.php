<?php

namespace App\Console\Commands;

use App\Log;
use App\MappingSite;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DailySite extends Command
{
    protected $mappingSites, $log;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate insert site from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MappingSite $mappingSite, Log $log, User $user)
    {
        parent::__construct();
        $this->mappingSites = $mappingSite;
        $this->log          = $log;
        $this->user         = $user;
    }

    public function get()
    {
        // ambil data dari erp
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/site', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
        ])->json();

        $this->store($response['data']);
    }

    // insert to mapping_site table
    public function store($sites)
    {
        foreach ($sites as $site) {
            // ambil data mapping_site 
            $data = DB::table('mapping_site')->where('kode', $site['site_code'])->select('updated_at')->first();

            // jika tidak ada data
            if (is_null($data)) {
                // simpan data mapping_site
                $mappingSite = $this->mappingSites->create(
                    [
                        'kode'         => $site['site_code'],
                        'branch_name'   => $site['branch_name'],
                        'nama_comp'     => $site['nama_comp'],
                        // 'kode_comp'     => $site['kode_comp'],
                        'sub'           => $site['sub'],
                        'provinsi'      => $site['provinsi'],
                        'telp_wa'       => $site['telp_wa'],
                        'status_ho'     => $site['status_ho'],
                        'updated_at'    => $site['last_updated'],
                        'min_transaction' => $site['min_transaksi']
                    ]
                );

                // check data dari db untuk akun distributor
                $checkUser = $this->user->where('account_type', '1')->where('account_role', 'distributor')->where('site_code', $site['site_code'])->first();

                // check jika kosong maka create
                if (is_null($checkUser)) {
                    $user = $this->user->create(
                        [
                            'site_code'    => $site['site_code'],
                            'name'          => $site['site_code'],
                            'email'         => strtolower($site['site_code']) . '@semutgajah.com',
                            'password'      => Hash::make('password'),
                            'account_type'  => '1',
                            'account_role'  => 'distributor',
                            'created_at'    => Carbon::now(),
                            'updated_at'    => Carbon::now()
                        ]
                    );

                    // simpan dalam logs
                    $logs = $this->log;

                    $logs->log_time     = Carbon::now();
                    $logs->activity     = "Distributor " . $site['site_code'] . " has been registered with id " . $user->id;
                    $logs->table_name   = 'users';
                    $logs->table_id     = $user->id;
                    $logs->from_user    = $user->id;
                    $logs->to_user      = null;
                    $logs->platform     = "web";

                    $logs->save();
                }

                // insert to logs table
                $this->log->create(
                    [
                        'table_id'     => $mappingSite->id,
                        'log_time'     => Carbon::now(),
                        'activity'      => 'Insert/update site code from erp with id : ' . $mappingSite->id,
                        'table_name'    => 'mapping_site',
                        'column_name'   => 'mapping_site.id, mapping_site.kode, mapping_site.branch_name, mapping_site.nama_comp, mapping_site.sub',
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => $mappingSite,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()
                    ]
                );
            } else {
                // check last updated sama dengan data di db
                if ($data->updated_at != $site['last_updated']) {
                    $mappingSite = $this->mappingSites->updateOrCreate(
                        ['kode'         => $site['site_code']],
                        [
                            'branch_name'  => $site['branch_name'],
                            'nama_comp'     => $site['nama_comp'],
                            // 'kode_comp'     => $site['kode_comp'],
                            'telp_wa'       => $site['telp_wa'],
                            'status_ho'     => $site['status_ho'],
                            'sub'           => $site['sub'],
                            'provinsi'      => $site['provinsi'],
                            'status_ho'     => $site['status_ho'],
                            'telp_wa'       => $site['telp_wa'],
                            'updated_at'    => $site['last_updated'],
                            'min_transaction' => $site['min_transaksi']
                        ]
                    );

                    $checkUser = $this->user
                        ->where('account_type', '1')
                        ->where('account_role', 'distributor')
                        ->where('site_code', $site['site_code'])
                        ->first();
                    // check jika kosong data user di db
                    if (is_null($checkUser)) {
                        $user = $this->user->create(
                            [
                                'site_code'    => $site['site_code'],
                                'name'          => $site['site_code'],
                                'email'         => $site['site_code'] . '@semutgajah.com',
                                'password'      => Hash::make('password'),
                                'account_type'  => '1',
                                'account_role'  => 'distributor',
                                'created_at'    => Carbon::now(),
                                'updated_at'    => Carbon::now()
                            ]
                        );
                    }

                    // insert to logs table
                    $this->log->updateOrCreate(
                        ['table_id'     => $mappingSite->id],
                        [
                            'log_time'     => Carbon::now(),
                            'activity'      => 'Insert/update site code from erp with id : ' . $mappingSite->id,
                            'table_name'    => 'mapping_site',
                            'column_name'   => 'mapping_site.id, mapping_site.kode, mapping_site.branch_name, mapping_site.nama_comp, mapping_site.sub',
                            'from_user'     => null,
                            'to_user'       => null,
                            'data_content'  => $mappingSite,
                            'platform'      => 'web',
                            'created_at'    => Carbon::now()
                        ]
                    );
                } else {
                    $checkUser = $this->user->where('account_type', '1')->where('account_role', 'distributor')->where('site_code', $site['site_code'])->first();
                    if (is_null($checkUser)) {
                        $user = $this->user->create(
                            [
                                'site_code'    => $site['site_code'],
                                'name'          => $site['site_code'],
                                'email'         => $site['site_code'] . '@semutgajah.com',
                                'password'      => Hash::make('password'),
                                'account_type'  => '1',
                                'account_role'  => 'distributor',
                                'created_at'    => Carbon::now(),
                                'updated_at'    => Carbon::now()
                            ]
                        );
                    }

                    // insert to logs table
                    $this->log->updateOrCreate(
                        ['table_id'     => 'all'],
                        [
                            'log_time'     => Carbon::now(),
                            'activity'      => 'Already check site code from erp with code ' . $site['site_code'],
                            'table_name'    => 'mapping_site',
                            'column_name'   => 'mapping_site.id, mapping_site.kode, mapping_site.branch_name, mapping_site.nama_comp, mapping_site.sub',
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

    public function ho()
    {
        $ho_sites = $this->mappingSites->where('status_ho', '1')->get();
        foreach ($ho_sites as $ho) {
            $checkUser = $this->user->where('account_type', '1')->where('account_role', 'distributor_ho')->where('site_code', $ho->kode)->first();
            if (is_null($checkUser)) {
                $user = $this->user->create(
                    [
                        'site_code'    => $ho->kode,
                        'name'          => 'HO' . $ho->sub,
                        'email'         => 'HO' . $ho->sub . '@semutgajah.com',
                        'password'      => Hash::make('password'),
                        'account_type'  => '1',
                        'account_role'  => 'distributor_ho',
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now()
                    ]
                );
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
        $this->ho();

        $this->info('Insert site from erp successfully');
    }
}
