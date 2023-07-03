<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

        \App\Console\Commands\ServiceScheduleCommand::class,
        \App\Console\Commands\DbClearCommand::class,
        \App\Console\Commands\DBSeedCommand::class,
        \App\Console\Commands\GenerateSwaggerCommand::class,
        \App\Console\Commands\provideronline::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('cronjob:services')->everyMinute();
        $schedule->command('cronjob:providerstatus')->everyMinute();
        $schedule->command('cronjob:demodata')->dailyAt('6:00');
        $schedule->command('cronjob:demodata')->dailyAt('6:00');
        $schedule->command('queue:work')->withoutOverlapping();

        //$schedule->command('cronjob:providers')->everyFiveMinutes();
    }



}
