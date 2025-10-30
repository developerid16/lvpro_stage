<?php

namespace App\Exports;

use App\Models\ReportJob;
use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Illuminate\Support\Facades\Cache;

class SaleReportExport implements FromQuery, ShouldQueue, WithMapping, WithHeadings,WithCustomChunkSize
{
    use Exportable;

    public $qdata = [];
    protected $totalChunks;


    public function __construct($qdata)
    {
        $this->qdata = $qdata;
         // Calculate total chunks based on known record count and chunk size
         $recordCount = 309664; // replace this with dynamic count if available
         $this->totalChunks = ceil($recordCount / $this->chunkSize());
    }
    public function query()
    {
        $sd = $this->qdata['start_date'];
        $ps = $this->qdata['product'];
        $bs = $this->qdata['brand'];
        $ed = $this->qdata['end_date'];
        $data = Sale::query()->with('brand:id,sku,brand_name,product_name');
        if ($sd && $ed) {
            $data = $data->whereBetween('date', [$sd . ' 00:00:00', $ed . ' 23:23:59']);
        }

        if ($ps) {
            $data = $data->where('sku', $ps);
        }
        if ($bs) {
            $data = $data->where('brand_code', $bs);
        }

        return $data;
    }
    public function failed(\Throwable $exception): void
    {

        ReportJob::where('udid', $this->qdata['udid'])->update([
            'status' => "Failed"
        ]);
        // handle failed export
    }
    public function map($sale): array
    {
        return [
            $sale->pos,
            $sale->loyalty,
            $sale->location,
            $sale->storage_location,
            $sale->brand->brand_name ?? '',
            $sale->brand->product_name ?? '',
            $sale->date->format(config('shilla.date-format')),
            $sale->system_time,
            $sale->ref,
            $sale->sku,
            numberFormat($sale->sale_amount, true),
            $sale->quantity_purchased,
            number_format($sale->key_earn),
        ];
    }
    public function headings(): array
    {
        return [
            "POS",
            "Loyalty",
            "Location",
            "Storage Location",
            "Brand",
            "Product",
            "Date",
            "Time",
            "Ref",
            "SKU",
            "Sale Amount",
            "Quantity Issuance",
            "Key Earn",
        ];
    }

    public function batchSize(): int
    {
        return 50000;
    }
    
    public function chunkSize(): int
    {
        return 10000;
    }
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\BeforeExport::class => function () {
                Cache::put('export_progress', 0);
                \Log::info('export_progress 0');
            },
            \Maatwebsite\Excel\Events\AfterChunk::class => function () {
                $processedChunks = Cache::get('export_progress', 0) + 1;
                Cache::put('export_progress', $processedChunks);
                \Log::info("export_progress $processedChunks");

                // Calculate progress percentage
                $percentage = ($processedChunks / $this->totalChunks) * 100;
                Cache::put('export_percentage', $percentage);
                \Log::info("export_percentage $percentage");
            },
            \Maatwebsite\Excel\Events\AfterExport::class => function () {
                Cache::forget('export_progress');
                Cache::forget('export_percentage');
                \Log::info("export_percentage Done");
            }
        ];
    }
}
