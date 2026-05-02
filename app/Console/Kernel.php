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
        $schedule->command('app:cancel-stripe-pending-orders')
            ->timezone('Asia/Kolkata')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground(); // ✅ add this so it doesn't block other scheduled tasks
        ;
    }

    // protected function schedule(Schedule $schedule): void
    // {
    //     $schedule->command('test:cron')
    //         ->timezone('Asia/Kolkata')
    //         ->everyFiveMinutes()
    //         ->withoutOverlapping()
    //     ;
    // }

    /**
     * Register the commands for the application.
    */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
