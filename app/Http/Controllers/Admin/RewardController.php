<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentManagement;
use App\Models\Location;
use App\Models\PartnerCompany;
use App\Models\Reward;
use App\Models\RewardDates;
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
        $this->layout_data['companies'] = PartnerCompany::where('status', 'Active')->get();
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

            if (!empty($row->csvFile)) {

                $csvUrl = asset("rewardvoucher/$row->csvFile");
                $icon   = asset("build/images/csv-icon.png");

                $final_data[$key]['image'] = "
                    <a href='$csvUrl' target='_blank'>
                        <img src='$icon' 
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

        // FIRST → validate main reward fields
        $exd = !isset($request->end_date) ? 1 : 0;

        $post_data = $this->validate($request, [
            'code'         => 'required|max:191|unique:rewards,code',
            'name'         => 'required|max:191',
            'reward_type'  => 'required',
            'description'  => 'required|max:500',
            'amount'       => 'required_if:reward_type,0',
            'quantity'     => 'required|numeric|min:0',
            'company_id'   => 'required|array|min:1',
            'company_id.*' => 'exists:partner_companies,id',
            'image_2'      => 'sometimes|required|image',
            'image_3'      => 'sometimes|image|required_if:is_featured,1',
            'labels'       => 'sometimes',
            'days'         => 'sometimes',
            'sku'          => 'sometimes',
            'parent_type'  => 'required',
            'countdown'    => 'sometimes',
            'expiry_day'   => 'required_if:reward_type,0|numeric|min:' . $exd,
            'clearing_method'=> 'required_if:reward_type,0',
            'inventory_type'=> 'required_if:reward_type,0',
            'csvFile' => 'required_if:inventory_type,1|mimes:csv,xls,xlsx',
            'inventory_qty'=> 'required_if:inventory_type,0',
            'voucher_value'=> 'required_if:reward_type,0',
            'voucher_set'  => 'required_if:reward_type,0',
        ]);

        // SECOND → validate ALL location date blocks BEFORE creating reward
        $locationsDigital = $request->input('locations_digital', []);

        foreach ($locationsDigital as $locId => $locData) {

           $validator = Validator::make($locData, [
                'publish_start_date' => 'required|date',
                'publish_start_time' => 'required',
                'publish_end_date'   => 'nullable|date|after_or_equal:publish_start_date',
                'publish_end_time'   => 'nullable',

                'sales_start_date'   => 'required|date',
                'sales_start_time'   => 'required',
                'sales_end_date'     => 'nullable|date|after_or_equal:sales_start_date',
                'sales_end_time'     => 'nullable',
            ], [

                // Required fields — with location ID
                'publish_start_date.required' => "Publish Start Date is required for location {$locId}",
                'publish_start_time.required' => "Publish Start Time is required for location {$locId}",
                'sales_start_date.required'   => "Sales Start Date is required for location {$locId}",
                'sales_start_time.required'   => "Sales Start Time is required for location {$locId}",

                // Invalid date formats (optional but clearer)
                'publish_start_time.date_format' => "Invalid Publish Start Time format at location {$locId}",
                'publish_end_time.date_format'   => "Invalid Publish End Time format at location {$locId}",
                'sales_start_time.date_format'   => "Invalid Sales Start Time format at location {$locId}",
                'sales_end_time.date_format'     => "Invalid Sales End Time format at location {$locId}",

                // After-or-equal rules
                'publish_end_date.after_or_equal' => "Publish End Date must be after Publish Start Date for location {$locId}",
                'sales_end_date.after_or_equal'   => "Sales End Date must be after Sales Start Date for location {$locId}",
            ]);


            if ($validator->fails()) {
                return response()->json([
                    "message" => "Validation Failed",
                    "errors"  => $validator->errors()
                ], 422);
            }
        }

       $post_data['company_id'] = implode(',', array_unique($request->company_id));


        // THIRD → now create the reward (safe now)
        $reward = Reward::create($post_data);

        // Save location dates
        foreach ($locationsDigital as $locId => $locData) {

            RewardDates::updateOrCreate(
                ['reward_id' => $reward->id, 'merchant_id' => (int)$locId],
                [
                    'publish_start_date' => $locData['publish_start_date'] ?? null,
                    'publish_start_time' => $this->normalizeTime($locData['publish_start_time'] ?? null),
                    'publish_end_date'   => $locData['publish_end_date'] ?? null,
                    'publish_end_time'   => $this->normalizeTime($locData['publish_end_time'] ?? null),
                    'sales_start_date'   => $locData['sales_start_date'] ?? null,
                    'sales_start_time'   => $this->normalizeTime($locData['sales_start_time'] ?? null),
                    'sales_end_date'     => $locData['sales_end_date'] ?? null,
                    'sales_end_time'     => $this->normalizeTime($locData['sales_end_time'] ?? null),
                    'qty'     => $locData['inventory_qty'] ?? null,
                ]
            );
        }
        $locationsPhysical = $request->input('locations', []);

        //add indventory qty in location table
        foreach ($locationsPhysical as $locId => $locData) {

            if (!isset($locData['selected'])) {
                continue; // skip unchecked
            }

            RewardDates::updateOrCreate(
                ['reward_id' => $reward->id, 'merchant_id' => (int) $locId],
                [
                    'qty' => $locData['inventory_qty'] ?? 0
                ]
            );
        }

        // Save tier rates
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'tier_')) {

                $tierId = str_replace('tier_', '', $key);

                RewardTierRate::updateOrCreate(
                    [
                        'reward_id' => $reward->id,
                        'tier_id' => $tierId
                    ],
                    [
                        'price' => $value
                    ]
                );
            }
        }

        // --------------- CSV UPLOAD (ONLY FOR inventory_type = 1) ---------------
        if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

            $file = $request->file('csvFile');

            // Save file in public path
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('rewardvoucher'), $filename);

            // Store file name in DB
            $reward->csvFile = $filename;
            $reward->save();

            // Read file (CSV / Excel)
            $rows = Excel::toArray([], public_path('rewardvoucher/' . $filename));

            $sheet = $rows[0]; // always first sheet

            foreach ($sheet as $row) {

                $code = trim($row[0] ?? '');

                if ($code === '' || strtolower($code) === 'code') {
                    continue; // skip header + empty rows
                }

                RewardVoucher::create([
                    'reward_id' => $reward->id,
                    'code'      => $code,
                    'is_used'   => 0
                ]);
            }
        }



        DB::commit();

        return response()->json(['status' => 'success', 'message' => 'Reward Created Successfully']);
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
        $this->layout_data['data'] = Reward::with(['rewardDates', 'tierRates'])->find($id);
        $data = $this->layout_data['data'];

        $this->layout_data['type'] = $data->parent_type;
        $this->layout_data['companies'] = PartnerCompany::where('status', 'Active')->get();

        // --- FIX: handle multiple company IDs ---
        $companyIds = [];

        if (!empty($data->company_id)) {
            if (is_array($data->company_id)) {
                $companyIds = $data->company_id;
            } else {
                $companyIds = explode(',', $data->company_id);
            }
        }

        // Load ALL locations for all selected companies
        $this->layout_data['locations'] = Location::whereIn('company_id', $companyIds)
            ->where('status', 'Active')
            ->get();

        $this->layout_data['tiers'] = Tier::all();

        // --- Existing location details ---
        $existingLocationsData = [];

        $details = RewardDates::where('reward_id', $data->id)->get();

        foreach ($details as $d) {
            $existingLocationsData[$d->merchant_id] = [
                'publish_start_date' => $d->publish_start_date,
                'publish_start_time' => $d->publish_start_time,
                'publish_end_date'   => $d->publish_end_date,
                'publish_end_time'   => $d->publish_end_time,
                'sales_start_date'   => $d->sales_start_date,
                'sales_start_time'   => $d->sales_start_time,
                'sales_end_date'     => $d->sales_end_date,
                'sales_end_time'     => $d->sales_end_time,
                'qty'     => $d->qty,
                'selected'           => 1,
            ];
        }


        $this->layout_data['existingLocationsData'] = $existingLocationsData ?? [];
        $this->layout_data['selectedCompanies'] = $companyIds ?? [];
        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        $reward = Reward::findOrFail($id);

        // -------------------------------
        // VALIDATION (same as store)
        // -------------------------------
        $post_data = $this->validate($request, [
            'code'         => 'required|max:191|unique:rewards,code,' . $id,
            'name'         => 'required|max:191',
            'reward_type'  => 'required',
            'description'  => 'required|max:500',
            'amount'       => 'required_if:reward_type,0',
            'quantity'     => 'required|numeric|min:0',
            'company_id'   => 'required|array|min:1',
            'company_id.*' => 'exists:partner_companies,id',
            'image_2'      => 'sometimes|required|image',
            'image_3'      => 'sometimes|image|required_if:is_featured,1',
            'labels'       => 'sometimes',
            'days'         => 'sometimes',
            'sku'          => 'sometimes',
            'parent_type'  => 'required',
            'countdown'    => 'sometimes',
            'expiry_day'   => 'required_if:reward_type,0|numeric',
            'clearing_method'=> 'required_if:reward_type,0',
            'inventory_type'=> 'required_if:reward_type,0',
            'csvFile'=> 'required_if:inventory_type,1',
            'inventory_qty'=> 'required_if:inventory_type,0',
            'voucher_value'=> 'required_if:reward_type,0',
            'voucher_set'  => 'required_if:reward_type,0',
        ]);

        // -----------------------------------
        // VALIDATE DIGITAL LOCATION BLOCKS
        // -----------------------------------

        $post_data['company_id'] = implode(',', array_unique($request->company_id));


        // Normalize all digital location times BEFORE validation
        $locationsDigital = $request->input('locations_digital', []);        

        foreach ($locationsDigital as $locId => $locData) {

            $validator = Validator::make(
                $locData,
                [
                    'publish_start_date' => 'required|date',
                    'publish_start_time' => 'required',

                    'publish_end_date'   => 'nullable|date|after_or_equal:publish_start_date',
                    'publish_end_time'   => 'nullable',

                    'sales_start_date'   => 'required|date',
                    'sales_start_time'   => 'required',

                    'sales_end_date'     => 'nullable|date|after_or_equal:sales_start_date',
                    'sales_end_time'     => 'nullable',
                ],
                [
                    // Custom messages with location ID
                    'publish_start_date.required' => "Publish Start Date is required for location {$locId}",
                    'publish_start_time.required' => "Publish Start Time is required for location {$locId}",
                    'sales_start_date.required'   => "Sales Start Date is required for location {$locId}",
                    'sales_start_time.required'   => "Sales Start Time is required for location {$locId}",

                    'publish_start_time.date_format' => "Invalid Publish Start Time format at location {$locId}",
                    'publish_end_time.date_format'   => "Invalid Publish End Time format at location {$locId}",
                    'sales_start_time.date_format'   => "Invalid Sales Start Time format at location {$locId}",
                    'sales_end_time.date_format'     => "Invalid Sales End Time format at location {$locId}",

                    'publish_end_date.after_or_equal' => "Publish End Date must be after Publish Start Date for location {$locId}",
                    'sales_end_date.after_or_equal'   => "Sales End Date must be after Sales Start Date for location {$locId}",
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    "message" => "Validation Failed",
                    "errors"  => $validator->errors()
                ], 422);
            }
        }

        $post_data['company_id'] = implode(',', $request->company_id);

        // Null cleanup
        $post_data['expiry_day']   = $post_data['expiry_day'] ?? null;
        $reward->update($post_data);

        // ----------------------------------
        // SAVE DIGITAL LOCATIONS
        // ----------------------------------
        foreach ($locationsDigital as $locId => $locData) {

            RewardDates::updateOrCreate(
                ['reward_id' => $reward->id, 'merchant_id' => (int)$locId],
                [
                    'publish_start_date' => $locData['publish_start_date'] ?? null,
                    'publish_start_time' => $locData['publish_start_time'] ?? null,
                    'publish_end_date'   => $locData['publish_end_date'] ?? null,
                    'publish_end_time'   => $locData['publish_end_time'] ?? null,
                    'sales_start_date'   => $locData['sales_start_date'] ?? null,
                    'sales_start_time'   => $locData['sales_start_time'] ?? null,
                    'sales_end_date'     => $locData['sales_end_date'] ?? null,
                    'sales_end_time'     => $locData['sales_end_time'] ?? null,
                    'qty'                => $locData['inventory_qty'] ?? null,
                ]
            );
        }


        $locationsPhysical = $request->input('locations', []);

        foreach ($locationsPhysical as $locId => $locData) {

            if (!isset($locData['selected'])) {
                continue; // skip unchecked
            }

            RewardDates::updateOrCreate(
                ['reward_id' => $reward->id, 'merchant_id' => (int) $locId],
                [
                    'qty' => $locData['inventory_qty'] ?? 0
                ]
            );
        }
      
        // ----------------------------------
        // TIER RATES
        // ----------------------------------
        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'tier_')) {
                $tierId = str_replace('tier_', '', $key);

                RewardTierRate::updateOrCreate(
                    [
                        'reward_id' => $reward->id,
                        'tier_id' => $tierId
                    ],
                    ['price' => $value]
                );
            }
        }

        // ------------------ CSV UPDATE LOGIC ------------------
        if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

            // 1️⃣ DELETE OLD FILE (if exists)
            if (!empty($reward->csvFile)) {
                $oldPath = public_path('rewardvoucher/' . $reward->csvFile);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            RewardVoucher::where('reward_id', $reward->id)->delete();

            $file = $request->file('csvFile');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('rewardvoucher'), $filename);

            $reward->csvFile = $filename;
            $reward->save();

            $path = public_path('rewardvoucher/' . $filename);
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $path);

            $sheet = $rows[0];

            foreach ($sheet as $row) {

                $code = trim($row[0] ?? '');

                if ($code === '' || strtolower($code) === 'code') {
                    continue; // skip header and blank rows
                }

                RewardVoucher::create([
                    'reward_id' => $reward->id,
                    'code'      => $code,
                    'is_used'   => 0
                ]);
            }
        }


        DB::commit();

        return response()->json(['status' => 'success', 'message' => 'Reward Updated Successfully']);
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
    public function getLocationsByCompany(Request $request)
    {
        $companyId = $request->get('company_id');

        if (!$companyId) {
            return response()->json(['status' => 'error', 'message' => 'Company ID is required']);
        }

        // 🔥 FIX HERE — convert JSON string to array if needed
        if (is_string($companyId)) {
            $decoded = json_decode($companyId, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $companyId = $decoded;
            }
        }

        // 🔥 Always ensure it's array
        if (!is_array($companyId)) {
            $companyId = [$companyId];
        }

        // Now this ALWAYS works
        $locations = Location::where('status', 'Active')
            ->whereIn('company_id', $companyId)
            ->select('id', 'name', 'code')
            ->get();

        if ($request->type == 'digital') {
            return response()->json([
                'status' => 'success',
                'html' => view($this->view_file_path . 'location-checkbox-d', ['locations' => $locations])->render(),
                'locations' => $locations
            ]);
        }

        return response()->json([
            'status' => 'success',
            'html' => view($this->view_file_path . 'location-checkbox-p', ['locations' => $locations])->render(),
            'locations' => $locations
        ]);
    }

    
    
}
