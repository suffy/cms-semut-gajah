<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class ReminderToUpdate extends Command
{
    protected $users;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:update {version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Wa to remind update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $users)
    {
        parent::__construct();
        $this->users = $users;
    }

    // get data from erp
    public function get()
    {
        // looping request by site_id
        // $this->info('Insert customer from code: ' . $this->argument('code'));
        $response = $this->users->where('app_version', $this->argument('version'))->get();
        // $this->info($response);
        $this->send($response);
    }

    public function send($response)
    {
        foreach($response as $row) {
            // send otp code
            $userkey = config('zenziva.USER_KEY_ZENZIVA');
            $passkey = config('zenziva.API_KEY_ZENZIVA');
            $telepon = $row->phone;
            $message = 
                    "Semut Gajah Official \r\n" . 
                    "Pemberitahuan \r\n \r\n" .
                    
                    "Halo " .  $row->customer_code . ", \r\n".
                    "Versi aplikasi yang anda gunakan sudah usang, silahkan update ke aplikasi dengan mengunjungi agar anda dapat menikmati performa Semut Gajah secara maksimal. \r\n \r\n" .  
                    "Copy link dibawah untuk update \r\n" . "http://play.google.com/store/apps/details?id=com.semutgajah";
            $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT,30);
            curl_setopt($curlHandle, CURLOPT_POST, 1);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                'userkey'   => $userkey,
                'passkey'   => $passkey,
                'to'        => $telepon,
                'message'   => $message
            ));
            $results = json_decode(curl_exec($curlHandle), true);
            curl_close($curlHandle);
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
    }
}
