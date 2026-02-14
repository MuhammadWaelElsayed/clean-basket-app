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
        $schedule->command('packages:renew')->daily();
        $schedule->command('packages:notify-expiring')->dailyAt('09:00');
        $schedule->command('release:hold-transactions')->everyThirtyMinutes();
        $schedule->command('carts:notify-abandoned')->dailyAt('10:00');

        $schedule->command('drivers:expire-requests')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/driver-requests.log'))
            ->runInBackground();

        // 🧪 للاختبار فقط - احذف هذا السطر بعد التجربة
        // $schedule->command('carts:notify-abandoned')->everyMinute();
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
