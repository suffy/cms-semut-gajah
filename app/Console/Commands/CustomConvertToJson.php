<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CustomConvertToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom:convert {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom convert customer data from erp to json';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function delete()
    {
        $folder     = Carbon::now()->subDays(2)->format('Y-m-d');
        // $this->info($folder);
        $rel_path   = '/json/' . $folder . '/';

        if (file_exists(public_path($rel_path))) {
            \File::deleteDirectory(public_path() . $rel_path); //menghapus file lama
        }
    }

    public function get()
    {
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/customer', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
            'kode'      => $this->argument('code'),
        ])->json();

        // $chunks     = array_chunk($response['data'], 38000);
        // $no         = 0;
        $folder     = Carbon::now()->format('Y-m-d');

        $rel_path   = '/json/' . $folder . '/';
        // delete directory if exist
            if(file_exists(public_path($rel_path))) {
                \File::deleteDirectory(public_path($rel_path));
            }

        if (!file_exists(public_path($rel_path))) {
            mkdir(public_path($rel_path), 0777, true);
        }
        // $path       = 'public' . $rel_path;
        $path           = public_path($rel_path);

        file_put_contents($path . 'customer.json', json_encode($response['data']));
    }

    public function handle()
    {
        $this->delete();

        $this->get();

        $this->info('convert customer from erp to json file successfully');
    }
}
