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
        // $schedule->command('broadcast:notification')->everyMinute();
        $schedule->command('spent:reset')->yearlyOn(9, 1, '00:02');
        $schedule->command('key:expiry')->yearlyOn(12, 1, '00:02');
        $schedule->command('locations:disable-expired')->daily();

        $schedule->command('member:basic-detail-modified')->daily();

        $schedule->command('member:basic-detail-ig')->daily();

        $schedule->command('member:latest-transaction')->daily();

        $schedule->command('member:customer-zone')->daily();

        // $schedule->command('master:sync-all')->dailyAt('00:00'); // call manully php artisan master:sync-all

        $schedule->command('master:sync-gender')->daily();
        $schedule->command('master:sync-marital-status')->daily();
        $schedule->command('master:sync-card-type')->daily();
        $schedule->command('master:sync-dependent-type')->daily();
        $schedule->command('master:sync-zone')->daily();
        $schedule->command('master:sync-membership-code')->daily();
        $schedule->command('master:sync-interest-group')->daily();
        $schedule->command('voucher:generate-next-year')->yearlyOn(12, 31, '23:59');
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
