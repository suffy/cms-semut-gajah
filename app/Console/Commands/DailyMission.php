<?php

namespace App\Console\Commands;

use App\Log;
use App\Mission;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyMission extends Command
{
    protected $mission, $log;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mission:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automate deactivate missions if it exceeds the valid date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Mission $mission, Log $log)
    {
        parent::__construct();
        $this->mission  = $mission;
        $this->logs     = $log;
    }

    // check valid date
    public function get()
    {
        // ambil data mission yang masih aktif
        $missions = $this->mission->where('status', 1)->get();
        foreach($missions as $mission) 
        {
            // jika sudah melewati data dari tanggal end
            if($mission->end_date < Carbon::now()->format('Y-m-d')) {
                // update status
                $mission->status = 0;
                $mission->save();
                
                // log
                $logs   = $this->logs
                        ->updateOrCreate(
                            ['table_id'      => $mission->id],
                            ['log_time'      => Carbon::now(),
                            'activity'      => 'Change mission status to ' . $mission->status,
                            'table_id'      => $mission->id,
                            'data_content'  => $mission,
                            'table_name'    => 'missions',
                            'column_name'   => 'missions.status, missions.start_date, missions.end_date',
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

        $this->info('Deactivate missions successfully');
    }
}
