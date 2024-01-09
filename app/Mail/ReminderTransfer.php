<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderTransfer extends Mailable
{
    use Queueable, SerializesModels;

    protected $dataOrder;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dataOrder)
    {
        $this->dataOrder = $dataOrder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $dataOrder = $this->dataOrder;
        
        return $this->view('email.reminder-transfer', compact('dataOrder'));
    }
}
