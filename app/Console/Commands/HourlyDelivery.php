<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\Delivery;
use App\Order;
use App\Log;

class HourlyDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delivery:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate update status delivery from erp';

    protected $logs, $orders, $delivery;
    public function __construct(Log $logs, Order $orders, Delivery $delivery)
    {
        parent::__construct();
        $this->delivery = $delivery;
        $this->orders   = $orders;
        $this->logs     = $logs;
    }

    public function handle()
    {
        // $response = Http::withHeaders([
        //                                 'X-tenant' => 'DIY-DIY0011'
        //                             ])->post('http://5.181.217.229:8000/csa//dashboard/tracking', [
        //                                 'periode'       => "2022-08-10",
        //                                 'customer_id'   => "310640"
        //                             ])->json()['result'];

        // get orders by status 3 if same get the latest order
        $orders = $this->orders->select('customer_id',  'status', 'updated_at')
                                ->where('status', 3)
                                ->with(['user' => function($q) {
                                    $q->select('id', 'name', 'site_code', 'customer_code');
                                }])
                                ->distinct('customer_id')
                                ->get();
        $this->get($orders);
        
        $this->info('successfully hourly delivery');
    }

    public function get($orders)
    {
        foreach($orders as $order) {
            $customer_code  = $order->user->customer_code;
            $user_id        = $order->customer_id;
            $words          = str_split($customer_code, 1);  
            $letter         = join('', array_slice($words, 0, 3));
            $number         = join('', array_slice($words, 3, 6));
            $period         = date('Y-m-d', strtotime($order['updated_at']));
            
            $response = Http::withHeaders([
                                    'X-tenant'      => $letter.'-' . $letter.'0011'
                                ])->post('http://5.181.217.229:8000/csa//dashboard/tracking', [
                                    'periode'       => $period,
                                    'customer_id'   => $number
                                ])->json()['result'];
                    
            // sort the newest data from erp
            // usort($response, function(array $a, array $b) {
            //     $aTimestamp = strtotime($a['created_date']);
            //     $bTimestamp = strtotime($b['created_date']);
            
            //     if($aTimestamp == $bTimestamp) return 0;
            //     return $aTimestamp < $bTimestamp;
            // });

            // $data = $response[0];
            $data = $response;

            $delivery = $this->delivery->updateOrCreate([
                                            'customer_code' => $customer_code],
                                            ['site_id'      => $data['siteid'],
                                            'latitude'      => $data['latitude_cell'],
                                            'longitude'     => $data['longitude_cell'],
                                            'driver_id'     => $data['driverid'],
                                            'loper_id'      => $data['loper_id'],
                                            'loper'         => $data['loper'],
                                            'no_kendaraan'  => $data['no_kendaraan'],
                                            'driver'        => $data['driver'],
                                            'periode'       => $data['periode'],
                                            'accuracy'      => $data['accuracy'],
                                            'created_at'    => date('Y-m-d H:i:s', strtotime($data['created_date']))
                                        ]);

            $this->logs
                    ->create([
                        'log_time'      => Carbon::now(),
                        'activity'      => 'get delivery data customer code ' . $customer_code,
                        'table_id'      => $delivery->id,
                        'data_content'  => $delivery,
                        'table_name'    => 'deliveries',
                        'column_name'   => 'deliveries.*',
                        'from_user'     => null,
                        'to_user'       => $user_id,
                        'platform'      => 'web',
                    ]);
        }
    }
}
