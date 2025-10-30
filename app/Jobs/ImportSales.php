<?php

namespace App\Jobs;

use App\Exports\SalesCSVError;
use App\Http\Controllers\Admin\SalesController;
use App\Models\AppUser;
use App\Models\RefundSale;
use App\Models\Reward;
use App\Models\Sale;
use App\Models\SaleBatch;
use App\Models\UserPurchasedReward;
use App\Models\VoucherLogs;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ImportSales implements ShouldQueue
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
        $batch = SaleBatch::find($this->batchId);
        $filePath = public_path('report') . '/' . $batch->file_name;

        $errors = [];
        //
        $header = null;
        $i = 0;
        DB::beginTransaction();

        if (($handle = fopen($filePath, 'r')) !== false) {

            $this->users = AppUser::get(['unique_id', 'id'])->keyBy('unique_id')->all();
            $this->sales =  Sale::groupBy('ref')->get(['ref', 'id'])->keyBy('ref')->all();
            $this->newSales = $this->sales;

            $this->refundSale =  RefundSale::groupBy('ref')->get(['ref', 'id'])->keyBy('ref')->all();
            $this->newRefundSale =   $this->refundSale;

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

                if ($i == 0) {
                    $header = $row;
                    Log::info('new header ' . json_encode($header));
                } else {


                    // 'date' [0]
                    // system_time' [1]
                    // loyalty' [2]
                    // location' [3]
                    // storage_location' [4]
                    // ref' [7]
                    // sku' [9]
                    // sale_amount' [10]
                    // quantity_purchased' [11]
                    // batch_id' [asd]
                    // pos' [5]
                    // voucher_no' [8]

                    // orginal req no [6]

                    // Day of Date,System Time,Loyalty,Location,Storage Location,POS No,Original Recpt No,Recpt Ref No,SKU_Code,Net Sales Anount(DOC),Quantity Purchased

                    // first  check if its is sale transaction or void transaction

                    Log::info($row);
                    if ($row[11] > 0 && $row[10] > 0) {
                        // sale transaction
                        $row[11] = Str::remove(',', $row[11]);
                        $row[10] = Str::remove(',', $row[10]);
                        $res = $this->saleCreate($row);
                        if ($res['status'] == false) {
                            $temp = $row;
                            $temp[12] = $res['message'];
                            $errors[] = $temp;
                        }
                    } elseif ($row[11] < 0 && $row[10] < 0) {
                        // void transaction
                        $row[11] = Str::remove(',', $row[11]);
                        $row[10] = Str::remove(',', $row[10]);
                        $row[10] = abs($row[10]);
                        $row[11] = abs($row[11]);

                        $res =  $this->voidTransaction($row);
                        if ($res['status'] == false) {
                            $temp = $row;
                            $temp[12] = $res['message'];
                            $errors[] = $temp;
                        }
                    } else {
                        $temp = $row;
                        $temp[12] = "Qty and Amount is different from each other.";
                        $errors[] = $temp;
                    }
                }

                $i++;
            }
            fclose($handle);
        }

        Log::info($errors);
        if (count($errors) > 0) {
            // lets create CSV because of errors and revert everything

            Excel::store(new SalesCSVError($errors), $this->batchId . '-error.csv', null, ExcelExcel::CSV);
            // return (new SalesCSVError($errors))->download($this->batchId . '-error.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
            DB::rollback();

            $batch->update(['status' => 'failed']);
        } else {
            // pass all now update uses key 
            $batch->update(['status' => 'Success']);

            $items = Sale::where('batch_id', $this->batchId)->selectRaw('*,sum(sale_amount) as sum')
                ->groupBy('ref')->get();
            foreach ($items as $item) {


                SalesController::updateMileStone($item, (float)  $item['sum']);
            }
            $items = RefundSale::where('batch_id', $this->batchId)->selectRaw('*,sum(sale_amount) as sum')
                ->groupBy('ref')->get();
            $rows = [];
            foreach ($items as $item) {

                $allSales = Sale::where('ref', $item['org_rec_no'])->selectRaw('*,sum(sale_amount) as sum')->groupBy('ref')->get();

                $errMsg = "";
                if ($allSales[0]->sum == $item['sum'] && $item['voucher_no']) {
                    // this is full refund if voucher has presnt in db 

                    $vnos = explode(',', $item['voucher_no']);
                    $differenceArray = array_diff(explode(',', $allSales[0]->voucher_no), $vnos);

                    if ($differenceArray) {

                        $errMsg = "Voucher is not valid as sale transaction.";
                    }


                    $upr = UserPurchasedReward::where([['reward_type', 0],  ['status', 'Redeemed'], ['user_id', $item->user_id]])->whereIn('unique_no', $vnos)->get();

                    if ($upr->count() !== count($vnos)) {
                        // Voucher is not valid.
                        $errMsg = "Voucher is not valid.";
                    } else {
                        foreach ($upr as  $up) {
                            $up->load(['reward']);
                            $exd = null;
                            if ($up->reward->expiry_day == 0) {
                                $exd = $up->reward->end_date;
                            } else {
                                $exd = Carbon::now()->addDays($up->reward->expiry_day);
                            }

                            $up->update([
                                'status' => "Purchased",
                                'expiry_date' => $exd,
                            ]);
                            VoucherLogs::create([
                                'voucher_no' => $up->unique_no,
                                'from_status' => 'Purchased',
                                'to_status' => 'Active',
                                'from_where' => 'CSV',
                                'remark' => '-',

                            ]);
                        }
                    }
                } elseif ($item['voucher_no']) {
                    // Voucher is not valid in partial refund.
                    $errMsg = "Voucher is not valid in partial refund.";
                }

                if ($errMsg) {
                    $rows[] = [
                        $item['date'],
                        $item['system_time'],
                        $item['loyalty'],
                        $item['location'],
                        $item['storage_location'],
                        $item['pos'],
                        $item['org_rec_no'],
                        $item['ref'],
                        $item['voucher_no'],
                        $item['sku'],
                        $item['sale_amount'],
                        $item['quantity_purchased'],
                        $errMsg,
                    ];
                } else {
                    $key = SalesController::refundSale($item, floor((float)  $item['sum']));
                    $item->key_earn = $key;
                    $item->save();
                }
            }
            if (count($rows) == 0) {
                DB::commit();
            } else {
                Excel::store(new SalesCSVError($rows), $this->batchId . '-error.csv', null, ExcelExcel::CSV);
                // return (new SalesCSVError($errors))->download($this->batchId . '-error.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
                DB::rollback();

                $batch->update(['status' => 'failed']);
            }
        }
        Log::info("Successfully updated");
    }
    private function saleCreate($row)
    {

        if (!isset($this->users[$row[2]])) {

            // return [
            //     'status' => false,
            //     'message' => 'No user found for this transaction'
            // ];
        }
        if (isset($this->sales[$row[7]])) {

            return [
                'status' => false,
                'message' => 'Duplicate receipt number sale'
            ];
        }

        try {
            //code...
            $date = Carbon::createFromFormat('n/j/YHis', $row[0] . '000001');
        } catch (\Throwable $th) {
            //throw $th;
            return [
                'status' => false,
                'message' => 'Date Format Error'
            ];
        }

        $today = Carbon::now();
        if ($date->gt($today)) {
            return [
                'status' => false,
                'message' => 'Invalid date '
            ];
        }
        $replaced = Str::replace('|', ',', $row[8]);

        // if (isset($this->newSales[$row[7]]) && $this->newSales[$row[7]]['sku'] == $row[9]) {
        //     // ref exits and sku also give error
        //     return [
        //         'status' => false,
        //         'message' => 'SKU already exists for receipt number'
        //     ];
        // }

        $sale =   Sale::create([
            'pos' => $row[5],
            'loyalty' => $row[2],
            'location' => $row[3],
            'storage_location' => $row[4],
            'ref' => $row[7],
            'batch_id' => $this->batchId,
            'sku' => $row[9],
            'voucher_no' =>   $replaced ?? '',
            'quantity_purchased' => $row[11],
            'sale_amount' => (float) $row[10],
            'date' => $date->format('Y-m-d'),
            'system_time' => $date->format('His')
        ]);

        if (!isset($this->newSales[$row[7]]) && $row[8]) {
            // first new record if Voucher has prest we need to check

            $vnos = explode('|', $row[8]);
            $upr = UserPurchasedReward::where([['reward_type', 0],  ['status', 'Purchased'], ['user_id', $this->users[$row[2]]['id'] ?? '0']])->whereIn('unique_no', $vnos)->get();

            if ($upr->count() !== count($vnos)) {
                return [
                    'status' => false,
                    'message' => 'Voucher is not valid or has expired. '
                ];
            } else {
                // all goood latest make this purchess to reedemd/ 

                // check voucher is available to reededm at moment
                $fres = "";
                $today = $date;

                $day = $date->format('w');
                $time = $date->format('H:i');

                foreach ($upr as $key => $up) {
                    # code...
                    $reward =  Reward::where([['id', $up->reward_id]])->where(function ($query) use ($today) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $today);
                    })->whereDate('start_date', '<=', $today)
                        ->where(function ($query) use ($time) {
                            $query->whereTime('start_time', '<=', $time)
                                ->orwhereNull('start_time');
                        })
                        ->where(function ($query) use ($time) {
                            $query->whereTime('end_time', '>=', $time)
                                ->orwhereNull('end_time');
                        })
                        ->where(function ($query) use ($day) {
                            $query->whereRaw('FIND_IN_SET(?, days)', [$day])
                                ->orwhereNull('days');
                        })->first();

                    if (!$reward) {
                        $fres = 'Voucher had been not available at moment';
                        break;
                    }
                }
                if ($fres) {
                    return [
                        'status' => false,
                        'message' => $fres
                    ];
                }
            }

            foreach ($upr as  $rd) {
                $rd->update([
                    'status' => "Redeemed",
                    'redeem_date' => Carbon::now(),
                    'meta_data' => json_encode($sale),
                ]);
                VoucherLogs::create([
                    'voucher_no' => $rd->unique_no,
                    'from_status' => 'Active',
                    'to_status' => 'Redeemed',
                    'from_where' => 'CSV',
                    'remark' => '-',

                ]);
            }
        }

        $this->newSales[$row[7]] =   [
            'ref' => $row[7],
            'id' => $sale['id'],
            'sku' => $row[9]

        ];

        return [
            'status' => true,
            'message' => 'Done '
        ];
    }
    private function voidTransaction($row)
    {

        if (!isset($this->users[$row[2]])) {

            // return [
            //     'status' => false,
            //     'message' => 'No user found for this transaction'
            // ];
        }
        if (isset($this->refundSale[$row[7]])) {

            return [
                'status' => false,
                'message' => 'Duplicate receipt number refund'
            ];
        }
        if ($row[6] == $row[7]) {
 
            return [
                'status' => false,
                'message' => 'Orignal receipt and receipt number cant be same'
            ];
        }


        $originalRecord = Sale::where([['ref', $row[6]], ['user_id', $this->users[$row[2]]['id'] ?? '0' ]])->get();

        if ($originalRecord->count() === 0) {
            return [
                'status' => false,
                'message' => 'Original receipt number not found'
            ];
        }

        $saleDate = Carbon::createFromFormat('Y-m-d H:i:s', $originalRecord[0]->date->format('Y-m-d') . " " . $originalRecord[0]->system_time);


        $date = Carbon::createFromFormat('n/j/YHis', $row[0] . '000101');

        if ($date->lte($saleDate)) {

            return [
                'status' => false,
                'message' =>  "Refund  date must be greater than to " . $saleDate->format('j-m-Y H:i:s')
            ];
        }

        $today = Carbon::now();
        if ($date->gt($today)) {
            return [
                'status' => false,
                'message' => 'Invalid date '
            ];
        }
        // if (isset($this->newRefundSale[$row[7]]) && $this->newRefundSale[$row[7]]['sku'] == $row[9]) {
        //     // ref exits and sku also give error
        //     return [
        //         'status' => false,
        //         'message' => 'SKU already exists for receipt number'
        //     ];
        // }
        $sku = $originalRecord->where('sku', $row[9])->values();
        if ($sku->count() > 0) {
            $saleQty = $sku->sum('quantity_purchased');
            $saleAmt = $sku->sum('sale_amount');
            $perUnit =  (float) round($saleAmt / $saleQty, 2);
            $amt =  (float)  round($row[10] / $row[11], 2);
            Log::info("pur unit $perUnit");
            Log::info("amtttttt $amt");
            if ($perUnit !=  $amt) {


                return [
                    'status' => false,
                    'message' =>  "Invalid price based on original receipt"
                ];
            }
            $refundQty = RefundSale::where([['org_rec_no', $row[6]], ['sku', $row[9]]])->sum('quantity_purchased');
            $actQyu = $refundQty +  $row[11];

            if ($actQyu > $saleQty) {

                return [
                    'status' => false,
                    'message' => "Invalid quantity based on original receipt new QTY $actQyu Sale QTY  $saleQty"
                ];
            }
        } else {
            return [
                'status' => false,
                'message' =>   "SKU " . $row[9] . " not found"
            ];
        }


        Log::info($row);
        $replaced = Str::replace('|', ',', $row[8]);
        $refundSaleData =   RefundSale::create([
            'pos' => $row[5],
            'loyalty' => $row[2],
            'location' => $row[3],
            'storage_location' => $row[4],
            'ref' => $row[7],
            'batch_id' => $this->batchId,
            'sku' => $row[9],
            'voucher_no' =>  $replaced ?? '',

            'quantity_purchased' => $row[11],
            'sale_amount' => (float) $row[10],
            'date' => $date->format('Y-m-d'),
            'system_time' => $date->format('His'),
            'org_rec_no' => $row[6],

        ]);

        $this->newRefundSale[$row[7]] =   [
            'ref' => $row[7],
            'id' => $refundSaleData['id'],
            'sku' => $row[9]

        ];
        return [
            'status' => true,
            'message' => 'Done '
        ];
    }
}
