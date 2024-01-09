<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Log;
use App\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SendNotificationPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $message, $users, $productId;
    public function __construct($message, $users, $productId)
    {
        $this->message  = $message;
        $this->users    = $users;
        $this->productId= $productId;
    }

    // array for select product
    private function arraySelectPrice($salurCode)
    {
        if($salurCode == 'SW' || $salurCode == 'WS' || $salurCode == 'SO') {
            return DB::raw("
                                    product_prices.id, 
                                    product_id,  
                                    harga_grosir_mt, 
                                    harga_promosi_coret_ritel_gt, 
                                    harga_promosi_coret_grosir_mt, 
                                    products.brand_id,
                                    harga_ritel_gt as ritel_gt,
                                    (CASE 
                                        WHEN products.brand_id::integer=005 THEN harga_ritel_gt 
                                        WHEN products.brand_id::integer=001 THEN harga_ritel_gt
                                        ELSE harga_grosir_mt 
                                        END) as harga_ritel_gt
                                ");
        } else {
            return DB::raw("
                                    product_prices.id, 
                                    product_id, 
                                    harga_ritel_gt, 
                                    harga_grosir_mt, 
                                    harga_promosi_coret_ritel_gt, 
                                    harga_promosi_coret_grosir_mt, 
                                    products.brand_id,
                                    harga_ritel_gt as rt_backup
                                ");
        };
    }

    private function arraySelectCart()
    {
        return ['user_id', 'product_id', 'qty'];
    }

    public function handle()
    {
        $users      = $this->users;
        $message    = $this->message;
        $productId  = $this->productId;

        foreach($users as $row) {
            $salurCode  = $row['salur_code'];
            $siteCode   = $row['site_code'];
            $userId     = $row['id'];
            $arrayPrice = $this->arraySelectPrice($salurCode);
            $arrayCart  = $this->arraySelectCart();

            $product    = Product::leftJoin('product_review', 'products.id', '=', 'product_review.product_id')
                                    ->join('product_availability', 'product_availability.product_id' ,'=', 'products.id')
                                    ->where('product_availability.site_code', $siteCode)
                                    ->select('products.*', DB::raw("ROUND(avg(star_review)::numeric, 1) as avg_rating, 'notif' as identity"))
                                    ->groupBy('products.id')
                                    ->with(['price'  => function($query) use($arrayPrice) {
                                        $query->select($arrayPrice)
                                        ->join('products', 'products.id', '=', 'product_prices.product_id');
                                    },  'cart' => function($query) use ($userId, $arrayCart) {
                                        $query->where('user_id', $userId)
                                            ->select($arrayCart);
                                    }, 'review.user'])
                                    ->find($productId);

            $product    = isset($product) ? $product : null;

            $fcm            = $row['fcm_token'];
            $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

            $data = [
                'to'        => $fcm,
                'data'      => $product,
                'notification' => [
                    'title' => 'Pemberitahuan',
                    'body'  => $message
                ]
            ];

            $this->sendNotification($SERVER_API_KEY,$data);

            $data_content = [
                'id'        => $row['id'], 
                'title'     => 'Pemberitahuan', 
                // 'type'      => 'apps', 
                'type'      => 'notif-product', 
                'message'   => $message,
                'type_name' => 'Apps',
                'data'      => $product
            ];

            $data_content = json_encode($data_content);
            
            Log::create([
                            'log_time'      => Carbon::now(),
                            'activity'      => 'notif broadcast message apps',
                            'table_id'      => null,
                            'data_content'  => $data_content,
                            'table_name'    => 'broadcast_wa',
                            'column_name'   => null,
                            'from_user'     => null,
                            'to_user'       => $row['id'],
                            'platform'      => 'apps',
                        ]);
        }
    }

    public function sendNotification($API_KEY, $data)
    {
        $response = Http::withHeaders([
            'Authorization'      => 'key='.$API_KEY,
            'Content-Type'       => 'application/json'
        ])->post('https://fcm.googleapis.com/fcm/send', $data)->json();
        
        // $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config

        // $response = Http::withHeaders([
        //     'Authorization'      => 'key='.$SERVER_API_KEY,
        //     'Content-Type'       => 'application/json'
        // ])->post('https://fcm.googleapis.com/fcm/send', [
        //     'to'        => $fcm_token,
        //     'data'      => [
        //         'tes' => $dataProduct
        //     ],
        //     'notification' => [
        //         'title' => 'Pemberitahuan',
        //         'body'  => $activity
        //     ]
        // ])->json();

        // $data = [
        //     "to" => $fcm_token,
        //     "data"      => [
        //         "id" => '60086'
        //     ],
        //     "notification" => [
        //         "title" => 'Pemberitahuan',
        //         "body"  => $activity
        //     ]
        // ];
        
        // $dataString = json_encode($data);
    
        // $headers = [
        //     'Authorization: key=' . $SERVER_API_KEY,
        //     'Content-Type: application/json',
        // ];

        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        // $response = curl_exec($ch);
    }
}
