<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AppUser;
use Illuminate\Http\Request;
use App\Models\AircrewCompany;
use App\Models\KeyPassbookDebit;
use App\Models\KeyPassbookCredit;
use App\Models\PassholderCompany;
use Spatie\Permission\Models\Role;
use App\Models\UserPurchasedReward;
use App\Http\Controllers\Controller;
use App\Models\RefundSale;
use App\Models\Sale;
use App\Models\UserPurchasedRewardLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AppUserController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {


        $this->view_file_path = "admin.app-user.";
        $permission_prefix = $this->permission_prefix = 'app-user';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Application User',
            'module_base_url' => url('admin/app-user')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        // $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update', 'editUser']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = AppUser::query();



        // • Create:
        //  ‣Customer Type(Airport Pass Holder, Aircrew), APHID, Name, Email, Password, Mobile Number, Gender, Date of Birth, APH expiry date, Company, Employment ID, Referral Code.
        $searched_from_relation = [];
        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'email',  'status', 'user_type', 'unique_id', 'gender', 'phone_number'], $searched_from_relation, []);

        $final_data = [];


        foreach ($query['data']->get() as $key => $row) {
            $row->setAppends(['company_data']);
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['user_type'] = $row->user_type;
            $final_data[$key]['unique_id'] = $row->unique_id;
            $final_data[$key]['gender'] = $row->gender;
            $final_data[$key]['phone_number'] = $row->country_code . ' ' . $row->phone_number;
            $final_data[$key]['date_of_birth'] =$row->date_of_birth ? $row->date_of_birth->format(config('shilla.date-format')) : '';
            $final_data[$key]['created_at'] =$row->created_at ? $row->created_at->format(config('shilla.date-format')) : '';
            $final_data[$key]['expiry_date'] = $row->expiry_date ? $row->expiry_date->format(config('shilla.date-format')) : '';
            $final_data[$key]['my_code'] = $row->my_code;
            $final_data[$key]['company_name'] = $row->company_data->name ?? '';
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['email'] = $row->email;
            $final_data[$key]['status'] = $row->status;
            if ($row->status === 'Inactive' || $row->status === 'Blacklist') {
                $final_data[$key]['status'] .= "<i class='mdi mdi-information text-primary action-icon font-size-18' title='$row->blacklist_reason'></i>";
            }




            $url = route('admin.app-user.show', ['app_user' => $row->id]);
            $editurl = route('admin.app-user-edit', ['id' => $row->id]);
            $transactionsurl = route('admin.app-user-transactions', ['id' => $row->id]);
            $action = "<div class='d-flex gap-3'>";

            $action .= "<a href='$url' class='edit' data-id='$row->id'><i class='mdi mdi-eye text-primary action-icon font-size-18'></i></a>";

            $action .= "<a href='$editurl' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            $action .= "<a href='$transactionsurl' class='edit' data-id='$row->id'><i class='mdi mdi-credit-card text-primary action-icon font-size-18'></i></a>";


            $final_data[$key]['action'] = $action . "</div>";
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function editUser($id)
    {
        $data = Appuser::findOrFail($id);
        if ($data->user_type === 'Aircrew') {
            $company =  AircrewCompany::where('status', 'Active')->orderBy('name')->get(['name', 'code', 'id']);
        } else {
            $company =  PassholderCompany::where('status', 'Active')->orderBy('name')->get(['name', 'code', 'id']);
        }

        // dd( $data );
        return view($this->view_file_path . "edit", compact('data', 'company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Appuser::findOrFail($id);
        $lastYear = Carbon::now()->subDays(365);
        $rewards = UserPurchasedReward::with('reward')->where('user_id', $user->id)->whereDate('created_at', '>=', $lastYear)->get();

        $keysData = collect([]);

        $keysDebit =  KeyPassbookDebit::where([['user_id', $user->id]])->whereIn('type', ['admin'])->whereDate('created_at', '>=', $lastYear)->get();

        foreach ($keysDebit as $item) {
            $keysData->push([
                "keys" =>  $item->key_use,

                "total" => 0,
                'type' => 'Credit',
                "meta_data" => $item->meta_data,

                "date" => $item->created_at
            ]);
        }

        $keysCredit =  KeyPassbookCredit::where([['user_id', $user->id]])->whereIn('earn_way',  ['admin_credit'])->whereDate('created_at', '>=', $lastYear)->get();
        foreach ($keysCredit as $item) {
            $keysData->push([
                "keys" =>  $item->no_of_key,
                'type' => 'Debit',
                "total" => 0,
                "meta_data" => $item->meta_data,
                "date" => $item->created_at
            ]);
        }

        $keysData = $keysData->sortBy([
            ['date', 'desc'],
        ]);
        return view($this->view_file_path . "show", compact('user', 'rewards', 'keysData'));
    }
    public function userTransactions(string $id, Request $request)
    {


        $user = Appuser::findOrFail($id);

        $filter  =  $request->all();





        $masterData = collect([]);

        $lastYear = Carbon::today()->subYears(2);

        $sales = collect([]);
        if ($request->filter && $request->filter === 'transactions') {
            $sales = Sale::where('user_id', $id)->whereDate('date', '>=', $lastYear)->orderBy('date', 'desc')->selectRaw(' * , sum(sale_amount) as tot_sale_amount,sum(key_earn) as tot_key_earn,sum(limit_reach) as tot_limit_reach')->orderBy('system_time', 'desc')->groupBy('ref')->get();
        } elseif (!$request->filter) {
            $sales = Sale::where('user_id', $id)->whereDate('date', '>=', $lastYear)->orderBy('date', 'desc')->selectRaw(' * , sum(sale_amount) as tot_sale_amount,sum(key_earn) as tot_key_earn,sum(limit_reach) as tot_limit_reach')->orderBy('system_time', 'desc')->groupBy('ref')->get();
        }


        $allRecords = Sale::whereIn('ref', $sales->pluck('ref'))->get();
        foreach ($sales as   $value) {
            $saleProduct = $allRecords->where('ref', $value->ref);

            $saleProduct->all();

            $temp = [];
            foreach ($saleProduct as $product) {
                $temp[] = [
                    'sku' => $product->sku,
                    'amount' =>  numberFormat($product->sale_amount, true)
                ];
            }

            $masterData->push([
                "text" => "Receipt No: " . $value->ref,
                "keys" => $value->tot_key_earn,
                "total" =>  $value->tot_sale_amount,
                "limit_reach" =>  $value->tot_limit_reach,
                'type' => 'positive',
                'products' => $temp,
                'key_text'=>"Keys Earned:",
                "date" => Carbon::createFromFormat('Y-m-d H:i:s', $value->date->format('Y-m-d') . ' ' . $value->system_time)
            ]);
        }

        // all purchase keys
        $uprs = [];
        if ($request->filter && $request->filter === 'purchased') {
            $uprs = UserPurchasedReward::with('reward:name,id')->where([['user_id', $id], ['key_use', '>', 0]])->whereDate('created_at', '>=', $lastYear)->get();
        } elseif (!$request->filter) {
            $uprs = UserPurchasedReward::with('reward:name,id')->where([['user_id', $id], ['key_use', '>', 0]])->whereDate('created_at', '>=', $lastYear)->get();
        }

        foreach ($uprs as $item) {
            $masterData->push([
                "text" => $item->reward->name,
                "keys" =>  $item->key_use,
                "total" => 0,
                'type' => 'negative',
                "date" => $item->created_at
            ]);
        }



        // get refund keys for
        $refundSales = collect([]);
        if ($request->filter && $request->filter === 'transactions') {
            $refundSales = RefundSale::where('user_id', $id)->whereDate('date', '>=', $lastYear)->orderBy('date', 'desc')->selectRaw('*, sum(sale_amount) as tot_sale_amount,sum(key_earn) as tot_key_earn')->orderBy('system_time', 'desc')->groupBy('ref')->get();
        } elseif (!$request->filter) {
            $refundSales = RefundSale::where('user_id', $id)->whereDate('date', '>=', $lastYear)->orderBy('date', 'desc')->selectRaw('*, sum(sale_amount) as tot_sale_amount,sum(key_earn) as tot_key_earn')->orderBy('system_time', 'desc')->groupBy('ref')->get();
        }
        $allRecords = RefundSale::whereIn('ref', $refundSales->pluck('ref'))->get();
      
        foreach ($refundSales as   $item) {

            $saleProduct = $allRecords->where('ref', $item->ref);
  
            $saleProduct->all();
            $temp = [];
            foreach ($saleProduct as $product) {
                $temp[] = [
                    'sku' => $product->sku,
                    'amount' =>  numberFormat($product->sale_amount, true)
                ];
            }

            $masterData->push([
                "text" => "Receipt No: " . $item->ref,
                "keys" => $item->tot_key_earn,
                "text_original" => "Original Receipt No: " .  $item->org_rec_no,

                "total" =>  $item->tot_sale_amount,
                'type' => 'negative',
                'products' => $temp,
                "date" => Carbon::createFromFormat('Y-m-d H:i:s', $item->date->format('Y-m-d') . ' ' . $item->system_time)
            ]);
        }
        //  foreach ($refundSales as $item) {

        //     $masterData->push([
        //         "text" => "Receipt No: " .  $item->ref,
        //         "text_original" => "Original Receipt No: " .  $item->org_rec_no,
        //         "keys" =>  $item->key_earn,
        //         "total" => $item->sale_amount,
        //         'type' => 'negative',
        //         "date" => Carbon::createFromFormat('Y-m-d H:i:s', $item->date->format('Y-m-d') . ' ' . $item->system_time)
        //     ]);
        // }

        // takes only Admin
        $keysDebit = [];
        if ($request->filter && $request->filter === 'refund') {
            $keysDebit =  KeyPassbookDebit::where([['user_id', $id]])->whereIn('type', ['admin', 'milestone_abandoned'])->whereDate('created_at', '>=', $lastYear)->get();
        } elseif (!$request->filter) {
            $keysDebit =  KeyPassbookDebit::where([['user_id', $id]])->whereIn('type', ['admin', 'milestone_abandoned'])->whereDate('created_at', '>=', $lastYear)->get();
        }

        foreach ($keysDebit as $item) {
            $masterData->push([
                "text" => $item->type === 'milestone_abandoned' ? 'Milestone Void' : "Admin Credit Key - " . $item->meta_data,
                "keys" =>  $item->key_use,
                "total" => 0,
                'type' => 'negative',
                "date" => $item->created_at
            ]);
        }


        // get all credit keys
        $keysCredit = [];
        if ($request->filter && $request->filter === 'referral') {
            $keysCredit =  KeyPassbookCredit::where([['user_id', $id]])->where(function ($query) {
                $query->where('earn_way',  'referral_bounce_earn')
                    ->orWhere('earn_way',  'referral_bounce');
            })->whereDate('created_at', '>=', $lastYear)->get();
        } else   if ($request->filter && $request->filter === 'milestone') {
            $keysCredit =  KeyPassbookCredit::where([['user_id', $id],])->whereIn('earn_way',  ['milestone_reached', 'referral_bounce_earn', 'referral_bounce'])->whereDate('created_at', '>=', $lastYear)->get();
        } elseif (!$request->filter) {
            $keysCredit =  KeyPassbookCredit::where([['user_id', $id], ['earn_way', '!=', 'spending_amount']])->whereDate('created_at', '>=', $lastYear)->get();
        }


        foreach ($keysCredit as $value) {

            $type = '';
            switch ($value->earn_way) {
                case 'milestone_reached':
                    # code...
                    $type = "Milestone reached ($value->meta_data)";
                    break;
                case 'spending_amount':
                    # code...
                    $type = "Spending reached";

                    break;
                case 'referral_bounce_earn':
                    # code...
                    $type = "Referral";

                    break;
                case 'referral_bounce':
                    $type = "Referral";
                    # code...
                    break;
                case 'admin_credit':
                    $type = "Admin Debit Key - " . $value->meta_data;
                    # code...
                    break;

                default:
                    # code...
                    $type = "Earn";

                    break;
            }
            $masterData->push([
                "text" => $type,
                "keys" =>  $value->no_of_key,
                'type' => 'positive',
                "total" => 0,
                "date" => $value->created_at
            ]);
        }
        // User Purchess Logs

        $logs =   UserPurchasedRewardLogs::where([['user_id', $id], ['action', 'Expiry Date Change']])->whereDate('created_at', '>=', $lastYear)->get();
        foreach ($logs as $log) {

            $masterData->push([
                "text" => $log->full_str,
                "keys" =>  $log->keys,
                'type' => $log->action == 'Admin Deleted' ? 'positive' : 'negative',
                "total" => 0,
                "date" => $log->created_at
            ]);
        }
        // END

        if (
            $request->has('sort') && $request->sort === 'asc'
        ) {

            $masterData = $masterData->sortBy([
                ['date', 'asc'],
            ]);
        } else {
            $masterData = $masterData->sortBy([
                ['date', 'desc'],
            ]);
        }



        $masterData = $masterData->values()->all();

        return view($this->view_file_path . "transactions", compact('user', 'masterData', 'filter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->layout_data['data'] = User::with('roles')->find($id);
        $this->layout_data['role'] = Role::where('name', '!=', 'Super Admin')->get();
        $this->layout_data['assign_roles'] = $this->layout_data['data']->roles->pluck('name');

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {

       
        $user = AppUser::find($id);
         // 'email' => 'regex:/^.+@.+$/i'.
        if ($user->user_type === 'Aircrew') {
            $post_data = $this->validate(
                $request,
                [
                    'name' => 'required',
                    'gender' => 'required',
                    'aircrew_unique' => 'required',
                    'company_id' => 'nullable',
                    'email' => "required|email|unique:app_users,email,$id",
                    'phone_number' => "required|numeric|unique:app_users,phone_number,$id",
                    'date_of_birth' => "required|date",
                    'country_code' => 'required',
                    'c_name' => 'required_if:company_id,null',
                    'c_code' => 'required_if:company_id,null',
                    'status' => 'required',
                    'blacklist_reason' => 'required_if:status,Inactive,Blacklist',
                    'password' => 'nullable|min:8',
                    'password_reason' => 'required_with:password',
                ],
                [
                    'c_name.required_if' => 'Please enter a company name',
                    'c_code.required_if' => 'Please enter a company code',
                ]
            );
        } else {
            $post_data = $this->validate($request, [
                'name' => 'required',
                'gender' => 'required',
                'email' => "required|email|unique:app_users,email,$id",
                'phone_number' => "required|numeric|unique:app_users,phone_number,$id",
                'date_of_birth' => "required|date",
                'country_code' => 'required',
                'expiry_date' => 'sometimes|date',
                'unique_id' => "required|unique:app_users,unique_id,$id|regex:/^S[a-zA-Z0-9]{7}$/",
                'company_id' => 'sometimes',
                'c_name' => 'required_if:company_id,null',
                'c_code' => 'required_if:company_id,null',
                'status' => 'required',
                'blacklist_reason' => 'required_if:status,Inactive,Blacklist',
                'password' => 'nullable|min:8',
                'password_reason' => 'required_with:password',

            ], [
                'c_name.required_if' => 'Please enter a company name',
                'unique_id.regex' => 'Please provide S with 7 digit.',
                'unique_id.unique' => 'THis APH is already registered.',
                'c_code.required_if' => 'Please enter a company code',
            ]);
        }

        if ($request->company_id) {
            unset($post_data['c_code']);
            unset($post_data['c_name']);
        }


        if ($post_data['status'] !== 'Blacklist' && $post_data['status'] !== 'Inactive') {

            $post_data['blacklist_reason'] = '';
            if (isset($post_data['expiry_date'])) {
                $exd = Carbon::parse($post_data['expiry_date']);

                if ($exd->gt(Carbon::now()) && $user->status === 'Expired') {
                    $post_data['status'] = 'Active';
                }
            }
        }
        if (isset($post_data['password']) && $post_data['password']) {
            $post_data['password'] = Hash::make($request->password);
            $post_data['password_reset'] = 1; 
        } else {
            unset($post_data['password']);
        }
 
         $user->update($post_data);
          return redirect('admin/app-user');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'User Delete Successfully']);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function adminKeyDebitCredit(Request $request)
    {
        $request->validate([
            'keys' => 'required|numeric|min:1',
            'type' => 'required',
            'reason' => 'required|max:50',
            'app_reason' => 'required|max:50',
        ]);
        $adminName = Auth::user()->name . ' (' . Auth::user()->email . ')';
        $user = Appuser::findOrFail($request->user_id);

        if ($request->type == 'debit') {
            KeyPassbookDebit::create([
                'user_id' => $request->user_id,
                'key_use' => $request->keys,
                'type' => "admin",
                'app_reason' => $request->app_reason,

                'meta_data' => 'Keys Adjustment by ' . $adminName . ' For ' . $request->reason,

            ]);
            $dk =  $request->keys;
            // lets debit key on by one
            $availableKeys =  KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $request->user_id]])->whereDate('expiry_date', '>=', Carbon::today())->orderBy('id', 'asc')->get();
            $nokeys  = $request->keys;
            $i = 0;
            while ($nokeys > 0 && count($availableKeys) > $i) {
                $ak = $availableKeys[$i];
                if ($ak->remain_keys >= $nokeys) {
                    // first expred key find that full fill order no need to go further
                    $keyused = $nokeys;
                    $nokeys = 0;
                } else {
                    // need to go further for next reward this reward dont have avaibaled key to fill order
                    $keyused = $ak->remain_keys;
                    $nokeys -= $ak->remain_keys;
                }

                $ak->decrement('remain_keys', $keyused);
                $i++;
            }
            $user->decrement('available_key', $dk);
        } else {

            $rk = $request->keys;
            // check if user has already in negative value as key

            if ($user->available_key < 0) {
                // remove nagetive value then add key to passbook
                // dd($user->available_key, $rk, $user->available_key +  $rk);
                $rk = $user->available_key +  $rk;
                // $user->increment('available_key', $request->keys - $rk);

                if ($rk <= 0) {

                    $user->available_key = $rk;
                    $user->save();
                } else {
                    $user->available_key = 0;
                    $user->save();
                }
            }
            KeyPassbookCredit::create([
                'user_id' => $request->user_id,
                'no_of_key' => $request->keys,
                'remain_keys' => $rk < 0 ? 0 : $rk,
                'earn_way' => 'admin_credit',
                'meta_data' => 'Keys Adjustment by ' . $adminName . ' For ' . $request->reason,
                'app_reason' => $request->app_reason,
                'expiry_date' => keyExpiryDate()
            ]);
            // $user->increment('available_key', $request->keys);

        }
        return redirect()->back()->with('message', "Entry added successfully");
    }
}
