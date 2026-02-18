<?php

namespace App\Console\Commands;

use App\Models\Master\MasterGender;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGenderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'master:sync-gender';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync gender master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService)
    {
        try {
            $this->info('Syncing Gender...');

            $records = $masterService->getGender();
            $items   = $records['OptionColl'] ?? [];

            foreach ($items as $item) {
                $record = MasterGender::updateOrCreate(
                    ['string_value' => $item['StringValue']],
                    [
                                'label'        => $item['Label'],
                                'updated_at' => now(),
                            ]
                );
                $record->touch();
            }

            $this->info('Gender synced: ' . count($items) . ' records.');
            Log::info('[SyncGenderCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Gender sync failed: ' . $e->getMessage());
            Log::error('[SyncGenderCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
