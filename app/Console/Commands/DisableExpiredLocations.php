<?php

namespace App\Console\Commands;

use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisableExpiredLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:disable-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable all locations whose end_date is expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // find locations that should be disabled
        $locations = Location::whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->where('status', '!=', 'Disabled')
            ->get();

        if ($locations->count() === 0) {
            $this->info('No expired locations found.');
            return;
        }

        foreach ($locations as $location) {
            $location->update([
                'status' => 'Disabled'
            ]);
        }

        $this->info($locations->count() . ' expired locations have been disabled.');
    }
}
