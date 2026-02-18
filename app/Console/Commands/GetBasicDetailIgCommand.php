<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SafraService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class GetBasicDetailIgCommand extends Command
{
    protected $signature = 'safra:basic-detail-ig {last_modified?} {limit?}';
    protected $description = 'Fetch IG Basic Detail';

    private $lastModified;
    private $limit;

    public function __construct()
    {
        parent::__construct();
        $this->lastModified = Config::get('safra.last_modified', '2025-09-17');
        $this->limit = Config::get('safra.limit', 5);
    }

    public function handle(SafraService $safraService)
    {
        // Arguments override config if provided
        $lastModified = $this->argument('last_modified') ?? $this->lastModified;
        $limit = $this->argument('limit') ?? $this->limit;

        try {
            $records = $safraService->getIGbasicdetail($lastModified, $limit);
            
            Log::info("{$this->description}: Last Modified: {$lastModified}, Limit: {$limit}, Records Count: " . count($records));

            $this->info("Fetched: " . count($records) . " records");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
        }
    }
}
