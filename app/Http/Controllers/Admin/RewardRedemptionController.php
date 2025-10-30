<?php

namespace App\Http\Controllers\Admin;

use App\Models\UserPurchasedRewardLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\UserPurchasedReward;
use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\KeyPassbookCredit;
use App\Models\KeyPassbookDebit;
use App\Models\Reward;
use Illuminate\Support\Facades\Auth;

class RewardRedemptionController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {
        // reward-redemption

        $this->view_file_path = "admin.reward-redemption.";
        $permission_prefix = $this->permission_prefix = 'reward-redemption';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Reward Redemption',
            'module_base_url' => url('admin/reward-redemption')
        ];

        $this->middleware("permission:reward-redemption-cms", ['only' => ['index']]);
        $this->middleware("permission:reward-redemption-pos", ['only' => ['posIndex']]);
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
        $query = UserPurchasedReward::whereHas('reward')->where('reward_type', 1);
        $query->with(["reward", "user"]);
        $relationSort = null;
        if ($request->sort === 'reward') {
            $relationSort = Reward::select('name')->whereColumn('rewards.id', 'user_purchased_reward.reward_id');
        } else if ($request->sort === 'name' || $request->sort ===  'unique_id') {
            $relationSort = AppUser::select($request->sort)->whereColumn('app_users.id', 'user_purchased_reward.user_id');
        }

        $searched_from_relation = ['user' => ['name', 'unique_id'], 'reward' => ['code', 'name']];
        $query = $this->get_sort_offset_limit_query($request, $query, ['status',], $searched_from_relation, ['user' => ['name', 'unique_id'], 'reward' => ['reward']], $relationSort);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['unique_id'] = $row->user->unique_id;
            $final_data[$key]['name'] = $row->user->name;
            $final_data[$key]['reward'] =  $row->reward->code . "-" .  $row->reward->name;
            $final_data[$key]['key_use'] =
                number_format($row->key_use);
            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));
            $final_data[$key]['expiry_date'] = $row->expiry_date->format(config('shilla.date-format'));
            $final_data[$key]['status'] = $row->status === 'Purchased' ? 'Issued' : $row->status;



            $action = "<div class='d-flex gap-3'>";

            $url = route('admin.redemption-reward.show', ['redemption_reward' => $row->id]);
            $action .= "<a href='$url' class='edit' data-id='$row->id'><i class='mdi mdi-eye text-primary action-icon font-size-18'></i></a>";

            $final_data[$key]['action'] = $action . "</div>";
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }
    /**
     * Display a listing of the resource.
     */
    public function posIndex()
    {

        return view($this->view_file_path . "pos-index")->with($this->layout_data);
    }

    public function datatablePOS(Request $request)
    {
        $query = UserPurchasedReward::where('reward_type', 0);
        $query->with(["reward", "user"]);


        $relationSort = null;
        if ($request->sort === 'reward') {
            $relationSort = Reward::select('name')->whereColumn('rewards.id', 'user_purchased_reward.reward_id');
        } else if ($request->sort === 'name' || $request->sort ===  'unique_id') {
            $relationSort = AppUser::select($request->sort)->whereColumn('app_users.id', 'user_purchased_reward.user_id');
        }
        $searched_from_relation = ['user' => ['name', 'unique_id'], 'reward' => ['code', 'name']];
        $query = $this->get_sort_offset_limit_query($request, $query, ['status',], $searched_from_relation, ['user' => ['name', 'unique_id'], 'reward' => ['reward']], $relationSort);



        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['unique_id'] = $row->user->unique_id;
            $final_data[$key]['name'] = $row->user->name;
            $final_data[$key]['reward'] =   $row->reward->code . "-" .   $row->reward->name;
            $final_data[$key]['key_use'] = number_format($row->key_use);
            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));
            $final_data[$key]['expiry_date'] = $row->expiry_date->format(config('shilla.date-format'));
            $final_data[$key]['status'] = $row->status === 'Purchased' ? 'Issued' : $row->status;



            $action = "<div class='d-flex gap-3'>";

            $url = route('admin.redemption-reward.show', ['redemption_reward' => $row->id]);
            $action .= "<a href='$url' class='edit' data-id='$row->id'><i class='mdi mdi-eye text-primary action-icon font-size-18'></i></a>";

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
        $upr =  UserPurchasedReward::findOrFail($id)->load(["reward", "user"]);
        $today = Carbon::today();

        $day = $today->format('w');
        $time = Carbon::now()->format('H:i');
        $available = false;
        $reward =  Reward::where([['id', $upr->reward_id]])->where(function ($query) use ($today) {
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
        if ($reward) {
            $available = true;
        }
        return view($this->view_file_path . "show", compact('upr', 'available'));
    }
    public function update(Request $request, string $id)
    {
        UserPurchasedReward::findOrFail($id)->update([
            'status' => $request->status,
            'reason' => $request->reason,
            'redeem_date' => Carbon::now(),
        ]);

        return redirect()->back();
    }
    public function deleteReward(Request $request)
    {
        $upr =  UserPurchasedReward::findOrFail($request->id);


        $status =  $upr->status === "Admin Deleted" ? "Purchased" : 'Admin Deleted';
        $upr->update([
            'status' => $status,
            'reason' => $request->reason,
        ]);

        $adminName = Auth::user()->name . ' (' . Auth::user()->email . ')';

        $log['user_id'] = $upr->user_id;
        $log['purchase_id'] = $upr->id;
        $log['reward_id'] = $upr->reward_id;
        $log['reason'] = $request->reason;
        $log['admin_id'] = Auth::id();
        $log['keys'] = $upr->key_use;
        $log['action'] =  $status;
        $log['full_str'] = "$adminName has change status to $status  for $request->reason.";
        if ($upr->key_use >  0) {
            $data['keys'] = $upr->key_use;
            if ($status == "Admin Deleted") {
                $data['reason'] ="For voucher Status update to $status  for $request->reason";
                $data['type'] = "credit";
            } else {
                $data['reason'] ="For voucher Status update to $status  for $request->reason";
                $data['type'] = "debit";
            }
            $this->updateKeyInUser($data, $upr->user_id);
        }
        UserPurchasedRewardLogs::create($log);
        return redirect()->back();
    }
    public function changeDateReward(Request $request)
    {
        $upr = UserPurchasedReward::findOrFail($request->id);
        $adminName = Auth::user()->name . ' (' . Auth::user()->email . ')';

        $log['user_id'] = $upr->user_id;
        $log['purchase_id'] = $upr->id;
        $log['reward_id'] = $upr->reward_id;
        $log['reason'] = $request->reason;

        $log['admin_id'] = Auth::id();
        $log['keys'] = $upr->key_use;
        $d = $upr->expiry_date->copy();
        $log['action'] =  "Expiry Date Change";
        $log['full_str'] = "$adminName has change expiry date  from $d to $request->date for $request->reason";
        $upr->update([
            'status' => 'Purchased',
            'reason' => $request->reason,
            'expiry_date' => $request->date,
        ]);
        UserPurchasedRewardLogs::create($log);


        return redirect()->back();
    }


    public function updateKeyInUser($data, $id)
    {
        $adminName = Auth::user()->name . ' (' . Auth::user()->email . ')';
        $user = Appuser::findOrFail($id);

        if ($data['type'] == 'debit') {
            KeyPassbookDebit::create([
                'user_id' => $id,  'key_use' => $data['keys'], 'type' => "admin", 'meta_data' => 'Keys Adjustment by ' . $adminName  . $data['reason'],

            ]);
            $dk =  $data['keys'];
            // lets debit key on by one
            $availableKeys =  KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $id]])->whereDate('expiry_date', '>=', Carbon::today())->orderBy('id', 'asc')->get();
            $nokeys  = $data['keys'];
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

            $rk = $data['keys'];
            // check if user has already in negative value as key

            if ($user->available_key < 0) {
                // remove nagetive value then add key to passbook
                // dd($user->available_key, $rk, $user->available_key +  $rk);
                $rk = $user->available_key +  $rk;
                // $user->increment('available_key', $data['keys'] - $rk);

                if ($rk <= 0) {

                    $user->available_key = $rk;
                    $user->save();
                } else {
                    $user->available_key = 0;
                    $user->save();
                }
            }
            KeyPassbookCredit::create([
                'user_id' => $id,
                'no_of_key' => $data['keys'],
                'remain_keys' => $rk < 0 ? 0 : $rk,
                'earn_way' => 'admin_credit',
                'meta_data' => 'Keys Adjustment by ' . $adminName . $data['reason'],
                'expiry_date' => keyExpiryDate()
            ]);
            // $user->increment('available_key', $request->keys);

        }
    }
}
