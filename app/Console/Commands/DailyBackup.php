<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Log;

class DailyBackup extends Command
{
    protected $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate Backup SQL to Google Drive';

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
        // memanggil command backup dari library spatie/laravel-backup
        \Artisan::call('backup:run --only-db');
        
        // simpan dalam logs
        $this->log->updateOrCreate(
            ['activity'     => 'Backup sql file to drive successfully'],
            ['log_time'     => Carbon::now(),
            'table_name'    => null,
            'column_name'   => null, 
            'from_user'     => null,
            'to_user'       => null,
            'data_content'  => null,
            'platform'      => 'web',
            'created_at'    => Carbon::now()]
        );
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get();

        $this->info('Backup sql file daily successfully');
    }
}
