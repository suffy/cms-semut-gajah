<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Log;
use App\User;
use App\Order;
use App\Mission;
use App\MissionTask;
use App\MissionTaskUser;
use App\MissionUser;
use App\PointHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HourlyMissionTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hourly:missionTasks';

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
        // type 1,2,3
        // 1 by qty, 2 by total price, 3 by login daily
        $missions = Mission::where('status', '1')
            ->where('end_date', '>=', Carbon::now())
            ->with(['tasks.user'])->get();
        $this->check($missions);
        $this->info('Check mission task successfully');
    }

    public function check($missions)
    {
        foreach ($missions as $mission) {
            $this->info('===============Check misi id============= ' . $mission->id);

            foreach ($mission->tasks as $key => $task) {
                $this->info('+++++Check task id++++++ ' . $task->id);
                // $task->user()->detach(14006);
                $usermision = MissionUser::pluck('user_id');

                $task_prev = ($key > 0) ? $mission->tasks[$key - 1] : null;

                if ($task->type == '1') { // by qty
                    $this->info('product id ' . $task->product_id);
                    $taskwhere = MissionTask::where('product_id', $task->product_id)
                        ->where('type', '1')
                        ->where('mission_id', $task->mission_id)
                        ->whereBetween('created_at', [$mission->start_date, $mission->end_date])
                        ->get();
                    $sisa = 0;

                    foreach ($taskwhere as $in => $valuetask) {
                        $this->info('task value id ' . $valuetask->id);
                        $orders = Order::select(DB::raw('customer_id, SUM(order_detail.qty) as order_count'))
                            ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
                            ->join('products', 'products.id', '=', 'order_detail.product_id')
                            ->where('orders.status', 4)
                            ->whereIn('orders.customer_id', $usermision)
                            ->where('status_faktur', 'F')
                            ->whereBetween('order_time', [$mission->start_date, $mission->end_date]);

                        if ($task->product_id) {
                            $orders = $orders
                                ->where('order_detail.product_id', $task->product_id);
                        }
                        if ($task->group_id) {
                            // cek group_id pada task dengan group_id dari product 
                            $orders = $orders
                                ->where('products.nama_group', $task->group_id);
                        }
                        if ($task->subgroup) {
                            $orders = $orders
                                ->where('products.subgroup', $task->subgroup);
                        }

                        $orders = $orders
                            ->groupBy('customer_id')
                            ->get();
                        if (count($orders) == 0) {
                            $this->info('Tidak ada orders sesuai dengan syarat task type 1 id ' . $task->id);
                        }

                        foreach ($orders as $order) {
                            $ordercount = $order->order_count;
                            $exists = $valuetask->user->contains($order->customer_id);

                            if (!$task_prev) {
                                $this->info('cek taks ke 1');
                                if (!$exists) {
                                    if ($in > 0) {
                                        $sisa = $sisa - $valuetask->qty;
                                    } else {
                                        $sisa = $ordercount - $sisa - $valuetask->qty;
                                    }

                                    if ($sisa >= 0) {
                                        $valuetask->user()->attach($order->customer_id, ['finish_at' => Carbon::now()]);
                                        Log::create([
                                            'log_time'      => Carbon::now(),
                                            'activity'      => 'User ' . $order->customer_id . ' already finish the mission with id ' . $mission->id . ' and task id ' . $valuetask->id . ' by job',
                                            'table_id'      => $mission->id,
                                            'data_content'  => null,
                                            'table_name'    => 'missions, mission_tasks, mission_task_users',
                                            'column_name'   => 'mission_task_users.mission_id, mission_task_users.user_id, mission_task_users.finish_at',
                                            'from_user'     => null,
                                            'to_user'       => $order->customer_id,
                                            'platform'      => 'web',
                                        ]);
                                        $this->info('yang lolos task type 1 dengan id ' . $valuetask->id . ' ' . $order->customer_id);
                                    } else {
                                        $this->info('tidak lolos task type 1 dengan id ' . $valuetask->id . ' ' . $order->customer_id);
                                    }
                                } else {
                                    $this->info('Sudah lolos task type 1 dengan id ' . $valuetask->id . ' ' . $order->customer_id);
                                }
                            } else {
                                $this->info('cek taks ke 2');
                                if ($task_prev->user->contains($order->customer_id)) {
                                    if (!$exists) {
                                        if ($in > 0) {
                                            $sisa = $sisa - $valuetask->qty;
                                        } else {
                                            $sisa = $ordercount - $sisa - $valuetask->qty;
                                        }

                                        if ($sisa >= 0) {
                                            $valuetask->user()->attach($order->customer_id, ['finish_at' => Carbon::now()]);
                                            Log::create([
                                                'log_time'      => Carbon::now(),
                                                'activity'      => 'User ' . $order->customer_id . ' already finish the mission with id ' . $mission->id . ' and task id ' . $valuetask->id . ' by job',
                                                'table_id'      => $mission->id,
                                                'data_content'  => null,
                                                'table_name'    => 'missions, mission_tasks, mission_task_users',
                                                'column_name'   => 'mission_task_users.mission_id, mission_task_users.user_id, mission_task_users.finish_at',
                                                'from_user'     => null,
                                                'to_user'       => $order->customer_id,
                                                'platform'      => 'web',
                                            ]);
                                            $this->info('yang lolos task type 1 dengan id ' . $valuetask->id . ' ' . $order->customer_id);
                                        } else {
                                            $this->info('tidak lolos task type 1 dengan id ' . $valuetask->id . ' ' . $order->customer_id);
                                        }
                                    } else {
                                        $this->info('Sudah lolos task type 1 dengan id ' . $valuetask->id . ' ' . $order->customer_id);
                                    }
                                } else {
                                    $this->info('task type 1 sebelum nya belum dengan id ' . $task_prev->id . ' ' . $order->customer_id);
                                }
                            }
                        }
                    }
                } else if ($task->type == '2') { // by total price
                    $orders = Order::select(DB::raw('customer_id, SUM(total_price) as total_product_price'))
                        ->join('order_detail', 'order_detail.order_id', '=', 'orders.id')
                        ->join('products', 'products.id', '=', 'order_detail.product_id')
                        ->whereIn('orders.customer_id', $usermision)
                        ->where('orders.status', 4)
                        ->where('status_faktur', 'F')
                        ->whereBetween('order_time', [$mission->start_date, $mission->end_date]);

                    if ($task->product_id) {
                        $orders = $orders
                            ->where('order_detail.product_id', $task->product_id);
                    }

                    if ($task->group_id) {
                        $orders = $orders
                            ->where('products.nama_group', $task->group_id);
                    }
                    if ($task->subgroup) {
                        $orders = $orders
                            ->where('products.subgroup', $task->subgroup);
                    }

                    $orders = $orders
                        ->groupBy('customer_id')
                        ->get();

                    if (count($orders) == 0) {
                        $this->info('Tidak ada orders sesuai dengan syarat task type 2 id ' . $task->id);
                    }

                    foreach ($orders as $order) {
                        if ($task->qty <= $order->total_product_price) {
                            // check already accomplish the task before
                            $exists = $task->user->contains($order->customer_id);
                            // check if first loop
                            if (!$task_prev) {
                                if (!$exists) {
                                    $task->user()->attach($order->customer_id, ['finish_at' => Carbon::now()]);
                                    Log::create([
                                        'log_time'      => Carbon::now(),
                                        'activity'      => 'User ' . $order->customer_id . ' already finish the mission with id ' . $mission->id . ' and task id ' . $task->id . ' by job',
                                        'table_id'      => $mission->id,
                                        'data_content'  => null,
                                        'table_name'    => 'missions, mission_tasks, mission_task_users',
                                        'column_name'   => 'mission_task_users.mission_id, mission_task_users.user_id, mission_task_users.finish_at',
                                        'from_user'     => null,
                                        'to_user'       => $order->customer_id,
                                        'platform'      => 'web',
                                    ]);
                                } else {
                                    $this->info('sudah lolos task type 2 dengan id ' . $task->id . ' ' . $order->customer_id);
                                }
                            } else {
                                // check previous task
                                if ($task_prev->user->contains($order->customer_id)) {
                                    if (!$exists) {
                                        $task->user()->attach($order->customer_id, ['finish_at' => Carbon::now()]);
                                        Log::create([
                                            'log_time'      => Carbon::now(),
                                            'activity'      => 'User ' . $order->customer_id . ' already finish the mission with id ' . $mission->id . ' and task id ' . $task->id . ' by job',
                                            'table_id'      => $mission->id,
                                            'data_content'  => null,
                                            'table_name'    => 'missions, mission_tasks, mission_task_users',
                                            'column_name'   => 'mission_task_users.mission_id, mission_task_users.user_id, mission_task_users.finish_at',
                                            'from_user'     => null,
                                            'to_user'       => $order->customer_id,
                                            'platform'      => 'web',
                                        ]);
                                    } else {
                                        $this->info('sudah lolos task type 2 dengan id ' . $task->id . ' ' . $order->customer_id);
                                    }
                                } else {
                                    $this->info('task type 2 sebelum nya belum dengan id ' . $task_prev->id . ' ' . $order->customer_id);
                                }
                            }
                        } else {
                            $this->info('tidak lolos task type 2 dengan id ' . $task->id . ' ' . $order->customer_id);
                        }
                    }
                }

                if (count($orders) != 0) {
                    // cek mission done
                    $cekTask = MissionTask::where('mission_id', $mission->id)->count();
                    $array_task_id  = $mission->tasks->pluck('id');
                    $cekUserTask = MissionTaskUser::where('user_id', $order->customer_id)
                        ->whereIn('mission_task_id', $array_task_id)
                        ->count();

                    if ($cekTask == $cekUserTask) {
                        MissionUser::where('mission_id', $mission->id)
                            ->where('user_id', $order->customer_id)
                            ->update(['mission_status' => 1]);
                        $this->info('misi id ' . $mission->id . ' User id ' . $order->customer_id . ' sudah selesai');

                        $customer_mission_done = MissionUser::where('mission_id', $mission->id)
                            ->where('mission_status', 1)
                            ->pluck('user_id');

                        // Give reward before mission done
                        $customer_orders = Order::select('customer_id')
                            ->where('orders.status', 4)
                            ->whereIn('orders.customer_id', $customer_mission_done)
                            ->whereBetween('order_time', [$mission->start_date, $mission->end_date])
                            ->groupBy('customer_id')
                            ->get()
                            ->pluck('customer_id');

                        $customers = User::whereIn('id', $customer_orders)
                            ->select('id', 'customer_code', 'fcm_token', 'point', 'name')
                            ->get();

                        foreach ($customers as $customer) {
                            // check if already get reward
                            $cekTaskMission = MissionTask::where('mission_id', $mission->id)->count();
                            $array_task_id  = $mission->tasks->pluck('id');
                            $cekUserTaskFinish = MissionTaskUser::where('user_id', $customer->id)
                                ->whereIn('mission_task_id', $array_task_id)
                                ->where('send_reward_at', '!=', null)
                                ->count();

                            // $exists = $customer->task->last()->pivot->status;
                            if ($cekTaskMission == $cekUserTaskFinish) {
                                $this->info('Customer ' . $customer->id . ' sudah mendapat reward dari misi id ' . $mission->id);
                            } else {
                                // send reward                    
                                if ($customer->point) {
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

                                $this->info('Customer ' . $customer->id . ' sudah selesain misi id ' . $mission->id . ' dan mendapat reward ' . $mission->reward);
                            }
                        }
                    }
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
