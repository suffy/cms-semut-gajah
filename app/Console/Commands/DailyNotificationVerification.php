<?php

namespace App\Console\Commands;

use App\Mail\NotificationIfVerified;
use Illuminate\Console\Command;
use App\NotificationVerification;
use App\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class DailyNotificationVerification extends Command
{
    protected $notifVerif, $log;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifVerif:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate send wa or email if user verificated';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationVerification $notifVerif, Log $log)
    {
        parent::__construct();
        $this->log          = $log;
        $this->notifVerif   = $notifVerif;
    }

    public function get()
    {
        $notif = $this->notifVerif
                ->where('status', null)
                ->with('user')
                ->get();
                
        $mail = new NotificationIfVerified();

        $this->store($notif, $mail);
    }

    public function store($notif, $mail)
    {
        if($notif->isEmpty()) {
            $this->info('Tidak ada user yang terferivikasi!');
        } else {
            $activity = "";
            foreach($notif as $row) {

                if(!is_null($row->user->phone)) {
                    try {   
                        // send notification if verification
                        $userkey = config('zenziva.USER_KEY_ZENZIVA');
                        $passkey = config('zenziva.API_KEY_ZENZIVA');
                        $telepon = $row->user->phone;
                        $message = 'Akun anda sudah terverifikasi oleh admin, silahkan login kedalam sistem Semut Gajah.';
                        $url = 'https://console.zenziva.net/wareguler/api/sendWA/';
                        $curlHandle = curl_init();
                        curl_setopt($curlHandle, CURLOPT_URL, $url);
                        curl_setopt($curlHandle, CURLOPT_HEADER, 0);
                        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
                        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 0);
                        curl_setopt($curlHandle, CURLOPT_TIMEOUT,500);
                        curl_setopt($curlHandle, CURLOPT_POST, 1);
                        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, array(
                            'userkey' => $userkey,
                            'passkey' => $passkey,
                            'to' => $telepon,
                            'message' => $message
                        ));
                        json_decode(curl_exec($curlHandle), true);
                        curl_close($curlHandle); 

                        $this->notifVerif->where('user_id', $row->user_id)->update([
                            'status' => 1,
                            'updated_at' => Carbon::now()
                        ]);

                        $activity = "by phone";
                    } catch(\Exception $e) {
                        $this->info($e->getMessage());
                    }
                } else {
                    try { 
                        Mail::to($row->user->email)->queue($mail);

                        $this->notifVerif->where('user_id', $row->user_id)->update([
                            'status' => 1,
                            'updated_at' => Carbon::now()
                        ]);
                    } catch(\Exception $e) {
                        $this->info($e->getMessage());
                    }

                    $activity = "by email";
                }

                $log = $this->log->updateOrCreate(
                    ['table_id'     => $row->id],
                    ['log_time'     => Carbon::now(),
                    'activity'      => 'Already give notification ' . $activity . ' with id : ' . $row->id,
                    'table_name'    => 'notification_verifications, users',
                    'column_name'   => 'users.id, users.phone, notifications_verificaitons.status, notification_verifications.updated_at, notification_verifications.checked_at',
                    'from_user'     => null,
                    'to_user'       => $row->id,
                    'data_content'  => null,
                    'platform'      => 'web',
                    'created_at'    => Carbon::now()]
                );

                // $this->info($notif);
            }
            $this->info('Notifikasi Berhasil Dikirim!');
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
        $this->info('Cron Job Notifikasi Berhasil Dijalankan!'); 
    }
}
