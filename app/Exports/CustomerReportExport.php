<?php

namespace App\Exports;

use App\Models\AppUser;
use App\Models\KeyPassbookCredit;
use App\Models\KeyPassbookDebit;
use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerReportExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public $startTime = '';
    public $last3month = '';
    public $last6month = '';

    public function __construct()
    {
        $this->startTime = Carbon::today();
        $this->last3month = Carbon::today()->subMonths(3);
        $this->last6month = Carbon::today()->subMonths(6);
    }
    public function headings(): array
    {
        return [
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
            'No.of Keys available (Current Cycle)'
        ];
    }

    public function query()
    {
        return AppUser::query();
    }
    public function map($item): array
    {
        return [
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

            $this->total($item->id),
            $this->last3month($item->id),
            $this->last6month($item->id),
            $this->lastTransaction($item->unique_id),
            $this->pastCycle($item->id),
            $this->currantCycle($item->id),
            $this->remainKey($item->id),


        ];
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

    // $value->total =  number_format(KeyPassbookCredit::where('user_id', $value->id)->sum('no_of_key'));

}
