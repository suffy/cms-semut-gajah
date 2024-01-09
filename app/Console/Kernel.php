<?php

namespace App\Console;

use App\Console\Commands\DailySalesman;
use App\Console\Commands\DailySite;
use App\Console\Commands\DailySubscribe;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\BroadcastWA;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DailySubscribe::class,
        DailySite::class,
        DailySalesman::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function scheduleTimezone()
    {
        return 'Asia/Jakarta';
    }

    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();                     // Tidak Kepakai
        $schedule->command('convert:daily')->daily();
        $schedule->command('customer:daily')->daily('1.00');
        $schedule->command('subscribe:daily')->daily();
        $schedule->command('site:daily')->daily();
        $schedule->command('salesman:daily')->dailyAt('2.00');
        // $schedule->command('notifVerif:daily')->dailyAt('8.00');     // Tidak Kepakai
        $schedule->command('backup:run --only-db')->dailyAt('4.00');
        $schedule->command('deleteBackup:daily')->dailyAt('5.00');
        $schedule->command('logs:daily')->dailyAt('21.00');
        $schedule->command('applogs:daily')->dailyAt('00.00');
        // $schedule->command('customerbinaan:daily')->daily();         // Tidak Kepakai
        // $schedule->command('completecomplaint:daily')->daily();      // Tidak Kepakai
        $schedule->command('completeorder:daily')->daily();
        $schedule->command('product:daily')->daily();
        $schedule->command('productSite:daily')->dailyAt('3.00');
        $schedule->command('promo:daily')->daily();
        // $schedule->command('stock:daily')->daily();                  // Tidak Kepakai        
        // $schedule->command('cod:daily')->hourly();                   // Tidak Kepakai
        $schedule->command('orderstatus:hourly')->hourly();
        // $schedule->command('creditlimit:month')->dailyAt('1.00');    // Tidak Kepakai
        $schedule->command('creditlimit:month')->weekly();
        $schedule->command('hourly:missionTasks')->hourly();
        // $schedule->command('remindertransfer:everyminute')->everyMinute(); // Tidak Kepakai
        // $schedule->command('queue:work')->hourly();

        // start send broadcast
        $schedule->call(function () {

            // ambil data broadcast dengan kondisi schedule
            $broadcasts = BroadcastWa::whereDate('schedule', Carbon::today())->get();
            // ambil data tanggal dan jam sekarang
            $date       = Carbon::now()->format('Y-m-d H:i:00');

            // loop
            foreach ($broadcasts as $row) {
                // jika date sama dengan schedule panggil job send:broadcast
                if ($date == $row->schedule) {
                    Artisan::call("send:broadcast {$row->id}");
                }
            }
        })
            ->hourly();
        // end
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
