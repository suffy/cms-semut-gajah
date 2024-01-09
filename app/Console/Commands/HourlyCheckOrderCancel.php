<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Order;
use App\User;
use App\Log;
use App\OrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class HourlyCheckOrderCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hourly:stockorder';

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
    public function __construct(Order $order, User $user, OrderDetail $orderDetail)
    {
        parent::__construct();
        $this->order = $order;
        $this->orderDetail = $orderDetail;
        $this->user         = $user;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function get()
    {
        $orders = $this->order->get();
        foreach ($orders as $order) {
            $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/semutgajah_status_order', [
                'X-API-KEY' => config('erp.x_api_key'),
                'token'     => config('erp.token_api'),
                'invoice_aplikasi'      => $order->invoice,
            ])->json();

            // $this->info($response['data']);

            if (array_key_exists('data', $response)) {
                $this->update($response['data']);
            }
        }
    }

    public function update($orders)
    {
        foreach ($orders as $order) {

            $data  = $this->order
                ->where('invoice', $order['invoice_aplikasi'])
                ->first();

            if (!is_null($data)) {
                $total_price = array();
                foreach ($order['products_success'] as $row) {
                    $detail = $this->orderDetail
                        ->where('order_id', $data->id)
                        ->where('product_id', $row['kodeprod'])
                        ->first();
                    if ($detail) {
                        if (($detail->qty - $detail->qty_cancel) != $row['total_qty_pemenuhan']) {
                            if ($detail->disc_cabang != 0) {
                                $new_price          = $row['total_qty_pemenuhan'] * $detail->price_apps;
                                $rp_cabang          = ($detail->disc_cabang / 100) * $new_price;
                                $total_price_update = $new_price - $rp_cabang;
                                array_push($total_price, $total_price_update);
                            } else {
                                $rp_cabang          = 0;
                                $total_price_update = $row['total_qty_pemenuhan'] * $detail->price_apps;
                                array_push($total_price, $total_price_update);
                            }

                            if ($row['total_qty_pemenuhan'] < $row['total_qty_order']) {
                                $qty_cancel = $row['total_qty_order'] - $row['total_qty_pemenuhan'];
                            } else {
                                $qty_cancel = 0;
                            }
                            $detail->update([
                                'qty_update' => $row['total_qty_pemenuhan'],
                                'qty_cancel' => $qty_cancel,
                                'total_price_update' => $total_price_update,
                                'total_price_cancel' => $detail->total_price - $total_price_update,
                            ]);
                            $payment_total = array_sum($total_price);
                            $dataUpdated =  $data->update([
                                'status'        => $order['status_erp'],
                                'payment_total' => $payment_total,
                                'payment_final' => $payment_total - $data->payment_point,
                                'stock_status' => 1,
                            ]);
                            $this->info('Check the out of stock product from erp with product_id = ' . $detail->product_id);
                        }
                    }
                }
              
                if (isset($dataUpdated)) {
                    $this->sendNotification($data->customer_id, $data->invoice, $data->id);
                    $this->info('data updated');
                    $data_content = [
                        'id'        => $data->id,
                        'title'     => 'Update Status Pesanan',
                        'type'      => 'cancel order',
                        'message'   => 'Update Status Pemesanan No. ' . $data->invoice,
                        'type_name' => 'Apps'
                    ];

                    $data_content = json_encode($data_content);

                    // $logs   = $this->logs
                    Log::create([
                        'log_time'      => Carbon::now(),
                        'activity'      => 'notif broadcast message apps',
                        'table_id'      => $data->id,
                        'data_content'  => $data_content,
                        'table_name'    => 'broadcast_wa',
                        'column_name'   => null,
                        'from_user'     => null,
                        'to_user'       => $data->customer_id,
                        'platform'      => 'apps',
                    ]);
                } else {
                    $this->info('data already updated');
                }
                Log::create([
                    'log_time'      => Carbon::now(),
                    'activity'      => 'update order canceled from erp',
                    'table_id'      => $data->id,
                    'data_content'  => $data,
                    'table_name'    => 'broadcast_wa',
                    'column_name'   => 'orders.id, users.id',
                    'from_user'     => null,
                    'to_user'       => $data->customer_id,
                    'platform'      => 'apps',
                ]);
            }
        }
    }

    public function sendNotification($user_id, $invoice, $id)
    {

        $fcm_token = $this->user
            ->where('id', $user_id)
            ->pluck('fcm_token')
            ->all(); // get fcm_token from user table

        $SERVER_API_KEY = config('firebase.server_api_key');                        // get server_api_key from config
        $data = [
            "registration_ids" => $fcm_token,
            "data" => [
                "order_id" => $id
            ],
            "notification"  => [
                "title" => 'Pemberitahuan',
                "body"  => 'Update Status Pemesanan No. ' . $invoice,
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
        $this->info($dataString);
        $this->info($response);
    }

    public function handle()
    {
        $this->get();

        $this->info('Update out of stock product from erp successfully');
    }
}
