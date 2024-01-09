<?php

namespace App\Console\Commands;

use App\Log;
use App\Order;
use App\User;
use App\PointHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HourlyOrderStatus extends Command
{

    protected $order, $log, $user, $pointHistory;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orderstatus:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate update status order from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order, Log $log, User $user, PointHistory $pointHistory)
    {
        parent::__construct();
        $this->order        = $order;
        $this->log          = $log;
        $this->user         = $user;
        $this->pointHistory = $pointHistory; 
    }

    // get data from erp
    public function get()
    {
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/orders', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
            // 'server'    => 'staging'
        ]);

        $this->update($response['data']);
    }

    // update status order
    public function update($orders)
    {
        foreach ($orders as $order) {
            if ($order['status_update_erp'] != null) {

                $data  = $this->order
                            ->where('invoice', $order['invoice'])
                            ->with(['data_item', 'data_user'])
                            ->first();                                                      // get data order by invoice from erp
                
                if (!is_null($data)) {                                                      // if not null

                    $activity   =   "";                                                     // give activity desc

                    if($order['status_update_erp']          == '1') {
                        $activity   =   "new order";
                    } else if($order['status_update_erp']   == '2') {
                        $activity   =   "order confirm";
                    } else if($order['status_update_erp']   == '3') {
                        $activity   =   "order process";
                    } else if($order['status_update_erp']   == '4') {
                        $activity   =   "order completed";
                    } else if($order['status_update_erp']   == '10') {
                        $activity   =   "order cancel";
                    }

                    if($data->updated_at != $order['last_updated_erp']) {                           // check if erp update
    
                        $dataUpdated = $data->update([
                                                    'delivery_status'   => $order['delivery_status'],
                                                    'delivery_time'     => $order['delivery_time'],
                                                    'status'            => $order['status_update_erp'],
                                                    'updated_at'        => $order['last_updated_erp']
                                                ]);                                                                     // update status order

                        // $data_log = $this->order->where('invoice', $order['invoice'])->first(); // get data from server to input into logs

                        if($data->status == "2" || $data->status == "3" || $data->status == "4") {
                            if($dataUpdated) {
                                $this->sendNotification($data->customer_id, $data->status);     // call method sendnotification to send notif into user phone
                                
                                if($data->status == 4 && $data->point == null) {
                                    $array_point = [];
                                    $point       = 0;
                                    foreach($data->data_item as $row) {
                                        if($row->point || $row->point_principal) {
                                            if($row->point_principal) {;
                                                array_push($array_point, $row->point_principal);
                                            } else {
                                                array_push($array_point, $row->point);
                                            }
                                        }
                                    }
        
                                    if(count($array_point) > 0) {
                                        $point                  = array_sum($array_point);
                                        $data->point            = $point;
                                        $data->complete_time   = Carbon::now();
                                        $data->save();
        
                                        $user = $this->user->find($data->customer_id);
        
                                        if($user->point) {
                                            $user->point += $point;
                                        } else {
                                            $user->point = $point;
                                        }
                    
                                        $user->save();
                    
                                        $pointHistory   = $this->pointHistory
                                                                ->create([
                                                                    'customer_id'   =>  $user->id,
                                                                    'order_id'      =>  $data->id,
                                                                    'deposit'       =>  $point,
                                                                    'status'        =>  'point dari order invoice ' . $data->invoice
                                                                ]);
                    
                                        $logs   = $this->log
                                                            ->create([
                                                                'log_time'      => Carbon::now(),
                                                                'activity'      => 'successfully sent point',
                                                                'table_id'      => $data->id,
                                                                'data_content'  => $pointHistory,
                                                                'table_name'    => 'users, point_histories',
                                                                'column_name'   => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                                                                'from_user'     => null,
                                                                'to_user'       => $user->id,
                                                                'platform'      => 'apps',
                                                            ]);
        
                                        // update data to erp
                                        Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                                            'X-API-KEY'         => config('erp.x_api_key'),
                                            'token'             => config('erp.token_api'),
                                            'invoice'           => $data->invoice,
                                            'kode'              => $user->site_code,
                                            'total_point'       => $point,
                                        ]);
                                    }
                                }
                            }
                        }

                        $data_log = $this->order
                                            ->where('invoice', $order['invoice'])
                                            ->first(); 
                        
                        $this->log->updateOrCreate(                                             // insert to logs if using cron job table
                            ['table_id'     => $data_log->id],
                            ['log_time'     => Carbon::now(),
                            'activity'      => $activity,
                            'table_name'    => 'orders',
                            'column_name'   => 'orders.id, orders.status, orders.updated_at',
                            'from_user'     => 'erp',
                            'to_user'       => null,
                            'data_content'  => $data,
                            'platform'      => 'web',
                            'created_at'    => Carbon::now()]
                        );
    
                    } else { 
                        // $data_log = $this->order
                        //                     ->where('invoice', $order['invoice'])
                        //                     ->first();                                          // get data from server to input into logs
                        
                        // $this->log->updateOrCreate(                                             // insert to logs if using cron job table
                        //     ['table_id'     => $data_log->id],
                        //     ['log_time'     => Carbon::now()]
                        // );
                    }

                } 
                // else {                                                                    // if data null

                //     $this->log->updateOrCreate(                                             // insert into logs with order_id and invoice
                //         ['table_id'     => $order['order_id']],
                //         ['log_time'     => Carbon::now(),
                //         'activity'      => 'Order with invoice : ' . $order['invoice'] . ' not found',
                //         'table_name'    => 'orders',
                //         'column_name'   => 'orders.id, orders.status, orders.updated_at',
                //         'from_user'     => 'erp',
                //         'to_user'       => null,
                //         'data_content'  => $order['invoice'] . ' - ' . $order['order_id'],
                //         'platform'      => 'web',
                //         'created_at'    => Carbon::now()]
                //     );
                // }   
            }
        }
    }

    public function sendNotification($user_id, $status)
    {
        $activity = ""; 
                                                                    // give status 
        if ($status         ==  '1') {
            $activity = 'Pesanan Baru';
        } else if ($status  ==  '2') {  
            $activity = 'Pesanan Anda Terkonfirmasi';
        } else if ($status  ==  '3') {
            $activity = 'Pesanan Anda Sedang Terkirim';
        } else if ($status  ==  '4') {
            $activity = 'Pesanan Selesai';
        }

        $fcm_token = $this->user
                            ->where('id', $user_id)
                            ->pluck('fcm_token')
                            ->all(); // get fcm_token from user table

        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification"  => [
                "title" => 'Status Orderan',
                "body"  => $activity,  
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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get();

        $this->info('Update order status from erp successfully');
    }
}
