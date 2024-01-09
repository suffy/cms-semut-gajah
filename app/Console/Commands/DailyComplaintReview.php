<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order;
use App\Log;
use Carbon\Carbon;

class DailyComplaintReview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complaintReview:daily';

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
    public function __construct(Order $order, Log $log)
    {
        parent::__construct();
        $this->order        = $order;
        $this->log          = $log;
    }

    public function get()
    {
        $complaintDate  = Carbon::now()->subDays(1)->format('Y-m-d') . '00:00:00';
        $reviewDate     = Carbon::now()->subMonth(3)->format(Y-m-d) . '00:00:00';
        $orders         = $this->order
                                    ->where('status', 4)
                                    ->where('order_time', '>=', $complaintDate)
                                    ->get();
        if($orders) {
            foreach($orders as $order) {
                if($order->status_complaint == NULL || $order->status_review == NULL) {
                    $order->status_complaint = 1;
                    $activity = NULL;
                    if($order->order_time >= $reviewDate) {
                        $order->status_review = 1;
                        $activity = '& review';
                    }
                    $order->save();

                    $this->log->create(                                            
                        ['table_id'     => $order->id,
                        'log_time'     => Carbon::now(),
                        'activity'      => 'close complaint ' . $activity . ' for user',
                        'table_name'    => 'orders',
                        'column_name'   => 'orders.status_complaint, orders.status_review',
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => $order,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()]
                    );
                }
            } 
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
