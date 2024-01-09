<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DailyConvertToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate convert master customer from erp to json file';

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
        // ambil data tanggal kemarin lusa
        $folder     = Carbon::now()->subDays(2)->format('Y-m-d');
        // data folder
        $rel_path   = '/json/' . $folder . '/';

        // cek jika folder sudah ada 
        if (file_exists(public_path($rel_path))) {
            // menghapus file lama
            \File::deleteDirectory(public_path() . $rel_path); 
        }
    }

    public function get()
    {
        // ambil data customer dari erp
        $response = Http::get('http://site.muliaputramandiri.com/restapi/api/master_data/customer', [
            'X-API-KEY' => config('erp.x_api_key'),
            'token'     => config('erp.token_api'),
            // 'kode'      => $this->argument('code'),
        ])->json();

        // ambil data tanggal
        $folder     = Carbon::now()->format('Y-m-d');

        // ambil nama directory
        $rel_path   = '/json/' . $folder . '/';
        // hapus jika ada
            if(file_exists(public_path($rel_path))) {
                \File::deleteDirectory(public_path($rel_path));
            }

        // jika belum ada folder membuat folder
        if (!file_exists(public_path($rel_path))) {
            mkdir(public_path($rel_path), 0777, true);
        }
        // $path       = 'public' . $rel_path;
        $path       = public_path($rel_path);

        // menyimpan file json kedalam directory
        // \File::put(public_path($path.'customer.json'), json_encode($response['data']));
        file_put_contents($path . 'customer.json', json_encode($response['data']));
    }

    public function handle()
    {
        $this->delete();

        $this->get();

        $this->info('convert customer from erp to json file successfully');
    }
}
