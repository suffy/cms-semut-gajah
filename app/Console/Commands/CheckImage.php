<?php

namespace App\Console\Commands;

use App\Log;
use App\Product;
use Carbon\Carbon;
use Exception;
use Intervention\Image\ImageManagerStatic as InterImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CheckImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate download products with no image';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Product $product, Log $log)
    {
        parent::__construct();
        $this->products         = $product;
        $this->log              = $log;
    }

    public function check()
    {
        // memanggil semua produk beserta image
        $list = $this->products->select('id', 'kodeprod', 'image', 'image_backup')->where('status', 1)->get();
        foreach($list as $row) {
            // check apakah url image yg tersimpan di database ada di folder
            if(!file_exists(public_path($row->image))) {
                // jika tidak ada, download ulang image
                $this->info('Product: '.$row->kodeprod.' - '.$row->image.' not found');
                try {
                    // $url = $row->image_backup;
                    // $info = pathinfo($url);
                    // $contents = file_get_contents($url);
                    // $rel_path = '/images/product/';
                    // $new_name = $row->kodeprod . "." . $info['extension'];
                    // $product_image = $rel_path . $new_name;
                    // $image_resize = InterImage::make($contents);
                    // $image_resize->save(('public/images/product/' . $new_name));
                    $this->download($row);
                } catch(\Exception $e) {
                    $this->info($e->getMessage());
                }
            } else if(is_null($row->image)) {
                // jika url image null tetapi image di folder ada, hanya menyimpan kembali di database
                $this->info('Product: '. $row->kodeprod . ' image in db is null');
                if($row->image_backup) {
                    $url                = $row->image_backup;
                    $info               = pathinfo($url);
                    $rel_path           = '/images/product/';
                    $new_name           = $row->kodeprod . "." . $info['extension'];
                    $product_image      = $rel_path . $new_name;
                    $row->image         = $product_image;
                    $row->save();
                }
            } else {
                $this->info('Product: '. $row->kodeprod . ' There is nothing to do.');
            }
        }
    }

    public function download($row)
    {
        $url = $row->image_backup;
        $info = pathinfo($url);
        $new_name = $row->kodeprod . "." . $info['extension'];

        $response = Http::withOptions([
            'verify' => false,

            // connect timeout
            'connect_timeout' => 60,

            // force ipv4
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ])
        ->withHeaders([
            'User-Agent' => 'Mozilla/5.0'
        ])
        ->timeout(120)
        ->retry(3, 2000)
        ->get($url);

        // cek response
        if (!$response->successful()) {

            throw new \Exception(
                'HTTP Error : '.$url.' Status: '. $response->status()
            );

        }

        // cek mime type
        $contentType = $response->header('Content-Type');

        if (!str_contains($contentType, 'image')) {

            throw new \Exception(
                'Response bukan gambar'
            );

        }

        // ambil body image
        $contents = $response->body();

        $image_resize = InterImage::make($contents);

        $image_resize->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $image_resize->save(
            public_path('images/product/' . $new_name),
            80
        );
    }

    // notif wa
    public function sendWaGroup($msg)
    {
        $link = config('app.url');

        Http::withHeaders([
            'x-api-key' => config('wabot.x_api_key')
        ])->post(config('wabot.url').'/send-group', [
            'groupId' => config('wabot.group_id'),
            'message' =>  $link."\n".$msg
        ])->json();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->sendWaGroup('Check Image sedang di proses');
        $this->check();
        $this->sendWaGroup('Check Image sukses di proses');
        $this->info('Finish The commands.');
    }
}
