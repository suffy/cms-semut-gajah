<?php

namespace App\Console\Commands;

use App\Log;
use App\Order;
use App\PointHistory;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Point;

class DailyCompleteOrder extends Command
{
    protected $order, $log, $pointHistory, $user;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'completeorder:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running autocomplete order after 2 days order finished';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order, Log $log, PointHistory $pointHistory, User $user)
    {
        parent::__construct();
        $this->order        = $order;
        $this->log          = $log;
        $this->pointHistory = $pointHistory;
        $this->user         = $user;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // ambil data hari kemarin
        $oneDay     = Carbon::now()->subDays(1)->format('d-m-Y');
        // ambil data hari kemarin lusa
        $twoDays    = Carbon::now()->subDays(2)->format('d-m-Y');

        // ambil order data pada bulan ini dengan status 3 atau barang terkirim
        $orders = $this->order
                ->where('status', '3')
                ->whereBetween('updated_at', [Carbon::now()->subDays(30), Carbon::now()])
                ->get();

        foreach ($orders as $order) {
            // cek data order updated_at sama dengan 3 hari yang lalu
            if ($order->updated_at->format('d-m-Y') == Carbon::now()->subDays(3)->format('d-m-Y')) {
                // ambil data order
                $order = $this->order
                                ->where('id', $order->id)
                                ->with(['data_item.product', 'data_user'])
                                ->first();

                // ambil data user
                $user = $this->user->find($order->customer_id);

                // start count point
                    $array_point = [];
                    $point       = 0;
                    foreach($order->data_item as $row) {
                        // jika ada point dari product atau point dari promo
                        if($row->point || $row->point_principal) {
                            if($row->point_principal) {
                                // memasukkan point_principal kedalam array 
                                array_push($array_point, $row->point_principal);
                            } else {
                                // memasukkan point kedalam array 
                                array_push($array_point, $row->point);
                            }
                        }
                    }

                    // jika array_point ada isinya
                    if(count($array_point) > 0) {
                        // hitung totalnya
                        $point       = array_sum($array_point);
                    }
                //end

            // update status at orders table
                $order->status          = '4';
                $order->point           = $point;
                $order->complete_time   = Carbon::now();
                $order->save();
            // end

            // update point at users table
                if($point > 0) {
                    if($user->point) {
                        $user->point += $point;
                    } else {
                        $user->point = $point;
                    }

                    $user->save();

                    $pointHistory   = $this->pointHistory
                                            ->create([
                                                'customer_id'   =>  $user->id,
                                                'order_id'      =>  $order->id,
                                                'deposit'       =>  $point,
                                                'status'        =>  'point dari order invoice ' . $order->invoice
                                            ]);

                    $logs   = $this->log
                                        ->create([
                                            'log_time'      => Carbon::now(),
                                            'activity'      => 'successfully sent point',
                                            'table_id'      => $user->id,
                                            'data_content'  => $pointHistory,
                                            'table_name'    => 'users, point_histories',
                                            'column_name'   => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                                            'from_user'     => null,
                                            'to_user'       => $user->id,
                                            'platform'      => 'apps',
                                        ]);
                }

                // update data to erp
                Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                    'X-API-KEY'         => config('erp.x_api_key'),
                    'token'             => config('erp.token_api'),
                    'invoice'           => $order->invoice,
                    'kode'              => $user->site_code,
                    'status_update_erp' => $order->status,
                    'total_point'       => $point
                ]);

                // kirim notifikasi ke user jika order sudah diselesaikan otomatis
                $activity = "Pesanan Anda Dengan Invoice " . $order->invoice . " Telah di selesaikan oleh sistem, terimakasih telah menggunakan apps Semut Gajah.";
                $this->sendNotification($order->customer_id, $activity);

                // simpan kedalam logs
                $logs   = $this->log
                                    ->create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => "order completed auto",
                                        'table_id'      => $order->id,
                                        'table_name'    => 'orders',
                                        'data_content'  => $order,
                                        'from_user'     => null,
                                        'to_user'       => $order->customer_id,
                                        'platform'      => 'apps'
                                    ]);
            }

            // kirim notifikasi jika hari kurang sehari atau duahari
            if ($order->updated_at->format('d-m-Y') == $oneDay || $order->updated_at->format('d-m-Y') == $twoDays) {
                $activity = "Segera Selesaikan Pesanan Anda Dengan Invoice " . $order->invoice;
                $this->sendNotification($order->customer_id, $activity);
            } 
        }

        $this->info('Successfully running autocomplete orders');
    }

    // method kirim notifikasi
    public function sendNotification($user_id, $activity)
    {
        // get fcm_token from user table
        $fcm_token = $this->user
                            ->where('id', $user_id)
                            ->pluck('fcm_token')
                            ->all(); 

        // ambil server_api_key dari firebase
        $SERVER_API_KEY = config('firebase.server_api_key');                        

        $data = [
            "registration_ids" => $fcm_token,
            "notification"  => [
                "title" => 'Pemberitahuan',
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
}
