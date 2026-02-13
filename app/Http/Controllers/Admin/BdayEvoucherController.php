<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\Evoucher;
use App\Models\ParticipatingMerchantLocation;
use App\Models\Reward;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\RewardVoucher;
use App\Models\UserPurchasedReward;
use App\Models\UserWalletVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct
use App\Rules\SingleCodeColumnFile;

class BdayEvoucherController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.birthday-voucher.";
        $permission_prefix    = $this->permission_prefix = 'bday-voucher';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Birthday Voucher',
            'module_base_url'   => url('admin/birthday-voucher'),
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
        $this->layout_data['category'] = Category::get();
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['rewards'] = Reward::get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '2')->orderBy('month','asc');

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';
            $final_data[$key]['quantity']       = number_format($row->inventory_qty);
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);


            $redeemed = UserWalletVoucher::where('reward_id', $row->id)
                ->where('status', 'used')
                ->count();

            $final_data[$key]['redeemed'] = max(0, $redeemed);
            $duration = $row->created_at->format(config('safra.date-format'));

            if (!empty($row->voucher_image)) {
                $imgUrl = asset("uploads/image/{$row->voucher_image}");

                $final_data[$key]['image'] = '
                    <a href="'.$imgUrl.'" target="_blank">
                        <img src="'.$imgUrl.'"
                            class="avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle"
                            alt="Voucher Image">
                    </a>';
            } else {
                $imgUrl = asset("uploads/image/no-image.png");
                $final_data[$key]['image'] = '<img src="'.$imgUrl.'" class="avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle" alt="Voucher Image">';
            }

            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));
            $fromMonth = $row->from_month
                ? Carbon::createFromFormat('Y-m', $row->from_month)
                    ->format(config('safra.month-format'))
                : null;

            $toMonth = $row->to_month
                ? Carbon::createFromFormat('Y-m', $row->to_month)
                    ->format(config('safra.month-format'))
                : null;

            if ($fromMonth && $toMonth) {
                $final_data[$key]['month'] = $fromMonth . ' To ' . $toMonth;
            } elseif ($fromMonth) {
                $final_data[$key]['month'] = $fromMonth;
            } else {
                $final_data[$key]['month'] = '-';
            }
            $final_data[$key]['month'] =  $row->month ? Carbon::createFromFormat('Y-m', $row->month)->format(config('safra.month-format')): null;

            

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
                    $status = '-';
                }
            }



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
            * VALIDATION BASED ON BLADE FIELDS ONLY
            * ---------------------------------------------------*/
            $rules = [
                'from_month' => 'required',
                'to_month' => 'required',
                'voucher_image'    => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'required|image|mimes:png,jpg,jpeg|max:2048',

                'name'               => 'required|string|max:191',
                'description'      => 'required|string',
                'term_of_use'      => 'required|string',
                'how_to_use'       => 'required|string',
    
                'merchant_id'        => 'required|exists:merchants,id',
                'voucher_validity'   => 'required|date',
                'inventory_type'     => 'required|in:0,1',
                'voucher_value'      => 'required|numeric|min:0',
                'voucher_set'        => 'required|integer|min:1',
                'set_qty'          => 'required|numeric|min:1',
                'clearing_method'    => 'required|in:0,1,2,3,4',
                'low_stock_1'        => 'nullable|min:0',
                'low_stock_2'        => 'nullable|min:0',
            ];

            $messages = [];
            $messages = [
                'term_of_use.required' => 'Voucher T&C is required',
                'term_of_use.string'   => 'Voucher T&C is required',
            ];



            /* ---------------------------------------------------
            * INVENTORY RULES
            * ---------------------------------------------------*/

            // Non-merchant → qty required
            if ($request->inventory_type == 0) {
                $rules['inventory_qty'] = 'required|integer|min:1';
            }


            // Merchant → file required
            if ((int) $request->inventory_type === 1) {
                $rules['csvFile'] = ['required','file','mimes:csv,xlsx', new SingleCodeColumnFile(),];
            }



            /* ---------------------------------------------------
            * CLEARING METHOD RULES
            * ---------------------------------------------------*/

            // External link → text required
            if ($request->clearing_method != 2 && $request->clearing_method != 4) {
                $rules['location_text'] = 'required';
                $messages = [
                    'location_text.required' => 'Location is required',
                ];
            }


            // External code + Merchant code → merchant + locations required
            if ($request->clearing_method == 2) {
                $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';

                $rules['participating_merchant_locations'] = 'required|array|min:1';

                foreach ($request->participating_merchant_locations ?? [] as $locId => $locData) {
                    if (isset($locData['selected'])) {
                        // nothing extra here – just mark as selected
                    }
                }
            }

            /* ---------------------------------------------------
            * VALIDATE
            * ---------------------------------------------------*/
            $validator = Validator::make($request->all(), $rules,$messages);

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

              /* ---------------------------------------------------
            * CREATE REWARD (e-Voucher only)
            * ---------------------------------------------------*/
            $startMonth = Carbon::createFromFormat('Y-m', $validated['from_month'])->startOfMonth();
            $endMonth   = Carbon::createFromFormat('Y-m', $validated['to_month'])->startOfMonth();

            $current = $startMonth->copy();          
            $existingMonths = [];

            while ($current->lte($endMonth)) {

                $monthValue = $current->format('Y-m');

                if (Reward::where('month', $monthValue)->exists()) {
                    $existingMonths[] = $monthValue;
                }

                $current->addMonth();
            }

            if (!empty($existingMonths)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'from_month' => [
                            'Voucher already exists for month: ' . implode(', ', $existingMonths)
                        ]
                    ]
                ], 422);
            }

              /* ---------------------------------------------------
            * UPLOAD IMAGE
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {
                $file = $request->file('voucher_image');
                $filename = time().'_'.$file->getClientOriginalName();
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
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($path, $filename);

                $validated['voucher_detail_img'] = $filename;
            }
   

            $current = $startMonth->copy();
            $filePath = null;
            $filename = null;
            $rows = [];
            if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                $file = $request->file('csvFile');

                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/csv'), $filename);

                $filePath = public_path('uploads/csv/'.$filename);

                $rows = Excel::toArray([], $filePath);
            }
            while ($current->lte($endMonth)) {

                $reward = Reward::create([
                    'type'               => '2',
                    'month'              => $current->format('Y-m'),
                    'from_month'         => $current->format('Y-m'),
                    'to_month'           => $current->format('Y-m'),
                    'voucher_image'       => $validated['voucher_image'],
                    'voucher_detail_img'  => $validated['voucher_detail_img'],
                    'name'               => $validated['name'],
                    'description'        => $validated['description'],
                    'term_of_use'        => $validated['term_of_use'],
                    'how_to_use'         => $validated['how_to_use'],
                    'merchant_id'        => $validated['merchant_id'],
                    'reward_type'        => 0,
                    'voucher_validity' => $request->filled('voucher_validity') ? $request->voucher_validity : null,
                    'inventory_type'      => (int) ($validated['inventory_type'] ?? 0),
                    'inventory_qty'       => (int) ($request->inventory_qty ?? 0),
                    'voucher_value'       => (float) ($validated['voucher_value'] ?? 0),
                    'voucher_set'         => (int) ($validated['voucher_set'] ?? 0),
                    'set_qty'             => (int) ($validated['set_qty'] ?? 0),
                    'clearing_method'     => (int) ($validated['clearing_method'] ?? 0),
                    'participating_merchant_id' => (int) ($request->participating_merchant_id ?? 0),
                    'hide_quantity'       => $request->boolean('hide_quantity'),
                    'low_stock_1'         => (int) ($validated['low_stock_1'] ?? 0),
                    'low_stock_2'         => (int) ($validated['low_stock_2'] ?? 0),
                    'is_draft'              => 2,
                ]);
                $updateRequest = RewardUpdateRequest::create([
                    'type'               => '2',
                    'reward_id'            => $reward->id,
                    'month'              => $current->format('Y-m'),
                    'from_month'         => $current->format('Y-m'),
                    'to_month'           => $current->format('Y-m'),
                    'voucher_image'       => $validated['voucher_image'],
                    'voucher_detail_img'  => $validated['voucher_detail_img'],
                    'name'               => $validated['name'],
                    'description'        => $validated['description'],
                    'term_of_use'        => $validated['term_of_use'],
                    'how_to_use'         => $validated['how_to_use'],
                    'merchant_id'        => $validated['merchant_id'],
                    'reward_type'        => 0,
                    'voucher_validity' => $request->filled('voucher_validity') ? $request->voucher_validity : null,
                    'inventory_type'      => (int) ($validated['inventory_type'] ?? 0),
                    'inventory_qty'       => (int) ($request->inventory_qty ?? 0),
                    'voucher_value'       => (float) ($validated['voucher_value'] ?? 0),
                    'voucher_set'         => (int) ($validated['voucher_set'] ?? 0),
                    'set_qty'             => (int) ($validated['set_qty'] ?? 0),
                    'clearing_method'     => (int) ($validated['clearing_method'] ?? 0),
                    'participating_merchant_id' => (int) ($request->participating_merchant_id ?? 0),
                    'hide_quantity'       => $request->boolean('hide_quantity'),
                    'low_stock_1'         => (int) ($validated['low_stock_1'] ?? 0),
                    'low_stock_2'         => (int) ($validated['low_stock_2'] ?? 0),
                    'is_draft'              => 2,
                    'request_by'           => auth()->id(),
                    'status'               => 'pending',
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

                // -------------------------------
                // PARTICIPATING LOCATIONS
                // -------------------------------
                if ($request->clearing_method == 2 && !empty($request->participating_merchant_locations)) {

                    foreach ($request->participating_merchant_locations as $locId => $locData) {

                        if (!isset($locData['selected'])) continue;

                        $merchantId = ParticipatingMerchantLocation::where('id', $locId)
                            ->value('participating_merchant_id');

                        if (!$merchantId) continue;

                        ParticipatingLocations::create([
                            'reward_id'                 => $reward->id,
                            'participating_merchant_id' => $merchantId,
                            'location_id'               => $locId,
                            'is_selected'               => 1,
                        ]);
                    }
                }

                // move to next month
                $current->addMonth();
            }


            DB::commit();
            return response()->json(['status'=>'success','message'=>'Reward Created Successfully']);

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
        $reward = Reward::with(['participatingLocations'])->find($id);
        $this->layout_data['data'] = $reward;
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['category'] = Category::get();
        $this->layout_data['club_location'] = ClubLocation::get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        // 🔥 Get location IDs
        $locationIds = $reward->participatingLocations->pluck('location_id')->unique()->values();

        // 🔥 Fetch names from participating_merchant_locations
        $locations = ParticipatingMerchantLocation::whereIn('id', $locationIds)->select('id', 'name')->get()
            ->map(function ($loc) {
                return [
                    'id'   => $loc->id,
                    'name' => $loc->name,
                ];
            });

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
            'participatingLocations' => $locations
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

                if (!$request->filled('inventory_qty')) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => [
                            'inventory_qty' => ['Inventory quantity is required.']
                        ]
                    ], 422);
                }

                if ($request->inventory_qty < $reward->inventory_qty) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => [
                            'inventory_qty' => ['Inventory cannot be reduced for current month.']
                        ]
                    ], 422);
                }

                $rewardUpdateRequest->update([
                    'inventory_qty' => $request->inventory_qty
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Inventory updated successfully'
                ]);
            }

            /* ===================================================
            | CASE 2: NON-CURRENT MONTH → ADMIN APPROVAL FLOW
            ===================================================*/

            $rules = [
                'from_month'        => 'required',
                'to_month'          => 'required',
                'voucher_image'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'name'              => 'required|string',
                'description'       => 'required|string',
                'term_of_use'       => 'required|string',
                'how_to_use'        => 'required|string',
                'merchant_id'       => 'required|exists:merchants,id',
                'voucher_validity'  => 'required|date',
                'inventory_type'    => 'required|in:0,1',
                'inventory_qty'     => 'nullable|integer|min:0',
                'voucher_value'     => 'required|numeric|min:0',
                'voucher_set'       => 'required|integer|min:1',
                'set_qty'       => 'required|integer|min:1',
                'clearing_method'   => 'required',
                'low_stock_1'       => 'nullable|min:0',
                'low_stock_2'       => 'nullable|min:0',
            ];

            $messages = [
                'term_of_use.required' => 'Voucher T&C is required',
                'term_of_use.string'   => 'Voucher T&C is required',
            ];

             /* ---------------------------------------------------
            * INVENTORY RULES
            * ---------------------------------------------------*/

            // Non-merchant → qty required
            if ($request->inventory_type == 0) {
                $rules['inventory_qty'] = 'required|integer|min:1';
            }

            // Merchant → file required
            if ($request->inventory_type == 1) {
                if(!$reward->csvFile){
                    $rules['csvFile'] = ['required','file','mimes:csv,xlsx', new SingleCodeColumnFile(),];
                }
            }


            /* ---------------------------------------------------
            * CLEARING METHOD RULES
            * ---------------------------------------------------*/

            // External link → text required
            if ($request->clearing_method != 2 && $request->clearing_method != 4) {
                $rules['location_text'] = 'required';
                $messages = [
                    'location_text.required' => 'Location is required',
                ];
            }


            if ((int) $request->clearing_method === 2) {

                $existingMerchantId = $reward->participating_merchant_id ?? null;
                $existingLocations  = $reward->participatingLocations ?? collect();

                // -------------------------------
                // Participating merchant
                // -------------------------------
                if (
                    !$request->has('participating_merchant_locations') &&
                    !$request->filled('participating_merchant_id') &&
                    !$existingMerchantId
                ) {
                    $rules['participating_merchant_id'] =
                        'required|exists:participating_merchants,id';
                }

                // -------------------------------
                // Participating locations
                // -------------------------------
                if (
                    !$request->filled('participating_merchant_locations') &&
                    $existingLocations->isEmpty()
                ) {
                    $rules['participating_merchant_locations'] =
                        'required|array|min:1';
                }

                // -------------------------------
                // If locations sent → check selected
                // -------------------------------
                if ($request->has('participating_merchant_locations')) {

                    $hasSelected = false;

                    foreach ($request->participating_merchant_locations as $loc) {
                        if (!empty($loc['selected'])) {
                            $hasSelected = true;
                            break;
                        }
                    }

                    if (!$hasSelected) {
                        return response()->json([
                            'status' => 'error',
                            'errors' => [
                                'participating_merchant_locations' => [
                                    'Please select at least one merchant location.'
                                ]
                            ]
                        ], 422);
                    }
                }
            }

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
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/image'), $filename);
                $validated['voucher_image'] = $filename;
            }

            /* ===================================================
            | CREATE UPDATE REQUEST
            ===================================================*/
            $data = [
                'type'           => '2',
                'from_month'     => $validated['from_month'],
                'to_month'       => $validated['to_month'],
                'request_by'     => auth()->id(),
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
                'name'           => $validated['name'],
                'description'    => $validated['description'],
                'term_of_use'    => $validated['term_of_use'],
                'how_to_use'     => $validated['how_to_use'],
                'merchant_id'    => $validated['merchant_id'],
                'reward_type'    => 0,
                'voucher_validity'   => $validated['voucher_validity'] ?? null,
                'inventory_type'      => (int) ($request['inventory_type'] ?? null),
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

            if ( $request->clearing_method == 2 &&  !empty($request->participating_merchant_locations)) {

                // 1️⃣ Remove old mappings
                RewardParticipatingMerchantLocationUpdate::where('reward_id', $reward->id)->delete();

                foreach ($request->participating_merchant_locations as $locId => $locData) {

                    if (!isset($locData['selected'])) {
                        continue;
                    }

                    // 2️⃣ Get merchant ID from location itself
                    $merchantId = ParticipatingMerchantLocation::where('id', $locId)
                        ->value('participating_merchant_id');

                    if (!$merchantId) {
                        continue; // safety
                    }

                    // 3️⃣ Save correct mapping
                    RewardParticipatingMerchantLocationUpdate::create([
                        'reward_id'                 => $reward->id,
                        'participating_merchant_id' => $merchantId,
                        'location_id'               => $locId,
                        'is_selected'               => 1,
                    ]);
                }
            }

             if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                // delete old vouchers
                RewardVoucher::where('reward_id', $reward->id)->delete();

                // delete old CSV
                if ($reward->csvFile) {
                    $oldFile = public_path('uploads/csv/' . $reward->csvFile);
                    if (file_exists($oldFile)) @unlink($oldFile);
                }

                $file = $request->file('csvFile');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/csv'), $filename);

                $updateRequest->csvFile = $filename;
                $updateRequest->save();
           

                // SAFE XLSX/CSV READING
                $rows = Excel::toArray([], public_path('uploads/csv/'.$filename));

                foreach ($rows[0] as $row) {
                    $code = trim($row[0] ?? '');
                    if ($code === '' || strtolower($code) === 'code') continue;

                    RewardVoucher::create([
                        'type' => '1',
                        'reward_id' => $reward->id,
                        'code'      => $code,
                        'is_used'   => 0,
                    ]);
                }
            }



            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Update request sent for admin approval'
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
    
    public function getMerchantLocationsWithOutlets(Request $request)
    {
        $clubLocations = ClubLocation::where('status', 'Active')->get();

        $result = [];

        foreach ($clubLocations as $club) {

            $merchantLocations = ParticipatingMerchant::select('id', 'name')->get();

            if ($merchantLocations->count() > 0) {
                $result[] = [
                    'club_id' => $club->id,
                    'club_name' => $club->name,
                    'merchant_locations' => $merchantLocations
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

}
