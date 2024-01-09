<?php

namespace App\Console\Commands;

use App\Log;
use App\Order;
use App\OrderDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyCompleteComplaint extends Command
{
    protected $order, $orderDetails, $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'completecomplaint:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running autocomplete complaint after 10 days orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order, OrderDetail $orderDetail, Log $log)
    {
        parent::__construct();
        $this->order        = $order;
        $this->orderDetails = $orderDetail;
        $this->log          = $log;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // get order_detail this month
        $orderDetails   = $this->orderDetails
                        ->select('id', 'order_id', 'created_at')
                        ->whereBetween('created_at', [Carbon::now()->subDays(30), Carbon::now()])
                        ->get();

        foreach ($orderDetails as $orderDetail) {
            // check and update status review and complaint
            if ($orderDetail->updated_at->format('d-m-Y') == Carbon::now()->subDays(10)->format('d-m-Y')) {
                $orderDetail    = $this->orderDetails->find($orderDetail->id)
                                ->where('status', '4')
                                ->update([
                                    'product_review_id' => 'complete',
                                    'complaint_id'      => $orderDetails->order_id,
                                ]);

                // get id user order
                $order = $this->order->find($orderDetail->order_id);

                // insert to logs table
                $log = $this->log->create([
                    'log_time'      => Carbon::now(),
                    'activity'      => 'Update status complaint with order id : ' . $orderDetail->order_id,
                    'table_id'      => $orderDetail->order_id,
                    'table_name'    => 'orders, order_detail',
                    'column_name'   => 'order_detail.product_review_id, order_detail.complaint_id',
                    'from_user'     => null,
                    'to_user'       => $order->customer_id,
                    'data_content'  => $order,
                    'platform'      => 'web',
                    'created_at'    => Carbon::now()
                ]);
            }
        }

        $this->info('Successfully running autocomplete complaints');
    }
}
