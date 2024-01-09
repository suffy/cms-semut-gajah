<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Log;

class DailyDeleteBackup extends Command
{
    protected $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deleteBackup:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate delete backup file from google drive 2 weeks ago';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Log $log)
    {
        parent::__construct();
        $this->log = $log;
    }

    public function get()
    {
        // ambil data yang ada dalam google drive
        $storage = Storage::disk('google')->listContents(config('drive.drive_folder_id'));

        $this->check($storage);
    }

    public function check($storage)
    {
        // ambil tanggal dari 2 minggu lalu
        $date = Carbon::now()->subWeeks(2)->format('Y-m-d-h-i-s');
        foreach($storage as $row) {
            // jika tanggal lebih dari tanggal file gdrive
            if($date > $row['filename']) {
                // hapus file
                $data = Storage::disk('google')->delete($row['path']);

                // jika hapus file
                if($data) {
                    // simpan dalam logs table
                    $this->log->updateOrCreate(
                        ['activity'     => 'Delete sql backup file from drive successfully'],
                        ['log_time'     => Carbon::now(),
                        'table_name'    => $row['name'],
                        'column_name'   => null, 
                        'from_user'     => null,
                        'to_user'       => null,
                        'data_content'  => null,
                        'platform'      => 'web',
                        'created_at'    => Carbon::now()]
                    );
                }
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

        $this->info('Delete backup file from drive 2 weeks ago successfully');
        // return 0;
    }
}
