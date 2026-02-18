<?php

namespace App\Console\Commands;

use App\Models\Master\MasterMembershipCode;
use App\Services\MasterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMembershipCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'master:sync-membership-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync membership code master data from API into database';

    /**
     * Execute the console command.
     */
    public function handle(MasterService $masterService): int
    {
        try {
            $this->info('Syncing Membership Code...');

            $records = $masterService->getMembershipCode();
            $items   = $records['membership_detail'] ?? [];

            foreach ($items as $item) {
                $record = MasterMembershipCode::updateOrCreate(
                    ['membershiptype_id' => $item['membershiptype_id']],
                    ['description'       => $item['description'],'updated_at' => now(),]
                );
                $record->touch();
            }

            $this->info('Membership Code synced: ' . count($items) . ' records.');
            Log::info('[SyncMembershipCodeCommand] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Membership Code sync failed: ' . $e->getMessage());
            Log::error('[SyncMembershipCodeCommand] Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
