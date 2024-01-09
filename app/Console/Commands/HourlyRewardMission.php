<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mission;
use App\Order;
use App\User;
use App\Log;
use App\PointHistory;
use App\MissionTaskUser;
use Illuminate\Support\Carbon;

class HourlyRewardMission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hourly:rewardMission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if users finish the latest task from each mission';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $missions = Mission::where('status', '1')->with(['tasks'])->get();
        $this->check($missions);
        $this->info('Check mission task successfully');
    }

    // 205000

    public function check($missions)
    {
        foreach($missions as $mission)
        {
            $customer_orders = Order::select('customer_id')
                                        ->where('orders.status', 4)
                                        ->whereBetween('order_time', [$mission->start_date, $mission->end_date])
                                        ->groupBy('customer_id')
                                        ->get()
                                        ->pluck('customer_id');

            $customers      = User::whereIn('id', $customer_orders)
                                        ->select('id', 'customer_code', 'fcm_token', 'point', 'name')
                                        ->get();
            $array_task_id  = $mission->tasks->pluck('id');
            $latest_task_id = $array_task_id->last();
            foreach($customers as $customer) {
                // get the latest task from mission
                if($customer->task->contains($latest_task_id)) {
                    // check if already get reward
                    $exists = $customer->task->last()->pivot->status;
                    if(!$exists) {
                        // send reward                    
                        if($customer->point) {
                            $customer->point += $mission->reward;
                        } else {
                            $customer->point = $mission->reward;
                        }
                        $customer->save();
    
                        // simpan point history
                        $pointHistory   = PointHistory::create([
                                                'customer_id'   =>  $customer->id,
                                                'mission_id'    => $mission->id,
                                                'deposit'       =>  $mission->reward,
                                                'status'        =>  'point dari misi id ' . $mission->id
                                            ]);
    
                        // simpan log point
                        Log::create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => 'successfully sent point',
                                        'table_id'      => $mission->id,
                                        'data_content'  => $pointHistory,
                                        'table_name'    => 'users, point_histories',
                                        'column_name'   => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                                        'from_user'     => null,
                                        'to_user'       => $customer->id,
                                        'platform'      => 'apps',
                                    ]);

                        // simpan log finish mission
                        Log::create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => 'user finish mission',
                                        'table_id'      => $mission->id,
                                        'data_content'  => $mission,
                                        'table_name'    => 'missions, users',
                                        'column_name'   => 'missions.id, users.id',
                                        'from_user'     => null,
                                        'to_user'       => $customer->id,
                                        'platform'      => 'apps',
                        ]);

                        // give parameter if already get the reward
                        MissionTaskUser::whereIn('mission_task_id', $array_task_id)
                                        ->where('user_id', $customer->id)
                                        ->update(['status' => 1, 'send_reward_at' => Carbon::now()]);
    
                        $this->sendNotification($customer->fcm_token, $mission->name, 'point', number_format($mission->reward, 0, ',', '.'));
    
                        $this->info('Customer ' . $customer->id . ' sudah selesain task id ' . $latest_task_id . ' dan mendapat reward');
                    } else {
                        $this->info('Customer ' . $customer->id . ' sudah mendapat reward dari task id ' . $latest_task_id);
                    }
                } else {
                    $this->info('Customer ' . $customer->id . ' belum selesain task id ' . $latest_task_id);
                }
            }
        }
    }

    // method kirim notifikasi
    public function sendNotification($fcm_user, $title, $reward, $nominal)
    {
        // ambil server_api_key dari firebase
        $SERVER_API_KEY = config('firebase.server_api_key');                        

        $data = [
            "registration_ids" => [$fcm_user],
            "notification"  => [
                "title" => 'Pemberitahuan',
                "body"  => 'Selamat Anda Telah Menyelesaikan Misi ' . $title . ' dan Mendapat Hadiah Berupa ' . $reward . ' Sebanyak Rp. ' . $nominal,  
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
