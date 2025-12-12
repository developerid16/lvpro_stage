<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\ContentManagement;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\ParticipatingMerchantLocation;
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
        $this->layout_data['category'] = Category::get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();
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
        $query = Reward::where('type', '0');

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';
            $final_data[$key]['no_of_keys'] = number_format($row->no_of_keys);

            $final_data[$key]['quantity']       = number_format($row->quantity);
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);

            $final_data[$key]['balance'] = $row->quantity == 0 ? 'Unlimited Stock' : number_format($row->quantity - $row->total_redeemed);

            $final_data[$key]['redeemed'] = number_format(UserPurchasedReward::where([['status', 'Redeemed'], ['reward_id', $row->id]])->count());

            $duration = $row->created_at->format(config('shilla.date-format'));

            if (!empty($row->voucher_image)) {

                $csvUrl = asset("uploads/image/$row->voucher_image");
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
                'voucher_image'      => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'name'               => 'required|string|max:191',
                'description'        => 'required|string',
                'term_of_use'        => 'required|string',
                'how_to_use'         => 'required|string',
                'friendly_url'     => 'nullable',
                'category_id'     => 'nullable',
                'merchant_id'        => 'required|exists:merchants,id',
                'reward_type'        => 'required|in:0,1',

                'usual_price'        => 'required|numeric|min:0',

                'publish_start'      => 'required',
                'sales_start'        => 'required',

                'hide_quantity'      => 'nullable|boolean',
                'low_stock_1'        => 'required|integer|min:0',
                'low_stock_2'        => 'required|integer|min:0',

                'publish_independent'=> 'required|boolean',
                'publish_inhouse'    => 'required|boolean',

                'send_reminder'      => 'required|boolean',
            ];

            /* ---------------------------------------------------
            * 2) DYNAMIC TIER VALIDATION
            * ---------------------------------------------------*/
            $tiers = Tier::all();
            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'required|numeric|min:0';
            }

            /* ---------------------------------------------------
            * 3) PHYSICAL VALIDATION
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                // max qty for physical
                $rules['max_quantity_physical'] = 'required|integer|min:1';

                // must send at least one location
                $rules['locations'] = 'required|array|min:1';

                $hasSelectedLocation = false;

                foreach ($request->locations ?? [] as $locId => $locData) {

                    if (isset($locData['selected'])) {
                        $hasSelectedLocation = true;

                        // inventory is required ONLY when selected
                        $rules["locations.$locId.inventory_qty"] = 'required|integer|min:1';
                    }
                }

                // If no checkbox selected → throw SAME error format as digital
                if (!$hasSelectedLocation) {
                    return response()->json([
                        "status" => "error",
                        "errors" => [
                            "locations" => ["Please select at least one location."]
                        ]
                    ], 422);
                }
            }


            /* ---------------------------------------------------
            * 4) DIGITAL VALIDATION
            * ---------------------------------------------------*/
            if ($request->reward_type == 0) {

                $rules = array_merge($rules, [

                    'max_quantity_digital' => 'required|integer|min:1',

                    'voucher_validity'     => 'required|date',

                    'inventory_type'       => 'required|in:0,1',
                    'voucher_value'        => 'required|numeric|min:1',
                    'voucher_set'          => 'required|numeric|min:1',
                    'clearing_method'      => 'required|in:0,1,2,3,4',
                ]);

                // non-merchant
                if ($request->inventory_type == 0) {
                    $rules['inventory_qty'] = 'required|integer|min:1';
                }

                // merchant
                if ($request->inventory_type == 1) {
                    $rules['csvFile'] = 'required|file';
                }

                // external code / merchant code (needs locations)
                if (in_array($request->clearing_method, [2])) {
                    $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';

                    $rules['participating_merchant_locations'] = 'required|array|min:1';

                    foreach ($request->participating_merchant_locations ?? [] as $locId => $locData) {
                        if (isset($locData['selected'])) {
                            // nothing extra here – just mark as selected
                        }
                    }
                }
            }

            /* ---------------------------------------------------
            * 5) RUN VALIDATOR
            * ---------------------------------------------------*/
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();



            /* ---------------------------------------------------
            * 6) IMAGE UPLOAD
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {

                $path = public_path('uploads/image');

                // Create directory if not exists
                if (!is_dir($path)) {
                    mkdir($path, 0775, true);
                }

                $file = $request->file('voucher_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($path, $filename);

                $validated['voucher_image'] = $filename;
            }


            if ($request->publish_start) {
                $validated['publish_start_date'] = date('Y-m-d', strtotime($request->publish_start));
                $validated['publish_start_time'] = date('H:i:s', strtotime($request->publish_start));
            }

            if ($request->publish_end) {
                $validated['publish_end_date'] = date('Y-m-d', strtotime($request->publish_end));
                $validated['publish_end_time'] = date('H:i:s', strtotime($request->publish_end));
            }

            if ($request->sales_start) {
                $validated['sales_start_date'] = date('Y-m-d', strtotime($request->sales_start));
                $validated['sales_start_time'] = date('H:i:s', strtotime($request->sales_start));
            }

            if ($request->sales_end) {
                $validated['sales_end_date'] = date('Y-m-d', strtotime($request->sales_end));
                $validated['sales_end_time'] = date('H:i:s', strtotime($request->sales_end));
            }


            /* ---------------------------------------------------
            * 7) CREATE REWARD
            * ---------------------------------------------------*/
            $maxQty = $request->reward_type == 0  ? $request->max_quantity_digital  : $request->max_quantity_physical;

            $reward = Reward::create([
                'type'               => '0',
                'voucher_image'      => $validated['voucher_image'],
                'name'               => $validated['name'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],

                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => $validated['reward_type'],

                'usual_price'        => $validated['usual_price'],
                'max_quantity'       => $maxQty ?? '',

                'publish_start_date' => $validated['publish_start_date'] ?? '',
                'publish_start_time' => $validated['publish_start_time']  ?? '',
                'publish_end_date'   => $validated['publish_end_date'] ?? '',
                'publish_end_time'   => $validated['publish_end_time'] ?? '',

                'sales_start_date'   => $validated['sales_start_date'] ?? '',
                'sales_start_time'   => $validated['sales_start_time'] ?? '',
                'sales_end_date'     => $validated['sales_end_date'] ?? '',
                'sales_end_time'     => $validated['sales_end_time'] ?? '',

                // physical-only fields
                'hide_quantity'            => $request->hide_quantity,
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

                'voucher_validity'           => $request->voucher_validity,
                'inventory_type'             => $request->inventory_type,
                'inventory_qty'              => $request->inventory_qty,
                'voucher_value'              => $request->voucher_value,
                'voucher_set'                => $request->voucher_set,
                'clearing_method'            => $request->clearing_method,
                'participating_merchant_id'  => $request->participating_merchant_id,
                'location_text'  => $request->location_text,
                'max_order'  => $request->max_order,                
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
            * DIGITAL: SAVE PARTICIPATING MERCHANT LOCATIONS
            * ---------------------------------------------------*/
            if ($request->reward_type == 0 
                && in_array($request->clearing_method, [2,4])
                && $request->participating_merchant_locations) {

                foreach ($request->participating_merchant_locations as $locId => $locData) {

                    if (!isset($locData['selected'])) {
                        continue;
                    }

                    ParticipatingLocations::create([
                        'reward_id'                  => $reward->id,
                        'participating_merchant_id'  => $request->participating_merchant_id,
                        'location_id'                => $locId,
                        'is_selected'                => 1,
                        'created_at'                 => now(),
                        'updated_at'                 => now(),
                    ]);
                }
            }

            /* ---------------------------------------------------
            * XLSX / CSV UPLOAD (merchant inventory)
            * ---------------------------------------------------*/
            if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                $file = $request->file('csvFile');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/csv'), $filename);

                $reward->csvFile = $filename;
                $reward->save();

                $filePath = public_path('uploads/csv/'.$filename);

                // READ XLSX OR CSV SAFELY
                $rows = Excel::toArray([], $filePath);

                foreach ($rows[0] as $row) {

                    $code = trim($row[0] ?? '');

                    if ($code === '' || strtolower($code) === 'code') {
                        continue;
                    }

                    RewardVoucher::create([
                        'type'      => '0',
                        'reward_id' => $reward->id,
                        'code'      => $code,
                        'is_used'   => 0
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
        $reward = Reward::with(['tierRates','rewardLocations','participatingLocations'])->find($id);
        $this->layout_data['data'] = $reward;
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        $this->layout_data['tiers'] = Tier::all();
        $this->layout_data['category'] = Category::get();
        // 👉 Build simple array: [location_id => inventory_qty]
        $this->layout_data['savedLocations'] = $reward ? $reward->rewardLocations->pluck('inventory_qty','location_id')  : [];
        $this->layout_data['participatingLocations'] = $reward ? $reward->participatingLocations->pluck('location_id')  : [];

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'savedLocations' => $this->layout_data['savedLocations'],
            'participatingLocations' => $this->layout_data['participatingLocations']
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
            * 1) BASE VALIDATION RULES
            * ---------------------------------------------------*/
            $rules = [
                'voucher_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',

                'name'          => 'required|string|max:191',
                'description'   => 'required|string',
                'term_of_use'   => 'required|string',
                'how_to_use'    => 'required|string',

                'merchant_id'   => 'required|exists:merchants,id',
                'friendly_url'     => 'nullable',
                'category_id'     => 'nullable',
                'reward_type'   => 'required|in:0,1',

                'usual_price'   => 'required|numeric|min:0',

                'publish_start' => 'required',
                'sales_start'   => 'required',
            ];

            /* ---------------------------------------------------
            * 2) TIER VALIDATION
            * ---------------------------------------------------*/
            $tiers = Tier::all();
            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'required|numeric|min:0';
            }

            /* ---------------------------------------------------
            * 3) PHYSICAL VALIDATION
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                $rules += [
                    'max_quantity_physical' => 'required|integer|min:1',

                    'low_stock_1'      => 'required|integer|min:0',
                    'low_stock_2'      => 'required|integer|min:0',

                    'publish_independent' => 'required|boolean',
                    'publish_inhouse'     => 'required|boolean',
                    'send_reminder'       => 'required|boolean',
                ];

                // must send at least one location
                $rules['locations'] = 'required|array|min:1';

                $hasSelectedLocation = false;

                foreach ($request->locations ?? [] as $locId => $locData) {

                    if (isset($locData['selected'])) {
                        $hasSelectedLocation = true;

                        // inventory is required ONLY when selected
                        $rules["locations.$locId.inventory_qty"] = 'required|integer|min:1';
                    }
                }

                // If no checkbox selected → throw SAME error format as digital
                if (!$hasSelectedLocation) {
                    return response()->json([
                        "status" => "error",
                        "errors" => [
                            "locations" => ["Please select at least one location."]
                        ]
                    ], 422);
                }
            }


            

            /* ---------------------------------------------------
            * 4) DIGITAL VALIDATION
            * ---------------------------------------------------*/
            if ($request->reward_type == 0) {

                $rules += [
                    'max_quantity_digital' => 'required|integer|min:1',

                    'voucher_validity' => 'required|date',

                    'inventory_type'   => 'required|in:0,1',
                    'voucher_value'    => 'required|numeric|min:1',
                    'voucher_set'      => 'required|numeric|min:1',
                    'clearing_method'  => 'required|in:0,1,2,3,4',
                ];

                if ($request->inventory_type == 0) {

                    $rules['inventory_qty'] = 'required|integer|min:1';

                } else {

                    // If no old file and user didn't upload new → required
                    if (!$reward->csvFile && !$request->hasFile('csvFile')) {
                        $rules['csvFile'] = 'required|file';
                    }
                }

                if (in_array($request->clearing_method, [2])) {

                    $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';

                    $rules['participating_merchant_locations'] = 'required|array|min:1';

                    foreach ($request->participating_merchant_locations ?? [] as $locId => $locData) {
                        if (isset($locData['selected'])) {
                            // OK
                        }
                    }
                }
            }

            /* ---------------------------------------------------
            * 5) RUN VALIDATION
            * ---------------------------------------------------*/
            $validated = $request->validate($rules);

            /* ---------------------------------------------------
            * 6) MANUAL LOCATION CHECK (PHYSICAL ONLY)
            * ---------------------------------------------------*/
            if ($request->reward_type == 1) {

                $locationErrors = [];

                foreach ($request->locations as $locId => $locData) {
                    if (isset($locData['selected']) &&
                        (!isset($locData['inventory_qty']) || $locData['inventory_qty'] === "")) {

                        $locationErrors["locations.$locId.inventory_qty"] =
                            ["Inventory quantity is required for selected location."];
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

                $uploadPath = public_path('uploads/image');

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


            if ($request->publish_start) {
                $validated['publish_start_date'] = date('Y-m-d', strtotime($request->publish_start));
                $validated['publish_start_time'] = date('H:i:s', strtotime($request->publish_start));
            }

            if ($request->publish_end) {
                $validated['publish_end_date'] = date('Y-m-d', strtotime($request->publish_end));
                $validated['publish_end_time'] = date('H:i:s', strtotime($request->publish_end));
            }

            if ($request->sales_start) {
                $validated['sales_start_date'] = date('Y-m-d', strtotime($request->sales_start));
                $validated['sales_start_time'] = date('H:i:s', strtotime($request->sales_start));
            }

            if ($request->sales_end) {
                $validated['sales_end_date'] = date('Y-m-d', strtotime($request->sales_end));
                $validated['sales_end_time'] = date('H:i:s', strtotime($request->sales_end));
            }


            /* ---------------------------------------------------
            * 5) UPDATE REWARD
            * ---------------------------------------------------*/
            $maxQty = $request->reward_type == 0 ? $request->max_quantity_digital : $request->max_quantity_physical;

            $reward->update([
                 'type'               => '0',
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'name'               => $validated['name'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],

                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => $validated['reward_type'],

                'usual_price'        => $validated['usual_price'],
                'max_quantity'       => $maxQty ?? '',

                'publish_start_date' => $validated['publish_start_date'] ?? '',
                'publish_start_time' => $validated['publish_start_time'] ?? '',
                'publish_end_date'   => $validated['publish_end_date'] ?? '',
                'publish_end_time'   => $validated['publish_end_time'] ?? '',

                'sales_start_date'   => $validated['sales_start_date'] ?? '',
                'sales_start_time'   => $validated['sales_start_time'] ?? '',
                'sales_end_date'     => $validated['sales_end_date'] ?? '',
                'sales_end_time'     => $validated['sales_end_time'] ?? '',

                // Physical fields
                'hide_quantity'            => $request->hide_quantity,
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

                // Digital
                'voucher_validity'          => $request->voucher_validity,
                'inventory_type'            => $request->inventory_type,
                'inventory_qty'             => $request->inventory_qty,
                'voucher_value'             => $request->voucher_value,
                'voucher_set'               => $request->voucher_set,
                'clearing_method'           => $request->clearing_method,
                'participating_merchant_id' => $request->participating_merchant_id,
                'location_text'             => $request->location_text,
                'max_order'                 => $request->max_order,
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

            /* ----------------------------------
         * DIGITAL → UPDATE PARTICIPATING MERCHANT OUTLETS
         * ---------------------------------- */
        if ($reward->reward_type == 0 && in_array($request->clearing_method, [2,4])) {

            ParticipatingLocations::where('reward_id', $reward->id)->delete();

            foreach ($request->participating_merchant_locations as $locId => $locData) {
                if (!isset($locData['selected'])) continue;

                ParticipatingLocations::create([
                    'reward_id'                 => $reward->id,
                    'participating_merchant_id' => $request->participating_merchant_id,
                    'location_id'               => $locId,
                    'is_selected'               => 1,
                ]);
            }
        }

        /* ---------------------------------------------------
        * DIGITAL → INVENTORY TYPE SWITCH (Merchant → Non-Merchant)
        * ---------------------------------------------------*/
        if ($request->inventory_type == 0) {

            // Delete all voucher codes
            RewardVoucher::where('reward_id', $reward->id)->delete();

            // Remove old CSV file if exists
            if (!empty($reward->csvFile)) {
                $oldFile = public_path('uploads/csv/' . $reward->csvFile);
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }

            // Clear DB reference
            $reward->csvFile = null;
            $reward->save();
        }
        /* ----------------------------------
         * DIGITAL CSV UPLOAD (ONLY MERCHANT TYPE)
         * ---------------------------------- */

        if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                $file = $request->file('csvFile');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/csv'), $filename);

                $reward->csvFile = $filename;
                $reward->save();

                $filePath = public_path('uploads/csv/'.$filename);

                // READ XLSX OR CSV SAFELY
                $rows = Excel::toArray([], $filePath);

                foreach ($rows[0] as $row) {

                    $code = trim($row[0] ?? '');

                    if ($code === '' || strtolower($code) === 'code') {
                        continue;
                    }

                    RewardVoucher::create([
                        'type'      => '0',
                        'reward_id' => $reward->id,
                        'code'      => $code,
                        'is_used'   => 0
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
        DB::beginTransaction();

        try {

            $reward = Reward::find($id);
            if (!$reward) {
                return response()->json(['status' => 'error', 'message' => 'Reward not found'], 404);
            }
        
            if ($reward->voucher_image && file_exists(public_path('uploads/image/' . $reward->voucher_image))) {
                unlink(public_path('uploads/image/' . $reward->voucher_image));
            }
            if ($reward->csvFile && file_exists(public_path('uploads/csv/' . $reward->csvFile))) {
                unlink(public_path('uploads/csv/' . $reward->csvFile));
            }
           
            RewardTierRate::where('reward_id', $reward->id)->delete();          
            RewardLocation::where('reward_id', $reward->id)->delete();        
            ParticipatingLocations::where('reward_id', $reward->id)->delete();
            RewardVoucher::where('reward_id', $reward->id)->delete();
            $reward->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reward deleted successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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

    public function getParticipatingMerchantLocations($merchant_id)
    {
        $locations = ParticipatingMerchantLocation::where('participating_merchant_id', $merchant_id)->where('status','Active')
            ->select('id', 'name')
            ->get();

        return response()->json([
            'status' => 'success',
            'locations' => $locations
        ]);
    }


    
    
}
