<?php

namespace App\Console\Commands;

use App\Models\Master\MasterZone;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncZoneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'master:sync-zone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync zone master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService): int
    {
        try {
            $this->info('Syncing Zone...');

            $records = $masterService->getZone();
            $items   = $records['Items'] ?? [];

            foreach ($items as $item) {
                $record = MasterZone::updateOrCreate(
                    ['zone_name' => $item['ZoneName']],
                    ['zone_code' => $item['ZoneCode'], 'updated_at' => now(),]
                );
                $record->touch();
            }

            $this->info('Zone synced: ' . count($items) . ' records.');
            Log::info('[SyncZoneCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Zone sync failed: ' . $e->getMessage());
            Log::error('[SyncZoneCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
