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
        // Refresh leaderboard setiap 15 menit untuk update ranking
        $schedule->command('leaderboard:refresh')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Reset monthly leaderboard (jika diperlukan untuk kompetisi bulanan)
        $schedule->command('leaderboard:reset')
            ->monthlyOn(1, '00:59')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
