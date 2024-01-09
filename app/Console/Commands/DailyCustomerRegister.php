<?php

namespace App\Console\Commands;

use App\MappingSite;
use App\User;
use App\UserAddress;
use App\UserAddresss;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class DailyCustomerRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'register:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $users, UserAddress $userAddress, MappingSite $mappingSite)
    {
        parent::__construct();
        $this->users = $users;
        $this->userAddress = $userAddress;
        $this->mappingSites = $mappingSite;
    }

    // get data from erp
    public function get()
    {
        // looping request by site_id
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/registrasi', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
            'signature' => 'fa5f0d365737bf2731ba3bbdbaf62377.54291'
        ])->json();
        $this->store($response['data']);
    }

    // insert to salesmen table
    public function store($response)
    {
        // $this->info(json_encode($response));
        foreach ($response as $register) {
            $users = $this->users->whereId($register['id_user'])->first();
            $mappingSite = $this->mappingSites->where('kode', $register['site_code'])->first();

            if ($users) {
                if ($users->updated_at != $register['updated_at']) {
                    $this->users->updateOrCreate(
                        ['id'                       => $register['id_user']], //user id
                        [
                            // 'name'              => $register['name'],
                            // 'email'             => $register['email'],
                            // 'phone'             => $register['phone'],
                            // 'password'          => Hash::make($register['password']),
                            // 'account_type'      => $register['account_type'],
                            // 'account_role'      => $register['account_role'],
                            // 'photo'             => null,
                            // 'fcm_token'         => $register['fcm_token'],
                            // 'otp_verified_at'   => $register['otp_verified_at'],
                            'customer_code'     => $register['customer_code'],
                            'kode_type'         => $register['kode_type'],
                            'class'             => $register['class'],
                            'type_payment'      => $register['type_payment'],
                            'site_code'         => $register['site_code'],
                            'code_approval'     => $register['code_approval'],
                            // 'photo_ktp'         => $register['photo_ktp'],
                            // 'photo_npwp'        => $register['photo_npwp'],
                            // 'photo_toko'        => $register['photo_toko'],
                            // 'selfie_ktp'        => $register['selfie_ktp'],
                            // 'shareloc'          => $register['shareloc'],
                            'platform'           => 'app',
                            'updated_at'         => $register['updated_at']
                        ]
                    );

                    $this->userAddress->updateOrCreate(
                        ['user_id'              => $register['id_user']], //user_id
                        [
                            'name'              => $register['name'],
                            'shop_name'         => $register['shop_name'],
                            'address'           => $register['address'],
                            'kelurahan'         => $register['kelurahan'],
                            'kecamatan'         => $register['kecamatan'],
                            'kota'              => $register['kota'],
                            'provinsi'          => $register['provinsi'],
                            'kode_pos'          => $register['kode_pos'],
                            'latitude'          => $register['latitude'],
                            'longitude'         => $register['longitude'],
                        ]
                    );
                }
            } else {
                $this->users->create([
                    'id'                => $register['id_user'],
                    'name'              => $register['name'],
                    'email'             => $register['email'],
                    'phone'             => $register['phone'],
                    'password'          => Hash::make($register['password']),
                    'account_type'      => 4,
                    'account_role'      => 'user',
                    'photo'             => null,
                    'platform'          => 'app',
                    'fcm_token'         => $register['fcm_token'],
                    'otp_verified_at'   => $register['otp_verified_at'],
                    'customer_code'     => $register['customer_code'],
                    'kode_type'         => $register['kode_type'],
                    'class'             => $register['class'],
                    'type_payment'      => $register['type_payment'],
                    'site_code'         => $register['site_code'],
                    'photo_ktp'         => $register['photo_ktp'],
                    'photo_npwp'        => $register['photo_npwp'],
                    'photo_toko'        => $register['photo_toko'],
                    'selfie_ktp'        => $register['selfie_ktp'],
                    'shareloc'          => $register['shareloc'],
                    'created_at'        => $register['created_at'],
                    'updated_at'        => $register['updated_at'],
                    'code_approval'     => $register['code_approval'],
                ]);

                $this->userAddress->updateOrCreate(
                    ['user_id'              => $register['id_user']],
                    [
                        'name'              => $register['name'],
                        'shop_name'         => $register['shop_name'],
                        'address'           => $register['address'],
                        'kelurahan'         => $register['kelurahan'],
                        'kecamatan'         => $register['kecamatan'],
                        'kota'              => $register['kota'],
                        'provinsi'          => $register['provinsi'],
                        'kode_pos'          => $register['kode_pos'],
                        'latitude'          => $register['latitude'],
                        'longitude'         => $register['longitude'],
                    ]
                );
            }

            if ($mappingSite) {
                $this->userAddress->where('user_id', $register['id_user'])->update([
                    'mapping_site_id' => $mappingSite->id,
                    'default_address'   => '1'
                ]);
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

        $this->info('Customer register from erp successfully');
    }
}
