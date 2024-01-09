<?php

namespace App\Console\Commands;

use App\Log;
use App\Order;
use App\User;
use App\PointHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckPoint extends Command
{
    protected $order, $log, $user, $pointHistory;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:point';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check point in orders table if point is null then set point';

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

    public function check()
    {
        // ambil data order yang sudah selesai namun belum ada pointi
        $orders = $this->order->where('status', '4')->whereNull('point')->with(['data_item.product', 'data_user'])->get();
        // looping data order
        foreach($orders as $order) {
            $total_price = 0;
            $total_point = 0;
            // looping data order detail
            foreach($order->data_item as $detail) {
                // jika ada total price 
                if($detail->total_price) {
                    $point = round($detail->total_price * $detail->product->ratio, 1);
                    $detail->point = $point;
                    $total_point += $point;
                    $detail->save();
                }

                // jika ada promo point 
                if($detail->point_xtra) {
                    $total_point += $detail->point_xtra;
                }
            }
            $order->point = $total_point;
            $order->save();

            // ambil data user
            $user = $this->user->find($order->customer_id);
        
            if($user->point) {
                $user->point += $total_point;
            } else {
                $user->point = $total_point;
            }

            $user->save();

            // simpan dalam point history
            $pointHistory   = $this->pointHistory
                                    ->create([
                                        'customer_id'   =>  $order->customer_id,
                                        'order_id'      =>  $order->id,
                                        'deposit'       =>  $total_point,
                                        'status'        =>  'point dari order invoice ' . $order->invoice
            ]);

            // simpan dalam logs
            $logs   = $this->log
                            ->create([
                                'log_time'      => Carbon::now(),
                                'activity'      => 'successfully sent point',
                                'table_id'      => $order->customer_id,
                                'data_content'  => $pointHistory,
                                'table_name'    => 'users, point_histories',
                                'column_name'   => 'users.point, point_histories.customer_id, point_histories.order_id, point_histories.deposit, point_histories.status',
                                'from_user'     => null,
                                'to_user'       => $order->customer_id,
                                'platform'      => 'apps',
                            ]);

            // update data to erp
            Http::put('http://site.muliaputramandiri.com/restapi/api/master_data/order', [
                'X-API-KEY'         => config('erp.x_api_key'),
                'token'             => config('erp.token_api'),
                'invoice'           => $order->invoice,
                'kode'              => $order->data_user->site_code,
                'total_point'       => $total_point
            ]);
        }
        // $this->info(count($orders));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->check();

        $this->info('Check and set order point successfully');
    }
}
