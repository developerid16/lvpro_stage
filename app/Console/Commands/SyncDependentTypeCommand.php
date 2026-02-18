<?php

namespace App\Console\Commands;

use App\Models\Master\MasterDependentType;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncDependentTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'master:sync-dependent-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync dependent type master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService): int
    {
        try {
            $this->info('Syncing Dependent Type...');

            $records = $masterService->getDependentType();
            $items   = $records['Items'] ?? [];

            foreach ($items as $item) {
                $record = MasterDependentType::updateOrCreate(
                    ['string_value' => $item['StringValue']],
                    ['label'        => $item['Label'],'updated_at' => now(),]
                );
                $record->touch();
            }

            $this->info('Dependent Type synced: ' . count($items) . ' records.');
            Log::info('[SyncDependentTypeCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Dependent Type sync failed: ' . $e->getMessage());
            Log::error('[SyncDependentTypeCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
