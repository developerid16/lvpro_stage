<?php

namespace App\Imports;

use App\Models\Sale;
use App\Models\SaleBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SalesImport  implements ToModel, ShouldQueue, WithChunkReading, WithEvents, WithStartRow

{

    public $batchId;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function model(array $row)
    {
         return new Sale([


            "date" => Carbon::createFromFormat('j/n/Y', $row['0'])->format('Y-m-d'),
            "batch_id" => $this->batchId,
            "system_time" => $row['1'],
            "loyalty" => $row['2'],
            "location" => $row['3'],
            "storage_location" => $row['4'],
            "pos" => $row['5'],
            "ref" => $row['6'],
            "sku" => $row['7'],
            "sale_amount" => $row['8'],
            'quantity_purchased' => $row['9']

        ]);
    }
    public function registerEvents(): array
    {
        $bid = $this->batchId;
        return [
            ImportFailed::class => function (ImportFailed $event) use ($bid) {
                SaleBatch::find($bid)->update([
                    'status' => "Failed"
                ]);
                Log::error($event);
            },

        ];
    }
    public function startRow(): int
    {
        return 2;
    }
    public function chunkSize(): int
    {
        return 1000;
    }
}
