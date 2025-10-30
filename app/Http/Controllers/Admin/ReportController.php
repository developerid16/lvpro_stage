<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SaleReportExport;
use App\Http\Controllers\Controller;
use App\Jobs\CustomerReport;
use App\Jobs\NotifyUserOfCompletedExportReport;
use App\Jobs\SalesReport;
use App\Models\AppUser;
use App\Models\ReportJob;
use App\Models\Sale;
use App\Models\BrandMapping;
use App\Models\KeyPassbookCredit;
use App\Models\KeyPassbookDebit;
use App\Models\UserPurchasedReward;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {



        $permission_prefix =   'report';



        // $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-customer", ['only' => ['customerIndex']]);
        $this->middleware("permission:$permission_prefix-sales", ['only' => ['sales']]);
        $this->middleware("permission:$permission_prefix-reward", ['only' => ['userPurchasedRewardReport']]);
    }
    //
    public function customerIndex(Request $request)
    {

        $sd = $request->get('start_date');
        $ed = $request->get('end_date');

        $data = AppUser::with('referral.byuser');
        $selected = $request->get('status');
        if ($request->has('status') && $request->status !== 'All') {
            $data =  AppUser::whereStatus($selected);
        } else if ($request->has('status') && $request->status === 'All') {

            $data =  AppUser::with('referral.byuser');
        }
        if ($sd && $ed) {
            $data =  $data->whereBetween('created_at', [$sd . ' 00:00:00', $ed . ' 23:23:59']);
        }
        $data =  $data->paginate(100)->withQueryString();
        $startTime = Carbon::today();
        $last3month = Carbon::today()->subMonths(3);
        $last6month = Carbon::today()->subMonths(6);
        $pd = "";
        $cd = "";
        foreach ($data as $key => $value) {
            $value->last3month =  number_format(KeyPassbookDebit::where('user_id', $value->id)->whereBetween('created_at', [$last3month->format('Y-m-d') . ' 00:00:01', $startTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use'));
            $value->last6month =  number_format(KeyPassbookDebit::where('user_id', $value->id)->whereBetween('created_at', [$last6month->format('Y-m-d') . ' 00:00:01', $startTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use'));
            $value->total =  number_format(KeyPassbookDebit::where('user_id', $value->id)->sum('key_use'));

            $value->lastTransaction = $this->lastTransaction($value->id);
            $past = $this->pastCycle($value->id);
            $value->pastCycle = $past['data'];
            $pd = $past['date'];
            $curunt = $this->currantCycle($value->id);
            $value->currantCycle = $curunt['data'];
            $cd = $curunt['date'];
            $value->remainKeys = $this->remainKey($value->id);
        }
        // return $data;
        return view('admin.reports.users', compact('data', 'selected', 'sd', 'ed', 'cd', 'pd'));
    }
    public function sales(Request $request)
    {
        $data = [];
        $sd = $request->get('start_date');
        $ps = $request->get('product');
        $bs = $request->get('brand');
        $ed = $request->get('end_date');
        $brands = BrandMapping::groupBy('brand_code')->get(['brand_name', 'brand_code']);
        $temp = false;
        if ($sd && $ed) {
            $data =  Sale::whereBetween('date', [$sd . ' 00:00:00', $ed . ' 23:23:59']);
            $temp = true;
        }

        if ($ps) {
            $dale = $data->where('sku', $ps);
        }
        if ($bs) {
            $dale = $data->where('brand_code', $bs);
        }

        if ($temp) {

            // $data = $data->with('brand:id,sku,brand_name,product_name')->get();
            $data =  $data->with('brand:id,sku,brand_name,product_name')->paginate(100)->withQueryString();
        }

        return view('admin.reports.sales', compact('data', 'sd', 'ed',  'brands', 'bs', 'ps'));
    }
    public function salesReportDownload(Request $request)
    {
        $data = $request->query();



        $uid = Str::uuid()->toString();;
        $insertData = [
            'user_id' => \Auth::id(),
            "udid" => $uid,
            "type" => "Sale",
            "name" => "Sales Report",
        ];
        $data['udid'] = $uid;
        $data['user_id'] = \Auth::id();
        ReportJob::create($insertData);

        try {
            // Excel::queueImport(new SalesImport($batchId), request()->file('file'), null, \Maatwebsite\Excel\Excel::CSV);
            SalesReport::dispatch($data);
        } catch (\Exception $e) {
            throw $e;
        }



        // Excel::queue(new SaleReportExport($data), $insertData['udid'] . '.xlsx')->chain([
        //     new NotifyUserOfCompletedExportReport($insertData),
        // ]);

        // (new SaleReportExport($data))->queue('invoices.xlsx');


        return redirect('admin/report-queue');
    }
    public function customerReportDownload(Request $request)
    {
        $data = $request->query();



        $uid = Str::uuid()->toString();;
        $insertData = [
            'user_id' => \Auth::id(),
            "udid" => $uid,
            "type" => "Customer",
            "name" => "Customer Report",
        ];
        $data['udid'] = $uid;
        $data['user_id'] = \Auth::id();
        ReportJob::create($insertData);
        try {
            // Excel::queueImport(new SalesImport($batchId), request()->file('file'), null, \Maatwebsite\Excel\Excel::CSV);
            CustomerReport::dispatch($data);
        } catch (\Exception $e) {
            throw $e;
        }



        // Excel::queue(new SaleReportExport($data), $insertData['udid'] . '.xlsx')->chain([
        //     new NotifyUserOfCompletedExportReport($insertData),
        // ]);

        // (new SaleReportExport($data))->queue('invoices.xlsx');


        return redirect('admin/report-queue');
    }


    public function reportQueueIndex()
    {
        return view('admin.reports.index');
    }
    public function reportQueueDatatable(Request $request)
    {
        $query = ReportJob::where('user_id', \Auth::id());
        $query->with('user');
        $query = $this->get_sort_offset_limit_query($request, $query, []);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['file_name'] = $row->file_name;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['user_name'] = $row->user->name;

            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));

            $final_data[$key]['status'] = $row->status;
            $action = "";

            if ($row->status === 'Success') {

                $action = "<div class='d-flex gap-3'>";

                $url = asset("report/$row->udid.csv");
                $action .= "<a href='$url' download><i class='mdi mdi-download text-danger action-icon font-size-18' ></i></a>";
                $action . "</div>";
            }

            $final_data[$key]['action'] = $action;
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }


    public function userPurchasedRewardReport(Request $request)
    {
        $data = [];
        $sd = $request->get('start_date');
        $ed = $request->get('end_date');
        $status = $request->get('status');
        if ($sd && $ed) {
            // with(["reward", "user"])
            $data =  UserPurchasedReward::with(['user:id,name,email,unique_id', 'reward:id,name'])->whereBetween('created_at', [$sd . ' 00:00:00', $ed . ' 23:23:59'])->when($status, function ($q) use ($status) {
                return $q->where('status',  $status);
            })->get();
        }
        return view('admin.reports.redemption', compact('data', 'sd', 'ed', 'status'));
    }
    public function productSearch(Request $request)
    {
        $searchTerm = $request->searchTerm;
        $products = BrandMapping::select('sku as id', 'product_name as text')->where('product_name', 'LIKE', "%{$searchTerm}%")->get(['sku', 'product_name']);
        return  response()->json($products, 200);
    }
    public function userSearch(Request $request)
    {
        $searchTerm = $request->searchTerm;
        $suers = [];
        if ($searchTerm) {
            $suers = AppUser::select('email as text', 'id')->where('name', 'LIKE', "%{$searchTerm}%")->orWhere('email', 'LIKE', "%{$searchTerm}%")->get(['id', 'email']);
        }
        return  response()->json($suers, 200);
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
    function pastCycle($id): array
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

        $d = number_format(KeyPassbookCredit::where('user_id', $id)->whereBetween('created_at', [$pastStartDate->format('Y-m-d') . ' 00:00:01', $pastEndDate->format('Y-m-d') . ' 23:59:59'])->sum('no_of_key'));
        return [
            'data' => $d,
            'date' => $pastStartDate->format('Y') . '-' . $pastEndDate->format('Y')
        ];
    }
    function currantCycle($id): array
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
        $d =  number_format(KeyPassbookCredit::where('user_id', $id)->whereBetween('created_at', [$startDate->format('Y-m-d') . ' 00:00:01', $endDate->format('Y-m-d') . ' 23:59:59'])->sum('no_of_key'));
        return [
            'data' => $d,
            'date' => $startDate->format('Y') . '-' . $endDate->format('Y')
        ];
    }
    function lastTransaction($id): string
    {
        $last =  Sale::where('user_id', $id)->first();
        return $last ? $last->date->format(config('shilla.date-format')) : 'NDA';
    }
}
