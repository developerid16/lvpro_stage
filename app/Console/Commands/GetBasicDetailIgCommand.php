<?php

namespace App\Console\Commands;

use App\Models\API\MemberBasicDetailIG;
use App\Services\SafraAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GetBasicDetailIgCommand extends Command
{
    protected $signature   = 'member:basic-detail-ig';
    protected $description = 'Fetch IG Basic Detail';

    public function handle(SafraAPIService $safraAPIService): int
    {
        // ✅ DB mathi LastModified levu
        $lastModified = DB::table('api_sync_logs')
            ->where('key', 'ig_basic_detail_last_modified')
            ->value('value') ?? Config::get('safra.last_modified');

        $limit = Config::get('safra.limit', 100);

        try {

            $this->info("Start Fetching IG | LastModified: {$lastModified}");

            $previousLastModified = null;

            do {

                $this->info("Calling IG API | LastModified: {$lastModified}");

                // 🔁 Retry logic
                $retry = 0;
                $maxRetry = 3;

                do {
                    try {
                        $response = $safraAPIService->getIGbasicdetail($lastModified, $limit);
                        break;
                    } catch (\Exception $e) {
                        $retry++;
                        Log::warning("IG Retry {$retry} | Error: " . $e->getMessage());
                        sleep(2);
                    }
                } while ($retry < $maxRetry);

                if ($retry == $maxRetry) {
                    throw new \Exception("IG API failed after {$maxRetry} retries");
                }
                
                $records = $response ?? [];
                $count   = count($records);

                if ($count === 0) {
                    $this->warn('No more IG records found.');
                    break;
                }

                foreach ($records as $item) {

                    // ⚠️ safety check
                    if (empty($item['Token'])) {
                        continue;
                    }

                    MemberBasicDetailIG::updateOrCreate(
                        [
                            'token' => $item['Token'],
                            'InterestGroupName' => $item['InterestGroupName'] ?? ''
                        ],
                        [
                            'ExpiryDate'            => $item['ExpiryDate'] ?? null,
                            'InterestGroupMainName' => $item['InterestGroupMainName'] ?? null,
                            'json'                  => json_encode($item),
                        ]
                    );
                }

                // 🔥 Last record thi next LastModified
                $lastRecord = end($records);

                if (!empty($lastRecord['ModifiedDateTime'])) {
                    $lastModified = Carbon::parse($lastRecord['ModifiedDateTime'])
                        ->addSecond() // duplicate avoid
                        ->format('Y-m-d H:i:s');
                }

                // ⚠️ Infinite loop protection
                if ($previousLastModified === $lastModified) {
                    $this->warn('Same LastModified detected → breaking loop');
                    break;
                }

                $previousLastModified = $lastModified;

                $this->info("Fetched: {$count} | Next LastModified: {$lastModified}");

                Log::info("[{$this->description}] Records: {$count}, Next LastModified: {$lastModified}");

                // 🔥 API overload avoid
                usleep(500000); // 0.5 sec delay

            } while ($count == $limit);

            // ✅ FINAL: LastModified DB ma save
            DB::table('api_sync_logs')->updateOrInsert(
                ['key' => 'ig_basic_detail_last_modified'],
                [
                    'value' => $lastModified,
                    'updated_at' => now()
                ]
            );

            $this->info("IG Sync Completed. Saved LastModified: {$lastModified}");

            return self::SUCCESS;

        } catch (\Exception $e) {

            $this->error($e->getMessage());

            Log::error("[{$this->description}] Failed: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
