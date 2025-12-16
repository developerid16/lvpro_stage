<?php

namespace App\Http\Controllers;

use App\Mail\RewardExpiredMail;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Reward;
use App\Models\AppUser;
use App\Models\RefundSale;
use App\Mail\AccountVerify;
use App\Mail\APHExpiry;
use App\Mail\BroadcastEmail;
use App\Mail\ForgotPassword;
use App\Mail\KeyExpiry;
use App\Models\BrandMapping;
use Illuminate\Http\Request;
use App\Mail\NewAdminRegister;
use App\Models\KeyPassbookDebit;
use App\Models\KeyPassbookCredit;
use App\Models\UserAccessRequest;
use Illuminate\Support\Facades\DB;
use App\Models\UserPurchasedReward;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

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
            new BroadcastEmail("subject", '<p>Changes</p>')
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
            //\DB::connection()->enableQueryLog();
    
    
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
    
    
    
            $users = AppUser::whereStatus('Active')->whereBetween('created_at', array($startDate, $endDate))->get();
            $usersAll = AppUser::whereBetween('created_at', array($startDate, $endDate))->get();
    
            $master['active_user'] =  $users->count();
    
            $master['active_reward'] =  Reward::whereStatus('Active')->whereBetween('created_at', array($startDate, $endDate))->count();
            $master['total_transaction'] =  Sale::whereBetween('created_at', array($startDate, $endDate))->count();
            $master['total_buy_reward'] =  UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->count();
            $master['total_redeemed_reward'] =  UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->whereStatus('Redeemed')->get()->count();
            $master['total_user'] = $usersAll->count();
    
    
            $chart['total_user_male'] = $usersAll->where('gender', 'Male')->count();
            $chart['total_user_female'] = $usersAll->where('gender', 'Female')->count();
            $chart['active_user_male'] =  $users->where('gender', 'Male')->count();
            $chart['active_user_female'] =  $users->where('gender', 'Female')->count();
            $weekchartname = [];
            $weekchartcustomer = [];
            $weekchartredmtion = [];
            $weekchartcomplatd = [];
            $weekchartsale = [];
    
            $today = Carbon::now()->subDay(6);
            for ($i = 1; $i <= 7; $i++) {
                $ymd = $today->format('Y-m-d');
                $usp = UserPurchasedReward::whereDate('created_at', $ymd)->get();
                $weekchartcustomer[] = AppUser::whereDate('created_at', $ymd)->count();
                $weekchartredmtion[] =  $usp->count();
                $weekchartcomplatd[] = $usp->where('status', 'Redeemed')->count();
                $weekchartname[] = $today->format('l');
                $weekchartsale[] =  Sale::whereDate('date', $ymd)->get()->sum('sale_amount');
                $today = $today->addDay();
            }
    
    
    
            $chart['week'] =  [
                "weekchartcustomer" => $weekchartcustomer,
                "weekchartredmtion" => $weekchartredmtion,
                "weekchartcomplatd" => $weekchartcomplatd,
                "weekchartname" => $weekchartname,
                "weekchartsale" => $weekchartsale,
            ];
            $monthnames = [];
            $monthpurches = [];
            $monthsignup = [];
            $today = Carbon::now()->subMonths(11);
            for ($i = 1; $i <= 12; $i++) {
                $ymd = $today->format('Y-m-d');
                $start = $today->copy()->startOfMonth();
                $end  =  $today->copy()->endOfMonth();
                $amt =  Sale::whereBetween('date', [$start->format('Y-m-d') . ' 00:00:00', $end->format('Y-m-d') . ' 23:23:59'])->get()->sum('sale_amount');
    
                $signup =  AppUser::whereBetween('created_at', [$start->format('Y-m-d') . ' 00:00:00', $end->format('Y-m-d') . ' 23:23:59'])->count();
    
    
    
                $monthpurches[] = round($amt, 2);
                $monthsignup[] =  $signup;
                $monthnames[] = $today->format('M Y');
                $today = $today->addMonth();
            }
            $chart['month'] =  [
                'monthnames' => $monthnames,
                'monthpurches' => $monthpurches,
                'monthsignup' => $monthsignup,
            ];
    
    
            $today = Carbon::now()->subYear();
    
            $lastYdate = Carbon::now()->subYear()->format('Y') . '-01-01';
            $top10keyearn =  KeyPassbookCredit::with('user:id,name')->select('*', DB::raw('sum(no_of_key) as tk'))->orderBy('tk', 'desc')->whereBetween('created_at', [$startDate->format('Y-m-d') . ' 00:00:00', $endDate->format('Y-m-d') . ' 23:23:59'])->groupBy('user_id')->limit(10)->get();
    
    
            $yearsData = [];
            // for ($i = 0; $i < 1; $i++) {
            //     $y = $today->format('Y');
            //     // $kpc = KeyPassbookCredit::select('*', DB::raw('sum(no_of_key) as tk'))->whereYear('created_at', $y)->groupBy('user_id')->get()->pluck('tk', 'user_id');
            //     $yearsData[$y] = 0;
            //     $today = $today->addYear();
            // }
    
            $last3month = Carbon::now()->subMonths(3);
            $usp =
    
                // $master['top5Redemption'] = Reward::orderBy('total_redeemed', 'DESC')->where('total_redeemed', '>', 0)->limit(5)->get(['name', 'id', 'total_redeemed']);
                $master['top5Redemption'] =
                UserPurchasedReward::select('id', 'reward_id', DB::raw('count(*) as count'))->with('reward:id,name')->whereDate('created_at', '>=', $last3month)->orderBy('count', 'desc')->groupBy('reward_id')->limit(5)->get();
            // return $master['top5Redemption'];
            $master['top5Refund']     = RefundSale::whereBetween('created_at', array($startDate, $endDate))->with('user')->has('user')->select('*', DB::raw('count(*) as count'), DB::raw('sum(sale_amount) as tsl'))->limit(5)->groupBy('user_id')->orderBy('tsl', 'desc')->get();
            $master['lowstock']  = Reward::whereBetween('created_at', [$startDate->format('Y-m-d') . ' 00:00:00', $endDate->format('Y-m-d') . ' 23:23:59'])->orderByRaw('quantity - total_redeemed ASC')->where('quantity', '>', 0)->limit(10)->get();
            foreach ($master['lowstock'] as $key => $value) {
                $master['lowstock'][$key]['quantity'] = number_format($value->quantity);
                $master['lowstock'][$key]['total_redeemed'] = number_format($value->total_redeemed);
                $master['lowstock'][$key]['balance'] = $value->quantity == 0 ? 'Unlimited Stock' : number_format($value->quantity - $value->total_redeemed);
                $master['lowstock'][$key]['redeemed'] = number_format(UserPurchasedReward::where([['status', 'Redeemed'], ['reward_id', $value->id]])->count());
            }
    
    
    
            $ranges = [ // the start of each age-range.
                '18-28',
                '29-38',
                '39-48',
                '49-58',
                '58-100'
            ];
    
            $rangeWithSales = [];
            $rangeWithCount = [];
            foreach ($ranges as $key => $age) {
                $aa = explode('-', $age);
    
                $userid =  AppUser::where('status', 'Active')->agedBetween($aa[0], $aa[1])->pluck('id');
    
                $sl = Sale::whereIn('user_id', $userid)->sum('sale_amount');
    
                $rangeWithSales[] = round($sl, 2);
                $rangeWithCount[] = count($userid);
            }
    
    
            $chart['ranges'] = $ranges;
            $chart['rangeWithCount'] = $rangeWithCount;
            $chart['rangeWithSales'] = $rangeWithSales;
    
    
            $lcendTime = $endDate->copy();
            $lcstartTime  = $startDate->copy();
            $allCycle = [];
            for ($i = 0; $i <= 1; $i++) {
    
                $debitKey =  KeyPassbookDebit::whereBetween('created_at', [$lcstartTime->format('Y-m-d') . ' 00:00:01', $lcendTime->format('Y-m-d') . ' 23:59:59'])->sum('key_use');
                $creditKey =   KeyPassbookCredit::whereBetween('created_at', [$lcstartTime->format('Y-m-d') . ' 00:00:01', $lcendTime->format('Y-m-d') . ' 23:59:59'])->sum('no_of_key');
    
                $allCycle[] = [
                    'debitKey' => $debitKey,
                    'creditKey' => $creditKey,
                    'remain' => $creditKey - $debitKey,
                    'cycle' => $lcstartTime->format('M j Y') . ' - ' . $lcendTime->format('M j Y'),
                ];
                // $startMonth = Carbon::create($now->subYear()->format('Y'), 9, 01, 0, 0, 0);
                $lcendTime = $lcendTime->subYear();
                $lcstartTime = $lcstartTime->subYear();
            }
    
             $rewards = Reward::whereBetween('created_at', array($startDate, $endDate))->get();
    
    
    
            $rewardWithType = [];
    
    
            $activeRewards = $rewards->where('status', 'Active');
    
            $activeP = UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->whereIn('reward_id', $activeRewards->pluck('id'))->get();
            $activeReward['name'] = "Active Reward";
            $activeReward['Issued'] =  $activeP->where('status', 'Purchased')->count();
            $activeReward['Total'] =  $activeP->count();
            $q = $activeRewards->sum('quantity');
            $is = $activeRewards->where('quantity', '>', 0)->sum('total_redeemed');
            $activeReward['Balance'] =  $q -  $is;
            $activeReward['Redeemed'] =  $activeP->where('status', 'Redeemed')->count();
            $rewardWithType[] = $activeReward;
    
            $activeRewards = $rewards->where('status', 'Disabled');
    
            $activeP = UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->whereIn('reward_id', $activeRewards->pluck('id'))->get();
            $activeReward['name'] = "Disabled Reward";
            $activeReward['Issued'] =  $activeP->where('status', 'Purchased')->count();
            $activeReward['Total'] =  $activeP->count();
            $q = $activeRewards->sum('quantity');
            $is = $activeRewards->where('quantity', '>', 0)->sum('total_redeemed');
            $activeReward['Balance'] =  $q -  $is;
            $activeReward['Redeemed'] =  $activeP->where('status', 'Redeemed')->count();
            $rewardWithType[] = $activeReward;
    
    
            $activeRewards = $rewards->where('status', 'Expired');
    
            $activeP = UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->whereIn('reward_id', $activeRewards->pluck('id'))->get();
            $activeReward['name'] = "Expired Reward";
            $activeReward['Issued'] =  $activeP->where('status', 'Purchased')->count();
            $activeReward['Total'] =  $activeP->count();
            $q = $activeRewards->sum('quantity');
            $is = $activeRewards->where('quantity', '>', 0)->sum('total_redeemed');
            $activeReward['Balance'] =  $q -  $is;
            $activeReward['Redeemed'] =  $activeP->where('status', 'Redeemed')->count();
            $rewardWithType[] = $activeReward;
    
    
    
            $activeRewards = $rewards->where('reward_type', '0');
    
    
            $activeP = UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->whereIn('reward_id', $activeRewards->pluck('id'))->get();
            $activeReward['name'] = "Cash Reward";
            $activeReward['Issued'] =  $activeP->where('status', 'Purchased')->count();
            $activeReward['Total'] =  $activeP->count();
            $q = $activeRewards->sum('quantity');
            $is = $activeRewards->where('quantity', '>', 0)->sum('total_redeemed');
            $activeReward['Balance'] =  $q -  $is;
            $activeReward['Redeemed'] =  $activeP->where('status', 'Redeemed')->count();
            $rewardWithType[] = $activeReward;
    
    
            $activeRewards = $rewards->where('reward_type', '1');
    
    
            $activeP = UserPurchasedReward::whereBetween('created_at', array($startDate, $endDate))->whereIn('reward_id', $activeRewards->pluck('id'))->get();
            $activeReward['name'] = "Product Reward";
            $activeReward['Issued'] =  $activeP->where('status', 'Purchased')->count();
            $activeReward['Total'] =  $activeP->count();
            $q = $activeRewards->sum('quantity');
            $is = $activeRewards->where('quantity', '>', 0)->sum('total_redeemed');
            $activeReward['Balance'] =  $q -  $is;
            $activeReward['Redeemed'] =  $activeP->where('status', 'Redeemed')->count();
    
            $rewardWithType[] = $activeReward;
    
    
    
    
    
            $salesSku =  Sale::whereBetween('created_at', array($startDate, $endDate))->select('id', 'sku', DB::raw('sum(quantity_purchased) as count'))->where('sku', '!=', '')->has('brand')->with('brand:id,product_name,sku')->orderBy('count', 'desc')->groupBy('sku')->limit(5)->get();
            $transactionSku =  Sale::whereBetween('created_at', array($startDate, $endDate))->select('id', 'sku', DB::raw('sum(sale_amount) as total_amount'))->where('sku', '!=', '')->has('brand')->with('brand:id,product_name,sku')->orderBy('total_amount', 'desc')->groupBy('sku')->limit(5)->get();
    
            // $skusss = ['166113000033', '166113000044', '063312000256', '664710000302', '094912000263'];
            // foreach ($salesSku as $key => $value) {
            //     Sale::where('sku', $value->sku)->update([
            //         'sku' => $skusss[$key]
            //     ]);
            // }
    
            $salesLocation =  Sale::whereBetween('created_at', array($startDate, $endDate))->select('id', 'location', DB::raw('sum(quantity_purchased) as count'))->orderBy('count', 'desc')->groupBy('location')->limit(5)->get();
    
            $transactionLocation =  Sale::whereBetween('created_at', array($startDate, $endDate))->select('id', 'location', DB::raw('sum(sale_amount) as total_amount'))->orderBy('total_amount', 'desc')->groupBy('location')->limit(5)->get();
    
    
    
    
            $salesBrand =  Sale::whereBetween('created_at', array($startDate, $endDate))->select('id', 'brand_code', 'sku', DB::raw('sum(quantity_purchased) as count'))->has('brand')->with('brand:id,brand_name,sku')->orderBy('count', 'desc')->groupBy('brand_code')->limit(5)->get();
            $transactionBrand =  Sale::whereBetween('created_at', array($startDate, $endDate))->select('id', 'brand_code', 'sku', DB::raw('sum(sale_amount) as total_amount'))->has('brand')->with('brand:id,brand_name,sku')->orderBy('total_amount', 'desc')->groupBy('brand_code')->limit(5)->get();
    
            $master['salesSku']  = $salesSku;
            $master['salesLocation']  = $salesLocation;
            $master['salesBrand']  = $salesBrand;
    
            $master['transactionLocation']  = $transactionLocation;
            $master['transactionSku']  = $transactionSku;
            $master['transactionBrand']  = $transactionBrand;
    
    
    
    
    
    
    
            $master['allCycle']  = $allCycle;
            $master['redemptionsStatus']  = $rewardWithType;
    
            // $queryLog = \DB::getQueryLog();
    
    
            return view('index', compact('master', 'chart', 'yearsData', 'top10keyearn'));
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
                'isSuccess' => true,
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

   
}
