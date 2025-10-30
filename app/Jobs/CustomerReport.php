<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AppUser;
use App\Models\KeyPassbookDebit;
use App\Models\KeyPassbookCredit;
use App\Models\Sale;
use App\Jobs\NotifyUserOfCompletedExportReport;
use Carbon\Carbon;

class CustomerReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $qdata = [];
    public $startTime = '';
    public $last3month = '';
    public $last6month = '';
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

        $sd = $this->qdata['start_date'] ?? '';
        $status = $this->qdata['status'] ?? '';
        $ed = $this->qdata['end_date'] ?? '';



        $this->startTime = Carbon::today();
        $this->last3month = Carbon::today()->subMonths(3);
        $this->last6month = Carbon::today()->subMonths(6);





        $data = AppUser::with('referral.byuser');
        $selected = $status;
         if ($status && $status !== 'All') {
            $data =  AppUser::whereStatus($selected);
        } else if ($status && $status === 'All') {

            $data =  AppUser::with('referral.byuser');
        }
        if ($sd && $ed) {
            $data =  $data->whereBetween('created_at', [$sd . ' 00:00:00', $ed . ' 23:23:59']);
        }
        // $data =  $data->paginate(100)->withQueryString();
        // $startTime = Carbon::today();
        // $last3month = Carbon::today()->subMonths(3);
        // $last6month = Carbon::today()->subMonths(6);
        // foreach ($data as $key => $value) {
        //     $value->last3month =  number_format(KeyPassbookDebit::where('user_id', $value->id)->whereBetween('created_at', [$last3month->format('Y-m-d') . ' 00:00:01', $startTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use'));
        //     $value->last6month =  number_format(KeyPassbookDebit::where('user_id', $value->id)->whereBetween('created_at', [$last6month->format('Y-m-d') . ' 00:00:01', $startTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use'));
        //     $value->total =  number_format(KeyPassbookDebit::where('user_id', $value->id)->sum('key_use'));

        //     $value->lastTransaction = $this->lastTransaction($value->id);
        //     $past = $this->pastCycle($value->id);
        //     $value->pastCycle = $past['data'];
        //     $pd = $past['date'];
        //     $curunt = $this->currantCycle($value->id);
        //     $value->currantCycle = $curunt['data'];
        //     $cd = $curunt['date'];
        //     $value->remainKeys = $this->remainKey($value->id);
        // }










        $filePath = public_path('report/' . $this->qdata['udid'] . '.csv');

        // Open the file in write mode
        $file = fopen($filePath, 'w');

        // Add headers to the CSV file
        fputcsv($file, [
            'User Type',
            'Aphid',
            'Name',
            'Email',
            'Mobile Number',
            'Status',
            'Date Of Birth',
            'Gender',
            'Age',
            'Aph Expiry Date',
            'Sign Up Date',
            'Last Login',
            'Spending(Total)',
            'Spending(last 3 Month)',
            'Spending(last 6 Month)',
            'Last transaction date',
            'No. of Keys earned (past Cycle)',
            'No. of Keys earned (Current Cycle)',
            'No.of Keys available (Current Cycle)',
            "Referrals Name",
            "Referrals APH",
            "Referral date",
            "Referral status",
        ]); // Replace with your actual column names

        // Fetch data in chunks and write to file
        $instance = $this;
        $data->chunk(50000, function ($rows) use ($file, $instance) {
            foreach ($rows as $item) {
                // Map the row data to an array for CSV export
                fputcsv($file, [
                    $item->user_type,
                    $item->unique_id,
                    $item->name,
                    $item->email,
                    $item->country_code . ' ' . $item->phone_number,
                    $item->status,
                    $item->date_of_birth ? $item->date_of_birth->format(config('shilla.date-format')) : '',
                    $item->gender,
                    $item->date_of_birth ?  $item->date_of_birth->age : '',
                    $item->expiry_date ? $item->expiry_date->format(config('shilla.date-format')) : '',
                    $item->created_at ? $item->created_at->format(config('shilla.date-format')) : '',
                    $item->last_login ? $item->last_login->format(config('shilla.date-format')) : '',

                    $instance->total($item->id),
                    $instance->last3month($item->id),
                    $instance->last6month($item->id),
                    $instance->lastTransaction($item->unique_id),
                    $instance->pastCycle($item->id),
                    $instance->currantCycle($item->id),
                    $instance->remainKey($item->id),
                    $item->referral ? $item->referral->byuser->name : 'ND',
                    $item->referral ? $item->referral->byuser->unique_id : 'ND',
                    $item->referral ? $item->referral->created_at->format(config('shilla.date-format')) : 'ND',
                    $item->referral ? $item->referral->status : 'ND',
                ]);
            }
        });

        // Close the file
        fclose($file);

        NotifyUserOfCompletedExportReport::dispatch($this->qdata);
    }


    function last3month($id): string
    {
        return number_format(KeyPassbookDebit::where('user_id', $id)->whereBetween('created_at', [$this->last3month->format('Y-m-d') . ' 00:00:01', $this->startTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use'));
    }
    function last6month($id): string
    {
        return number_format(KeyPassbookDebit::where('user_id', $id)->whereBetween('created_at', [$this->last6month->format('Y-m-d') . ' 00:00:01', $this->startTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use'));
    }
    function total($id): string
    {
        return  number_format(KeyPassbookDebit::where('user_id', $id)->sum('key_use'));
    }
    function remainKey($id): string
    {
        // key expiry line
        $today = Carbon::now();
        $month = $today->month;
        $keyExpiryDate = $today->day(30)->month(11);
        if ($month === 12) {
            $keyExpiryDate->addYear();
        }
        return number_format(KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $id], ['expiry_date', '<=', $keyExpiryDate->format('Y-m-d') . ' 23:59:59']])->sum('remain_keys'));
    }
    function pastCycle($id): string
    {

        $today = Carbon::now();
        $month = $today->month;
        $pastStartDate = '';
        $pastEndDate = '';
        if ($month >= 9) {
            $pastStartDate = Carbon::now()->day(01)->month('09')->year($today->year - 1);
            $pastEndDate = Carbon::now()->day(31)->month('08');
            // dd( $pastStartDate,$pastEndDate );
        } else {
            $pastStartDate = Carbon::now()->day(01)->month('09')->year($today->year - 2);
            $pastEndDate = Carbon::now()->day(31)->month('08')->year($today->year - 1);
            //             01-09-2022
            // 31-08-2022 
        }

        return number_format(KeyPassbookCredit::where('user_id', $id)->whereBetween('created_at', [$pastStartDate->format('Y-m-d') . ' 00:00:01', $pastEndDate->format('Y-m-d') . ' 23:59:59'])->sum('no_of_key'));
    }
    function currantCycle($id): string
    {
        // '01-09-' to 
        $today = Carbon::now();
        $month = $today->month;

        $startDate = '';
        $endDate = '';
        if ($month >= 9) {
            $startDate = Carbon::now()->day(01)->month('09');
            $endDate = Carbon::now()->day(31)->month('08')->year($today->year + 1);
        } else {
            $startDate = Carbon::now()->day(01)->month('09')->year($today->year - 1);
            $endDate = Carbon::now()->day(31)->month('08');


            // 


        }
        return number_format(KeyPassbookCredit::where('user_id', $id)->whereBetween('created_at', [$startDate->format('Y-m-d') . ' 00:00:01', $endDate->format('Y-m-d') . ' 23:59:59'])->sum('no_of_key'));
    }
    function lastTransaction($id): string
    {
        $last =  Sale::where('loyalty', $id)->first();
        return $last ? $last->date->format(config('shilla.date-format')) : 'NDA';
    }
}
