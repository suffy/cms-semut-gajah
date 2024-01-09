<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ShoppingCart;
use App\User;

class DailyNotifCheckout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remindCheckout:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Push Notification To Remind Checkout Product At Shopping Cart';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $carts, $user;

    public function __construct(ShoppingCart $carts, User $user)
    {
        parent::__construct();
        $this->carts    = $carts;
        $this->user     = $user;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get();

        $this->info('Send Notification Checkout successfully');
    }

    public function get()
    {
        $carts  = $this->carts->select('user_id')->groupBy('user_id')->get()->pluck('user_id');

        foreach($carts as $userId) {
            $fcm_token  = $this->user->where('id', $userId)->pluck('fcm_token')->all();
            $this->sendNotification($fcm_token);
        }
    }

    public function sendNotification($fcm_token)
    {

        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        $data = [
            "registration_ids" => $fcm_token,
            "notification" => [
                "title" => 'Pemberitahuan',
                "body" => 'Hai, Jangan Lupa Checkout Pesananan Kamu Yang Masih Ada Di Shopping Cart Yaa.',  
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
