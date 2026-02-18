<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SafraService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GetBasicDetailInfoByModifiedCommand extends Command
{
    protected $signature = 'safra:basic-detail-modified';
    protected $description = 'Fetch Basic Detail Info By Modified Date';

    private $lastModified;
    private $limit;

    public function __construct()
    {
        parent::__construct();

        $this->lastModified = Config::get('safra.last_modified');
        $this->limit = Config::get('safra.limit');
    }

    public function handle(SafraService $safraService)
    {
        try {
            $records = $safraService->basicDetailInfoModified(
                $this->lastModified,
                $this->limit
            );

            Log::info("{$this->description}: Last Modified: {$this->lastModified}, Limit: {$this->limit}, Records Count: " . count($records));

            $this->info("Fetched: " . count($records) . " records");
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
