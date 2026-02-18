<?php

namespace App\Console\Commands;

use App\Models\Master\MasterInterestGroup;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncInterestGroupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'master:sync-interest-group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync interest group master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService): int
    {
        try {
            $this->info('Syncing Interest Group...');

            $records = $masterService->getInterestGroup();
            $items   = $records['InterestClub'] ?? [];

            foreach ($items as $item) {
                $record = MasterInterestGroup::updateOrCreate(
                    ['interest_group_id' => $item['interest_group_id']],
                    [
                        'interest_group_main_id'   => $item['interest_group_main_id'],
                        'interest_group_main_name' => $item['interest_group_main_name'],
                        'interest_group_name'      => $item['interest_group_name'],
                        'ig_type'                  => $item['ig_type'] ?? null,
                        'updated_at' => now(),
                    ]
                );
                $record->touch();
            }

            $this->info('Interest Group synced: ' . count($items) . ' records.');
            Log::info('[SyncInterestGroupCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Interest Group sync failed: ' . $e->getMessage());
            Log::error('[SyncInterestGroupCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
