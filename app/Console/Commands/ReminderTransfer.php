<?php

namespace App\Console\Commands;

use App\Jobs\ReminderTransferJob;
use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReminderTransfer extends Command
{
    protected $orders;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remindertransfer:everyminute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running auto reminder for transfer payments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        parent::__construct();
        $this->orders = $order;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // get orders
        $orders = $this->orders
                ->with('user')
                ->whereBetween('order_time', [Carbon::now()->subHours(1)->format('Y-m-d H:i:00'), Carbon::now()->subHours(1)->format('Y-m-d H:i:s')])
                ->where('payment_link', null)
                ->where('status', 2)
                ->get();

        $dataOrders = new ReminderTransferJob($orders);
        dispatch($dataOrders);

        $this->info($orders);
    }
}
