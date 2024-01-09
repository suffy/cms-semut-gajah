<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyAppLogs extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'applogs:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Application logs ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {   
        @unlink(storage_path('logs/laravel.log'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->get();

        $this->info('Delete App logs daily successfully');
    }
}
