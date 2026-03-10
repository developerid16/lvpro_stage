<?php

namespace App\Console\Commands;

use App\Models\API\GetSRPMerchandiseItemList;
use App\Services\SafraAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SRPMerchandiseItemList extends Command
{
    protected $signature   = 'ax6:get-srp-merchandise-item-list';
    protected $description = 'Sync Srp merchandise Item list from API into database';

    public function handle(SafraAPIService $safraAPIService): int
    {
        try {
            $this->info('Syncing SRP merchandise item list...');

            $request = [
                "ItemID"   => "",
                "ItemName" => ""
            ];

            // 👉 API call
            // $response = $safraAPIService->GetSRPMerchandiseItemList($request);
            $response = $safraAPIService->getMerchandiseItemList($request);
            
            // 👉 Extract items safely
            $items = $response['Items'] ?? [];

            if (empty($items)) {
                $this->warn('No items found from API.');
                return self::SUCCESS;
            }
            foreach ($items as $item) {

                GetSRPMerchandiseItemList::updateOrCreate(
                    ['item_id' => $item['ITEMID']],
                    [
                        // 'item_name' => $item['ITEMNAME'] ?? null,
                        'item_name' => $item['SEARCHNAME'] ?? null,
                        'json'      => json_encode($item), // optional full data
                        'updated_at'=> now(),
                    ]
                );
            }

            Log::info('[GetMerchandiseItemList] Synced ' . count($items) . ' records.');

            return self::SUCCESS;

        } catch (\Throwable $e) {

            Log::error('[GetMerchandiseItemList] Failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
