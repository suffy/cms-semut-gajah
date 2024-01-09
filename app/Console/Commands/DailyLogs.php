<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Log;

class DailyLogs extends Command
{
    protected $log;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete logs older than 7 days';

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
        // ambil data lebih dari seminggu yang lalu dan hapus
        $delete_log = $this->log->whereDate('created_at', '<', Carbon::now()->subDays(7))->delete();

        // logs
        $logs = $this->log;

        $logs->log_time     = Carbon::now();
        $logs->activity     = "Delete log older than " . Carbon::now()->subDays(7);
        $logs->table_name   = 'logs';
        $logs->platform     = "web";

        $logs->save();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get();

        $this->info('Delete logs daily older than 7 days successfully');
    }
}
