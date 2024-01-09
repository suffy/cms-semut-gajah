<?php

namespace App\Jobs;

use App\Mail\ReminderTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ReminderTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dataOrders;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dataOrders)
    {
        $this->dataOrders = $dataOrders;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        foreach ($this->dataOrders as $dataOrder) {
            $mail = new ReminderTransfer($dataOrder);
            
            Mail::to($dataOrder->user->email)->send($mail);
        }
    }
}
