<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClubLocation;
use App\Models\ContentManagement;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\PartnerCompany;
use App\Models\Reward;
use App\Models\RewardDates;
use App\Models\RewardLocation;
use App\Models\RewardTierRate;
use App\Models\RewardVoucher;
use App\Models\Tier;
use App\Models\UserPurchasedReward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct

class RewardController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.reward.";
        $permission_prefix    = $this->permission_prefix    = 'reward';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Reward',
            'module_base_url'   => url('admin/reward'),
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $type = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $this->layout_data['type'] = $type;
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['tiers'] = Tier::all();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }
    /**
     * Display a listing of the resource.
     */
    public function updateAutomatedReward(Request $request)
    {


        $data = ContentManagement::whereIn('name', [
            "birthday_reward_limit",
            "birthday_reward_voucher",
            "birthday_reward_group",
            "birthday_reward_email_noti",
            "birthday_reward_push_noti",
            "birthday_reward_sms_noti",
            "welcome_reward_limit",
            "welcome_reward_voucher",
            "welcome_reward_group",
            "welcome_reward_email_noti",
            "welcome_reward_push_noti",
            "welcome_reward_sms_noti",
        ])->get();
        foreach ($data as $d) {
            $keyData  = $d['name'];
            $d->value = $request->$keyData ?? '';
            $d->save();
        }
        return response()->json(['status' => 'success', 'message' => 'Data Updated Successfully']);
    }
    public function indexAutomatedReward(Request $request)
    {

        $this->layout_data['data'] = ContentManagement::whereIn('name', [

            "birthday_reward_limit",
            "birthday_reward_voucher",
            "birthday_reward_group",
            "birthday_reward_email_noti",
            "birthday_reward_push_noti",
            "birthday_reward_sms_noti",
            "welcome_reward_limit",
            "welcome_reward_voucher",
            "welcome_reward_group",
            "welcome_reward_email_noti",
            "welcome_reward_push_noti",
            "welcome_reward_sms_noti",
        ])->pluck('value', 'name');
        $this->layout_data['data']['welcome_reward_group'] = json_decode($this->layout_data['data']['welcome_reward_group'] ?: '[]', true);

        $this->layout_data['data']['birthday_reward_group'] = json_decode($this->layout_data['data']['birthday_reward_group']  ?: '[]', true);
        $this->layout_data['rewards'] = Reward::all();
        return view($this->view_file_path . "automated-index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $type  = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $query = Reward::where('parent_type', $type);

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['no_of_keys'] = number_format($row->no_of_keys);

            $final_data[$key]['quantity']       = number_format($row->quantity);
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);

            $final_data[$key]['balance'] = $row->quantity == 0 ? 'Unlimited Stock' : number_format($row->quantity - $row->total_redeemed);

            $final_data[$key]['redeemed'] = number_format(UserPurchasedReward::where([['status', 'Redeemed'], ['reward_id', $row->id]])->count());

            $duration = $row->created_at->format(config('shilla.date-format'));

            if (!empty($row->voucher_image)) {

                $csvUrl = asset("reward_images/$row->voucher_image");
                $icon   = asset("build/images/csv-icon.png");

                $final_data[$key]['image'] = "
                    <a href='$csvUrl' target='_blank'>
                        <img src='$csvUrl' 
                            class='avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle' 
                            alt='CSV File'>
                    </a>";
            }

            if ($row->end_date) {
                $duration .= ' to ' . $row->end_date->format(config('shilla.date-format'));
            } else {
                $duration .= " - No Expiry";
            }
            $final_data[$key]['duration']   = $duration;
            $final_data[$key]['created_at'] = $row->created_at->format(config('shilla.date-format'));

            $final_data[$key]['status'] = $row->status;

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            // if ($type === 'campaign-voucher') {
            // $url = url("admin/campaign-voucher-assign/$row->id");
            // $action .= "<a href='$url' title='Assign voucher to users.' ><i class='mdi mdi-card text-info action-icon font-size-18'></i></a>";
            // }
            $final_data[$key]['action'] = $action . "</div>";
        }
        $data          = [];
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
        DB::beginTransaction();

        try {

            /* ---------------------------------------------------
            * 1) BASE VALIDATION RULES
            * ---------------------------------------------------*/
            $rules = [
                'voucher_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'name'          => 'required|string|max:191',
                'description'   => 'required|string',
                'term_of_use'   => 'required|string',
                'how_to_use'    => 'required|string',

                'merchant_id'   => 'required|exists:merchants,id',

                'reward_type'   => 'required|in:0,1',

                'usual_price'   => 'required|numeric|min:0',
                'max_quantity'  => 'required|integer|min:0',

                'publish_start_date' => 'required|date',
                'sales_start_date'   => 'required|date',
            ];

            /* ---------------------------------------------------
            * 2) DYNAMIC TIER VALIDATION
            * ---------------------------------------------------*/
            $tiers = Tier::all();
            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'required|numeric|min:0';
            }

            /* ---------------------------------------------------
            * 3) PHYSICAL REWARD EXTRA VALIDATION
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                $rules = array_merge($rules, [
                    'low_stock_1'      => 'required|integer|min:0',
                    'low_stock_2'      => 'required|integer|min:0',

                    'publish_independent' => 'required|boolean',
                    'publish_inhouse'     => 'required|boolean',

                    'send_reminder'       => 'required|boolean',
                ]);
            }

            /* ---------------------------------------------------
            * 4) RUN VALIDATOR WITHOUT AUTO-FAIL
            * ---------------------------------------------------*/
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "errors" => $validator->errors()
                ], 422);
            }

            // Always use validated data only
            $validated = $validator->validated();


            /* ---------------------------------------------------
            * 5) LOCATION VALIDATION (ONLY IF PHYSICAL)
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                if (!$request->locations || empty($request->locations)) {
                    return response()->json([
                        "status" => "error",
                        "errors" => [
                            "locations" => ["Please select at least one location."]
                        ]
                    ], 422);
                }

                $locationErrors = [];

                foreach ($request->locations as $locId => $locData) {

                    // If location is selected → inventory qty required
                    if (isset($locData['selected'])) {

                        if (!isset($locData['inventory_qty']) || $locData['inventory_qty'] === "") {
                            $locationErrors["locations.$locId.inventory_qty"] =
                                ["Inventory quantity is required for selected location."];
                        }
                    }
                }

                if (!empty($locationErrors)) {
                    return response()->json([
                        "status" => "error",
                        "errors" => $locationErrors
                    ], 422);
                }
            }


            /* ---------------------------------------------------
            * 6) IMAGE UPLOAD
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {

                $path = public_path('reward_images');

                // Create directory if not exists
                if (!is_dir($path)) {
                    mkdir($path, 0775, true);
                }

                $file = $request->file('voucher_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($path, $filename);

                $validated['voucher_image'] = $filename;
            }



            /* ---------------------------------------------------
            * 7) CREATE REWARD
            * ---------------------------------------------------*/
            $reward = Reward::create([
                'voucher_image'      => $validated['voucher_image'],
                'name'               => $validated['name'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],

                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => $validated['reward_type'],

                'usual_price'        => $validated['usual_price'],
                'max_quantity'       => $validated['max_quantity'],

                'publish_start_date' => $validated['publish_start_date'],
                'publish_start_time' => $request->publish_start_time,
                'publish_end_date'   => $request->publish_end_date,
                'publish_end_time'   => $request->publish_end_time,

                'sales_start_date'   => $validated['sales_start_date'],
                'sales_start_time'   => $request->sales_start_time,
                'sales_end_date'     => $request->sales_end_date,
                'sales_end_time'     => $request->sales_end_time,

                // physical-only fields
                'low_stock_1'            => $request->low_stock_1,
                'low_stock_2'            => $request->low_stock_2,

                'friendly_url'           => $request->friendly_url,

                'category_id'            => $request->category_id,
                'club_classification_id' => $request->club_classification_id,
                'fabs_category_id'       => $request->fabs_category_id,
                'smc_classification_id'  => $request->smc_classification_id,

                'ax_item_code'           => $request->ax_item_code,

                'publish_independent'    => $request->publish_independent ?? 0,
                'publish_inhouse'        => $request->publish_inhouse ?? 0,

                'send_reminder'          => $request->send_reminder ?? 0,
            ]);


            /* ---------------------------------------------------
            * 8) SAVE TIER RATES
            * ---------------------------------------------------*/
            foreach ($tiers as $tier) {

                RewardTierRate::create([
                    'reward_id' => $reward->id,
                    'tier_id'   => $tier->id,
                    'price'     => $request->input("tier_{$tier->id}"),
                ]);
            }


            /* ---------------------------------------------------
            * 9) SAVE LOCATION DATA (PHYSICAL ONLY)
            * ---------------------------------------------------*/
            if ($request->reward_type == 1 && $request->has('locations')) {

                foreach ($request->locations as $locId => $locData) {

                    // Store ONLY if checkbox selected
                    if (!isset($locData['selected'])) {
                        continue; // skip unselected
                    }

                    RewardLocation::create([
                        'reward_id'     => $reward->id,
                        'merchant_id'   => $validated['merchant_id'],
                        'location_id'   => $locId,
                        'is_selected'   => 1,  // always 1 since only selected stored
                        'inventory_qty' => $locData['inventory_qty'] ?? 0,
                    ]);
                }
            }



            /* ---------------------------------------------------
            * SUCCESS
            * ---------------------------------------------------*/
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Reward Created Successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    function normalizeTime($time)
    {
        if (!$time) return null;
        return substr($time, 0, 5);  // Keep only "HH:MM"
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $reward = Reward::with(['tierRates','rewardLocations'])->find($id);

        $this->layout_data['data'] = $reward;
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['tiers'] = Tier::all();

        // 👉 Build simple array: [location_id => inventory_qty]
        $this->layout_data['savedLocations'] = $reward
            ? $reward->rewardLocations->pluck('inventory_qty','location_id')
            : [];

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'savedLocations' => $this->layout_data['savedLocations']
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            /* ---------------------------------------------------
            * 1) FIND REWARD
            * ---------------------------------------------------*/
            $reward = Reward::findOrFail($id);


            /* ---------------------------------------------------
            * 2) VALIDATION
            * ---------------------------------------------------*/
            $rules = [
                'voucher_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',

                'name'          => 'required|string|max:191',
                'description'   => 'required|string',
                'term_of_use'   => 'required|string',
                'how_to_use'    => 'required|string',

                'merchant_id'   => 'required|exists:merchants,id',

                'reward_type'   => 'required|in:0,1',

                'usual_price'   => 'required|numeric|min:0',
                'max_quantity'  => 'required|integer|min:0',

                'publish_start_date' => 'required|date',
                'sales_start_date'   => 'required|date',
            ];

            /* --- TIER VALIDATION --- */
            $tiers = Tier::all();
            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'required|numeric|min:0';
            }

            /* --- PHYSICAL FIELDS VALIDATION --- */
            if ($request->reward_type == 1) {
                $rules = array_merge($rules, [
                    'low_stock_1'      => 'required|integer|min:0',
                    'low_stock_2'      => 'required|integer|min:0',

                    'publish_independent' => 'required|boolean',
                    'publish_inhouse'     => 'required|boolean',

                    'send_reminder'       => 'required|boolean',
                ]);
            }

            $validated = $request->validate($rules);


            /* ---------------------------------------------------
            * 3) LOCATION VALIDATION (PHYSICAL ONLY)
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                if (!$request->locations || empty($request->locations)) {
                    return response()->json([
                        "status" => "error",
                        "errors" => [
                            "locations" => ["Please select at least one location."]
                        ]
                    ], 422);
                }

                $locationErrors = [];

                foreach ($request->locations as $locId => $locData) {

                    if (isset($locData['selected'])) {

                        if (!isset($locData['inventory_qty']) || $locData['inventory_qty'] === "") {
                            $locationErrors["locations.$locId.inventory_qty"] =
                                ["Inventory quantity is required for selected location."];
                        }
                    }
                }

                if (!empty($locationErrors)) {
                    return response()->json([
                        "status" => "error",
                        "errors" => $locationErrors
                    ], 422);
                }
            }


            /* ---------------------------------------------------
            * 4) IMAGE UPLOAD
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {

                $uploadPath = public_path('reward_images');

                // Ensure directory exists
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0775, true);
                }

                // Ensure directory is writable
                if (!is_writable($uploadPath)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Upload directory is not writable: reward_images'
                    ], 500);
                }

                // Delete old image (ignore errors)
                if (!empty($reward->voucher_image)) {
                    $oldFile = $uploadPath . '/' . $reward->voucher_image;

                    if (file_exists($oldFile)) {
                        @unlink($oldFile); // <-- the @ prevents warning if permission denied
                    }
                }

                // Upload new image
                $file = $request->file('voucher_image');
                $filename = time() . '_' . $file->getClientOriginalName();

                $file->move($uploadPath, $filename);

                $validated['voucher_image'] = $filename;
            }



            /* ---------------------------------------------------
            * 5) UPDATE REWARD
            * ---------------------------------------------------*/
            $reward->update([
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'name'               => $validated['name'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],

                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => $validated['reward_type'],

                'usual_price'        => $validated['usual_price'],
                'max_quantity'       => $validated['max_quantity'],

                'publish_start_date' => $validated['publish_start_date'],
                'publish_start_time' => $request->publish_start_time,
                'publish_end_date'   => $request->publish_end_date,
                'publish_end_time'   => $request->publish_end_time,

                'sales_start_date'   => $validated['sales_start_date'],
                'sales_start_time'   => $request->sales_start_time,
                'sales_end_date'     => $request->sales_end_date,
                'sales_end_time'     => $request->sales_end_time,

                // Physical fields
                'low_stock_1'        => $request->low_stock_1,
                'low_stock_2'        => $request->low_stock_2,
                'friendly_url'       => $request->friendly_url,

                'category_id'            => $request->category_id,
                'club_classification_id' => $request->club_classification_id,
                'fabs_category_id'       => $request->fabs_category_id,
                'smc_classification_id'  => $request->smc_classification_id,
                'ax_item_code'           => $request->ax_item_code,

                'publish_independent'    => $request->publish_independent ?? 0,
                'publish_inhouse'        => $request->publish_inhouse ?? 0,
                'send_reminder'          => $request->send_reminder ?? 0,
            ]);


            /* ---------------------------------------------------
            * 6) UPDATE TIER RATES
            * ---------------------------------------------------*/
            foreach ($tiers as $tier) {
                RewardTierRate::updateOrCreate(
                    ['reward_id' => $reward->id, 'tier_id' => $tier->id],
                    ['price'     => $request->input("tier_{$tier->id}")]
                );
            }


            /* ---------------------------------------------------
            * 7) UPDATE LOCATION DATA
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                // Delete old rows
                RewardLocation::where('reward_id', $reward->id)->delete();

                // Insert fresh
                foreach ($request->locations as $locId => $locData) {

                    if (!isset($locData['selected'])) {
                        continue;
                    }

                    RewardLocation::create([
                        'reward_id'     => $reward->id,
                        'merchant_id'   => $validated['merchant_id'],
                        'location_id'   => $locId,
                        'is_selected'   => 1,
                        'inventory_qty' => $locData['inventory_qty'] ?? 0,
                    ]);
                }
            }


            /* ---------------------------------------------------
            * SUCCESS
            * ---------------------------------------------------*/
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Reward Updated Successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                "status"  => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Reward::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Reward Delete Successfully']);
    }

    /**
     * Get locations by company ID
     */
    public function getMerchantLocations($merchant_id)
    {
        $locations = ClubLocation::where('merchant_id', $merchant_id)->where('status','Active')
            ->select('id', 'name')
            ->get();

        return response()->json([
            'status' => 'success',
            'locations' => $locations
        ]);
    }


    
    
}
