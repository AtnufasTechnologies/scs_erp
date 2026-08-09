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
        // $schedule->command('inspire')->hourly();
        $schedule->command('faculty:sync')->dailyAt('02:00');
        $schedule->command('attendance:mark-absent')->everyTenMinutes();
        $schedule->command('quiz:auto-submit-expired')->everyMinute();
        $schedule->command('generate-daily-quote')->dailyAt('15:02')->timezone('Asia/Kolkata');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
