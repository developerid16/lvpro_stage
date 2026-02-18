<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// import your command class
use App\Console\Commands\DisableExpiredLocations;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // list command classes here
        DisableExpiredLocations::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('reward:expired')->dailyAt('23:55');
        $schedule->command('delete:oldreport')->dailyAt('00:01');
        $schedule->command('customer:report')->dailyAt('01:00');
        $schedule->command('expired:notification')->dailyAt('00:05');
        $schedule->command('broadcast:notification')->everyMinute();
        $schedule->command('spent:reset')->yearlyOn(9, 1, '00:02');
        $schedule->command('key:expiry')->yearlyOn(12, 1, '00:02');
        $schedule->command('locations:disable-expired')->daily();

        // $schedule->command('safra:basic-detail-modified')->everyMinute();

        // $schedule->command('safra:basic-detail-ig')->everyMinute();

        // $schedule->command('safra:latest-transaction')->everyMinute();

        // $schedule->command('safra:customer-zone')->everyMinute();

        // $schedule->command('master:sync-all')->dailyAt('00:00'); // call manully php artisan master:sync-all

        $schedule->command('master:sync-gender')->everyMinute();
        $schedule->command('master:sync-marital-status')->everyMinute();
        $schedule->command('master:sync-card-type')->everyMinute();
        $schedule->command('master:sync-dependent-type')->everyMinute();
        $schedule->command('master:sync-zone')->everyMinute();
        $schedule->command('master:sync-membership-code')->everyMinute();
        $schedule->command('master:sync-interest-group')->everyMinute();
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
