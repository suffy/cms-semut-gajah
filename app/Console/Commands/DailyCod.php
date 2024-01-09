<?php

namespace App\Console\Commands;

use App\Log;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyCod extends Command
{
    protected $order, $log, $user;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cod:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running automate get notification cod';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order, Log $log, User $user)
    {
        parent::__construct();
        $this->order    = $order;
        $this->log      = $log;
        $this->user     = $user;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    
    public function handle()
    {
        // get order data with cod payment method
        $orders = $this->order->where('status', '3')->get();

        foreach ($orders as $order) {
            // for data_content log
            $dataContent = $this->order->where('id', $order->id)->with('data_item.product')->first();

            $fcm_token  = $this->user->where('id', $order->customer_id)->pluck('fcm_token')->all();

            // h-2 notification barang di terima
            if ($order->updated_at->format('d-m-Y') == Carbon::now()->subDays(2)->format('d-m-Y')) {
                $activity   =   "H-2 jadwal COD";
                // insert to log
                $log = $this->log->create([
                    'log_time'      => Carbon::now(),
                    'activity'      => 'Notification COD h-2',
                    'data_content'  => $dataContent,
                    'table_name'    => 'orders',
                    'table_id'      => $order->id,
                    'column_name'   => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders',
                    'from_user'     => null,
                    'to_user'       => $order->customer_id,
                    'platform'      => 'web'
                ]);

                $this->sendNotification($fcm_token, $activity);
            }

            // h-1 notification barang di terima
            if ($order->updated_at->format('d-m-Y') == Carbon::now()->subDays(1)->format('d-m-Y')) {
                $activity   =   "H-1 jadwal COD";
                // insert to log
                $log = $this->log->create([
                    'log_time'      => Carbon::now(),
                    'activity'      => 'Notification COD h-1',
                    'data_content'  => $dataContent,
                    'table_name'    => 'orders',
                    'table_id'      => $order->id,
                    'column_name'   => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders',
                    'from_user'     => null,
                    'to_user'       => $order->customer_id,
                    'platform'      => 'web'
                ]);

                $this->sendNotification($fcm_token, $activity);
            }

            // hari h notification barang di terima
            if ($order->updated_at->format('d-m-Y') == Carbon::now()->subDays(0)->format('d-m-Y')) {
                $activity   =   "Hari ini jadwal COD";
                // insert to log
                $log = $this->log->create([
                    'log_time'      => Carbon::now(),
                    'activity'      => 'Notification COD h',
                    'data_content'  => $dataContent,
                    'table_name'    => 'orders',
                    'table_id'      => $order->id,
                    'column_name'   => 'orders.customer_id, orders.name, orders.phone, orders.provinsi, orders.kota, orders.kecamatan, orders.kelurahan, orders.address, orders.payment_method, orders.coupon_id, orders.status, orders',
                    'from_user'     => null,
                    'to_user'       => $order->customer_id,
                    'platform'      => 'web'
                ]);

                $this->sendNotification($fcm_token, $activity);
            }
        }
        
        $this->info('Successfully create notification cod');
    }

    public function sendNotification($fcm_token, $activity)
    {

        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => 'Status Orderan',
                "body" => $activity,  
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
    }
}
