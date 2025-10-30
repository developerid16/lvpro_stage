<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;

class TierController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.tier.";
        $permission_prefix = $this->permission_prefix = 'tiers';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Tier',
            'module_base_url' => url('admin/tiers')
        ];

        // $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        // $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        // $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        // $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $tm = TierMilestone::orderBy('max', 'desc')->first();

        $tire = Tier::get();
        $this->layout_data['tier'] = $tire;
        $this->layout_data['milestones'] = TierMilestone::orderBy('min','asc')->paginate(20);

        $this->layout_data['rewards'] =  Reward::where('status', 'Active')->get(['code', 'name', 'id']);
        $this->layout_data['last_milestone'] =   $tm;
        return view($this->view_file_path . "manage")->with($this->layout_data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tier $tier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tier $tier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $reqData =   $request->validate([
            'instore_multiplier' => 'required|numeric|min:0',
            'isc_multiplier' => 'required|numeric|min:0',
            'spend_amount' => 'sometimes|numeric|min:0',

            'id' => 'required'
        ]);

        $tier = Tier::findOrFail($request->id);
        $tier->load("milestones");
        if ($tier->t_order > 1) {
            $upperTier =   Tier::where([
                ['t_order', '>', $tier->t_order],
                ['spend_amount', '<', $request->spend_amount],
            ])->first();
            if ($upperTier) {
                return response()->json(['status' => 'failed', 'message' => 'Spend amount is must be less than the upper tier is :-$' . $upperTier->spend_amount]);
            }
        }
        $milestones =  TierMilestone::whereIn('id', $request->milestone_id)->get();
        foreach ($milestones as $key => $milestone) {
            $keyType = $request["milestone_type_$tier->id-$key"];
            $updateData = [

                'name' => $request['milestone_name'][$key],
                'amount' => $request['milestone_amount'][$key],
                'type' =>  $keyType,
                'no_of_keys' => $keyType == "key" ? $request['milestone_no_of_keys'][$key] : null,
                'reward_id' => $keyType == "reward" ? $request['milestone_reward_id'][$key] : null,
            ];
            $milestone->update($updateData);
        }
        $tier->update($reqData);
        return response()->json(['status' => 'success', 'message' => 'Data is saved successfully']);
    }
    function milestoneSave(Request $request)
    {
//         $tm = TierMilestone::orderBy('max', 'desc')->first();
//         $limit = 1000000;

//         $now  = $tm->max;

// while ($now < $limit) {

//             $tp  = $now + 500;
//     TierMilestone::create([
//         'name' => "$" . number_format($tp),
//         'amount' => $tp,
//         'type' =>  "key",
//         'tier_id' => 1,
//         'no_of_keys' => 500, 
//         'reward_id' =>  null,
//         'min' => $now + 1,
//         'max' => $now + 500, 
//     ]);
//             $now += 500;
// }
        return redirect('admin/tiers');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tier $tier)
    {
        //
    }
}
