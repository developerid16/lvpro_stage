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

class EvoucherController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.evoucher.";
        $permission_prefix    = $this->permission_prefix    = 'evoucher';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'E-Voucher: Digital Voucher',
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
            $final_data[$key]['is_draft'] = $row->is_draft == 1 ? 'Yes' : 'No';

            $final_data[$key]['status'] = $row->status;
            $methods = [
                0 => 'CSO Issuance',
                1 => 'Push by Member ID',
                2 => 'Push by Parameter',
                3 => 'Push by API SRP',
                4 => 'App/Web',
            ];

            
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



            $final_data[$key]['status'] = $status;
            $final_data[$key]['cso_method'] = $methods[$row->cso_method] ?? '-';

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

        $isDraft = $request->action === 'draft' ?? 0; 

        DB::beginTransaction();

        try {
            if ($isDraft) {
                $validated = $request->all();

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

                /* ---------------------------------------------------
                * FORMAT DATES
                * ---------------------------------------------------*/
                foreach (['publish', 'sales'] as $prefix) {
                    if ($request->{$prefix . '_start'}) {
                        $request[$prefix.'_start_date'] = date('Y-m-d', strtotime($request->{$prefix . '_start'}));
                        $request[$prefix.'_start_time'] = date('H:i:s', strtotime($request->{$prefix . '_start'}));
                    }

                    if ($request->{$prefix . '_end'}) {
                        $request[$prefix.'_end_date'] = date('Y-m-d', strtotime($request->{$prefix . '_end'}));
                        $request[$prefix.'_end_time'] = date('H:i:s', strtotime($request->{$prefix . '_end'}));
                    }
                }

                $locationTextId = CustomLocation::getOrCreate(
                    $request->location_text ?? ''
                );

                $reward = Reward::create([
                    'type'  => '1',
                    'days' => $request->input('days'),
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,

                    'voucher_image'  => $validated['voucher_image'] ?? '',
                    'voucher_detail_img' => $validated['voucher_detail_img'] ?? '',
                    'name'           => $request->name,
                    'description'    => $request->description,
                    'term_of_use'    => $request->term_of_use,
                    'how_to_use'     => $request->how_to_use,
                    'is_draft'       => 1,

                    'merchant_id'    => Reward::intOrZero($request, 'merchant_id'),
                    'reward_type'    => 0,
                    'cso_method'     => Reward::intOrZero($request, 'cso_method'),
                    'direct_utilization' => Reward::intOrZero($request, 'direct_utilization'),
                    'max_quantity'   => Reward::intOrZero($request, 'max_quantity'),

                    'publish_start_date' => $request->publish_start_date ?? null,
                    'publish_start_time' => $request->publish_start_time ?? null,
                    'publish_end_date'   => $request->publish_end_date ?? null,
                    'publish_end_time'   => $request->publish_end_time ?? null,

                    'sales_start_date'   => $request->sales_start_date ?? null,
                    'sales_start_time'   => $request->sales_start_time ?? null,
                    'sales_end_date'     => $request->sales_end_date ?? null,
                    'sales_end_time'     => $request->sales_end_time ?? null,

                    'voucher_validity' => $request->filled('voucher_validity') ? $request->voucher_validity : null,


                    'inventory_type'     => Reward::intOrZero($request, 'inventory_type'),
                    'inventory_qty'      => Reward::intOrZero($request, 'inventory_qty'),

                    'category_id'        => Reward::intOrZero($request, 'category_id'),

                    'friendly_url'       => $request->friendly_url,
                    'voucher_value'      => Reward::intOrZero($request, 'voucher_value'),
                    'voucher_set'        => Reward::intOrZero($request, 'voucher_set'),
                    'set_qty'            => Reward::intOrZero($request, 'set_qty'),

                    'clearing_method'    => Reward::intOrZero($request, 'clearing_method'),

                    'location_text'      => Reward::intOrZero($request, 'location_text'),
                    'participating_merchant_id' => Reward::intOrZero($request, 'participating_merchant_id'),

                    'hide_quantity'      => $request->boolean('hide_quantity'),
                    'low_stock_1'        => Reward::intOrZero($request, 'low_stock_1'),
                    'low_stock_2'        => Reward::intOrZero($request, 'low_stock_2'),
                    'suspend_deal'       => $request->boolean('suspend_deal'),
                    'suspend_voucher'    => $request->boolean('suspend_voucher'),
                    'is_featured' => $request->boolean('is_featured'),
                ]);


                 /* ---------------------------------------------------
                * SAVE PARTICIPATING LOCATIONS
                * ---------------------------------------------------*/
                if ($request->clearing_method == 2 && !empty($request->participating_merchant_locations)) {

                    foreach ($request->participating_merchant_locations as $locId => $locData) {

                        if (!isset($locData['selected'])) {
                            continue;
                        }

                        // Fetch merchant from location
                        $merchantId = ParticipatingMerchantLocation::where('id', $locId)
                            ->value('participating_merchant_id');

                        if (!$merchantId) {
                            continue;
                        }

                        ParticipatingLocations::create([
                            'reward_id'                 => $reward->id,
                            'participating_merchant_id' => $merchantId,
                            'location_id'               => $locId,
                            'is_selected'               => 1,
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
                            'type'      => '1',
                            'reward_id' => $reward->id,
                            'code'      => $code,
                            'is_used'   => 0
                        ]);
                    }
                }

                DB::commit();
                return response()->json(['status'=>'success','message'=>'Saved As Draft Successfully  And Sent For Approval Successfully']);
                
            }else{

                /* ---------------------------------------------------
                | BASE RULES
                --------------------------------------------------- */
                $rules = [
                    'voucher_image'    => 'required|image|mimes:png,jpg,jpeg|max:2048',
                    'voucher_detail_img' => 'required|image|mimes:png,jpg,jpeg|max:2048',
    
                    'cso_method'             => 'required',
                    'name'             => 'required|string|max:191',
                    'description'      => 'required|string',
                    'term_of_use'      => 'required|string',
                    'how_to_use'       => 'required|string',
    
                    'merchant_id'      => 'required|exists:merchants,id',
    
                    'publish_start'    => 'required|date',
                    'publish_end'      => 'required|date|after_or_equal:publish_start',
                    'sales_start'      => 'required|date',
                    'sales_end'        => 'required|date|after_or_equal:sales_start',
    
                    'friendly_url'     => 'nullable|string',
                    'direct_utilization'=> 'nullable|boolean',
    
                    'max_quantity'     => 'required|integer|min:1',
                    'voucher_validity' => 'required|date|after_or_equal:sales_end',
    
    
                    'category_id'      => 'nullable',
                    'inventory_type'   => 'required|in:0,1',
                    'voucher_value'    => 'required|numeric|min:0',
                    'voucher_set'      => 'required|integer|min:1',
                    'set_qty'          => 'required|numeric|min:1',
    
                    'clearing_method'  => 'required|in:0,1,2,3,4',
    
                    'low_stock_1'      => 'nullable|min:0',
                    'low_stock_2'      => 'nullable|min:0',
                ];
    
                $messages = [
                    'term_of_use.required' => 'Voucher T&C is required',
                    'voucher_detail_img.required' => 'Voucher Detail Image is required',
                    'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                    'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                    'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes',
                ];
    
                /* ---------------------------------------------------
                | INVENTORY RULES
                --------------------------------------------------- */
                if ((int) $request->inventory_type === 0) {
                    $rules['inventory_qty'] = 'required|integer|min:1';
                }
    
                if ((int) $request->inventory_type === 1) {
                    $rules['csvFile'] = ['required','file','mimes:csv,xlsx', new SingleCodeColumnFile(),];
                }
    
            
                /* ---------------------------------------------------
                | CLEARING METHOD RULES
                --------------------------------------------------- */

                if ((int) $request->clearing_method === 2) {

                    $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';

                    $rules['participating_merchant_locations'] = 'required_with:participating_merchant_id|array';
                
                    $messages['participating_merchant_locations.required_with'] = 'Participating merchant outlets is required.';
                }
                else{
                     $rules['location_text'] = 'required';
                    $messages['location_text.required'] = 'Location is required';

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

            /* ---------------------------------------------------
            * FORMAT DATES
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
            * CREATE REWARD (e-Voucher only)
            * ---------------------------------------------------*/
            $reward = Reward::create([
                'type'  => '1',
                'days'  => $request->input('days'),

                'start_time' => $request->start_time,
                'end_time'   => $request->end_time,

                'voucher_image'       => $validated['voucher_image'],
                'voucher_detail_img'  => $validated['voucher_detail_img'],
                'name'                => $validated['name'],
                'description'         => $validated['description'],
                'term_of_use'         => $validated['term_of_use'],
                'how_to_use'          => $validated['how_to_use'],

                'merchant_id'         => (int) ($validated['merchant_id'] ?? 0),
                'reward_type'         => 0,
                'cso_method'          => (int) ($request->cso_method ?? 0),
                'direct_utilization'  => (int) ($validated['direct_utilization'] ?? 0),
                'max_quantity'        => (int) ($validated['max_quantity'] ?? 0),

                'publish_start_date'  => $validated['publish_start_date'] ?? null,
                'publish_start_time'  => $validated['publish_start_time'] ?? null,
                'publish_end_date'    => $validated['publish_end_date'] ?? null,
                'publish_end_time'    => $validated['publish_end_time'] ?? null,

                'sales_start_date'    => $validated['sales_start_date'] ?? null,
                'sales_start_time'    => $validated['sales_start_time'] ?? null,
                'sales_end_date'      => $validated['sales_end_date'] ?? null,
                'sales_end_time'      => $validated['sales_end_time'] ?? null,

                'voucher_validity' => $request->filled('voucher_validity') ? $request->voucher_validity : null,
                'inventory_type'      => (int) ($validated['inventory_type'] ?? 0),
                'inventory_qty'       => (int) ($request->inventory_qty ?? 0),

                'friendly_url'        => $validated['friendly_url'] ?? null,
                'category_id'         => (int) ($request->filled('category_id') ? $request->category_id : 0),

                'voucher_value'       => (float) ($validated['voucher_value'] ?? 0),
                'voucher_set'         => (int) ($validated['voucher_set'] ?? 0),
                'set_qty'             => (int) ($validated['set_qty'] ?? 0),

                'clearing_method'     => (int) ($validated['clearing_method'] ?? 0),

                'location_text'       => (int) ($locationTextId ?? 0),
                'participating_merchant_id' => (int) ($request->participating_merchant_id ?? 0),

                'hide_quantity'       => $request->boolean('hide_quantity'),
                'low_stock_1'         => (int) ($validated['low_stock_1'] ?? 0),
                'low_stock_2'         => (int) ($validated['low_stock_2'] ?? 0),
                'suspend_deal'        => (int) ($validated['suspend_deal'] ?? 0),
                'suspend_voucher'     => (int) ($validated['suspend_voucher'] ?? 0),
                'is_featured' => $request->boolean('is_featured'),
                'is_draft'              => 2,
            ]);

            $updateRequest = RewardUpdateRequest::create([
                'reward_id'            => $reward->id,
                'request_by'           => auth()->id(),
                'status'               => 'pending',
                'type'                 => '1',

                'voucher_image'        => $validated['voucher_image'],
                'voucher_detail_img'   => $validated['voucher_detail_img'],
                'name'                 => $validated['name'],
                'days'                 => $request->input('days'),
                'start_time'           => $request->start_time,
                'end_time'             => $request->end_time,
                'description'          => $validated['description'],
                'term_of_use'          => $validated['term_of_use'],
                'how_to_use'           => $validated['how_to_use'],

                'merchant_id'          => $validated['merchant_id'],
                'reward_type'          => 0,
                'cso_method'     => Reward::intOrZero(request: $request, 'cso_method'),
                'max_quantity'         => $validated['max_quantity'],
                'direct_utilization'   => $validated['direct_utilization'] ?? 0,

                'publish_start_date'   => $validated['publish_start_date'] ?? null,
                'publish_start_time'   => $validated['publish_start_time'] ?? null,
                'publish_end_date'     => $validated['publish_end_date'] ?? null,
                'publish_end_time'     => $validated['publish_end_time'] ?? null,

                'sales_start_date'     => $validated['sales_start_date'] ?? null,
                'sales_start_time'     => $validated['sales_start_time'] ?? null,
                'sales_end_date'       => $validated['sales_end_date'] ?? null,
                'sales_end_time'       => $validated['sales_end_time'] ?? null,

                'voucher_validity'     => $validated['voucher_validity'] ?? null,
                'category_id'          => $validated['category_id'],
                'friendly_url'         => $validated['friendly_url'],
                'inventory_type'       => $validated['inventory_type'],
                'inventory_qty'        => $request->inventory_qty ?? 0,

                'voucher_value'        => $validated['voucher_value'],
                'voucher_set'          => $validated['voucher_set'],
                'set_qty'              => $validated['set_qty'],
                'clearing_method'      => $validated['clearing_method'],

                'location_text'        => $locationTextId,
                'participating_merchant_id' => $request->participating_merchant_id ?? 0,

                'hide_quantity'        => $request->boolean('hide_quantity'),
                'low_stock_1'          => $validated['low_stock_1'],
                'low_stock_2'          => $validated['low_stock_2'],
                'suspend_deal'         => $request->has('suspend_deal') ? 1 : 0,
                'suspend_voucher'      => $request->has('suspend_voucher') ? 1 : 0,
                'is_featured' => $request->boolean('is_featured'),
                'is_draft'             => 2,
            ]);

            /* ---------------------------------------------------
            * SAVE PARTICIPATING LOCATIONS
            * ---------------------------------------------------*/
            if ($request->clearing_method == 2 && !empty($request->participating_merchant_locations)) {

                foreach ($request->participating_merchant_locations as $locId => $locData) {

                    if (!isset($locData['selected'])) {
                        continue;
                    }

                    // Fetch merchant from location
                    $merchantId = ParticipatingMerchantLocation::where('id', $locId)
                        ->value('participating_merchant_id');

                    if (!$merchantId) {
                        continue;
                    }

                    ParticipatingLocations::create([
                        'reward_id'                 => $reward->id,
                        'participating_merchant_id' => $merchantId,
                        'location_id'               => $locId,
                        'is_selected'               => 1,
                    ]);
                    RewardParticipatingMerchantLocationUpdate::create([
                        'reward_id'                 => $reward->id,
                        'participating_merchant_id' => $merchantId,
                        'location_id'               => $locId,
                        'is_selected'               => 1,
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

                $updateRequest->csvFile = $filename;
                $updateRequest->save();

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
                        'type'      => '1',
                        'reward_id' => $reward->id,
                        'code'      => $code,
                        'is_used'   => 0
                    ]);
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
                return response()->json(['status' => 'success', 'message' => 'Reward Updated Successfully And Sent For Approval Successfully']);


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

                'low_stock_1'      => 'nullable|min:0',
                'low_stock_2'      => 'nullable|min:0',
            ];

            $messages = [
                'term_of_use.required' => 'Voucher T&C is required',
                'voucher_detail_img.required' => 'Voucher Detail Image is required',
                'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes',
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
            else{
                    $rules['location_text'] = 'required';
                $messages['location_text.required'] = 'Location is required';

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
            return response()->json(['status' => 'success', 'message' => 'Reward Updated Successfully And Sent For Approval Successfully']);

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
           
            RewardParticipatingMerchantLocationUpdate::where('reward_id', $reward->id)->delete();
            RewardUpdateRequest::where('reward_id', $reward->id)->delete();
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



    public function pushMemberVoucher(Request $request)
    {
        try {
            // ------------------------------------
            // VALIDATION
            // ------------------------------------
            $validator = Validator::make($request->all(), [
                'push_voucher'   => 'required',
                'reward_id'      => 'required|exists:rewards,id',
                'memberId'       => 'required|file',
                'redemption_start_date' => 'nullable|date',
                'redemption_end_date'   => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // ------------------------------------
            // FILE UPLOAD
            // ------------------------------------
            $fileName = null;

            if ($request->hasFile('memberId')) {
                $file = $request->file('memberId');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/push_voucher'), $fileName);
            }

            // ------------------------------------
            // MEMBER IDS (stored comma-separated)
            // ------------------------------------
            $memberIdsArray = array_filter(
                array_map('trim', explode(',', $request->push_voucher)),
                fn($id) => $id !== ""
            );

            $memberIds = implode(',', $memberIdsArray); // <-- FINAL STRING

            // ------------------------------------
            // SAVE SINGLE RECORD
            // ------------------------------------
            PushVoucherMember::create([
                'type' => '0',
                'reward_id' => $request->reward_id,
                'member_id' => $memberIds,   // stored comma-separated
                'file'      => $fileName,

                'redemption_start_date' => $request->redemption_start_date
                    ? date('Y-m-d', strtotime($request->redemption_start_date))
                    : null,

                'redemption_start_time' => $request->redemption_start_date
                    ? date('H:i:s', strtotime($request->redemption_start_date))
                    : null,

                'redemption_end_date' => $request->redemption_end_date
                    ? date('Y-m-d', strtotime($request->redemption_end_date))
                    : null,

                'redemption_end_time' => $request->redemption_end_date
                    ? date('H:i:s', strtotime($request->redemption_end_date))
                    : null,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Push voucher saved successfully.'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function pushParameterVoucher(Request $request)
    {
        try {

            // --------------------------
            // VALIDATION
            // --------------------------
            $validator = Validator::make($request->all(), [
                'voucher'            => 'required',
                'reward_id1'         => 'required|exists:rewards,id',

                'publish_channels'   => 'nullable|array',
                'card_types'         => 'nullable|array',
                'dependent_types'    => 'nullable|array',
                'marital_status'     => 'nullable|array',
                'gender'             => 'nullable|array',

                'age_mode'           => 'required',
                'age_from'           => 'nullable',
                'age_to'             => 'nullable',

                'redemption_start_date' => 'nullable|date',
                'redemption_end_date'   => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }


            // --------------------------
            // PREPARE FIELDS
            // --------------------------
            $publish_channels  = $request->publish_channels ? implode(',', $request->publish_channels) : null;
            $card_types        = $request->card_types ? implode(',', $request->card_types) : null;
            $dependent_types   = $request->dependent_types ? implode(',', $request->dependent_types) : null;
            $marital_status    = $request->marital_status ? implode(',', $request->marital_status) : null;
            $gender            = $request->gender ? implode(',', $request->gender) : null;


            // --------------------------
            // CREATE RECORD
            // --------------------------
            PushVoucherMember::create([
                'type'        => 1, // PARAMETER PUSH
                'member_id'     => $request->voucher,
                'reward_id'   => $request->reward_id1,

                'publish_channels' => $publish_channels,
                'card_types'       => $card_types,
                'dependent_types'  => $dependent_types,
                'marital_status'   => $marital_status,
                'gender'           => $gender,

                'age_mode' => $request->age_mode,
                'age_from' => $request->age_from,
                'age_to'   => $request->age_to,

                'redemption_start_date' => $request->redemption_start_date
                    ? date('Y-m-d', strtotime($request->redemption_start_date))
                    : null,

                'redemption_start_time' => $request->redemption_start_date
                    ? date('H:i:s', strtotime($request->redemption_start_date))
                    : null,

                'redemption_end_date' => $request->redemption_end_date
                    ? date('Y-m-d', strtotime($request->redemption_end_date))
                    : null,

                'redemption_end_time' => $request->redemption_end_date
                    ? date('H:i:s', strtotime($request->redemption_end_date))
                    : null,
            ]);


            return response()->json([
                'status'  => 'success',
                'message' => 'Voucher pushed successfully by parameters.'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
   
}
