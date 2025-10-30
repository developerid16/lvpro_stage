<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Sale;
use App\Jobs\NotifyUserOfCompletedExportReport;

class SalesReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $qdata = [];
    /**
     * Create a new job instance.
     */
    public function __construct($qdata)
    {
        $this->qdata = $qdata;
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        //

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

       
        $filePath = public_path('report/'. $this->qdata['udid'] .'.csv');

        // Open the file in write mode
        $file = fopen($filePath, 'w');

        // Add headers to the CSV file
        fputcsv($file, [  "POS",
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
        "Key Earn"]); // Replace with your actual column names

        // Fetch data in chunks and write to file
        $data->chunk(50000, function ($rows) use ($file) {
            foreach ($rows as $sale) {
                // Map the row data to an array for CSV export
                fputcsv($file, [
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
                ]);
            }
        });

        // Close the file
        fclose($file);

        NotifyUserOfCompletedExportReport::dispatch($this->qdata);
    }
}
