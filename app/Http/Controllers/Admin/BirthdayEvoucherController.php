<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\ParticipatingMerchantLocation;
use App\Models\Reward;
use App\Models\RewardLocation;
use App\Models\RewardLocationUpdate;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\RewardVoucher;
use App\Models\UserWalletVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BirthdayEvoucherController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.birthday-voucher.";
        $permission_prefix    = $this->permission_prefix = 'birthday-voucher';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Birthday Voucher',
            'module_base_url'   => url('admin/birthday-voucher'),
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $this->layout_data['category'] = Category::orderBy('name', 'ASC')->get();
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->orderBy('name', 'ASC')->get();
        $this->layout_data['rewards'] = Reward::get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->orderBy('name', 'ASC')->get();

        // 🔥 ADD THIS
        $this->layout_data['club_location'] = ClubLocation::where('status','Active')->orderBy('name', 'ASC')->get();

        return view($this->view_file_path . "index")
            ->with($this->layout_data);
    }



    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '2')->orderBy('month','asc');

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $month = '-';
    
    
            if (!empty($row->month)) {
    
                $rawMonth = $row->month;
    
                // If JSON string like '["2025-02"]'
                if (is_string($rawMonth) && str_starts_with(trim($rawMonth), '[')) {
                    $rawMonth = json_decode($rawMonth, true);
                }
    
                // If array → take first value only
                if (is_array($rawMonth)) {
                    $rawMonth = $rawMonth[0] ?? null;
                }
    
                if (!empty($rawMonth)) {
                    try {
                        $month = Carbon::parse(trim($rawMonth))
                            ->format(config('safra.month-format'));
                    } catch (\Exception $e) {
                        $month = '-';
                    }
                }
            }

            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';
            $final_data[$key]['quantity']       = number_format($row->inventory_qty);
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);

            $claimed = UserWalletVoucher::where('reward_id', $row->id)->where('reward_status', 'issued')->count();
            $final_data[$key]['claimed'] = max(0, $claimed);

            $redeemed = UserWalletVoucher::where('reward_id', $row->id)
                ->where('status', 'used')
                ->count();

            $final_data[$key]['redeemed'] = max(0, $redeemed);          
            $final_data[$key]['image'] = imagePreviewHtml("uploads/image/{$row->voucher_image}");

            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));
            $final_data[$key]['month'] = $month;
           
            $now = Carbon::now();

            /* ---------------- DRAFT OVERRIDE ---------------- */
            if (
                $row->is_draft == 1 &&
                !RewardUpdateRequest::where('reward_id', $row->id)->exists()
            ) {
                $status = '-';
            } else {

                $salesStart = ($row->sales_start_date && $row->sales_start_time)
                    ? Carbon::parse($row->sales_start_date.' '.$row->sales_start_time)
                    : null;

                $salesEnd = ($row->sales_end_date && $row->sales_end_time)
                    ? Carbon::parse($row->sales_end_date.' '.$row->sales_end_time)
                    : null;

                $hasApproved = RewardUpdateRequest::where('reward_id', $row->id)
                    ->where('status', 'approve')
                    ->exists();

                $hasPending = RewardUpdateRequest::where('reward_id', $row->id)
                    ->where('status', 'pending')
                    ->exists();

                /*
                FINAL PRIORITY
                1. Expired
                2. Pending approval
                3. Approved (ONLY if start date is future)
                4. Active
                */

                // 1. EXPIRED
                if (
                    ($row->voucher_validity && Carbon::parse($row->voucher_validity)->lt($now)) ||
                    ($salesEnd && $now->gt($salesEnd))
                ) {
                    $status = 'expired';
                }

                // 2. PENDING APPROVAL
                elseif ($hasPending) {
                    $status = 'pending approval';
                }

                // 3. APPROVED (only if sales not started yet)
                elseif ($hasApproved && $salesStart && $now->lt($salesStart)) {
                    $status = 'approved';
                }

                // 4. ACTIVE (approved OR not, but within window)
                elseif (
                    (!$salesStart || $now->gte($salesStart)) &&
                    (!$salesEnd || $now->lte($salesEnd))
                ) {
                    $status = 'active';
                }

                // SAFETY
                else {
                    $status = 'Upcoming';
                }
            }

           // ---------------- ISSUED (Match Birth Month Only) ----------------
            $issuedCount = 0;

            if (!empty($row->month)) {

                try {
                    // Extract month from 2026-01 → 01
                    $monthNumber = Carbon::parse($row->month)->format('m');

                    // Match with dob like 01/1997
                    $issuedCount = AppUser::where('age', 'like', $monthNumber.'/%')->count();

                } catch (\Exception $e) {
                    $issuedCount = 0;
                }
            }

            $final_data[$key]['issued'] = number_format($issuedCount);


            $final_data[$key]['status'] = $status;
            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
          
            $final_data[$key]['action'] = $action . "</div>";
        }
        $data          = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }
    /**
     * Display a listing of the resource.
     */
   
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
            * BASE VALIDATION
            * ---------------------------------------------------*/
            $rules = [
                'month' => 'required|array|min:1',
                'month.*' => 'required|date_format:Y-m',

                'voucher_image'       => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img'  => 'required|image|mimes:png,jpg,jpeg|max:2048',

                'name'           => 'required|string|max:191',
                'description'    => 'required|string',
                'term_of_use'    => 'required|string',
                'how_to_use'     => 'required|string',

                'merchant_id'      => 'required|exists:merchants,id',
                'expiry_type' => 'required|in:fixed,validity,no_expiry',

                'voucher_validity' => [
                    'required_if:expiry_type,fixed',
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {

                        if ($request->expiry_type === 'fixed') {

                            $salesEndDate = \Carbon\Carbon::parse($request->sales_end)->format('Y-m-d');
                            $validityDate = \Carbon\Carbon::parse($value)->format('Y-m-d');

                            if ($validityDate < $salesEndDate) {
                                $fail('Voucher expiry date must be after or equal to Redemption end date.');
                            }

                        }
                    }
                ],

                'validity_month' => 'required_if:expiry_type,validity|nullable|integer|min:1|max:12',
    
                'voucher_value'    => 'required|numeric|min:0',
                'voucher_set'      => 'required|integer|min:1',
                'set_qty'          => 'required|numeric|min:1',
                'clearing_method'  => 'required|in:0,1,2,3,4',
                'low_stock_1'      => 'nullable|min:0',
                'low_stock_2'      => 'nullable|min:0',
            ];

            $messages = [
                // Voucher Name
                'name.required' => 'Voucher Name is required.',
                'name.string'   => 'Voucher Name must be valid text.',
                'name.max'      => 'Voucher Name may not be greater than 191 characters.',

                'term_of_use.required'         => 'Voucher T&C is required',
                'set_qty.required' => 'Voucher set quantity is required.',
                'set_qty.integer'  => 'Voucher set quantity must be a valid number.',
                'set_qty.min'      => 'Voucher set quantity must be at least 1.',
                'voucher_detail_img.required'  => 'Voucher Detail Image is required',
                'voucher_detail_img.image'     => 'Voucher Detail Image must be an image file',
                'voucher_detail_img.mimes'     => 'Voucher Detail Image must be png, jpg, jpeg',
                'voucher_detail_img.max'       => 'Voucher Detail Image may not be greater than 2048 KB',

                // Voucher Validity Date
                'expiry_type.required' => 'Please select voucher expiry type.',

                'voucher_validity.required_if' => 'Voucher expiry date is required when Fixed Expiry Date is selected.',
                'voucher_validity.date' => 'Voucher expiry date must be a valid date.',

                'validity_month.required_if' => 'Validity period is required when Validity Period is selected.',
                'validity_month.integer' => 'Validity period must be a number.',
                'validity_month.min' => 'Validity period must be at least 1 month.',
                'validity_month.max' => 'Validity period may not be greater than 12 months.',

            ];

            /* ---------------------------------------------------
            * INVENTORY TYPE RULES
            * ---------------------------------------------------*/

            $rules['inventory_qty'] = 'required|integer|min:1';
            

          

            /* ---------------------------------------------------
            * VALIDATE
            * ---------------------------------------------------*/
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            /* ---------------------------------------------------
            * CLUB LOCATION VALIDATION
            * ---------------------------------------------------*/

          

            $locationErrors = [];
            $anyLocationUsed = false;

            if (!empty($request->locations)) {

                foreach ($request->locations as $clubId => $clubData) {

                    $inventoryQty = isset($clubData['inventory_qty']) 
                        ? (int) $clubData['inventory_qty'] 
                        : 0;

                    $merchantId = $clubData['merchant_id'] ?? null;

                    $clubName = ClubLocation::find($clubId)->name ?? "Club ID: $clubId";

                    if ($inventoryQty > 0) {

                        $anyLocationUsed = true;

                        if (empty($merchantId)) {
                            $locationErrors[] = "Please select Participating Merchant for {$clubName}";
                        }

                        if (
                            empty($request->selected_outlets[$clubId]) ||
                            !is_array($request->selected_outlets[$clubId])
                        ) {
                            $locationErrors[] = "Please select at least one outlet for {$clubName}";
                        }
                    }
                }

                // 🔥 After loop → check if none selected
                if (!$anyLocationUsed) {
                    $locationErrors[] = "Please enter inventory for at least one location";
                }
            }

            /* At least one club must have inventory */
            if (!$anyLocationUsed) {
                $locationErrors[] = "Please set inventory quantity for at least one club location";
            }

            if (!empty($locationErrors)) {
                return response()->json([
                    "status" => "error",
                    "errors" => [
                        "locations" => $locationErrors
                    ]
                ], 422);
            }

            /* ---------------------------------------------------
            * MONTH DUPLICATE CHECK
            * ---------------------------------------------------*/

            $months = $request->month;

            $existingMonths = Reward::whereIn('month', $months)
                ->pluck('month')
                ->toArray();

            if (!empty($existingMonths)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'month' => [
                            'Voucher already exists for month: ' . implode(', ', $existingMonths)
                        ]
                    ]
                ], 422);
            }

            // Continue your reward creation logic here...


              /* ---------------------------------------------------
            * UPLOAD IMAGE
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {
                $file = $request->file('voucher_image');
                $filename = generateHashFileName($file);
                // $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/image'), $filename);
                $validated['voucher_image'] = $filename;
            }


            if ($request->hasFile('voucher_detail_img')) {

                $path = public_path('uploads/image');

                // Create directory if not exists
                if (!is_dir($path)) {
                    mkdir($path, 0775, true);
                }

                $file = $request->file('voucher_detail_img');
                $filename = generateHashFileName($file);
                // $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($path, $filename);

                $validated['voucher_detail_img'] = $filename;
            }
   

            $filePath = null;
            $filename = null;
            $rows = [];
          
            

            foreach ($months as $monthValue) {


               $reward = Reward::create([
                    'type'        => '2',
                    'month'       => $monthValue,
                    'from_month'  => $monthValue,
                    'to_month'    => $monthValue,
                    'voucher_image'      => $validated['voucher_image'],
                    'voucher_detail_img' => $validated['voucher_detail_img'],
                    'name'        => $validated['name'],
                    'description' => $validated['description'],
                    'term_of_use' => $validated['term_of_use'],
                    'how_to_use'  => $validated['how_to_use'],
                    'merchant_id' => $validated['merchant_id'],
                    'reward_type' => 0,
                    'expiry_type' => $request['expiry_type'],
                    'voucher_validity' => $request['expiry_type'] === 'fixed' ? $request['voucher_validity'] : null,
                    'validity_month' => $request['expiry_type'] === 'validity'  ? $request['validity_month'] : null,
                    'inventory_type'   => 0,
                    'inventory_qty'    => (int) ($request->inventory_qty ?? 0),
                    'voucher_value'    => (float) $validated['voucher_value'],
                    'voucher_set'      => (int) $validated['voucher_set'],
                    'set_qty'          => (int) $validated['set_qty'],
                    'clearing_method'  => (int) $validated['clearing_method'],
                    'participating_merchant_id' => (int) ($request->participating_merchant_id ?? 0),
                    'hide_quantity' => $request->boolean('hide_quantity'),
                    'low_stock_1' => (int) ($validated['low_stock_1'] ?? 0),
                    'low_stock_2' => (int) ($validated['low_stock_2'] ?? 0),
                    'is_draft' => 2,
                ]);

                $updateRequest = RewardUpdateRequest::create([
                    'type'        => '2',
                    'reward_id'   => $reward->id,
                    'month'       => $monthValue,
                    'from_month'  => $monthValue,
                    'to_month'    => $monthValue,
                    'voucher_image'      => $validated['voucher_image'],
                    'voucher_detail_img' => $validated['voucher_detail_img'],
                    'name'        => $validated['name'],
                    'description' => $validated['description'],
                    'term_of_use' => $validated['term_of_use'],
                    'how_to_use'  => $validated['how_to_use'],
                    'merchant_id' => $validated['merchant_id'],
                    'reward_type' => 0,
                    'expiry_type' => $request['expiry_type'],
                    'voucher_validity' => $request['expiry_type'] === 'fixed' ? $request['voucher_validity'] : null,
                    'validity_month' => $request['expiry_type'] === 'validity'  ? $request['validity_month'] : null,
                    'inventory_type'   => 0,
                    'inventory_qty'    => (int) ($request->inventory_qty ?? 0),
                    'voucher_value'    => (float) $validated['voucher_value'],
                    'voucher_set'      => (int) $validated['voucher_set'],
                    'set_qty'          => (int) $validated['set_qty'],
                    'clearing_method'  => (int) $validated['clearing_method'],
                    'participating_merchant_id' => (int) ($request->participating_merchant_id ?? 0),
                    'hide_quantity' => $request->boolean('hide_quantity'),
                    'low_stock_1' => (int) ($validated['low_stock_1'] ?? 0),
                    'low_stock_2' => (int) ($validated['low_stock_2'] ?? 0),
                    'is_draft' => 2,
                    'request_by' => auth()->id(),
                    'status' => 'pending',
                ]);

                // $current->addMonth();

                if ($filePath) {

                    $updateRequest->update(['csvFile' => $filename]);
                    $reward->update(['csvFile' => $filename]);

                    foreach ($rows[0] as $row) {

                        $code = trim($row[0] ?? '');

                        if ($code === '' || strtolower($code) === 'code') {
                            continue;
                        }

                        RewardVoucher::create([
                            'type'      => 1,
                            'reward_id' => $reward->id,
                            'code'      => $code,
                            'is_used'   => 0
                        ]);
                    }
                }

                // -----------------------------------
                // CLUB LOCATIONS (Inventory per club)
                // -----------------------------------
                $validClubSelected = false;

                if (!empty($request->locations)) {

                    foreach ($request->locations as $clubId => $clubData) {

                        $inventoryQty = isset($clubData['inventory_qty'])  ? (int) $clubData['inventory_qty'] : 0;

                        // ❌ Skip if inventory empty or zero
                        if ($inventoryQty <= 0) {
                            continue;
                        }

                        RewardLocation::create([
                            'reward_id'   => $reward->id,
                            'location_id' => $clubId,
                            'merchant_id' => $validated['merchant_id'],
                            'inventory_qty' => $inventoryQty,
                            'total_qty'     => $inventoryQty,
                            'is_selected'   => 1,
                        ]);

                        RewardLocationUpdate::create([
                            'reward_id'     => $reward->id,
                            'merchant_id'   => $validated['merchant_id'],
                            'location_id'   => $clubId,
                            'is_selected'   => 1,
                            'inventory_qty' => $inventoryQty,
                            'total_qty'     => $inventoryQty,
                        ]);

                        $validClubSelected = true;
                    }
                }



                // -----------------------------------
                // PARTICIPATING OUTLETS
                // -----------------------------------
                if ($request->clearing_method == 2 && !empty($request->selected_outlets)) {

                    foreach ($request->selected_outlets as $clubId => $outletIds) {

                        foreach ($outletIds as $locId) {

                            $merchantId = ParticipatingMerchantLocation::where('id', $locId)->value('participating_merchant_id');

                            if (!$merchantId) continue;

                            ParticipatingLocations::create([
                                'reward_id'                 => $reward->id,
                                'club_location_id'          => $clubId,
                                'participating_merchant_id' => $merchantId,
                                'location_id'               => $locId,
                                'is_selected'               => 1,
                            ]);

                            RewardParticipatingMerchantLocationUpdate::create([
                                'reward_id'                 => $reward->id,
                                'club_location_id'          => $clubId,
                                'participating_merchant_id' => $merchantId,
                                'location_id'               => $locId,
                                'is_selected'               => 1,
                            ]);
                        }
                    }
                }
            }


               

            DB::commit();
            return response()->json(['status'=>'success','message'=>'Reward Created Successfully And Sent For Approval Successfully']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status'=>'error','message'=>$e->getMessage()], 500);
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
        $reward = Reward::with([
            'participatingLocations',
            'rewardLocations'   // 👈 IMPORTANT
        ])->find($id);

        $this->layout_data['data'] = $reward;
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->orderBy('name', 'ASC')->get();
        $this->layout_data['category'] = Category::orderBy('name', 'ASC')->get();
        $this->layout_data['club_location'] = ClubLocation::orderBy('name', 'ASC')->get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->orderBy('name', 'ASC')->get();

        // -------------------------------
        // GROUP SELECTED OUTLETS
        // -------------------------------
       // Get all location ids first
        $allLocationIds = $reward->participatingLocations
            ->pluck('location_id')
            ->unique()
            ->values();

        // Fetch names from real table
        $locationMap = ParticipatingMerchantLocation::whereIn('id', $allLocationIds)
            ->pluck('name', 'id'); // id => name

        // Group properly with id + name
        $groupedLocations = $reward->participatingLocations
            ->groupBy('club_location_id')
            ->map(function ($items) use ($locationMap) {
                return $items->map(function ($item) use ($locationMap) {
                    return [
                        'id'   => $item->location_id,
                        'name' => $locationMap[$item->location_id] ?? null,
                    ];
                })->values();
            });


        // -------------------------------
        // GET CLUB INVENTORY
        // -------------------------------
        $clubInventory = $reward->rewardLocations
            ->mapWithKeys(function ($item) {
                return [
                    $item->location_id => $item->inventory_qty
                ];
            });

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'selectedOutlets' => $groupedLocations,
            'clubInventory'   => $clubInventory
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $reward = Reward::findOrFail($id);
            $rewardUpdateRequest = RewardUpdateRequest::where('reward_id', $reward->id)->latest()->first();
            $currentMonth = now()->format('Y-m');

            /* ===================================================
            | CASE 1: CURRENT MONTH → ONLY INVENTORY UPDATE
            ===================================================*/

            if ($reward->month === $currentMonth) {

                if ($reward->inventory_type != 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Current month reward cannot be edited.'
                    ], 422);
                }

                if (empty($request->locations)) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => [
                            'locations' => ['Inventory data is required.']
                        ]
                    ], 422);
                }

                foreach ($request->locations as $clubId => $clubData) {

                    $newQty = isset($clubData['inventory_qty'])
                        ? (int) $clubData['inventory_qty']
                        : 0;

                    if ($newQty <= 0) {
                        continue;
                    }

                    $existingQty = RewardLocation::where('reward_id', $reward->id)
                        ->where('location_id', $clubId)
                        ->value('inventory_qty') ?? 0;

                    // 🔴 Prevent decreasing
                    if ($newQty < $existingQty) {
                        return response()->json([
                            'status' => 'error',
                            'errors' => [
                                "locations.{$clubId}.inventory_qty" =>
                                    ["Inventory cannot be reduced for current month."]
                            ]
                        ], 422);
                    }

                    // ✅ Update only if increased
                    RewardLocation::where('reward_id', $reward->id)
                        ->where('location_id', $clubId)
                        ->update([
                            'inventory_qty' => $newQty,
                            'total_qty'     => $newQty
                        ]);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Inventory Updated Successfully'
                ]);
            }


          
            

            /* ===================================================
            | CASE 2: NON-CURRENT MONTH → ADMIN APPROVAL FLOW
            ===================================================*/

            $rules = [
                'month' => 'required',

                'voucher_image'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'name'              => 'required|string',
                'description'       => 'required|string',
                'term_of_use'       => 'required|string',
                'how_to_use'        => 'required|string',
                'merchant_id'       => 'required|exists:merchants,id',
                'expiry_type' => 'required|in:fixed,validity,no_expiry',

                'voucher_validity' => [
                    'required_if:expiry_type,fixed',
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {

                        if ($request->expiry_type === 'fixed') {

                            $salesEndDate = \Carbon\Carbon::parse($request->sales_end)->format('Y-m-d');
                            $validityDate = \Carbon\Carbon::parse($value)->format('Y-m-d');

                            if ($validityDate < $salesEndDate) {
                                $fail('Voucher expiry date must be after or equal to Redemption end date.');
                            }

                        }
                    }
                ],

                'validity_month' => 'required_if:expiry_type,validity|nullable|integer|min:1|max:12',
    
                'inventory_qty'     => 'nullable|integer|min:0',
                'voucher_value'     => 'required|numeric|min:0',
                'voucher_set'       => 'required|integer|min:1',
                'set_qty'       => 'required|integer|min:1',
                'clearing_method'   => 'required',
                'low_stock_1'       => 'nullable|min:0',
                'low_stock_2'       => 'nullable|min:0',
            ];

            $messages = [     
                // Voucher Name
                'name.required' => 'Voucher name is required.',
                'name.string'   => 'Voucher name must be valid text.',
                'name.max'      => 'Voucher name may not be greater than 191 characters.',
         
                'term_of_use.required' => 'Voucher T&C is required',
                'set_qty.required' => 'Voucher set quantity is required.',
                'set_qty.integer'  => 'Voucher set quantity must be a valid number.',
                'set_qty.min'      => 'Voucher set quantity must be at least 1.',
                'voucher_detail_img.required' => 'Voucher Detail Image is required',
                'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes',

                // Voucher Validity Date
                'expiry_type.required' => 'Please select voucher expiry type.',

                'voucher_validity.required_if' => 'Voucher expiry date is required when Fixed Expiry Date is selected.',
                'voucher_validity.date' => 'Voucher expiry date must be a valid date.',

                'validity_month.required_if' => 'Validity period is required when Validity Period is selected.',
                'validity_month.integer' => 'Validity period must be a number.',
                'validity_month.min' => 'Validity period must be at least 1 month.',
                'validity_month.max' => 'Validity period may not be greater than 12 months.',

            ];

             /* ---------------------------------------------------
            * INVENTORY RULES
            * ---------------------------------------------------*/

            // Non-merchant → qty required
            $rules['inventory_qty'] = 'required|integer|min:1';
         
            /* ---------------------------------------------------
            * VALIDATE
            * ---------------------------------------------------*/
           
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();
                        /* ---------------------------------------------------
            * CLUB LOCATION VALIDATION (UPDATE)
            * ---------------------------------------------------*/

            $locationErrors = [];
            $anyLocationUsed = false;

            if (!empty($request->locations)) {

                $firstClubId = array_key_first($request->locations);

                foreach ($request->locations as $clubId => $clubData) {

                    $inventoryQty = isset($clubData['inventory_qty'])
                        ? (int) $clubData['inventory_qty']
                        : 0;

                    $merchantId = $clubData['merchant_id'] ?? null;

                    $clubName = ClubLocation::find($clubId)->name ?? "Club ID: $clubId";

                    /* 1️⃣ FIRST CLUB → inventory required */
                    if ($clubId == $firstClubId && $inventoryQty <= 0) {
                        $locationErrors[] = "Inventory quantity is required for {$clubName}";
                    }

                    /* 2️⃣ If inventory > 0 → validate */
                    if ($inventoryQty > 0) {

                        $anyLocationUsed = true;

                        $selectedOutlets = $request->selected_outlets[$clubId] ?? [];

                        // 🔴 Outlets required
                        if (empty($selectedOutlets) || !is_array($selectedOutlets)) {
                            $locationErrors[] = "Please select at least one outlet for {$clubName}";
                        }

                        /*
                        🔴 Merchant required ONLY IF:
                        - No merchant sent
                        - AND no existing merchant in update table
                        */
                        $existingMerchant = RewardLocationUpdate::where('reward_id', $reward->id)
                                            ->where('location_id', $clubId)
                                            ->value('merchant_id');

                        if (
                            empty($merchantId) &&
                            empty($existingMerchant) &&
                            (empty($selectedOutlets) || !is_array($selectedOutlets))
                        ) {
                            $locationErrors[] = "Please select Participating Merchant for {$clubName}";
                        }

                    }
                }
            }

            /* At least one club must have inventory */
            if (!$anyLocationUsed) {
                $locationErrors[] = "Please set inventory quantity for at least one club location";
            }

            if (!empty($locationErrors)) {
                return response()->json([
                    "status" => "error",
                    "errors" => [
                        "locations" => $locationErrors
                    ]
                ], 422);
            }


            /* ===================================================
            | CHECK EXISTING PENDING REQUEST
            ===================================================*/
            if (RewardUpdateRequest::where('reward_id', $reward->id)
                ->where('status', 'pending')
                ->exists()) {

                // return response()->json([
                //     'status' => 'error',
                //     'errors' => [
                //         'from_month' => ['Your request is already pending approval.']
                //     ]
                // ], 422);
            }

            /* ===================================================
            | IMAGE UPLOAD
            ===================================================*/
            if ($request->hasFile('voucher_image')) {
                $file = $request->file('voucher_image');
                $filename = generateHashFileName($file);
                // $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/image'), $filename);
                $validated['voucher_image'] = $filename;
            }

            $month = $request->month;

            if (is_array($month)) {
                $month = $month[0];
            }

            /* ===================================================
            | CREATE UPDATE REQUEST
            ===================================================*/
            $data = [
                'type'           => '2',
                'month'       => $month,
                'from_month'  => $month,
                'to_month'    => $month,
                'request_by'     => auth()->id(),
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
                'name'           => $validated['name'],
                'description'    => $validated['description'],
                'term_of_use'    => $validated['term_of_use'],
                'how_to_use'     => $validated['how_to_use'],
                'merchant_id'    => $validated['merchant_id'],
                'reward_type'    => 0,
                'expiry_type' => $validated['expiry_type'],
                'voucher_validity' => $validated['expiry_type'] === 'fixed' ? $validated['voucher_validity'] : null,
                'validity_month' => $validated['expiry_type'] === 'validity'  ? $validated['validity_month'] : null,
                'inventory_type'      => 0,
                'inventory_qty'      => (int) ($request['inventory_qty'] ?? null),
                'voucher_value'      =>(int) ($validated['voucher_value']),
                'voucher_set'        =>(int) ($validated['voucher_set']),
                'set_qty'            =>(int) ($validated['set_qty']),
                'clearing_method'    =>(int) ($validated['clearing_method']),
                'location_text'  => $request->location_text,
                'participating_merchant_id' => $request->participating_merchant_id ?? 0,
                'hide_quantity'  => $request->hide_quantity ? 1 : 0,
                'low_stock_1'    => $validated['low_stock_1'] ?? 0,
                'low_stock_2'    => $validated['low_stock_2'] ?? 0,
                'status'         => 'pending',
            ];

            $updateRequest = RewardUpdateRequest::updateOrCreate(
                [
                    'reward_id' => $reward->id,
                    'type'    => '2',
                    'status'    => 'pending',
                ],
                $data
            );

           /* -----------------------------------
            | UPDATE PARTICIPATING OUTLETS (UPDATE TABLE)
            -----------------------------------*/

            if ($request->clearing_method == 2 && !empty($request->selected_outlets)) {

                RewardParticipatingMerchantLocationUpdate::where('reward_id', $reward->id)->delete();
                foreach ($request->selected_outlets as $clubId => $outletIds) {

                    if (empty($outletIds)) {
                        continue;
                    }

                    foreach ($outletIds as $locId) {

                        $merchantId = ParticipatingMerchantLocation::where('id', $locId)->value('participating_merchant_id');

                        if (!$merchantId) {
                            continue;
                        }

                        RewardParticipatingMerchantLocationUpdate::create([
                            'reward_id'                 => $reward->id,
                            'participating_merchant_id' => $merchantId,
                            'club_location_id' => $clubId,
                            'location_id'               => $locId,
                            'is_selected'               => 1,
                        ]);
                       
                    }
                }
            }


            /* -----------------------------------
            | UPDATE CLUB INVENTORY (UPDATE TABLE)
            -----------------------------------*/

            if (!empty($request->locations)) {

                foreach ($request->locations as $clubId => $clubData) {

                    $inventoryQty = isset($clubData['inventory_qty'])
                        ? (int) $clubData['inventory_qty']
                        : 0;

                    if ($inventoryQty <= 0) {
                        continue;
                    }

                    RewardLocationUpdate::updateOrCreate(
                        [
                            'reward_id'   => $reward->id,
                            'location_id' => $clubId,
                        ],
                        [
                            'merchant_id'   => $validated['merchant_id'],
                            'is_selected'   => 1,
                            'inventory_qty' => $inventoryQty,
                            'total_qty'     => $inventoryQty,
                        ]
                    );
                }
            }



            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Voucher Updated Successfully And Sent For Approval Successfully'
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

            // $walletExists = UserWalletVoucher::where('reward_id', $reward->id)->exists();

            // if ($walletExists) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'This reward exists in user wallet. You cannot delete it.'
            //     ], 404);
            // }

        
            if ($reward->voucher_image && file_exists(public_path('uploads/image/' . $reward->voucher_image))) {
                unlink(public_path('uploads/image/' . $reward->voucher_image));
            }
            if ($reward->voucher_detail_img && file_exists(public_path('uploads/image/' . $reward->voucher_detail_img))) {
                unlink(public_path('uploads/image/' . $reward->voucher_detail_img));
            }
            if ($reward->csvFile && file_exists(public_path('uploads/csv/' . $reward->csvFile))) {
                unlink(public_path('uploads/csv/' . $reward->csvFile));
            }
           
            RewardUpdateRequest::where('reward_id', $reward->id)->delete();
            RewardParticipatingMerchantLocationUpdate::where('reward_id', $reward->id)->delete();
            ParticipatingLocations::where('reward_id', $reward->id)->delete();
            RewardVoucher::where('reward_id', $reward->id)->delete();
            $reward->delete();

            AdminLogger::log('delete', Reward::class, $id);
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

    public function getLocations(Request $request)
    {
        $merchantId = $request->merchant_id;

        $locations = ClubLocation::where('merchant_id', $merchantId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }
    
    public function getClubMerchantOutletStructure(Request $request)
    {
        $merchantIds = $request->merchant_ids;

        if (empty($merchantIds)) {
            return response()->json([
                'status' => 'success',
                'locations' => []
            ]);
        }

        // normalize to array
        if (!is_array($merchantIds)) {
            $merchantIds = [$merchantIds];
        }

        $locations = ParticipatingMerchantLocation::whereIn(
                'participating_merchant_id',
                $merchantIds
            )
            ->where('status', 'Active')
            ->select('id', 'name', 'participating_merchant_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'locations' => $locations
        ]);
        
    }

}
