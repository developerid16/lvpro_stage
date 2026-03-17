<?php

namespace App\Http\Controllers;

use App\Mail\RewardExpiredMail;
use App\Models\User;

use App\Mail\AccountVerify;
use App\Mail\APHExpiry;
use App\Mail\BroadcastEmail;
use App\Mail\ForgotPassword;
use App\Mail\KeyExpiry;
use Illuminate\Http\Request;
use App\Mail\NewAdminRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Services\SafraService;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
    public function index(Request $request)
    {
        return redirect('/');
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }
    function editorImage(Request $request)
    {
        $post_data = $this->validate($request, [

            'file' => 'required|image',
        ]);

        $fileName = '';
        if ($request->hasFile('file')) {
            $imageName = time() . rand() . '.' . $request->file->extension();
            $request->file->move(public_path('images'), $imageName);
            $fileName  = $imageName;
        }
        return response()->json(['location' => $fileName]);
    }
    function emailSend()
    {

        $email = "sagar@gmail.com";
        $data['name'] =  'Sagar';
        $data['otp'] =  '0000';
        Mail::to($email)->send(
            new ForgotPassword($data)
        );

        Mail::to($email)->send(
            new AccountVerify($data)
        );
        Mail::to($email)->send(
            new BroadcastEmail("subject", '<p>Changes</p>',[])
        );
        $keyData['text'] = "You have  Keys that are expiring within  days";
        $keyData['balance'] = "Keys Balance: Keys";

        Mail::to($email)->send(
            new KeyExpiry($keyData)
        );
        $data['password'] = "asd";
        $data['name'] =  "asd";
        Mail::to($email)->send(
            new NewAdminRegister($data)
        );

        $keyData['text'] = "Your rewards are expiring in  days. Login to view your rewards now!";

        Mail::to($email)->send(
            new RewardExpiredMail($keyData)
        );
        $emailData['text'] = "Your account is expiring within  days. Please update your Airport Pass ID and expiry date on the app to retain your account and Keys earned.";

        Mail::to($email)->send(
            new APHExpiry($emailData)
        );
        dd("all email send");
    }


    public function root()
    {
        return view('index');
    }

    // voucher trend data for dashboard graph 
    public function voucherTrendData(Request $request)
    {
        $type = $request->type ?? 'week';

        /* ================= YEAR ================= */

        if ($type == 'year') {

            $year = date('Y');

            $issuanceRaw = DB::table('user_wallet_vouchers')
                ->selectRaw("MONTH(created_at) as month, COUNT(id) as total")
                ->whereYear('created_at',$year)
                ->where('reward_status','!=','purchased')
                ->groupByRaw("MONTH(created_at)")
                ->pluck('total','month')
                ->toArray();

            $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            $issuanceValues = [];

            for($i=1;$i<=12;$i++){
                $issuanceValues[] = $issuanceRaw[$i] ?? 0;
            }


            $redeemRaw = DB::table('user_wallet_vouchers')
                ->selectRaw("MONTH(redeemed_at) as month, COUNT(id) as total")
                ->whereYear('redeemed_at',$year)
                ->where('status','used')
                ->where('reward_status','!=','purchased')
                ->groupByRaw("MONTH(redeemed_at)")
                ->pluck('total','month')
                ->toArray();

            $redeemValues = [];

            for($i=1;$i<=12;$i++){
                $redeemValues[] = $redeemRaw[$i] ?? 0;
            }

        }

        /* ================= MONTH ================= */

        elseif ($type == 'month') {

            $month = date('m');
            $year  = date('Y');

            $days = date('t');

            $issuanceRaw = DB::table('user_wallet_vouchers')
                ->selectRaw("DAY(created_at) as day, COUNT(id) as total")
                ->whereYear('created_at',$year)
                ->whereMonth('created_at',$month)
                ->where('reward_status','!=','purchased')
                ->groupByRaw("DAY(created_at)")
                ->pluck('total','day')
                ->toArray();

            $labels = [];
            $issuanceValues = [];

            for($i=1;$i<=$days;$i++){
                $labels[] = $i;
                $issuanceValues[] = $issuanceRaw[$i] ?? 0;
            }


            $redeemRaw = DB::table('user_wallet_vouchers')
                ->selectRaw("DAY(redeemed_at) as day, COUNT(id) as total")
                ->whereYear('redeemed_at',$year)
                ->whereMonth('redeemed_at',$month)
                ->where('status','used')
                ->where('reward_status','!=','purchased')
                ->groupByRaw("DAY(redeemed_at)")
                ->pluck('total','day')
                ->toArray();

            $redeemValues = [];

            for($i=1;$i<=$days;$i++){
                $redeemValues[] = $redeemRaw[$i] ?? 0;
            }

        }

        /* ================= WEEK ================= */

        else {

            $start = now()->startOfWeek();
            $end   = now()->endOfWeek();

            $labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

            $issuanceRaw = DB::table('user_wallet_vouchers')
                ->selectRaw("DAYOFWEEK(created_at) as day, COUNT(id) as total")
                ->whereBetween('created_at',[$start,$end])
                ->where('reward_status','!=','purchased')
                ->groupByRaw("DAYOFWEEK(created_at)")
                ->pluck('total','day')
                ->toArray();

            $issuanceValues = [];

            for($i=2;$i<=8;$i++){
                $issuanceValues[] = $issuanceRaw[$i] ?? 0;
            }


            $redeemRaw = DB::table('user_wallet_vouchers')
                ->selectRaw("DAYOFWEEK(redeemed_at) as day, COUNT(id) as total")
                ->whereBetween('redeemed_at',[$start,$end])
                ->where('status','used')
                ->where('reward_status','!=','purchased')
                ->groupByRaw("DAYOFWEEK(redeemed_at)")
                ->pluck('total','day')
                ->toArray();

            $redeemValues = [];

            for($i=2;$i<=8;$i++){
                $redeemValues[] = $redeemRaw[$i] ?? 0;
            }
        }

        return response()->json([
            'labels'=>$labels,
            'issuance'=>$issuanceValues,
            'redeem'=>$redeemValues
        ]);
    }

    // outlet redemption data for dashboard graph
    public function outletRedemptionData()
    {
        $data = DB::table('user_wallet_vouchers as uwv')
        ->join('reward_participating_locations as rpl','uwv.reward_id','=','rpl.reward_id')
        ->join('participating_merchant_location as pml','pml.id','=','rpl.location_id')
        ->join('rewards as r','uwv.reward_id','=','r.id')
        ->where('uwv.status','used')
        ->where('uwv.reward_status','!=','purchased')
        ->select(
            'pml.name as outlet_name',
            DB::raw('COUNT(uwv.id) as redemption_count'),
            DB::raw('SUM(r.voucher_value) as redemption_value')
        )
        ->groupBy('pml.name')
        ->orderByDesc('redemption_count')
        ->get();

        $labels = $data->pluck('outlet_name');
        $count  = $data->pluck('redemption_count');
        $value  = $data->pluck('redemption_value');

        return response()->json([
            'labels'=>$labels,
            'count'=>$count,
            'value'=>$value
        ]);
    }

    //redemption rate trend data
    public function redemptionRateTrendData(Request $request)
    {
        $type = $request->type ?? 'week';

        if ($type == 'month') {

            $month = date('m');
            $year  = date('Y');
            $daysInMonth = date('t');

            $data = DB::table('user_wallet_vouchers')
                ->selectRaw("
                    DAY(created_at) as day,
                    COUNT(CASE WHEN reward_status='issued' THEN 1 END) as issued,
                    COUNT(CASE WHEN status='used' THEN 1 END) as redeemed
                ")
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->groupByRaw("DAY(created_at)")
                ->get()
                ->keyBy('day');

            $labels = [];
            $issued = [];
            $redeemed = [];
            $rate = [];

            for ($i = 1; $i <= $daysInMonth; $i++) {

                $labels[] = $i;

                $iss = isset($data[$i]) ? $data[$i]->issued : 0;
                $red = isset($data[$i]) ? $data[$i]->redeemed : 0;

                $issued[] = (int)$iss;
                $redeemed[] = (int)$red;

                $rate[] = $iss > 0 ? round(($red / $iss) * 100, 2) : 0;
            }

        } else {

            $start = now()->startOfWeek();
            $end   = now()->endOfWeek();

            $weekDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

            $data = DB::table('user_wallet_vouchers')
                ->selectRaw("
                    DAYNAME(created_at) as day,
                    COUNT(CASE WHEN reward_status='issued' THEN 1 END) as issued,
                    COUNT(CASE WHEN status='used' THEN 1 END) as redeemed
                ")
                ->whereBetween('created_at', [$start, $end])
                ->groupByRaw("DAYNAME(created_at)")
                ->get()
                ->keyBy('day');

            $labels = [];
            $issued = [];
            $redeemed = [];
            $rate = [];

            foreach ($weekDays as $day) {

                $labels[] = $day;

                $iss = isset($data[$day]) ? $data[$day]->issued : 0;
                $red = isset($data[$day]) ? $data[$day]->redeemed : 0;

                $issued[] = (int)$iss;
                $redeemed[] = (int)$red;

                $rate[] = $iss > 0 ? round(($red / $iss) * 100, 2) : 0;
            }
        }

        return response()->json([
            'labels'   => $labels,
            'issued'   => $issued,
            'redeemed' => $redeemed,
            'rate'     => $rate
        ]);
    }

    //voucher issuance method data
    public function voucherIssuanceMethodData()
    {
        $methods = [
            0 => 'CSO Issuance',
            1 => 'Push by Member ID',
            2 => 'Push by Parameter',
            3 => 'Push by API SRP',
            4 => 'All Members',
        ];

        $raw = DB::table('user_wallet_vouchers as uwv')
            ->join('rewards as r','uwv.reward_id','=','r.id')
            ->where('uwv.reward_status','issued')
            ->select('r.cso_method', DB::raw('COUNT(uwv.id) as total'))
            ->groupBy('r.cso_method')
            ->pluck('total','r.cso_method')
            ->toArray();

        $labels = [];
        $values = [];

        foreach ($methods as $key => $name) {
            $labels[] = $name;
            $values[] = isset($raw[$key]) ? (int)$raw[$key] : 0;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }

    public function categoryPerformanceData()
    {
        $data = DB::table('payment_transactions as pt')
            ->join('user_wallet_vouchers as uwv','pt.receipt_no','=','uwv.receipt_no')
            ->join('rewards as r','uwv.reward_id','=','r.id')
            ->join('category as c','r.category_id','=','c.id')
            ->where('r.type', '0')   // filter rewards.type = 0
            ->select(
                'c.name as category_name',
                DB::raw('COUNT(pt.id) as transaction_count'),
                DB::raw('COUNT(DISTINCT pt.user_id) as unique_members'),
                DB::raw('SUM(r.purchased_qty) as total_sets'),
                DB::raw('SUM(pt.request_amount) as total_revenue')
            )
            ->groupBy('c.name')
            ->orderByDesc('transaction_count')
            ->get();

        return response()->json($data);
    }

    //monthly transaction trend
    public function monthlyTransactionsTrendData()
    {
        $year = date('Y');

        $raw = DB::table('payment_transactions as pt')
            ->join('user_wallet_vouchers as uwv','pt.receipt_no','=','uwv.receipt_no')
            ->join('rewards as r','uwv.reward_id','=','r.id')
            ->selectRaw("
                MONTH(pt.created_at) as month,
                COUNT(pt.id) as transaction_count,
                SUM(r.purchased_qty) as sets_sold
            ")
            ->whereYear('pt.created_at',$year)
            ->groupByRaw("MONTH(pt.created_at)")
            ->get()
            ->keyBy('month');

        $months = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
            7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
        ];

        $labels = [];
        $transactions = [];
        $sets = [];

        foreach($months as $num=>$name){

            $labels[] = $name;

            $transactions[] = isset($raw[$num]) ? (int)$raw[$num]->transaction_count : 0;

            $sets[] = isset($raw[$num]) ? (int)$raw[$num]->sets_sold : 0;
        }

        return response()->json([
            'labels'=>$labels,
            'transactions'=>$transactions,
            'sets'=>$sets
        ]);
    }

    // purchase frequency data
    public function purchaseFrequencyData()
    {
        // get number of purchases per member
        $data = DB::table('payment_transactions')
            ->select('user_id', DB::raw('COUNT(id) as purchase_count'))
            ->groupBy('user_id')
            ->get();

        $groups = [
            '1 Purchase' => 0,
            '2-4 Purchases' => 0,
            '5-10 Purchases' => 0,
            '11+ Purchases' => 0
        ];

        $totalTransactions = 0;

        foreach ($data as $row) {

            $count = $row->purchase_count;
            $totalTransactions += $count;

            if ($count == 1) {
                $groups['1 Purchase']++;
            } elseif ($count >= 2 && $count <= 4) {
                $groups['2-4 Purchases']++;
            } elseif ($count >= 5 && $count <= 10) {
                $groups['5-10 Purchases']++;
            } else {
                $groups['11+ Purchases']++;
            }
        }

        $labels = array_keys($groups);
        $members = array_values($groups);

        $percentages = [];

        foreach ($members as $index => $memberCount) {
            $percentages[] = $totalTransactions > 0
                ? round(($memberCount / count($data)) * 100,2)
                : 0;
        }

        return response()->json([
            'labels'=>$labels,
            'members'=>$members,
            'percentages'=>$percentages
        ]);
    }

    public function demographicPurchaseData(Request $request)
    {
        $type = $request->type ?? 'age';
        $range = $request->range ?? 'all';

        $users = DB::table('payment_transactions as pt')
            ->join('app_users as u','pt.user_id','=','u.session_id')
            ->leftJoin('master_genders as g','u.gender','=','g.label')
            ->leftJoin('master_marital_statuses as ms','u.marital_status','=','ms.label')
            ->leftJoin('master_zones as z','u.residence_zone','=','z.zone_name')
            ->select(
                'pt.user_id',
                'u.age',
                'g.label as gender',
                'ms.label as marital_status',
                'z.zone_name as region'
            )
            ->distinct('pt.user_id')   // prevent duplicate users
            ->get()
            ->unique('user_id');       // extra safety

        $ageGroups = [
            '18-30'=>0,
            '31-45'=>0,
            '46-60'=>0,
            '60+'=>0
        ];

        $gender = [];
        $region = [];
        $marital = [];

        foreach($users as $user){

            if(!$user->age) continue;

            $birth = \Carbon\Carbon::createFromFormat('m/Y',$user->age);
            $age = $birth->age;

            if($age >=18 && $age <=30) $ageGroups['18-30']++;
            elseif($age <=45) $ageGroups['31-45']++;
            elseif($age <=60) $ageGroups['46-60']++;
            else $ageGroups['60+']++;

            if($user->gender){
                $gender[$user->gender] = ($gender[$user->gender] ?? 0) + 1;
            }

            if($user->region){
                $region[$user->region] = ($region[$user->region] ?? 0) + 1;
            }

            if($user->marital_status){
                $marital[$user->marital_status] = ($marital[$user->marital_status] ?? 0) + 1;
            }
        }

        if($type == 'gender'){
            return response()->json([
                'labels'=>array_keys($gender),
                'values'=>array_values($gender)
            ]);
        }

        if($type == 'region'){
            return response()->json([
                'labels'=>array_keys($region),
                'values'=>array_values($region)
            ]);
        }

        if($type == 'marital'){
            return response()->json([
                'labels'=>array_keys($marital),
                'values'=>array_values($marital)
            ]);
        }

        return response()->json([
            'labels'=>array_keys($ageGroups),
            'values'=>array_values($ageGroups)
        ]);
    }

    public function memberParticipationData(Request $request)
    {
        $type = $request->type ?? 'week';

        $months = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',
            7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'
        ];

        $allData = DB::table('payment_transactions')
            ->select('user_id','created_at')
            ->get();

        // 🔥 overall unique users
        $totalUniqueUsers = $allData->pluck('user_id')->unique()->count();

        // 🔥 overall repeat users (2+ transactions)
        $totalRepeatUsers = $allData
            ->groupBy('user_id')
            ->filter(fn($g) => $g->count() >= 2)
            ->count();

        $overallRepeatRate = $totalUniqueUsers > 0
            ? round(($totalRepeatUsers / $totalUniqueUsers) * 100, 2)
            : 0;

        // 🔹 Grouped data
        $data = $allData->groupBy(function($item) use ($type){

            if($type == 'week'){
                return \Carbon\Carbon::parse($item->created_at)->format('D');
            }

            if($type == 'year'){
                return \Carbon\Carbon::parse($item->created_at)->month;
            }

            return \Carbon\Carbon::parse($item->created_at)->format('d');
        });

        $labels = [];
        $uniqueData = [];
        $repeatRate = [];

        foreach ($data as $key => $items) {

            if($type == 'year'){
                $label = $months[$key];
            }else{
                $label = $key;
            }

            $labels[] = $label;

            $uniqueMembers = collect($items)->pluck('user_id')->unique()->count();

            $repeatMembers = collect($items)
                ->groupBy('user_id')
                ->filter(fn($g) => $g->count() >= 2)
                ->count();

            $uniqueData[] = $uniqueMembers;

            $repeatRate[] = $uniqueMembers > 0
                ? round(($repeatMembers / $uniqueMembers) * 100, 2)
                : 0;
        }

        return response()->json([
            'labels' => array_values($labels),
            'unique_members' => array_values($uniqueData),
            'repeat_rate' => array_values($repeatRate),

            // 🔥 NEW DATA
            'total_unique_members' => $totalUniqueUsers,
            'overall_repeat_rate' => $overallRepeatRate
        ]);
    }

    public function campaignPerformanceData()
    {
        $data = DB::table('rewards')
            ->select(
                'name as campaign',
                DB::raw('SUM(purchased_qty) as issued'),
                DB::raw("SUM(CASE WHEN status = 'redeemed' THEN 1 ELSE 0 END) as redeemed")
            )
            ->groupBy('name')
            ->get();

        $labels = [];
        $issued = [];
        $redeemed = [];
        $rate = [];

        foreach ($data as $row) {

            $labels[] = $row->campaign;

            $issued[] = $row->issued ?? 0;
            $redeemed[] = $row->redeemed ?? 0;

            $rate[] = $row->issued > 0
                ? round(($row->redeemed / $row->issued) * 100, 2)
                : 0;

                $issued[] = (int) $row->issued ?? 0;
                $redeemed[] = (int) $row->redeemed ?? 0;

                $rate[] = ($row->issued > 0)
                    ? round(($row->redeemed / $row->issued) * 100, 2)
                : 0;
        }

        
        return response()->json([
            'labels' => $labels,
            'issued' => $issued,
            'redeemed' => $redeemed,
            'rate' => $rate
        ]);
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar = '/images/' . $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            return response()->json([
                'isSuccess' => true,
                'Message' => "User Details Updated successfully!"
            ], 200); // Status code here
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            return response()->json([
                'isSuccess' => false,
                'Message' => "Something went wrong!"
            ], 200); // Status code here
        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }

    public function checkMember(SafraService $safra)
    { 

        $response = $safra->call(
            'sfrControlMember/GetBasicDetailInfoByModified',
            [
                'LastModifiedTime' => '1994-09-17T00:00:00',
                'Limit' => 1,
            ],
            'request'
        );

        return response()->json($response->json());
    }
}
