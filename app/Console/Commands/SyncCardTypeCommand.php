<?php

namespace App\Console\Commands;

use App\Models\Master\MasterCardType;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCardTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'master:sync-card-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync card type master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService): int
    {
        try {
            $this->info('Syncing Card Type...');

            $records = $masterService->getCardType();
            $items   = $records['Items'] ?? [];

            foreach ($items as $item) {
                $record = MasterCardType::updateOrCreate(
                    ['card_type'        => $item['CardType']],
                    ['card_description' => $item['CardDescription'],'updated_at' => now(),]
                );
                $record->touch();
            }

            $this->info('Card Type synced: ' . count($items) . ' records.');
            Log::info('[SyncCardTypeCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Card Type sync failed: ' . $e->getMessage());
            Log::error('[SyncCardTypeCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
