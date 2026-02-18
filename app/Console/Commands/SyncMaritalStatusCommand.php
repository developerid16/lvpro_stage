<?php

namespace App\Console\Commands;

use App\Models\Master\MasterMaritalStatus;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMaritalStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'master:sync-marital-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync marital status master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService): int
    {
        try {
            $this->info('Syncing Marital Status...');

            $records = $masterService->getMaritalStatus();
            $items   = $records['Items'] ?? [];

            foreach ($items as $item) {
                $record = MasterMaritalStatus::updateOrCreate(
                    ['string_value' => $item['StringValue']],
                    ['label'        => $item['Label'],'updated_at' => now(),]
                );
                $record->touch();
            }

            $this->info('Marital Status synced: ' . count($items) . ' records.');
            Log::info('[SyncMaritalStatusCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Marital Status sync failed: ' . $e->getMessage());
            Log::error('[SyncMaritalStatusCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
