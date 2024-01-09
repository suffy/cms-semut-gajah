<?php

namespace App\Console\Commands;

use App\Log;
use App\Promo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\ShoppingCart;

class DailyPromo extends Command
{
    protected $promo, $log, $shoppingCart;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promo:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate deactivate promos if it exceeds the valid date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Promo $promo, Log $log, ShoppingCart $shoppingCart)
    {
        parent::__construct();
        $this->promo    = $promo;
        $this->logs      = $log;
        $this->shoppingCarts    = $shoppingCart;
    }

    // check valid date
    public function get()
    {
        // ambil data promo yang masih aktif
        $promos = $this->promo->where('status', 1)->whereNull('special')->get();
        foreach($promos as $promo) 
        {
            // jika sudah melewati data dari tanggal end
            if($promo->end < Carbon::now()->format('Y-m-d')) {
                // update status
                $promo->status = 0;
                // update shoppingcart yang belum ter checkout 
                $this->shoppingCarts->where('promo_id', $promo->id)->update(['promo_id' => NULL]);
                $promo->save();
                
                // log
                $logs   = $this->logs
                        ->updateOrCreate(
                            ['table_id'      => $promo->id],
                            ['log_time'      => Carbon::now(),
                            'activity'      => 'Change promo status to ' . $promo->status,
                            'table_id'      => $promo->id,
                            'data_content'  => $promo,
                            'table_name'    => 'promos',
                            'column_name'   => 'promos.status, promos.start, promos.end',
                            'from_user'     => null,
                            'to_user'       => null,
                            'platform'      => 'web',
                        ]);
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

        $this->info('Deactivate promos successfully');
    }
}
