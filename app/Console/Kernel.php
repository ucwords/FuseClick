<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Filesystem\Filesystem;
use DB;

use App\Console\Commands\OfferSync;
use App\Console\Commands\AutoSync;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        OfferSync::class,
        AutoSync::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $file = new Filesystem;
            $file->cleanDirectory('storage/app');
        })->daily();

        //08,13,18,23,28,33,38,43,48,53,58,03 * * * * *
        $schedule->command('auto:sync vinsmok')->cron('*/15 * * * *')->runInBackground()->sendOutputTo(storage_path('logs/vinsmok.log'))->withoutOverlapping();
        //$schedule->command('auto:sync mobimelon')->cron('*/15 * * * * *')->runInBackground()->sendOutputTo(storage_path('logs/mobimelon.log'))->withoutOverlapping();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
