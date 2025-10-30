<?php

namespace App\Jobs;

use App\Exports\SalesCSVError;
use App\Http\Controllers\Admin\SalesController;
use App\Models\AppUser;
use App\Models\RefundSale;
use App\Models\Sale;
use App\Models\SaleBatch;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class ImportSalesOld implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public $batchId = "";
    public $users = [];
    public $sales = [];
    public $newSales = [];
    public $refundSale = [];
    public $newRefundSale = [];

    public function __construct($batchId)
    {
        //
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $delimiter = ',';
        // $batch = SaleBatch::find($this->batchId);

        $filePath = public_path('report') . '/' . 'fdt5.csv';

        $errors = [];
        //
        $header = null;
        $expiredData = [];
        $i = 0;
        DB::beginTransaction();

        if (($handle = fopen($filePath, 'r')) !== false) {

            $this->users = AppUser::get(['unique_id', 'id', 'expiry_date','customer_id'])->keyBy('customer_id')->all();
 
            $this->sales = Sale::groupBy('ref')->get(['ref', 'id'])->keyBy('ref')->all();
            $this->newSales = $this->sales; 

            $this->refundSale = RefundSale::groupBy('ref')->get(['ref', 'id'])->keyBy('ref')->all();
            $this->newRefundSale = $this->refundSale;

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

                if ($i == 0) {
                    $header = $row;
                    Log::info('new header ' . json_encode($header));
                } else {


                    if ($row[10] > 0 && $row[9] > 0 && $row[2]) {
                        // sale transaction
                        $row[9] = Str::remove(',', $row[9]);
                        $row[10] = Str::remove(',', $row[10]);
                        $res = $this->saleCreate($row);
                        if ($res['status'] == false) {
                            $temp = $row;
                            $temp[12] = $res['message'];
                            $errors[] = $temp;
                        }
                    } elseif ($row[10] < 0 && $row[9] < 0 && $row[2]) {
                        // void transaction
                        $row[9] = Str::remove(',', $row[9]);
                        $row[10] = Str::remove(',', $row[10]);
                        $row[10] = abs($row[10]);
                        $row[9] = abs($row[9]);

                        $res = $this->voidTransaction($row);
                        if ($res['status'] == false) {
                            $temp = $row;
                            $temp[12] = $res['message'];
                            $errors[] = $temp;
                        }
                    } else {
                        // $temp = $row;
                        // $temp[12] = "Qty and Amount is different from each other.";
                        // $errors[] = $temp;
                        $row[9] = Str::remove(',', $row[9]);
                        $row[10] = Str::remove(',', $row[10]);
                        $res = $this->saleCreate($row);
                        if ($res['status'] == false) {
                            $temp = $row;
                            $temp[12] = $res['message'];
                            $errors[] = $temp;
                        }
                    }
                }

                $i++;
            }
            fclose($handle);
        }

        $errors =  [];

        Log::info($errors);
        if (count($errors) > 0) {
            // lets create CSV because of errors and revert everything

            Excel::store(new SalesCSVError($errors), $this->batchId . '-error.csv', null, ExcelExcel::CSV);
            // return (new SalesCSVError($errors))->download($this->batchId . '-error.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
            DB::rollback();

            $batch->update(['status' => 'failed']);
        } else {
            // pass all now update uses key
            // $batch->update(['status' => 'Success']);

            $items = Sale::where('batch_id', $this->batchId)->whereDate('date', '>=', '2022-08-01')->selectRaw('*,sum(sale_amount) as sum')
                ->groupBy('ref')->get();

            // TODO:OLD
            foreach ($items as $item) {

                SalesController::updateMileStoneAssign($item, (float) $item['sum']);
            }
            $items = RefundSale::where('batch_id', $this->batchId)->whereDate('date', '>=', '2022-08-01')->selectRaw('*,sum(sale_amount) as sum')
                ->groupBy('ref')->get();
            $rows = [];
            foreach ($items as $item) {

                $key = SalesController::refundSaleAssign($item, floor((float) $item['sum']));
                $item->key_earn = $key;
                $item->save();

             
            }
            DB::commit();
            Log::info("Successfully updated");
            return;
         
        }


        


        Log::info("Successfully updated");
    }
    private function saleCreate($row, $needcheck = true)
    {
        try {
            //code...
            $date = Carbon::createFromFormat('Y-m-d h:i:s', $row[2]);
        } catch (\Throwable $th) {
            //throw $th;
            return [
                'status' => false,
                'message' => 'Date Format Error',
            ];
        }

        $insertRecord = true;
        $iserror = false;
        $user_id = null;
        if (!isset($this->users[$row[13]])) {

            $iserror = true;
        } else {
            $user_id = $this->users[$row[13]]['id'];
             //if($needcheck === true){

            //Log::info($this->users[$row[3]]);
            // Log::info($this->users[$row[3]]['expiry_date']);
            //$ed = $this->users[$row[3]]['expiry_date'];

            //if ($ed->gte($date)) {


            //} else {
            //    $insertRecord = true;
            //    $iserror = true;
            //    Log::info("Error becase of date is above the EXPRI date");
            //   Log::info($row);
            //   $expiredData [] = $row;
            //}
        }




        $userarr = [
            'S2648922',
            'S2597864',
            'S2782154',
            'S2605425',
            'S2698591',
            'S2690343',
            'S2751947',
            'S2771403',
            'S2736066',
            'S2833975',
            'S2707897',
            'S2762829',
            'S2772888',
            'S2783961',
            'S2653760',
            'S2824695',
            'S2615319',
            'S2829111',
            'S2831020'
        ];
        if ($iserror === false) {

            if ($row[3] && in_array($row[3], $userarr)) {

                // VIP
                $oldData = Carbon::parse('2024-09-28 00:00:00');
            } else {
                // GARBO
                $oldData = Carbon::parse('2025-07-06 00:00:00');
            }
            if ($date->lte($oldData)) {
            } else {
                $iserror = true;
                $insertRecord = false;

                Log::info("Error becase of date is above the selected date");
                Log::info($row);
            }
        }


     
        if ($insertRecord === true) {


             $sale = Sale::create([
                'pos' => '-',
                'loyalty' => $row[3],
                'user_id' => $user_id,
                'location' => $row[5],
                'storage_location' => $row[4],
                'ref' => $row[6],
                'batch_id' => $this->batchId,
                'sku' => '',
                'voucher_no' => '',
                'quantity_purchased' => $row[10],
                'sale_amount' => (float) $row[9],
                'date' => $date->format('Y-m-d'),
                'key_earn' => 0,
                "limit_reach" => str_contains( $row[15], 'Hit limit of 30000') ? 1 : 0,
                'system_time' => $date->format('His'),
            ]);
            $this->newSales[$row[7]] = [
                'ref' => $row[7],
                'id' => $sale['id'],
                'sku' => $row[9],

            ];
        }

        // $this->newSales[$row[7]] =   [
        //     'ref' => $row[7],
        //     'id' => $sale['id'],
        //     'sku' => $row[9]

        // ];

        return [
            'status' => true,
            'message' => 'Done ',
        ];
    }
    private function voidTransaction($row, $needcheck = true)
    {

        $date = Carbon::createFromFormat('Y-m-d h:i:s', $row[2]);

        $insertRecord = true;
        $user_id = null;


        $iserror = false;
        if (!isset($this->users[$row[13]])) {
            $iserror = true;
        } else {
            $user_id = $this->users[$row[13]]['id'];

            //if($needcheck === true){
            //Log::info($this->users[$row[3]]);
            // Log::info($this->users[$row[3]]['expiry_date']);
            //  $ed = $this->users[$row[3]]['expiry_date'];

            // 2023-10-11 ED 2024-12-12
            //  if ($ed->gte($date)) {
            //
            //  } else {
            //     $iserror = true;
            //$insertRecord = true;
            //   Log::info("Error becase of date is above the EXPRI date");
            //   Log::info($row);
            //   $expiredData [] = $row;

        }




        $userarr = [
            'S2648922',
            'S2597864',
            'S2782154',
            'S2605425',
            'S2698591',
            'S2690343',
            'S2751947',
            'S2771403',
            'S2736066',
            'S2833975',
            'S2707897',
            'S2762829',
            'S2772888',
            'S2783961',
            'S2653760',
            'S2824695',
            'S2615319',
            'S2829111',
            'S2831020'
        ];
        if ($iserror === false) {

            if ($row[3] && in_array($row[3], $userarr)) {

                $oldData = Carbon::parse('2024-09-28 00:00:00');
            } else {
                $oldData = Carbon::parse('2025-07-06 00:00:00');
            }
            if ($date->lte($oldData)) {
            } else {
                $iserror = true;
                $insertRecord = false;

                Log::info("Error becase of date is above the selected date");
                Log::info($row);
            }
        }

        if ($insertRecord == true) {

            $refundSaleData = RefundSale::create([
                'pos' => '-',
                'loyalty' => $row[3],
                'location' => $row[5],
                'storage_location' => $row[4],
                'ref' => $row[6],
                'user_id' => $user_id,

                'batch_id' => $this->batchId,
                'sku' => '',
                'voucher_no' => '',
                'quantity_purchased' => $row[10],
                'sale_amount' => (float) $row[9],
                'key_earn' => 0,
                'date' => $date->format('Y-m-d'),
                'system_time' => $date->format('His'),
                'org_rec_no' => '',

            ]);
        }


        return [
            'status' => true,
            'message' => 'Done ',
        ];
    }
}
