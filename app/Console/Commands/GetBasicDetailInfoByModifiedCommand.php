<?php

namespace App\Console\Commands;

use App\Models\API\MemberBasicDetailsModified;
use App\Services\SafraAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GetBasicDetailInfoByModifiedCommand extends Command
{
    protected $signature   = 'member:basic-detail-modified';
    protected $description = 'Fetch Basic Detail Info By Modified Date';

    public function handle(SafraAPIService $safraAPIService): int
    {
        // ✅ DB mathi last modified levu (fallback config)
        $lastModified = DB::table('api_sync_logs')
            ->where('key', 'basic_detail_last_modified')
            ->value('value') ?? Config::get('safra.last_modified');

        $limit = Config::get('safra.limit', 100);

        try {

            $this->info("Start Fetching | LastModified: {$lastModified}");

            $previousLastModified = null;

            do {

                $this->info("Calling API | LastModified: {$lastModified}");

                // 🔁 Retry logic
                $retry = 0;
                $maxRetry = 3;

                do {
                    try {
                        $records = $safraAPIService->basicDetailInfoModified($lastModified, $limit);
                        break;
                    } catch (\Exception $e) {
                        $retry++;
                        Log::warning("Retry {$retry} | Error: " . $e->getMessage());
                        sleep(2);
                    }
                } while ($retry < $maxRetry);

                if ($retry == $maxRetry) {
                    throw new \Exception("API failed after {$maxRetry} retries");
                }

                $records = is_array($records) ? $records : [];
                $count   = count($records);

                if ($count === 0) {
                    $this->warn('No more records found.');
                    break;
                }

                foreach ($records as $item) {
                    MemberBasicDetailsModified::updateOrCreate(
                        ['token' => $item['Token']],
                        [
                            'BirthMonth'            => $item['BirthMonth'] ?? null,
                            'BirthYear'             => $item['BirthYear'] ?? null,
                            'MemberCategory'        => $item['MemberCategory'] ?? null,
                            'MembershipTypeCode'    => $item['MembershipTypeCode'] ?? null,
                            'SafraMembershipExpiry' => $item['SafraMembershipExpiry'] ?? null,
                            'json'                  => json_encode($item),
                        ]
                    );
                }

                // 🔥 Last record thi next LastModified
                $lastRecord = end($records);

                if (!empty($lastRecord['ModifiedDateTime'])) {
                    $lastModified = $lastRecord['ModifiedDateTime'];

                    // ✅ duplicate avoid (1 second add)
                    $lastModified = Carbon::parse($lastModified)
                        ->addSecond()
                        ->format('Y-m-d H:i:s');
                }

                // ⚠️ infinite loop protection
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

            // ✅ FINAL: DB ma save karo (MOST IMPORTANT)
            DB::table('api_sync_logs')->updateOrInsert(
                ['key' => 'basic_detail_last_modified'],
                [
                    'value' => $lastModified,
                    'updated_at' => now()
                ]
            );

            $this->info("Process Completed. Saved LastModified: {$lastModified}");

            return self::SUCCESS;

        } catch (\Exception $e) {

            $this->error($e->getMessage());

            Log::error("[{$this->description}] Failed: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
