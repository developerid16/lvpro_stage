<?php

namespace App\Console\Commands;

use App\Models\API\MemberBasicDetailIG;
use App\Services\SafraServiceAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GetBasicDetailIgCommand extends Command
{
    protected $signature   = 'member:basic-detail-ig';
    protected $description = 'Fetch IG Basic Detail';

    public function handle(SafraServiceAPI $safraServiceAPI): int
    {
        $lastModified = Config::get('safra.last_modified');
        $limit        = Config::get('safra.limit');

        try {
            $this->info("Fetching IG Basic Detail | Last Modified: {$lastModified} | Limit: {$limit}");

            $records = $safraServiceAPI->getIGbasicdetail($lastModified, $limit);
            $records = is_array($records) ? $records : [];

            if (empty($records)) {
                $this->warn('No records found.');
                Log::warning("[{$this->description}] No records found. Last Modified: {$lastModified}, Limit: {$limit}");
                return self::SUCCESS;
            }

            foreach ($records as $item) {
                $record = MemberBasicDetailIG::updateOrCreate(
                    ['token' => $item['Token']],
                    [
                        'ExpiryDate'            => $item['ExpiryDate']            ?? null,
                        'InterestGroupMainName' => $item['InterestGroupMainName'] ?? null,
                        'InterestGroupName'     => $item['InterestGroupName']     ?? null,
                        'json'                  => json_encode($item),
                    ]
                );
                $record->touch();
            }

            $this->info('Fetched: ' . count($records) . ' records.');
            Log::info("[{$this->description}] Last Modified: {$lastModified}, Limit: {$limit}, Records: " . count($records));

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error("[{$this->description}] Failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}