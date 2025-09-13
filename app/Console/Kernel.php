<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process reward cycles every minute
        $schedule->call(function(){
            \App\Services\RewardService::processRewardCycles();
        })->everyMinute();

        // Process Top10 100k grants every minute (internal logic enforces 30min interval)
        $schedule->call(function(){
            \App\Services\RewardService::processTopTenGrant();
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
