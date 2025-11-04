<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Reward;
use App\Models\AppUser;
use App\Models\GroupUser;
use App\Models\VoucherLogs;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\KeyPassbookDebit;
use App\Models\KeyPassbookCredit;
use Illuminate\Support\Facades\DB;
use App\Models\UserPurchasedReward;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CampaignVoucherGroup;
use App\Models\CampaignVoucherLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


use Illuminate\Support\Facades\Validator;
 




class CampaignVoucherGroupController extends Controller
{

    function __construct()
    {
        $this->view_file_path = "admin.campaign-voucher-group.";
        $permission_prefix = $this->permission_prefix = 'campaign-voucher-group';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Campaign Voucher Group',
            'module_base_url' => url('admin/campaign-voucher-group')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view($this->view_file_path . "create")->with($this->layout_data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $group = CampaignVoucherGroup::create([
            'name' => $request->name,
        ]);

        $isCheckBox = false;
        $insertData = [];
        if ($request->has('user') && $request->user == 1) {
            // all user are allowed to add
            $isCheckBox = true;
            $checkUsers = AppUser::pluck('id');
        } else {
            $appUsers = AppUser::query();
            if ($request->has('female') && $request->female == 1) {
                // only female are allowed to add
                $isCheckBox = true;
                $appUsers->orWhere('gender', 'Female');
            }
            if ($request->has('male') && $request->male == 1) {
                // only male are allowed to add
                $isCheckBox = true;
                $appUsers->orWhere('gender', 'Male');
            }
            if ($request->has('aph') && $request->aph == 1) {
                // only male are allowed to add
                $isCheckBox = true;
                $appUsers->orWhere('gender', 'Airport Pass Holder');
            }
            if ($request->has('aircrew') && $request->aircrew == 1) {
                // only male are allowed to add
                $appUsers->orWhere('gender', 'Aircrew');
                $isCheckBox = true;
            }
            $checkUsers = $appUsers->pluck('id');
        }

        if ($isCheckBox === false) {

            foreach ($request->users as  $value) {
                $insertData[] = [
                    'group_id' => $group->id,
                    'user_id' => $value
                ];
            }
        } else {
            foreach ($checkUsers as  $value) {
                $insertData[] = [
                    'group_id' => $group->id,
                    'user_id' => $value
                ];
            }
        }

        GroupUser::insert($insertData);
        return redirect('admin/campaign-voucher-group');
        //
    }

    public function datatable(Request $request)
    {
        $query = CampaignVoucherGroup::withCount('users');

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;

            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['total_reward'] = $row->reward_count;
            $final_data[$key]['total_person'] = number_format($row->users_count);

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $url = url("admin/campaign-voucher-group/$row->id/edit");
                $action .= "<a href='$url' class='edit'  ><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            $final_data[$key]['action'] = $action . "</div>";
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }

    /**
     * Display the specified resource.
     */
    public function show(CampaignVoucherGroup $campaignVoucherGroup)
    {
        // users.user
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $data =  CampaignVoucherGroup::findOrFail($id);
        $data->load('users.user:id,email');


        $this->layout_data['data']  = $data;
        return view($this->view_file_path . "create")->with($this->layout_data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //

        $group = CampaignVoucherGroup::findOrFail($id);

        $group->name = $request->name;
        $group->save();
        $isCheckBox = false;
        $insertData = [];
        GroupUser::where('group_id', $id)->delete();

        if ($request->has('user') && $request->user == 1) {
            // all user are allowed to add
            $isCheckBox = true;
            $checkUsers = AppUser::pluck('id');
        } else {
            $appUsers = AppUser::query();
            if ($request->has('female') && $request->female == 1) {
                // only female are allowed to add
                $isCheckBox = true;
                $appUsers->orWhere('gender', 'Female');
            }
            if ($request->has('male') && $request->male == 1) {
                // only male are allowed to add
                $isCheckBox = true;
                $appUsers->orWhere('gender', 'Male');
            }
           
            $checkUsers = $appUsers->pluck('id');
        }

        if ($isCheckBox === false) {

            foreach ($request->users as  $value) {
                $insertData[] = [
                    'group_id' => $group->id,
                    'user_id' => $value
                ];
            }
        } else {
            foreach ($checkUsers as  $value) {
                $insertData[] = [
                    'group_id' => $group->id,
                    'user_id' => $value
                ];
            }
        }

        GroupUser::insert($insertData);
        return redirect('admin/campaign-voucher-group');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        GroupUser::where('group_id', $id)->delete();
        CampaignVoucherGroup::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Reward Delete Successfully']);
    }



    public function assignIndex($id)
    {

        $data = Reward::where([['id', $id]])->firstOrFail();
        $this->layout_data['data'] = $data;
        $this->layout_data['logs'] = CampaignVoucherLog::where('reward_id', $id)->latest()->get();

        $this->layout_data['group'] =            CampaignVoucherGroup::all();
        return view($this->view_file_path . "assign")->with($this->layout_data);
    }
    public function assignStore(Request $request, $id)
    {
        $request->validate([
            'group' => 'required_if:type,group',
            'users' => 'required_if:type,user'
        ]);
        $auth = Auth::user();
        $reward = Reward::where([['id', $id]])->firstOrFail();
        $log = "Assign  $reward->name by  $auth->name ($auth->email) to the ";
        $users = [];
        if ($request->type === 'user') {
            $users = $request->users;
            $email =    AppUser::whereIn('id', $users)->pluck('email');
            $log .= $email->implode(',');
            $log .= " Users";
        } else if ($request->type === 'group') {
            $users = GroupUser::whereIn('group_id', $request->group)->groupBy('user_id')->pluck('user_id');
            CampaignVoucherGroup::whereIn('id', $request->group)->increment('reward_count');
            $names = CampaignVoucherGroup::whereIn('id', $request->group)->pluck('name');
            $log .= $names->implode(',');
            $log .= "Groups";
        } else if ($request->type === 'csv') {
            $fileName = "";
            if ($request->hasFile('csv')) {
                $fileName = time() . rand() . '.' . $request->csv->getClientOriginalExtension();
                $request->csv->move(public_path('report'), $fileName);
            }
            $filePath = public_path('report') . '/' . $fileName;

            if (($handle = fopen($filePath, 'r')) !== false) {
                $i = 0;

                $emails = [];
                while (($row = fgetcsv($handle, 0, ',')) !== false) {
                    if ($i == 0) {
                        $header = $row;
                    } else {
                        $emails[] = $row[0];
                    }
                    $i++;
                }
                $users =    AppUser::whereIn('email', $emails)->pluck('id');
            }


            $log .= "CSV File";
            $link = asset('report') . '/' . $fileName;
            $log .= " <a href='$link' download='' class='text-danger'>Click to download</a>";
        }
        $exd = null;

        if($request->reason){
            $log .= " With Remarks : $request->reason";
        }

        if ($reward->expiry_day == 0) {
            // defalut is coupen expiry
            $exd = $reward->end_date;
        } else {
            $exd = Carbon::now()->addDays($reward->expiry_day);
        }



        if ($reward->quantity != 0) {

            $blanace =  $reward->quantity - $reward->total_redeemed;
            if ($blanace < count($users)) {
                return redirect()->back()->with('message', "Sorry, we only have $blanace units available.");
            }
        }

        DB::beginTransaction();

        foreach ($users as $user) {
            $uuid = (string) Str::ulid();


            
            if ($reward->reward_type == 0) {
                $sn = "SC";
                $randomNumber = rand(100, 10000); 
                $uuid  = $sn . Carbon::now()->format('ymjhisjmy') . $randomNumber ;
                $sn  = $uuid;
            } else {
                $sn = "SR";
                $uuid  = $sn . $uuid;
            }


            try {
                // lets buy reward first after debit key based on expiry so user get best benefit

                $d =      UserPurchasedReward::create([
                    'user_id' => $user,
                    'reward_id' => $reward->id,
                    'key_use' =>  0,
                    'status' => "Purchased",
                    'get_from' => "Purchased",
                    'unique_no' =>  $uuid,
                    'voucher_serial' => $sn,
                    'reward_type' => $reward->reward_type,
                    'expiry_date' => $exd, // need to be dynamic based on rewarded todo
                ]);



                // whoooo all done
            } catch (\Exception $ex) {
                Log::error("While purchasing reward");
                Log::error($ex);
                DB::rollback();
                return response()->json(['status' => false, "msg" => "Something went wrong.",], 500);
            }
        }
        $reward->increment('total_redeemed', count($users));

        CampaignVoucherLog::create([
            'admin_id' => Auth::id(),
            'reward_id' => $id,
            'log' => $log,
        ]);
        DB::commit();


        return redirect()->back()->with('message', 'Process completed.');
    }


    public function redeemVoucher()
    {
        return view($this->view_file_path . "redeem-voucher")->with($this->layout_data);
    }
    public function redeemVoucherVerify(Request $request)
    {
           $fcode = 406;
        $fres = "invalid Data Request";

        
        $validator = Validator::make(
            $request->all(),
            [
                 'Voucher_No' => 'required',


            ]
        );

        if ($validator->fails()) {
            return response()->json(['request' => [
                "api" => "redeem"
            ], "status" => [
                'status_code' => $fcode,
                'status_message' => $fres,
            ], 'data' => 'fail'], 400);
        } else {
            $fcode = 2003;
            $fres = "Voucher number not found";
        }
        $vnos = explode(',', $request->Voucher_No);

        $upr = UserPurchasedReward::where([['reward_type', 0], ['status', 'Purchased']])->whereIn('unique_no', $vnos)->get();
        if ($upr->count() != count($vnos)) {
            return response()->json(['request' => [
                "api" => "redeem"
            ], "status" => [
                'status_code' => 2003,
                'status_message' => "Voucher is not valid or has expired.",
            ], 'data' => 'fail'], 400);
        }
        if ($upr) {
            $today = Carbon::today();

            $day = $today->format('w');
            $time = Carbon::now()->format('H:i');
            $fcodeInner = "";
            $fresInner = "";
            $rewardData = [];
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
                if (!isset($reward->id)) {

                    $fcodeInner = 2004;
                    $fresInner = 'Voucher had been not avaiavale at moment';
                    break;
                }
            }
            if ($fcodeInner && $fresInner) {
                return response()->json(['request' => [
                    "api" => "Redeemed"
                ], "status" => [
                    'status_code' => $fcodeInner,
                    'status_message' =>  $fresInner,
                ], 'data' => 'fail'], 400);
            }
            foreach ($upr as  $rd) {
                          $rd->load(['reward']);

                $rewardData = $rd;
                $rd->update([
                    'status' => "Redeemed",
                    'redeem_date' => Carbon::now(),
                    'meta_data' => json_encode($request->all()),
                ]);
                VoucherLogs::create([
                    'voucher_no' => $rd->unique_no,
                    'from_status' => 'Active',
                    'to_status' => 'Redeemed',
                    'from_where' => 'API',
                    'remark' => '-',

                ]);
            }
            $t = Carbon::now()->format('ymjhisjmy');
            return response()->json([
                'request' => [
                    "api" => "redeem"
                ], "status" => [
                    'status_code' => 200,
                    'status_message' => "Success"
                ], 'data' => [
                    "Response_ID" => $t,
                    "date_time" => $t,
                    "voucher_name" => $rewardData->reward->code,
                    "voucher_amount" => $rewardData->reward->amount,
                    "voucher_no" => $rewardData->voucher_serial,
                    "approvalCode" =>  $t,
                ]
            ]);
        }

        return response()->json(['request' => [
            "api" => "redeem"
        ], "status" => [
            'status_code' => $fcode,
            'status_message' => $fres,
        ], 'data' => 'fail'], 400);
    }

}
