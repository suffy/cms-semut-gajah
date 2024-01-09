<?php

namespace App\Console\Commands;

use App\Log;
use App\Salesman;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DailySalesman extends Command
{
    protected $salesmen, $log;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salesman:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate insert salesman from erp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Salesman $salesman, Log $log)
    {
        parent::__construct();
        $this->salesmen = $salesman;
        $this->log      = $log;
    }

    // get data from erp
    public function get()
    {
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/salesman', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
        ])->json();

        $this->store($response['data']);
    }

    // insert to salesmen table
    public function store($sites)
    {
        foreach ($sites as $site) {
            $salesmen = $this->salesmen->updateOrCreate(
                            ['kodesales'     => $site['kodesales']],
                            ['kodesales_erp' => $site['kodesales_erp'],
                            'namasales'     => $site['namasales'],
                            'kode'          => $site['kode']]
                        );

            // insert to logs table
            $this->log->updateOrCreate(
                ['table_id'     => $salesmen->id],
                ['log_time'     => Carbon::now(),
                'activity'      => 'Insert/update salesman from erp with id : ' . $salesmen->id,
                'table_name'    => 'salesmen',
                'column_name'   => 'salesmen.id, salesmen.kodesales, salesmen.kodesales_erp, salesmen.namasales, salesmen.kode',
                'from_user'     => null,
                'to_user'       => null,
                'data_content'  => $salesmen,
                'platform'      => 'web',
                'created_at'    => Carbon::now()]
            );
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

        $this->info('Insert salesman from erp successfully');
    }
}
