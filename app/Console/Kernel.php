<?php

namespace App\Console;
use App\Jobs\DeleteOldTrashedPosts;
use App\Jobs\FetchRandomUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
    // Run every minute for testing purposes
    $schedule->job(new DeleteOldTrashedPosts)->everyMinute();
    $schedule->job(new FetchRandomUser)->everyMinute();
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