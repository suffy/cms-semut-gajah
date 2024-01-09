<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\BroadcastWA;
use App\Log;
use App\User;
use Carbon\Carbon;

class SendBroadcastWa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:broadcast {id}';

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
    protected $broadcastWa, $logs, $users;

    public function __construct(BroadcastWa $broadcastWa, Log $logs, User $users)
    {
        parent::__construct();
        $this->broadcastWa = $broadcastWa;
        $this->logs        = $logs;
        $this->users       = $users;
    }

    public function get()
    {
        $id = $this->argument('id');

        $broadcast = $this->broadcastWa
                                ->where('id', $id)
                                ->with(['broadcast_wa_detail.user' => function($query) {
                                    $query->select('id', 'phone', 'email', 'name', 'fcm_token');
                                }])
                                ->first();
        
        if($broadcast->type == 'wa') {
            foreach($broadcast->broadcast_wa_detail as $row) {
                // Send broadcast
                $userkey = config('zenziva.USER_KEY_ZENZIVA');
                $passkey = config('zenziva.API_KEY_ZENZIVA');
                $telepon = $row->user->phone;
                $message = $broadcast->message;
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
                    'userkey'   => $userkey,
                    'passkey'   => $passkey,
                    'to'        => $telepon,
                    'message'   => $message
                ));
                $results = json_decode(curl_exec($curlHandle), true);
                curl_close($curlHandle);
                
                $logs   = $this->logs
                ->create([
                    'log_time'      => Carbon::now(),
                    'activity'      => 'notif broadcast message whatsapp',
                    'table_id'      => $broadcast->id,
                    'data_content'  => $broadcast,
                    'table_name'    => 'broadcast_wa',
                    'column_name'   => 'title, schedule, classification, message, type',
                    'from_user'     => null,
                    'to_user'       => $row->send_to,
                    'platform'      => 'apps',
                ]);
            }
            $this->info('Whatsapp');
        } else if($broadcast->type == 'apps') {  
            if($broadcast->classification == "distributor") {
                foreach($broadcast->broadcast_wa_detail as $row) {
                    $site_code = $row->send_to;
                    $users = $this->users->where('site_code', $site_code)->whereNotNull('fcm_token')->where('account_type', '4')->where('account_role', 'user')->get();

                    foreach($users as $user) {
                        $fcm_token = $user->fcm_token;
            
                        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

                        $message = str_replace("{name}", $user->name, $broadcast->message);
            
                        $data = [
                        "registration_ids" => [$fcm_token],
                        "notification"  => [
                            "title" => 'Pemberitahuan',
                            "body"  => $message,  
                            "sound" => 'default'
                        ]
                        ];
            
                        $dataString = json_encode($data);
            
                        $headers = [
                            'Authorization: key=' . $SERVER_API_KEY,
                            'Content-Type: application/json',
                        ];
            
                        $ch = curl_init();
            
                        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            
                        $response = curl_exec($ch);
    
                        // $logs = $this->logs;
                        // $logs->log_time     = Carbon::now();
                        // $logs->table_id     = $broadcast->id;
                        // $logs->activity     = "notif broadcast message apps";
                        // $logs->data_content = $broadcast;
                        // $logs->table_name   = 'broadcast_wa';
                        // $logs->column_name  = 'title, schedule, classification, message, type';
                        // $logs->to_user      = $row->send_to;
                        // $logs->platform     = "web";
                        // $logs->save();
    
                        $logs   = $this->logs
                        ->create([
                            'log_time'      => Carbon::now(),
                            'activity'      => 'notif broadcast message apps',
                            'table_id'      => $broadcast->id,
                            'data_content'  => $broadcast,
                            'table_name'    => 'broadcast_wa',
                            'column_name'   => 'title, schedule, classification, message, type',
                            'from_user'     => null,
                            'to_user'       => $user->id,
                            'platform'      => 'apps',
                        ]);
                    }
                }
            } else if($broadcast->classification == "user") {
                foreach($broadcast->broadcast_wa_detail as $row) {
                    $fcm_token = $row->user->fcm_token;
        
                    $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

                    $message = str_replace("{name}", $row->user->name, $broadcast->message);
        
                    $data = [
                    "registration_ids" => [$fcm_token],
                    "notification"  => [
                        "title" => 'Pemberitahuan',
                        "body"  => $message,  
                        "sound" => 'default'
                    ]
                    ];
        
                    $dataString = json_encode($data);
        
                    $headers = [
                        'Authorization: key=' . $SERVER_API_KEY,
                        'Content-Type: application/json',
                    ];
        
                    $ch = curl_init();
        
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        
                    $response = curl_exec($ch);

                    // $logs = $this->logs;
                    // $logs->log_time     = Carbon::now();
                    // $logs->table_id     = $broadcast->id;
                    // $logs->activity     = "notif broadcast message apps";
                    // $logs->data_content = $broadcast;
                    // $logs->table_name   = 'broadcast_wa';
                    // $logs->column_name  = 'title, schedule, classification, message, type';
                    // $logs->to_user      = $row->send_to;
                    // $logs->platform     = "web";
                    // $logs->save();

                    $logs   = $this->logs
                                    ->create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => 'notif broadcast message apps',
                                        'table_id'      => $broadcast->id,
                                        'data_content'  => $broadcast,
                                        'table_name'    => 'broadcast_wa',
                                        'column_name'   => 'title, schedule, classification, message, type',
                                        'from_user'     => null,
                                        'to_user'       => $row->send_to,
                                        'platform'      => 'apps',
                                    ]);
                }
            }
            $this->info('Apps');
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

        $this->info('Send broadcast message successfully');
    }
}
