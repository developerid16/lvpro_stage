<?php

namespace App\Console\Commands;

use App\Models\APILogs;
use Illuminate\Console\Command;
use App\Http\Controllers\Admin\SalesController;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\AppUser;
use App\Models\RefundSale;
use App\Models\KeyPassbookCredit;
use App\Models\UserTier;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Str;

class importFailTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:faildata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //

        dd("==========");


        $i = 0;
        $filePath = public_path('report') . '/' . "keyearn2.csv"; 

        if (($handle = fopen($filePath, 'r')) !== false) {

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if ($i == 0) {
                    $header = $row;
                } else {
                   $user =   AppUser::where([['unique_id', $row[0]]])->first();

                   if($user){

                    KeyPassbookCredit::create([
                        'user_id' =>$user->id,
                        'no_of_key' => $row[1],
                        'remain_keys' => $row[1],
                        'earn_way' => 'admin_credit',
                        'meta_data' => $row[2],
                        'expiry_date' => keyExpiryDate()
                    ]);
                }

                    // $user =   AppUser::where([['email', $row[0]], ['email_noti', '0']])->first();


                    // if (!$user) {

                    //     $ed['unique_id'] = $row[1];
                    //     $email = $row[0];
                    //     $ed['en_email'] = Crypt::encryptString($email);
                    //     Mail::to($row[0])->send(
                    //         new BroadcastEmail($value->email_subject, $ed, $attachments)
                    //     );
                    // }
                }
                $i++;
            }
        }



        dd("end is here ");
        // First find if user spend anything in new cycle

        $activeUser = UserTier::where([['status', 'Active'], ['amount_spend', '1']])->get();

        foreach ($activeUser as  $value) {
            $sales = Sale::whereDate('date', '>=', '2024-09-01')->where('user_id', $value->user_id)->groupBy('ref ')
                ->selectRaw(' * ,sum(sale_amount) as totalsale')
                ->get();
            $totalSpend = 0;
            foreach ($sales as $key => $value) {
                $totalSpend +=  floor((float)$value->totalsale);
            }
            $refundsales = RefundSale::whereDate('date', '>=', '2024-09-01')->where('user_id', $value->user_id)->groupBy('ref ')
                ->selectRaw(' * ,sum(sale_amount) as totalsale')
                ->get();
            foreach ($refundsales as $key => $value) {
                $totalSpend -=  floor((float)$value->totalsale);
            }

            if ($value->amount_spend ==  $totalSpend) {
                // Nothing to do here this user not effect
                \Log::info("User is not effected " .  $activeUser->user_id);
            } else {
                // this user effect need to reset 
                \Log::info("User is effected " .  $activeUser->user_id);
                //TODO: here we need to reset his tire
            }
        }

        dd("Done");



        $effctedUser = [];

        $users = AppUser::whereIn('id', $effctedUser)->get();

        foreach ($users as $user) {
            // Delete old keys and reset limit reach


            // Delete Key that user earn in new cycle because we give again 

            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user->id)->whereDate('expiry_date', '2025-11-30')->get();



            // delete key becase we give again to users 
            $kpd = KeyPassbookDebit::whereIn('type', ['refund_sale', 'milestone_abandoned'])->where('user_id', $user->id)->get();

            $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user->id)->get();
            $keyuserd = $debitskey->sum('key_use');


            // Get first all sales file 
            $sales = Sale::whereDate('date', '>=', '2024-09-01')->where('user_id', $user->id)->groupBy('ref ')
                ->selectRaw('sum(sale_amount) as totalsale, *')
                ->get();

            foreach ($sales as $key => $value) {

                // First Reset the old keys and limit

                Sale::where('ref', $value->ref)->update([
                    'key_earn' => 0,
                    'limit_reach' => 0
                ]);



                // but before also need to reset the keys of 


                // Now we need to give key again to users 
                SalesController::updateMileStone($value, (float)   $value->totalsale);
            }

            // Now Refund the transaction 
            $refundsales = RefundSale::whereDate('date', '>=', '2024-09-01')->where('user_id', $user->id)->groupBy('ref ')
                ->selectRaw('sum(sale_amount) as totalsale, *')
                ->get();

            foreach ($refundsales as $key => $value) {
                SalesController::updateMileStone($value, (float)   $value->totalsale);
            }


            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user->id)->whereDate('expiry_date', '2025-11-30')->get();


            // now check again if user spent key then we earnd 

            if ($kpc->sum('remain_keys') <  $keyuserd) {
                \Log::info("No User not spent key morethen earn");
            } else {
                \Log::info("User Spent key more then earned need to reduse key if as admin debit ");
                $keydebit = $keyuserd - $kpc->sum('no_of_key');
                \Log::info("Extra keyearn" . $keydebit);
            }
        }





        // $data = APILogs::wheredate('start_time', '>=', '2024-09-01')->whereStatus(0)->where('response_data', 'LIKE', "%Net amount must be%")->get();
        // dd("Here is the console command", $data->count());

        // for ($i = 0; $i < $data->count(); $i++) {
        //     $singleData = $data[$i]->toArray();
        //     $singleData['req_data']['Request_ID']  .=   "-RETRY";
        //     // dd($singleData->req_data['Request_ID']);
        //     // LIST_PD_INF
        //     $res = $this->createTransaction($singleData['req_data']);
        //     \Log::info("RESULT: " . $singleData['req_data']['Request_ID']);
        //     \Log::info($res);
        //     if ($res['status']['status_code'] == 200) {
        //         $data[$i]->update([
        //             'status' => 1
        //         ]);
        //     }
        // }

        // dd("Here is the console command", $data->count());
    }


    function createTransaction($request)
    {





        $trans = $request['Trans'];



        $trans['NET_AM'] = Str::remove(',', $trans['NET_AM']);
        $batch =  Sale::where('batch_id', $request['Request_ID'])->first();
        if ($batch) {
            return ['request' => [
                "api" => "createTransaction"
            ], "status" => [
                'status_code' => 2002,
                'status_message' => "Transaction already process",
            ], 'data' => 'fail'];
        }
        $sales = Sale::whereRef($trans['REC_NO'])->first();
        if ($sales) {
            return ['request' => [
                "api" => "createTransaction"
            ], "status" => [
                'status_code' => 2001,
                'status_message' => "Duplicate receipt number",
            ], 'data' => 'fail'];
        }
        $isValid = AppUser::where('unique_id', $trans['LOY_ID'])->first();
        if (!$isValid) {
            return ['request' => [
                "api" => "createTransaction"
            ], "status" => [
                'status_code' => 2003,
                'status_message' => "No user found for this transaction",
            ], 'data' => 'fail'];
        }
        // dd($trans['SYS_DATE'])
        $date = Carbon::createFromFormat('YmdHis', $trans['SYS_DATE']);

        $today = Carbon::now();
        if ($date->gt($today)) {
            return ['request' => [
                "api" => "createTransaction"
            ], "status" => [
                'status_code' => 2003,
                'status_message' => " Invalid date ",
            ], 'data' => 'fail'];
        }
        $totamt = 0;
        $arrCollect = collect($trans['LIST_PD_INF'])->unique('PD_No');


        $unique =  $arrCollect->values()->count();
        if ($unique !== count($trans['LIST_PD_INF'])) {
            return ['request' => [
                "api" => "createTransaction"
            ], "status" => [
                'status_code' => 2003,
                'status_message' => "Duplicate SKU found",
            ], 'data' => 'fail'];
        }
        foreach ($trans['LIST_PD_INF'] as $key => $t) {
            $trans['LIST_PD_INF'][$key]['PD_SL_AM'] = Str::remove(',', $t['PD_SL_AM']);
            $trans['LIST_PD_INF'][$key]['PD_Q'] = Str::remove(',', $t['PD_Q']);

            $totamt += (float)  $trans['LIST_PD_INF'][$key]['PD_SL_AM'];
        }

        $totamt = round($totamt, 2);
        $netamt = round($trans['NET_AM'], 2);

        $vNo =  str_replace(' ', '', str_replace(
            array("\n", "\r"),
            '',
            $trans['Voucher_No']
                ?? ''
        ));
        $netAMT = 0;
        foreach ($trans['LIST_PD_INF'] as $t) {
            Sale::create([
                'pos' => $trans['POS_NO'],
                'loyalty' => $trans['LOY_ID'],
                'location' => $trans['ST_ID'],
                'storage_location' => $trans['ST_ID'],
                'ref' => $trans['REC_NO'],
                'batch_id' => $request['Request_ID'],
                'sku' => $t['PD_No'],
                'voucher_no' => $vNo ?? '',
                'quantity_purchased' => $t['PD_Q'],
                'sale_amount' => (float) $t['PD_SL_AM'],
                'date' => $date->format('Y-m-d'),
                'system_time' => $date->format('His')
            ]);
            $netAMT += (float)  $t['PD_SL_AM'];
        }
        $time = Carbon::now()->format('ymjhisjmy');
        $item = Sale::where('ref', $trans['REC_NO'])->first();
        if ($item) {
            SalesController::updateMileStone($item, (float)  $netAMT);
        }
        return ['request' => [
            "api" => "createTransaction"
        ], "status" => [
            'status_code' => 200,
            'status_message' => "Success",
        ], 'data' => ['Response_ID' => $request['Request_ID'], 'date_time' => $time]];
    }
}
