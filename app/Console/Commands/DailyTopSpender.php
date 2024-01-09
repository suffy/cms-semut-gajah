<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\TopSpenderReward;
use App\TopSpenderWinner;
use App\PointHistory;
use App\TopSpender;
use App\Order;
use App\User;
use App\Log;

class DailyTopSpender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topSpender:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dailly Decide The Winner of Top Spender Promo';

    protected $logs, $orders, $topSpender, $winners, $users, $pointHistory, $rewards; 
    public function __construct(Log $logs, Order $orders, TopSpender $topSpender, TopSpenderWinner $winners, User $users, PointHistory $pointHistory, TopSpenderReward $rewards)
    {
        parent::__construct();
        $this->logs         = $logs;
        $this->orders       = $orders;
        $this->topSpender   = $topSpender;
        $this->winners      = $winners;
        $this->users        = $users;
        $this->pointHistory = $pointHistory;
        $this->rewards      = $rewards;
    }

    public function check()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $data = $this->topSpender
                            ->where('end', $yesterday)
                            ->get();

        foreach($data as $row) {
            $list = $this->orders
                            ->select('orders.customer_id as id', 'users.name as name', DB::raw('SUM(order_detail.total_price) as total'))
                            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->join('users', 'users.id', '=', 'orders.customer_id')
                            ->where('orders.status', '=', '4')
                            ->where('status_faktur', '=', 'F')
                            ->whereBetween('orders.order_time', [$row->start, $row->end]);
                            
                        if($row->site_code != null) {
                            $list = $list->where('orders.site_code', '=', $row->site_code);
                        }
                        if($row->brand_id != null) {
                            $list = $list->where('order_detail.products.brand_id', '=', $row->brand_id);
                        }
                        if($row->product != null) {
                            $list = $list->where('order_detail.product_id', '=', $row->product_id);
                        }

                        $list = $list
                                    ->groupBy('orders.customer_id', 'users.name')
                                    ->orderBy('total', 'desc')
                                    ->limit($row->limit)
                                    ->get();

            $rewards = $this->rewards
                                ->select('id', 'pos', 'nominal')
                                ->where('top_spender_id', $row->id)
                                ->orderBy('pos', 'asc')
                                ->get()->toArray();

            if($list) {
                foreach($list as $i => $win) {
                    $nominal = $rewards[$i]['nominal'];
                    // print_r($nominal);
                    $this->winners->create([
                        'top_spender_id'    => $row->id,
                        'customer_id'       => $win->id,
                        'total'             => $nominal
                    ]);

                    if($row->reward == 'point') {
                        $user = $this->users
                                        ->find($win->id);
        
                        if($user->point) {
                            $user->point += $nominal;
                        } else {
                            $user->point = $nominal;
                        }
    
                        $user->save();
    
                        $pointHistory   = $this->pointHistory
                                                ->create([
                                                    'customer_id'   =>  $win->id,
                                                    // 'order_id'      =>  $data->id,
                                                    'top_spender_id'=> $row->id,
                                                    'deposit'       =>  $nominal,
                                                    'status'        =>  'point dari promo top spender id ' . $row->id
                                                ]);
    
                        $logs   = $this->logs
                                            ->create([
                                                'log_time'      => Carbon::now(),
                                                'activity'      => 'successfully sent point',
                                                'table_id'      => $row->id,
                                                'data_content'  => $pointHistory,
                                                'table_name'    => 'users, point_histories',
                                                'column_name'   => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                                                'from_user'     => null,
                                                'to_user'       => $win->id,
                                                'platform'      => 'apps',
                                            ]);
                    }
    
                    $row->updated_at = Carbon::now();
                    $row->save();

                    $this->sendNotification($win->id, $row->title, $row->reward, number_format($nominal, 0, ',', '.'));

                    $data_content = [
                                        'id'        => $row->id, 
                                        'title'     => 'Top Spender', 
                                        'type'      => 'apps', 
                                        'message'   => 'Selamat Anda Menang Promo ' . $row->title . ' dan Mendapat Hadiah Berupa ' . $row->reward . ' Sebanyak Rp. ' . number_format($nominal, 0, ',', '.'),
                                        'type_name' => 'Apps'
                                    ];

                    $data_content = json_encode($data_content);
                    
                    $logs   = $this->logs
                                    ->create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => 'notif broadcast message apps',
                                        'table_id'      => $row->id,
                                        'data_content'  => $data_content,
                                        'table_name'    => 'broadcast_wa',
                                        'column_name'   => null,
                                        'from_user'     => null,
                                        'to_user'       => $win->id,
                                        'platform'      => 'apps',
                                    ]);
                }

                $logs   = $this->logs
                                ->create([
                                    'log_time'      => Carbon::now(),
                                    'activity'      => 'end top spender ' . $row->title,
                                    'table_id'      => $row->id,
                                    'data_content'  => $row,
                                    'table_name'    => 'broadcast_wa',
                                    'column_name'   => null,
                                    'from_user'     => null,
                                    'to_user'       => $win->id,
                                    'platform'      => 'apps',
                                ]);
            }
        }   
    }

    // method kirim notifikasi
    public function sendNotification($user_id, $title, $reward, $nominal)
    {
        // get fcm_token from user table
        $fcm_token = $this->users
                            ->where('id', $user_id)
                            ->pluck('fcm_token')
                            ->all(); 

        // ambil server_api_key dari firebase
        $SERVER_API_KEY = config('firebase.server_api_key');                        

        $data = [
            "registration_ids" => $fcm_token,
            "notification"  => [
                "title" => 'Pemberitahuan',
                "body"  => 'Selamat Anda Menang Promo ' . $title . ' dan Mendapat Hadiah Berupa ' . $reward . ' Sebanyak Rp. ' . $nominal,  
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

    public function handle()
    {
        $this->check();

        $this->info('Store Top Spender Winner successfully');
    }
}
