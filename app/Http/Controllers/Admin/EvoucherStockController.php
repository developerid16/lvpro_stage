<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomLocation;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\Evoucher;
use App\Models\ParticipatingMerchantLocation;
use App\Models\PushVoucherMember;
use App\Models\Reward;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\RewardVoucher;
use App\Models\UserPurchasedReward;
use App\Models\UserWalletVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct
use App\Rules\SingleCodeColumnFile;
use Carbon\Carbon;

class EvoucherStockController extends Controller
{
    
    public function __construct()
    {

        $this->view_file_path = "admin.evoucher-stock.";
        $permission_prefix = $this->permission_prefix = 'evoucher-stock';

       $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'E-Voucher Management Listing',
            'reward_base_url'   => url('admin/evoucher-stock'),
            'module_base_url'   => url('admin/evoucher'),
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
        $this->layout_data['memberReward'] = Reward::where('cso_method',1)->get();
        $this->layout_data['parameterReward'] = Reward::where('cso_method',2)->get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '1');
        
        if (auth()->user()->role != 1) {
            $query->where('added_by', auth()->id());
            }
            
        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';

            $final_data[$key]['quantity']       = number_format($row->inventory_qty);
            $final_data[$key]['balance']       = number_format($row->inventory_qty - $row->purchased_qty);
            
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);
            
            
            $redeemed = UserWalletVoucher::where('reward_id', $row->id)
            ->where('status', 'used')
            ->count();
            $issued = UserWalletVoucher::where('reward_id', $row->id)
            ->where('status', 'issued')
            ->count();
            $final_data[$key]['issuance']       = number_format($issued);

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

            $start = $row->publish_start_date;
            $end   = $row->publish_end_date;

            $startDate = $start ? \Carbon\Carbon::parse($start) : null;
            $endDate   = $end ? \Carbon\Carbon::parse($end) : null;

            // block zero-date (-0001-11-30)
            $isValidStart = $startDate && $startDate->year > 0;
            $isValidEnd   = $endDate && $endDate->year > 0;

            if ($isValidStart && $isValidEnd) {
                $duration =
                    $startDate->format(config('safra.date-only')) .
                    ' to ' .
                    $endDate->format(config('safra.date-only'));
            } elseif ($isValidStart) {
                $duration = $startDate->format(config('safra.date-only'));
            } else {
                $duration = '-';
            }

            $final_data[$key]['duration'] = $duration;

            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            
            if (Auth::user()->can($this->permission_prefix . '-stock-adjustment')) {

                $current_qty = $row->inventory_qty - $row->purchased_qty;

                $action .= "<a href='javascript:void(0)' 
                                class='stock-adjustment'  
                                data-id='{$row->id}'
                                data-name='{$row->name}'
                                data-inventory='{$row->inventory_qty}'
                                data-purchased='{$current_qty}'
                                data-hide='{$row->hide_catalogue}'
                                data-hide-time='{$row->hide_cat_time}'
                                title='Stock Adjustment'>
                                <i class='mdi mdi-warehouse text-info action-icon font-size-18'></i>
                            </a>";
            }



            if (Auth::user()->can($this->permission_prefix . '-hide-catalogue')) {
                $action .= ' <div class="form-check form-switch m-0"> <input class="form-check-input hide-catalogue-switch"
                type="checkbox" data-id="'.$row->id.'" '.($row->hide_catalogue ? 'checked' : '').'  title="Hide From Catalogue"> </div>';
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
        $reward = Reward::with('participatingLocations')->findOrFail($id);

        $reward->voucher_validity =  ($reward->voucher_validity == '0000-00-00') ? '' : $reward->voucher_validity;
        $this->layout_data['data'] = $reward;
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();

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
            'html'   => $html,
            'participatingLocations' => $locations
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $isDraft = $request->action === 'draft' ? 1 : 0;
        $reward = Reward::findOrFail(id: $id);

        DB::beginTransaction();

        try {
            if ($isDraft) {
                $validated = $request->all();

                if ($request->hasFile('voucher_image')) {

                    $uploadPath = public_path('uploads/image');
                    if (!is_dir($uploadPath)) mkdir($uploadPath, 0775, true);

                    // delete old
                    if ($reward->voucher_image) {
                        $oldFile = $uploadPath . '/' . $reward->voucher_image;
                        if (file_exists($oldFile)) @unlink($oldFile);
                    }

                    $file = $request->file('voucher_image');
                    $filename = time().'_'.$file->getClientOriginalName();
                    $file->move($uploadPath, $filename);

                    $validated['voucher_image'] = $filename;
                }

                if ($request->hasFile('voucher_detail_img')) {

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
                    if (!empty($reward->voucher_detail_img)) {
                        $oldFile = $uploadPath . '/' . $reward->voucher_detail_img;

                        if (file_exists($oldFile)) {
                            @unlink($oldFile); // <-- the @ prevents warning if permission denied
                        }
                    }

                    // Upload new image
                    $file = $request->file('voucher_detail_img');
                    $filename = time() . '_' . $file->getClientOriginalName();

                    $file->move($uploadPath, $filename);

                    $validated['voucher_detail_img'] = $filename;
                }


                /* ---------------------------------------------------
                * 5) DATE FORMATTING
                * ---------------------------------------------------*/
                foreach (['publish', 'sales'] as $prefix) {

                    if ($request->{$prefix . '_start'}) {
                        $validated[$prefix.'_start_date'] = date('Y-m-d', strtotime($request->{$prefix . '_start'}));
                        $validated[$prefix.'_start_time'] = date('H:i:s', strtotime($request->{$prefix . '_start'}));
                    }

                    if ($request->{$prefix . '_end'}) {
                        $validated[$prefix.'_end_date'] = date('Y-m-d', strtotime($request->{$prefix . '_end'}));
                        $validated[$prefix.'_end_time'] = date('H:i:s', strtotime($request->{$prefix . '_end'}));
                    }
                }
                $locationTextId = CustomLocation::getOrCreate(
                    $request->location_text ?? ''
                );

                /* ---------------------------------------------------
                * 6) UPDATE REWARD
                * ---------------------------------------------------*/
                $reward->update([
                    'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                    'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,

                    'name'               => $validated['name'],
                    'days'               => $request->input('days'), // ARRAY ONLY
                    'start_time' => $request['start_time'],
                    'end_time' => $request['end_time'],

                    'description'        => $validated['description'],
                    'term_of_use'        => $validated['term_of_use'],
                    'how_to_use'         => $validated['how_to_use'],

                    'merchant_id'        => $validated['merchant_id'] ?? 0,
                    'reward_type'        => 0,
                    'type'               => '1',
                    'cso_method'         => $request->cso_method ?? $reward->cso_method,
                    'max_quantity'       => $validated['max_quantity'],
                    'direct_utilization'       => $validated['direct_utilization'] ?? 0,

                    'publish_start_date' => $validated['publish_start_date'] ?? $reward->publish_start_date,
                    'publish_start_time' => $validated['publish_start_time'] ?? $reward->publish_start_time,
                    'publish_end_date'   => $validated['publish_end_date'] ?? $reward->publish_end_date,
                    'publish_end_time'   => $validated['publish_end_time'] ?? $reward->publish_end_time,

                    'sales_start_date'   => $validated['sales_start_date'] ?? $reward->sales_start_date,
                    'sales_start_time'   => $validated['sales_start_time'] ?? $reward->sales_start_time,
                    'sales_end_date'     => $validated['sales_end_date'] ?? $reward->sales_end_date,
                    'sales_end_time'     => $validated['sales_end_time'] ?? $reward->sales_end_time,

                    'voucher_validity' => $request->filled('voucher_validity')
                    ? $request->voucher_validity
                    : null,

                    'category_id'            => $request->filled('category_id') ? $request->category_id : 0,

                    'friendly_url'     => $validated['friendly_url'],
                    'inventory_type'     => $validated['inventory_type'],
                    'inventory_qty'      => $request['inventory_qty'] ?? null,

                    'voucher_value'      => $validated['voucher_value'],
                    'voucher_set'        => $validated['voucher_set'],
                    'set_qty'            => $validated['set_qty'],
                    'clearing_method'    => $validated['clearing_method'],

                    'location_text'      => $locationTextId,
                    'participating_merchant_id' => $request->participating_merchant_id ?? 0,

                    'hide_quantity'      => $request->hide_quantity ? 1 : 0,
                    'low_stock_1'        => $validated['low_stock_1'],
                    'low_stock_2'        => $validated['low_stock_2'],
                    'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                    'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,
                    'is_featured' => $request->boolean('is_featured'),

                ]);


                /* ---------------------------------------------------
                * 7) UPDATE PARTICIPATING LOCATIONS
                * ---------------------------------------------------*/
                if ( $request->clearing_method == 2 &&  !empty($request->participating_merchant_locations)) {

                    // 1️⃣ Remove old mappings
                    ParticipatingLocations::where('reward_id', $reward->id)->delete();

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
                        ParticipatingLocations::create([
                            'reward_id'                 => $reward->id,
                            'participating_merchant_id' => $merchantId,
                            'location_id'               => $locId,
                            'is_selected'               => 1,
                        ]);
                    }
                }


                /* ---------------------------------------------------
                * 8) INVENTORY (merchant → upload file)
                * ---------------------------------------------------*/
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

                    $reward->csvFile = $filename;
                    $reward->save();

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

                /* ---------------------------------------------------
                * SUCCESS
                * ---------------------------------------------------*/
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Reward Updated Successfully']);


            }

            /* ---------------------------------------------------
            * 1) FETCH REWARD
            * ---------------------------------------------------*/

           /* ---------------------------------------------------
            | UPDATE VALIDATION RULES
            --------------------------------------------------- */
            $rules = [
                'voucher_image'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'cso_method'             => 'required',

                'name'             => 'required|string|max:191',
                'description'      => 'required|string',
                'term_of_use'      => 'required|string',
                'how_to_use'       => 'required|string',

                'merchant_id'      => 'required|exists:merchants,id',

                'friendly_url'     => 'nullable|string',
                'category_id'      => 'nullable',

                'publish_start'    => 'required|date',
                'publish_end'      => 'required|date|after_or_equal:publish_start',

                'sales_start'      => 'required|date',
                'sales_end'        => 'required|date|after_or_equal:sales_start',

                'direct_utilization'=> 'nullable|boolean',

                'max_quantity'     => 'required|integer|min:1',
                'voucher_validity' => 'required|date|after_or_equal:sales_end',


                'inventory_type'   => 'required|in:0,1',
                'voucher_value'    => 'required|numeric|min:0',
                'voucher_set'      => 'required|integer|min:1',
                'set_qty'          => 'required|numeric|min:1',
                'clearing_method'  => 'required|in:0,1,2,3,4',

                'low_stock_1'      => 'required|integer|min:0',
                'low_stock_2'      => 'required|integer|min:0',
            ];

            $messages = [
                'term_of_use.required' => 'Voucher T&C is required',
            ];

            /* --------------------------------------------
            | INVENTORY RULES
            -------------------------------------------- */
            if ((int) $request->inventory_type === 0) {
                $rules['inventory_qty'] = 'required|integer|min:1';
            }

            if ($request->inventory_type == 1) {
                if(!$reward->csvFile){
                    $rules['csvFile'] = ['required','file','mimes:csv,xlsx', new SingleCodeColumnFile(),];
                }
            }

            /* --------------------------------------------
            | CLEARING METHOD RULES
            -------------------------------------------- */
            if ($request->clearing_method != 2 && $request->clearing_method != 4) {
                $rules['location_text'] = 'required';
                $messages = [
                    'location_text.required' => 'Location is required',
                ];
            }

            if ((int) $request->clearing_method === 2) {

                $existingMerchantId = $reward->participating_merchant_id ?? null;
                $existingLocations  = $reward->participatingLocations ?? collect();

                $locationsInput = $request->input('participating_merchant_locations', []);

                $hasLocations = false;

                if (!empty($locationsInput)) {
                    foreach ($locationsInput as $loc) {
                        if (!empty($loc['selected'])) {
                            $hasLocations = true;
                            break;
                        }
                    }
                }

                // ------------------------------------
                // Merchant required ONLY if:
                // no locations selected AND
                // no existing merchant
                // ------------------------------------
                if (!$hasLocations && !$existingMerchantId) {
                    $rules['participating_merchant_id'] =
                        'required|exists:participating_merchants,id';
                }

                // ------------------------------------
                // Locations required if:
                // no locations selected AND
                // no existing locations
                // ------------------------------------
                if (!$hasLocations && $existingLocations->isEmpty()) {
                    $rules['participating_merchant_locations'] =
                        'required|array|min:1';
                }

                // ------------------------------------
                // Extra safety: if locations sent but none selected
                // ------------------------------------
                if ($request->has('participating_merchant_locations') && !$hasLocations) {
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


            /* ---------------------------------------------------
            | VALIDATE
            --------------------------------------------------- */
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();


            /* ---------------------------------------------------
            * 4) IMAGE UPLOAD
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {

                $uploadPath = public_path('uploads/image');
                if (!is_dir($uploadPath)) mkdir($uploadPath, 0775, true);

                // delete old
                if ($reward->voucher_image) {
                    $oldFile = $uploadPath . '/' . $reward->voucher_image;
                    if (file_exists($oldFile)) @unlink($oldFile);
                }

                $file = $request->file('voucher_image');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move($uploadPath, $filename);

                $validated['voucher_image'] = $filename;
            }

            if ($request->hasFile('voucher_detail_img')) {

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
                if (!empty($reward->voucher_detail_img)) {
                    $oldFile = $uploadPath . '/' . $reward->voucher_detail_img;

                    if (file_exists($oldFile)) {
                        @unlink($oldFile); // <-- the @ prevents warning if permission denied
                    }
                }

                // Upload new image
                $file = $request->file('voucher_detail_img');
                $filename = time() . '_' . $file->getClientOriginalName();

                $file->move($uploadPath, $filename);

                $validated['voucher_detail_img'] = $filename;
            }


            /* ---------------------------------------------------
            * 5) DATE FORMATTING
            * ---------------------------------------------------*/
            foreach (['publish', 'sales'] as $prefix) {

                if ($request->{$prefix . '_start'}) {
                    $validated[$prefix.'_start_date'] = date('Y-m-d', strtotime($request->{$prefix . '_start'}));
                    $validated[$prefix.'_start_time'] = date('H:i:s', strtotime($request->{$prefix . '_start'}));
                }

                if ($request->{$prefix . '_end'}) {
                    $validated[$prefix.'_end_date'] = date('Y-m-d', strtotime($request->{$prefix . '_end'}));
                    $validated[$prefix.'_end_time'] = date('H:i:s', strtotime($request->{$prefix . '_end'}));
                }
            }


            $locationTextId = CustomLocation::getOrCreate(
                $request->location_text ?? ''
            ); 

            $data = [
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
                'request_by'         => auth()->id(),
                'reward_id'          => $reward->id,
                'status'             => 'pending',
                'name'               => $validated['name'],
                'days'               => $request->input('days'),
                'start_time'         => $request['start_time'],
                'end_time'           => $request['end_time'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],
                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => 0,
                'type'               => '1',
                'cso_method'         => $request->cso_method ?? $reward->cso_method,
                'direct_utilization' => $validated['direct_utilization'] ?? 0,
                
                'publish_start_date' => $validated['publish_start_date'] ?? $reward->publish_start_date,
                'publish_start_time' => $validated['publish_start_time'] ?? $reward->publish_start_time,
                'publish_end_date'   => $validated['publish_end_date'] ?? $reward->publish_end_date,
                'publish_end_time'   => $validated['publish_end_time'] ?? $reward->publish_end_time,

                'sales_start_date'   => $validated['sales_start_date'] ?? $reward->sales_start_date,
                'sales_start_time'   => $validated['sales_start_time'] ?? $reward->sales_start_time,
                'sales_end_date'     => $validated['sales_end_date'] ?? $reward->sales_end_date,
                'sales_end_time'     => $validated['sales_end_time'] ?? $reward->sales_end_time,
                
                'voucher_validity'   => $validated['voucher_validity'] ?? null,
                'friendly_url'       => $validated['friendly_url'],
                'inventory_type'     => $validated['inventory_type'],
                
                'max_quantity'       =>(int) ($validated['max_quantity']),
                'category_id'        =>(int) ($validated['category_id']),
                'inventory_qty'      => (int) ($request['inventory_qty'] ?? null),
                'voucher_value'      =>(int) ($validated['voucher_value']),
                'voucher_set'        =>(int) ($validated['voucher_set']),
                'set_qty'            =>(int) ($validated['set_qty']),
                'clearing_method'    =>(int) ($validated['clearing_method']),
                'location_text'      => $locationTextId,
                'participating_merchant_id' => $request->participating_merchant_id ?? 0,
                
                'hide_quantity'   => $request->hide_quantity ? 1 : 0,
                'low_stock_1'     => $validated['low_stock_1'],
                'low_stock_2'     => $validated['low_stock_2'],
                'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,
                'is_featured' => $request->boolean('is_featured'),
            ];

            $updateRequest = RewardUpdateRequest::updateOrCreate(
                [
                    'reward_id' => $reward->id,
                    'type'    => '1',
                    'status'    => 'pending',
                ],
                $data
            );

                /* ---------------------------------------------------
            * 7) UPDATE PARTICIPATING LOCATIONS
            * ---------------------------------------------------*/
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

            
            // else{

            //     /* ---------------------------------------------------
            //     * 6) UPDATE REWARD
            //     * ---------------------------------------------------*/
            //     $reward->update([
            //         'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
            //         'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
            //         'name'               => $validated['name'],
            //         'days'               => $request->input('days'), // ARRAY ONLY
            //         'start_time' => $request['start_time'],
            //         'end_time' => $request['end_time'],
            //         'description'        => $validated['description'],
            //         'term_of_use'        => $validated['term_of_use'],
            //         'how_to_use'         => $validated['how_to_use'],
    
            //         'merchant_id'        => $validated['merchant_id'],
            //         'reward_type'        => 0,
            //         'type'               => '1',
            //         'cso_method'         => $request->cso_method ?? $reward->cso_method,
            //         'max_quantity'       => $validated['max_quantity'],
            //         'direct_utilization'       => $validated['direct_utilization'] ?? 0,
    
            //         'publish_start_date' => $validated['publish_start_date'] ?? $reward->publish_start_date,
            //         'publish_start_time' => $validated['publish_start_time'] ?? $reward->publish_start_time,
            //         'publish_end_date'   => $validated['publish_end_date'] ?? $reward->publish_end_date,
            //         'publish_end_time'   => $validated['publish_end_time'] ?? $reward->publish_end_time,
    
            //         'sales_start_date'   => $validated['sales_start_date'] ?? $reward->sales_start_date,
            //         'sales_start_time'   => $validated['sales_start_time'] ?? $reward->sales_start_time,
            //         'sales_end_date'     => $validated['sales_end_date'] ?? $reward->sales_end_date,
            //         'sales_end_time'     => $validated['sales_end_time'] ?? $reward->sales_end_time,
    
            //         'voucher_validity'   => $validated['voucher_validity'] ?? null, 
    
            //         'category_id'            => $request->filled('category_id') ? $request->category_id : 0,

            //         'friendly_url'     => $validated['friendly_url'],
            //         'inventory_type'     => $validated['inventory_type'],
            //         'inventory_qty'      => $request['inventory_qty'] ?? null,
    
            //         'voucher_value'      => $validated['voucher_value'],
            //         'voucher_set'        => $validated['voucher_set'],
            //         'set_qty'            => $validated['set_qty'],
            //         'clearing_method'    => $validated['clearing_method'],
    
            //         'location_text'      => $locationTextId,
            //         'participating_merchant_id' => $request->participating_merchant_id ?? 0,
    
            //         'hide_quantity'      => $request->hide_quantity ? 1 : 0,
            //         'low_stock_1'        => $validated['low_stock_1'],
            //         'low_stock_2'        => $validated['low_stock_2'],
            //         'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
            //         'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,
            //     ]);

            //     /* ---------------------------------------------------
            //     * 7) UPDATE PARTICIPATING LOCATIONS
            //     * ---------------------------------------------------*/
            //     if ( $request->clearing_method == 2 &&  !empty($request->participating_merchant_locations)) {
    
            //         // 1️⃣ Remove old mappings
            //         ParticipatingLocations::where('reward_id', $reward->id)->delete();
    
            //         foreach ($request->participating_merchant_locations as $locId => $locData) {
    
            //             if (!isset($locData['selected'])) {
            //                 continue;
            //             }
    
            //             // 2️⃣ Get merchant ID from location itself
            //             $merchantId = ParticipatingMerchantLocation::where('id', $locId)
            //                 ->value('participating_merchant_id');
    
            //             if (!$merchantId) {
            //                 continue; // safety
            //             }
    
            //             // 3️⃣ Save correct mapping
            //             ParticipatingLocations::create([
            //                 'reward_id'                 => $reward->id,
            //                 'participating_merchant_id' => $merchantId,
            //                 'location_id'               => $locId,
            //                 'is_selected'               => 1,
            //             ]);
            //         }
            //     }

            // }                



            /* ---------------------------------------------------
            * 8) INVENTORY (merchant → upload file)
            * ---------------------------------------------------*/
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

            /* ---------------------------------------------------
            * SUCCESS
            * ---------------------------------------------------*/
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Reward Updated Successfully']);

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
            if ($reward->voucher_detail_img && file_exists(public_path('uploads/image/' . $reward->voucher_detail_img))) {
                unlink(public_path('uploads/image/' . $reward->voucher_detail_img));
            }
            if ($reward->csvFile && file_exists(public_path('uploads/csv/' . $reward->csvFile))) {
                unlink(public_path('uploads/csv/' . $reward->csvFile));
            }
           
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


    public function getDates($id)
    {
        $reward = Reward::findOrFail($id);
    
        return response()->json([
            'publish_start' => $reward->publish_start_date
                ? Carbon::parse($reward->publish_start_date)
                    ->format(config('safra.date-format'))
                : null,
    
            'publish_end' => $reward->publish_end_date
                ? Carbon::parse($reward->publish_end_date)
                    ->format(config('safra.date-format'))
                : null,
        ]);
    }

    public function toggleHideCatalogue(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:rewards,id',
            'status' => 'required|boolean'
        ]);

        $reward = Reward::findOrFail($request->id);

        $reward->update([
            'hide_catalogue' => $request->status,
            'hide_cat_time'  => now()
        ]);


        return response()->json([
            'status' => true,
            'message'    => 'Hide catalogue successfully updated'
        ]);
    }


    public function stockAdjustment(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer|exists:rewards,id',
            'type' => 'required|in:plus,minus',
            'qty'  => 'required|integer|min:1'
        ], [
            'qty.required' => 'Quantity is required',
            'qty.integer'  => 'Quantity must be an integer value',
            'qty.min'      => 'Quantity must be at least 1'
        ]);

        $reward = Reward::findOrFail($request->id);

        $updateRequest = RewardUpdateRequest::where('reward_id', $reward->id)->where('status', 'pending')->where('type', '1')->first();

        $baseQty = $updateRequest ? $updateRequest->inventory_qty : $reward->inventory_qty;

        // =============================
        // MINUS LOGIC WITH HIDE CHECK
        // =============================
        $newQty  = (int) $request->qty;
        $baseQty = (int) $baseQty;

        $isReducing = $newQty < $baseQty;

        if ($isReducing) {

            if ($reward->hide_catalogue == 1) {

                if (!$reward->hide_cat_time) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Hide time not found'
                    ], 400);
                }

                $allowTime = Carbon::parse($reward->hide_cat_time)->addMinutes(60);

                if (now()->lt($allowTime)) {

                    $remainingMinutes = now()->diffInMinutes($allowTime);

                    return response()->json([
                        'status' => false,
                        'message' => "You can reduce stock after {$remainingMinutes} minutes"
                    ], 400);
                }
            }

            if ($newQty < 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Not enough stock'
                ], 400);
            }
        }


        // =============================
        // UPDATE OR CREATE REQUEST
        // =============================
        if ($updateRequest) {

            $updateRequest->inventory_qty = $newQty;
            $updateRequest->save();

        } else {

            $data = $reward->toArray();
            unset($data['id']);

            $data['reward_id']     = $reward->id;
            $data['request_by']    = auth()->id();
            $data['status']        = 'pending';
            $data['type']          = '1';
            $data['inventory_qty'] = $newQty;

            RewardUpdateRequest::create($data);
        }

        return response()->json([
            'status' => true,
            'message' => 'Stock adjustment successfully'
        ]);
    }

}
