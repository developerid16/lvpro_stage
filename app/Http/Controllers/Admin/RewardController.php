<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Http\Controllers\Controller;
use App\Models\API\GetSRPMerchandiseItemList;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\ParticipatingMerchantLocation;
use App\Models\Reward;
use App\Models\RewardLocation;
use App\Models\RewardTierRate;
use App\Models\RewardVoucher;
use App\Models\Tier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct
use App\Rules\SingleCodeColumnFile;
use App\Models\CustomLocation;
use App\Models\Fabs;
use App\Models\RewardLocationUpdate;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\UserWalletVoucher;
use Illuminate\Support\Collection;
class RewardController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.reward.";
        $permission_prefix    = $this->permission_prefix    = 't&d-reward';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Treats & Deals',
            'module_base_url'   => url('admin/reward'),
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

        $type = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $this->layout_data['type'] = $type;
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->orderBy('name', 'ASC')->get();
        $this->layout_data['category'] = Category::orderBy('name', 'ASC')->get();
        $this->layout_data['fabs'] = Fabs::where('status','Active')->orderBy('name', 'ASC')->get();

        $this->layout_data['getSRPMerchandiseItemList'] = GetSRPMerchandiseItemList::orderBy('item_name', 'ASC')->get();

        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->orderBy('name', 'ASC')->get();
        $this->layout_data['tiers'] = Tier::where('status', 'Active')->orderBy('tier_name', 'ASC')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }
    
    public function datatable(Request $request)
    {
        $type  = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $query = Reward::where('type', '0');
        if (auth()->user()->role != 1) { // not Super Admin
            $query->where('added_by', auth()->id());
        }

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $total_quantity = 0;
            if ($row->reward_type == 1) {
                // physical
                $total_quantity = RewardLocation::where('reward_id', $row->id)->sum('inventory_qty');
            } else {
                // digital
                $total_quantity = $row->inventory_qty;
            }
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';
            $final_data[$key]['amount'] = number_format($row->usual_price, 2);

            $final_data[$key]['quantity'] = max(0, (int) $total_quantity);

            $purchased = UserWalletVoucher::where('reward_id', $row->id)->where('reward_status', 'purchased')->count();

            $final_data[$key]['purchased'] = max(0, $purchased);

            $final_data[$key]['balance'] = max(0, $total_quantity - $purchased);

            $redeemed = UserWalletVoucher::where('reward_id', $row->id)->where('status', 'used')->count();

            $final_data[$key]['redeemed'] = max(0, $redeemed);

            $duration = $row->created_at->format(config('safra.date-format'));
            $final_data[$key]['image'] = imagePreviewHtml("uploads/image/{$row->voucher_image}");
           
            $start = $row->publish_start_date;
            $end   = $row->publish_end_date;

            $startDate = $start ? \Carbon\Carbon::parse($start) : null;
            $endDate   = $end ? \Carbon\Carbon::parse($end) : null;

            // block zero-date (-0001-11-30)
            $isValidStart = $startDate && $startDate->year > 0;
            $isValidEnd   = $endDate && $endDate->year > 0;

            if ($isValidStart && $isValidEnd) {
                $duration = $startDate->format(config('safra.date-only')) . ' to ' . $endDate->format(config('safra.date-only'));
            } elseif ($isValidStart) {
                $duration = $startDate->format(config('safra.date-only'));
            } else {
                $duration = '-';
            }

            $final_data[$key]['duration'] = $duration;
            $final_data[$key]['status'] = $duration;

            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));

            $final_data[$key]['is_draft'] = $row->is_draft == 1 ? 'Yes' : 'No';
            $final_data[$key]['status'] = $row->status;

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

                $latestRequest = RewardUpdateRequest::where('reward_id', $row->id)->latest('id')->first();

                $hasApproved = $latestRequest && $latestRequest->status === 'approve';
                $hasPending  = $latestRequest && $latestRequest->status === 'pending';
                $hasRejected = $latestRequest && $latestRequest->status === 'rejected';

                /*
                FINAL PRIORITY
                1. Expired
                2. Rejected
                3. Pending approval
                4. Approved (ONLY if start date is future)
                5. Active
                */

                // 1. EXPIRED
                if (
                    ($row->voucher_validity && Carbon::parse($row->voucher_validity)->lt($now)) ||
                    ($salesEnd && $now->gt($salesEnd))
                ) {
                    $status = 'expired';
                }

                // 2. REJECTED
                elseif ($hasRejected) {
                    $status = 'rejected';
                }

                // 3. PENDING APPROVAL
                elseif ($hasPending) {
                    $status = 'pending approval';
                }

                // 4. APPROVED (only if sales not started yet)
                elseif ($hasApproved && $salesStart && $now->lt($salesStart)) {
                    $status = 'approved';
                }

                // 5. ACTIVE
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

            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {

                if ($status == 'pending approval') {

                    $action .= "<a href='javascript:void(0)' 
                                    class='' 
                                    style='cursor:not-allowed;color:#b6b8c4 !important;' 
                                    title='Editable only after approval'>
                                    <i class='mdi mdi-pencil action-icon font-size-18'></i>
                                </a>";

                } else {

                    $action .= "<a href='javascript:void(0)' 
                                    class='edit' 
                                    data-id='$row->id'
                                    title='Edit'>
                                    <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                                </a>";
                }
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
        $isDraft = $request->action === 'draft'; 
        $tiers = Tier::where('status', operator: 'Active')->get();

        DB::beginTransaction();
        try {
            if ($isDraft) {
                $validated = $request->all();

                if ($request->hasFile('voucher_image')) {

                    $path = public_path('uploads/image');

                    // Create directory if not exists
                    if (!is_dir($path)) {
                        mkdir($path, 0775, true);
                    }

                    $file = $request->file('voucher_image');
                    $filename = generateHashFileName($file);
                    $file->move($path, $filename);

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
                    $file->move($path, $filename);

                    $validated['voucher_detail_img'] = $filename;
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


                $locationTextId = CustomLocation::getOrCreate(
                    $request->location_text ?? ''
                );

                /* ---------------------------------------------------
                * 7) CREATE REWARD
                * ---------------------------------------------------*/
                $maxQty = $request->reward_type == 0  ? $request->max_quantity_digital  : $request->max_quantity_physical;

                $reward = Reward::create([
                    'type'               => '0',
                    'cso_method'          => (int) ($request['cso_method'] ?? 0),
                    'is_draft'           => 1,
                    'voucher_image'      => $validated['voucher_image'] ?? '',
                    'voucher_detail_img' => $validated['voucher_detail_img'] ?? '',
                    'name'               => $validated['name'],
                    'description'        => $validated['description'],
                    'term_of_use'        => $validated['term_of_use'],
                    'how_to_use'         => $validated['how_to_use'],

                    'merchant_id'        => $validated['merchant_id'] ?? 0,
                    'reward_type' => isset($validated['reward_type']) && $validated['reward_type'] !== '' ? (int) $validated['reward_type'] : 0,
                    'usual_price' => isset($validated['usual_price']) && $validated['usual_price'] !== '' ? (float) $validated['usual_price'] : 0,
                    'max_quantity' => isset($maxQty) && $maxQty !== '' ? (int) $maxQty : 0,

                    'publish_start_date' => $validated['publish_start_date'] ?? null,
                    'publish_start_time' => $validated['publish_start_time']  ?? null,
                    'publish_end_date'   => $validated['publish_end_date'] ?? null,
                    'publish_end_time'   => $validated['publish_end_time'] ?? null,

                    'sales_start_date'   => $validated['sales_start_date'] ?? null,
                    'sales_start_time'   => $validated['sales_start_time'] ?? null,
                    'sales_end_date'     => $validated['sales_end_date'] ?? null,
                    'sales_end_time'     => $validated['sales_end_time'] ?? null,

                    // physical-only fields
                    'hide_quantity'            => $request->hide_quantity ?? 0,
                   'low_stock_1' => $request->filled('low_stock_1') ? (int) $request->low_stock_1 : 0,
                    'low_stock_2' => $request->filled('low_stock_2') ? (int) $request->low_stock_2 : 0,


                    'friendly_url'           => $request->friendly_url,

                    'category_id'            => $request->filled('category_id') ? $request->category_id : null,
                    'club_classification_id' => $request->filled('club_classification_id') ? $request->club_classification_id : null,
                    'fabs_category_id'       => $request->filled('fabs_category_id') ? $request->fabs_category_id : null,
                    'smc_classification_id'  => $request->filled('smc_classification_id') ? $request->smc_classification_id : null,


                    'ax_item_code'           => $request->ax_item_code ?? null,

                    'publish_independent'    => $request->publish_independent ?? 0,
                    'publish_inhouse'        => $request->publish_inhouse ?? 0,

                    'send_reminder'          => $request->send_reminder ?? 0,

                    'where_use'                  => $request->where_use,
                    'expiry_type' => $request->expiry_type,
                    'voucher_validity' => $request->expiry_type === 'fixed' ? $request->voucher_validity : null,
                    'validity_month' => $request->expiry_type === 'validity'  ? $request->validity_month : null,
                
                    'inventory_type'            => $request->filled('inventory_type') ? (int) $request->inventory_type : 0,
                    'inventory_qty'             => $request->filled('inventory_qty') ? (int) $request->inventory_qty : 0,
                    'voucher_value'             => $request->filled('voucher_value') ? (float) $request->voucher_value : 0,
                    'voucher_set'               => $request->filled('voucher_set') ? (int) $request->voucher_set : 0,
                    'set_qty'                   => $request->filled('set_qty') ? (int) $request->set_qty : 0,
                    'clearing_method'           => $request->filled('clearing_method') ? (int) $request->clearing_method : 0,
                    'participating_merchant_id' => $request->filled('participating_merchant_id') ? (int) $request->participating_merchant_id : 0,
                    'location_text'             => $request->filled('location_text') ? $locationTextId : '',
                    'max_order'                 => $request->filled('max_order') ? (int) $request->max_order : 0,

                    'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                    'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,  
                    'is_featured' => $request->boolean('is_featured'),         
                ]);


                /* ---------------------------------------------------
                * 8) SAVE TIER RATES
                * ---------------------------------------------------*/
                foreach ($tiers as $tier) {

                    RewardTierRate::create([
                        'reward_id' => $reward->id,
                        'tier_id'   => $tier->id,
                        'price' => $request->filled("tier_{$tier->id}")
                            ? (float) $request->input("tier_{$tier->id}")
                            : null,
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
                            'total_qty' => $locData['inventory_qty'] ?? 0,
                        ]);
                    }
                }


                /* ---------------------------------------------------
                * DIGITAL: SAVE PARTICIPATING MERCHANT LOCATIONS
                * ---------------------------------------------------*/
                if ( $request->reward_type == 0 && $request->clearing_method == 2 && !empty($request->participating_merchant_locations)) {

                    $merchantIds = $request->participating_merchant_id ?? [];

                    // normalize merchant IDs
                    if (!is_array($merchantIds)) {
                        $merchantIds = [$merchantIds];
                    }

                    foreach ($merchantIds as $merchantId) {

                        foreach ($request->participating_merchant_locations as $locId => $locData) {

                            if (!isset($locData['selected'])) {
                                continue;
                            }

                            ParticipatingLocations::create([
                                'reward_id'                 => $reward->id,
                                'participating_merchant_id' => $merchantId, // ✅ single ID
                                'location_id'               => $locId,
                                'is_selected'               => 1,
                            ]);
                        }
                    }
                }
            

                if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                    $file = $request->file('csvFile');
                    $filename = generateHashFileName($file);
                    $file->move(public_path('uploads/csv'), $filename);


                    $filePath = public_path('uploads/csv/'.$filename);

                    // READ XLSX OR CSV
                    $rows = Excel::toArray([], $filePath);

                    $count = 0;

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

                        $count++; // ✅ count valid codes
                    }
                    $reward->csvFile = $filename;
                    // ✅ store count in inventory_qty
                    $reward->inventory_qty = $count;
                    $reward->save();
                }


                 DB::commit();
                return response()->json(['status'=>'success','message'=>'Saved As Draft Successfully']);
            }else{

            
                $rules = [
                    'voucher_image'      => 'required|image|mimes:png,jpg,jpeg|max:2048',
                    'voucher_detail_img' => 'required|image|mimes:png,jpg,jpeg|max:2048',
                    'name'               => 'required|string|max:191',
                    'description'        => 'required|string',
                    'term_of_use'        => 'required|string',
                    'how_to_use'         => 'nullable|string',
                    'merchant_id'        => 'required|exists:merchants,id',
                    'reward_type'        => 'required|in:0,1',
                    'expiry_type' => 'required|in:fixed,validity,no_expiry',

                    'voucher_validity' => [
                        'required_if:expiry_type,fixed',
                        'nullable',
                        'date',
                        function ($attribute, $value, $fail) use ($request) {

                            if ($request->expiry_type === 'fixed') {

                                $salesEndDate = Carbon::parse($request->sales_end)->startOfDay();
                                $validityDate = Carbon::parse($value)->startOfDay();

                                if ($validityDate->lt($salesEndDate)) {
                                    $fail('Voucher expiry date must be after or equal to Redemption end date.');
                                }

                            }
                        }
                    ],

                    'validity_month' => 'required_if:expiry_type,validity|nullable|integer|min:1|max:24',
        
                    'usual_price' => ['required','numeric','min:0','regex:/^\d+(\.\d{1,2})?$/'],
                   
                    'publish_start'    => 'required|date',
                    'publish_end'      => 'required|date|after:publish_start',
                    'sales_start'      => [
                        'required',
                        'date',
                        'after_or_equal:publish_start',
                        'before_or_equal:publish_end',
                    ],
                    'sales_end'        => [
                        'required',
                        'date',
                        'after_or_equal:sales_start',
                        'before_or_equal:publish_end',
                    ],
    
                    'low_stock_1'      => 'nullable|min:0',
                    'low_stock_2'      => 'nullable|min:0',             
                    'send_reminder'      => 'nullable|boolean',
                    'ax_item_code'      => 'required',

                    'max_quantity_physical' => 'required_if:reward_type,1|integer|min:1',
                    'max_quantity_digital' => 'required_if:reward_type,0|integer|min:1',
                    'inventory_type'       => 'required_if:reward_type,0|in:0,1',
                    'voucher_value'        => 'required_if:reward_type,0|numeric|min:1',
                    'clearing_method'      => 'required_if:reward_type,0|in:0,1,2,3,4',
                    'friendly_url' => 'nullable|regex:/^(?!https?:\/\/)[a-zA-Z0-9-]+$/',
                ];
    
                $messages = [
                     // Voucher name
                    'name.required' => 'Voucher name is required.',
                    'name.string'   => 'Voucher name must be valid text.',
                    'name.max'      => 'Voucher name may not be greater than 191 characters.',

                    // Publish Start
                    'publish_start.required' => 'Publish start date & time is required.',
                    'publish_start.date'     => 'Publish start date & time must be a valid date and time.',

                    // Publish End
                    'publish_end.required'        => 'Publish end date & time is required.',
                    'publish_end.date'            => 'Publish end date & time must be a valid date and time.',
                    'publish_end.after'           => 'Publish end date & time must be after Publish start date & time.',

                    // Sales Start
                    'sales_start.required'          => 'Sales start date & time is required.',
                    'sales_start.date'              => 'Sales start date & time must be a valid date.',
                    'sales_start.after_or_equal'    => 'Sales start date & time must be on or after Publish start date & time.',
                    'sales_start.before_or_equal'   => 'Sales start date & time must be on or before Publish end date & time.',

                    // Sales End
                    'sales_end.required'            => 'Sales end date & time is required.',
                    'sales_end.date'                => 'Sales end date & time must be a valid date.',
                    'sales_end.after_or_equal'      => 'Sales end date & time must be on or after Sales start date & time.',
                    'sales_end.before_or_equal'     => 'Sales end date & time must be on or before Publish end date & time.',

                    // Voucher Validity Date
                   'expiry_type.required' => 'Please select voucher expiry type.',

                    'voucher_validity.required_if' => 'Voucher expiry date is required when Fixed Expiry Date is selected.',
                    'voucher_validity.date' => 'Voucher expiry date must be a valid date.',

                    'validity_month.required_if' => 'Validity period is required when Validity Period is selected.',
                    'validity_month.integer' => 'Validity period must be a number.',
                    'validity_month.min' => 'Validity period must be at least 1 month.',
                    'validity_month.max' => 'Validity period may not be greater than 24 months.',
                       
                    'set_qty.required' => 'Voucher set quantity is required.',
                    'set_qty.integer'  => 'Voucher set quantity must be a valid number.',
                    'set_qty.min'      => 'Voucher set quantity must be at least 1.',
                    
                    'term_of_use.required' => 'Voucher T&C is required',
                    'voucher_detail_img.required' => 'Voucher Detail Image is required',
                    'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                    'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                    'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes',
                    'max_quantity_physical.required_if' => 'Max quantity is required',
                    'max_quantity_digital.required_if' => 'Max quantity is required',
                    'inventory_type.required_if'       => 'Internal/External is required',
                    'voucher_value.required_if'        => 'Voucher value is required',
                    'clearing_method.required_if'      => 'Clearing method is required',
                    'friendly_url.regex' => 'Only letters allowed. URLs, numbers, spaces, and special characters like "https://" are not allowed.',
                ];
    
                /* ---------------- TIER RULES ---------------- */
    
                foreach ($tiers as $tier) {
                    $rules["tier_{$tier->id}"] = 'nullable|numeric|min:0';
                    $messages["tier_{$tier->id}.nullable"] = "{$tier->tier_name} price is required";
                }
            
                if ((int) $request->inventory_type === 1) {
                    $rules['csvFile'] = ['required','file','mimes:csv,xlsx', new SingleCodeColumnFile(),];
                }
                if ((int) $request->reward_type == 0) {
                    $rules['voucher_set']         = 'required|numeric|min:1';   
                    $rules['set_qty']         = 'required|numeric|min:1';
                    $messages['set_qty.required'] = 'Voucher set quantity is required.';
                    $messages['set_qty.numeric']  = 'Voucher set quantity must be a valid number.';
                    $messages['set_qty.min']      = 'Voucher set quantity must be at least 1.';
                }
                
                $validator = Validator::make($request->all(), $rules, $messages);

                /* ---------------- LOCATION VALIDATION ---------------- */


                if ((int) $request->reward_type === 1) {

                    $rules['max_quantity_physical'] = 'required|integer|min:1';
                    $rules['locations'] = 'required|array|min:1';

                    $hasSelected = false;

                    foreach ($request->locations ?? [] as $locId => $locData) {

                        if (!empty($locData['selected'])) {

                            $hasSelected = true;

                            $rules["locations.$locId.inventory_qty"] = 'required|integer|min:1';

                            $messages["locations.$locId.inventory_qty.required"] = 'This location inventory quantity is required.';
                            $messages["locations.$locId.inventory_qty.integer"]  = 'This location inventory quantity must be a number.';
                            $messages["locations.$locId.inventory_qty.min"]      = 'This location inventory quantity must be at least 1.';
                        }
                    }
                }

                $validator = Validator::make($request->all(), $rules, $messages);

                /* AFTER VALIDATION CHECK */
                $validator->after(function ($validator) use ($request) {

                    if ((int) $request->reward_type === 1) {

                        $hasSelected = false;

                        foreach ($request->locations ?? [] as $locData) {
                            if (!empty($locData['selected'])) {
                                $hasSelected = true;
                                break;
                            }
                        }

                        if (!$hasSelected) {
                            $validator->errors()->add(
                                'locations',
                                'Please select at least one location.'
                            );
                        }
                    }
                });

                /* FINAL CHECK */
                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                
    
                /* ---------------- DIGITAL ---------------- */
                if ($request->reward_type == '0') {            
    
                     if ((int) $request->clearing_method === 2) {

                        $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';

                        $rules['participating_merchant_locations'] = 'required_with:participating_merchant_id|array';
                    
                        $messages['participating_merchant_locations.required_with'] = 'Participating merchant outlets is required.';
                    }
                    else{
                        $rules['location_text'] = 'required';
                        $messages['location_text.required'] = 'Location is required';

                    }
                    if ((int) $request->inventory_type === 0) {
                        $rules['inventory_qty'] = 'required|integer|min:1';
                    }
                }


                foreach ($tiers as $tier) {
                    $price = $request->input("tier_{$tier->id}");
                    if ($price > $request->usual_price) {
                        $validator->errors()->add(
                            "tier_{$tier->id}",
                            "{$tier->tier_name} price cannot be greater than Usual Price"
                        );
                    }
                }

                $validator = Validator::make($request->all(), $rules, $messages);

    
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
                    $filename = generateHashFileName($file);
                    $file->move($path, $filename);
    
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
                    $file->move($path, $filename);
    
                    $validated['voucher_detail_img'] = $filename;
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
    
    
                $locationTextId = CustomLocation::getOrCreate(
                    $request->location_text ?? ''
                );
    
                /* ---------------------------------------------------
                * 7) CREATE REWARD
                * ---------------------------------------------------*/
                $maxQty = $request->reward_type == 0  ? $request->max_quantity_digital  : $request->max_quantity_physical;
    
                $reward = Reward::create([
                    'type'               => '0',
                    'status'    => 'pending',
                    'cso_method'          => (int) ($request['cso_method'] ?? 0),
                    'voucher_image'      => $validated['voucher_image'],
                    'voucher_detail_img' => $validated['voucher_detail_img'],
                    'name'               => $validated['name'],
                    'description'        => $validated['description'],
                    'term_of_use'        => $validated['term_of_use'],
                    'how_to_use'         => $validated['how_to_use'],
    
                    'merchant_id'        => $validated['merchant_id'] ?? 0,
                    'reward_type' => $request->filled('reward_type') ? (int) $request->reward_type : 0,
                    'usual_price' => isset($validated['usual_price']) && $validated['usual_price'] !== '' ? (float) $validated['usual_price']  : 0,
                    'max_quantity' => isset($maxQty) && $maxQty !== '' ? (int) $maxQty : 0,
    
                    'publish_start_date' => $validated['publish_start_date'] ?? '',
                    'publish_start_time' => $validated['publish_start_time']  ?? '',
                    'publish_end_date'   => $validated['publish_end_date'] ?? '',
                    'publish_end_time'   => $validated['publish_end_time'] ?? '',
    
                    'sales_start_date'   => $validated['sales_start_date'] ?? '',
                    'sales_start_time'   => $validated['sales_start_time'] ?? '',
                    'sales_end_date'     => $validated['sales_end_date'] ?? '',
                    'sales_end_time'     => $validated['sales_end_time'] ?? '',
    
                    // physical-only fields
                    'hide_quantity'            => $request->hide_quantity ?? 0,
                    'low_stock_1'            => $request->filled($request->low_stock_1) ? $request->low_stock_1 :  0,
                    'low_stock_2'            => $request->filled($request->low_stock_2) ? $request->low_stock_2 : 0,
    
                    'friendly_url'           => $request->friendly_url,
    
                    'category_id'            => $request->filled('category_id') ? $request->category_id : null,
                    'club_classification_id' => $request->filled('club_classification_id') ? $request->club_classification_id : null,
                    'fabs_category_id'       => $request->filled('fabs_category_id') ? $request->fabs_category_id : null,
                    'smc_classification_id'  => $request->filled('smc_classification_id') ? $request->smc_classification_id : null,
                    'ax_item_code'           => $request->ax_item_code ?? null,
    
                    'publish_independent'    => $request->publish_independent ?? 0,
                    'publish_inhouse'        => $request->publish_inhouse ?? 0,
    
                    'send_reminder'          => $request->send_reminder ?? 0,
    
                    'expiry_type' => $request->expiry_type,
                    'voucher_validity' => $request->expiry_type === 'fixed' ? $request->voucher_validity : null,
                    'validity_month' => $request->expiry_type === 'validity'  ? $request->validity_month : null,

                    'where_use'                 => $request->filled('where_use') ? $request->where_use : null,
                    'inventory_type'            => $request->filled('inventory_type') ? $request->inventory_type : 0,
                    'inventory_qty'             => $request->filled('inventory_qty') ? $request->inventory_qty : 0,
                    'voucher_value'             => $request->filled('voucher_value') ? $request->voucher_value : 0,
                    'voucher_set'               => $request->filled('voucher_set') ? $request->voucher_set : 0,
                    'set_qty'                   => $request->filled('set_qty') ? $request->set_qty : 0,
                    'clearing_method'           => $request->filled('clearing_method') ? $request->clearing_method : null,
                    'participating_merchant_id' => $request->filled('participating_merchant_id') ? $request->participating_merchant_id : 0,
                    'location_text'             => $request->filled('location_text') ? $locationTextId : '',
                    'max_order'                 => $request->filled('max_order') ? $request->max_order : 0,
                    'suspend_deal'              => $request->filled('suspend_deal') ? 1 : 0,
                    'suspend_voucher'           => $request->filled('suspend_voucher') ? 1 : 0,
                    'is_featured' => $request->boolean('is_featured'),    
                    'is_draft'             => 2,            
                ]);
    
                $updateRequest = RewardUpdateRequest::create([
                    
                    'reward_id' => $reward->id,
                    'status'    => 'pending',
                    'type'      => '0',            
                    'cso_method'          => (int) ($request['cso_method'] ?? 0),

                    'request_by'          => auth()->id(),
                    'voucher_image'       => $validated['voucher_image'] ?? $reward->voucher_image,
                    'voucher_detail_img'  => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
                    'name'                => $validated['name'],
                    'description'         => $validated['description'],
                    'term_of_use'         => $validated['term_of_use'],
                    'how_to_use'          => $validated['how_to_use'],
    
                    'merchant_id'         => $validated['merchant_id'] ?? 0,
                    'reward_type' => isset($validated['reward_type']) && $validated['reward_type'] !== ''
                        ? (int) $validated['reward_type'] : 0,
    
                    'usual_price' => isset($validated['usual_price']) && $validated['usual_price'] !== ''
                        ? (float) $validated['usual_price'] : 0,
    
                    'max_quantity' => isset($maxQty) && $maxQty !== ''? (int) $maxQty : 0,
    
                    'publish_start_date'  => $validated['publish_start_date'] ?? '',
                    'publish_start_time'  => $validated['publish_start_time'] ?? '',
                    'publish_end_date'    => $validated['publish_end_date'] ?? '',
                    'publish_end_time'    => $validated['publish_end_time'] ?? '',
    
                    'sales_start_date'    => $validated['sales_start_date'] ?? '',
                    'sales_start_time'    => $validated['sales_start_time'] ?? '',
                    'sales_end_date'      => $validated['sales_end_date'] ?? '',
                    'sales_end_time'      => $validated['sales_end_time'] ?? '',
    
                    'hide_quantity'       => $request->hide_quantity,
                    'low_stock_1'         => $request->low_stock_1,
                    'low_stock_2'         => $request->low_stock_2,
                    'friendly_url'        => $request->friendly_url,
    
                    'category_id'            => $request->category_id ?? null,
                    'club_classification_id' => $request->club_classification_id ?? null,
                    'fabs_category_id'       => $request->fabs_category_id ?? null,
                    'smc_classification_id'  => $request->smc_classification_id ?? null,
                    'ax_item_code'           => $request->ax_item_code ?? null,
    
                    'publish_independent' => $request->publish_independent ?? 0,
                    'publish_inhouse'     => $request->publish_inhouse ?? 0,
                    'send_reminder'       => $request->send_reminder ?? 0,
    
                    'where_use'           => $request->where_use,               
                    'suspend_deal'        => $request->has('suspend_deal') ? 1 : 0,
                    'suspend_voucher'     => $request->has('suspend_voucher') ? 1 : 0,
                    'is_featured' => $request->boolean('is_featured'),
                     'expiry_type' => $request->expiry_type,
                    'voucher_validity' => $request->expiry_type === 'fixed' ? $request->voucher_validity : null,
                    'validity_month' => $request->expiry_type === 'validity'  ? $request->validity_month : null,
                    'inventory_type'            => $request->filled('inventory_type') ? (int) $request->inventory_type : 0,
                    'inventory_qty'             => $request->filled('inventory_qty') ? (int) $request->inventory_qty : 0,
                    'voucher_value'             => $request->filled('voucher_value') ? (float) $request->voucher_value : 0,
                    'voucher_set'               => $request->filled('voucher_set') ? (int) $request->voucher_set : 0,
                    'set_qty'                   => $request->filled('set_qty') ? (int) $request->set_qty : 0,
                    'clearing_method'           => $request->filled('clearing_method') ? (int) $request->clearing_method : 0,
                    'participating_merchant_id' => $request->filled('participating_merchant_id') ? (int) $request->participating_merchant_id : 0,
                    'location_text'             => $request->filled('location_text') ? $locationTextId : '',
                    'max_order'                 => $request->filled('max_order') ? (int) $request->max_order : 0,
                    'is_draft'             => 2,
                ]);
    
    
                /* ---------------------------------------------------
                * 8) SAVE TIER RATES
                * ---------------------------------------------------*/
                foreach ($tiers as $tier) {
    
                    RewardTierRate::create([
                        'reward_id' => $reward->id,
                        'tier_id'   => $tier->id,
                        'price'     => $request->input("tier_{$tier->id}") ?? null,
                    ]);
                }
    
    
                /* ---------------------------------------------------
                * 9) SAVE LOCATION DATA (PHYSICAL ONLY)
                * ---------------------------------------------------*/
                if ($request->reward_type == '1' && $request->has('locations')) {
    
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
                            'total_qty' => $locData['inventory_qty'] ?? 0,
                        ]);
                        RewardLocationUpdate::create([
                            'reward_id'     => $reward->id,
                            'merchant_id'   => $validated['merchant_id'],
                            'location_id'   => $locId,
                            'is_selected'   => 1,  // always 1 since only selected stored
                            'inventory_qty' => $locData['inventory_qty'] ?? 0,
                            'total_qty' => $locData['inventory_qty'] ?? 0,
                        ]);
                    }
                }
    
    
                /* ---------------------------------------------------
                * DIGITAL: SAVE PARTICIPATING MERCHANT LOCATIONS
                * ---------------------------------------------------*/
                if ( $request->reward_type == 0 && $request->clearing_method == 2 && !empty($request->participating_merchant_locations)) {
    
                    $merchantIds = $request->participating_merchant_id ?? [];
    
                    // normalize merchant IDs
                    if (!is_array($merchantIds)) {
                        $merchantIds = [$merchantIds];
                    }
    
                    foreach ($merchantIds as $merchantId) {
    
                        foreach ($request->participating_merchant_locations as $locId => $locData) {
    
                            if (!isset($locData['selected'])) {
                                continue;
                            }
    
                            ParticipatingLocations::create([
                                'reward_id'                 => $reward->id,
                                'participating_merchant_id' => $merchantId, // ✅ single ID
                                'location_id'               => $locId,
                                'is_selected'               => 1,
                            ]);
                            RewardParticipatingMerchantLocationUpdate::create([
                                'reward_id'                 => $reward->id,
                                'participating_merchant_id' => $merchantId, // ✅ single ID
                                'location_id'               => $locId,
                                'is_selected'               => 1,
                            ]);
                        }
                    }
                }
                
    
                if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {
    
                    $file = $request->file('csvFile');
                    $filename = generateHashFileName($file);
                    $file->move(public_path('uploads/csv'), $filename);
    
                    $filePath = public_path('uploads/csv/'.$filename);
    
                    // READ XLSX OR CSV
                    $rows = Excel::toArray([], $filePath);
    
                    $count = 0;
    
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
    
                        $count++; // ✅ count valid codes
                    }
    
                    // ✅ store count in inventory_qty
                    $reward->csvFile = $filename;
                    $reward->inventory_qty = $count;
                    $reward->save();
                    $updateRequest->inventory_qty = $count;
                    $updateRequest->csvFile = $filename;
                    $updateRequest->save();
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
        $reward->voucher_validity =  ($reward->voucher_validity == '0000-00-00') ? '' : $reward->voucher_validity;

        $this->layout_data['data'] = $reward;
        $this->layout_data['location_text'] = null;

        if (!empty($reward->location_text)) {
            $this->layout_data['location_text'] = CustomLocation::where('id', $reward->location_text)
                ->value('name');
        }
        $this->layout_data['fabs'] = Fabs::where('status','Active')->orderBy('name', 'ASC')->get();
        $this->layout_data['getSRPMerchandiseItemList'] = GetSRPMerchandiseItemList::orderBy('item_name', 'ASC')->get();

        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->orderBy('name', 'ASC')->get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->orderBy('name', 'ASC')->get();

        $this->layout_data['tiers'] = Tier::where('status', 'Active')->orderBy('tier_name', 'ASC')->get();
        $this->layout_data['category'] = Category::orderBy('name', 'ASC')->get();
        // 👉 Build simple array: [location_id => inventory_qty]
        $this->layout_data['savedLocations'] = $reward ? $reward->rewardLocations->pluck('inventory_qty','location_id')  : [];
        $locationIds = $reward->participatingLocations->pluck('location_id')->unique()->values();

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
            'savedLocations' => $this->layout_data['savedLocations'],
            'participatingLocations' => $locations
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $isDraft = $request->action === 'draft'; 
        $tiers = Tier::where('status', operator: 'Active')->get();
        $reward = Reward::findOrFail($id);

        DB::beginTransaction();

        try {
            if ($isDraft) {
                $validated = $request->all();

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
                    
                    $filename = generateHashFileName($file);

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
                    $filename = generateHashFileName($file);
                
                    $file->move($uploadPath, $filename);

                    $validated['voucher_detail_img'] = $filename;
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



                $maxQty = $request->reward_type == 0 ? $request->max_quantity_digital : $request->max_quantity_physical;

                $locationTextId = CustomLocation::getOrCreate(
                    $request->location_text ?? ''
                );

                $reward->update([
                    'type'               => '0',
                    'cso_method'          => (int) ($request['cso_method'] ?? 0),
                    'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                    'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
                    'name'               => $validated['name'],
                    'description'        => $validated['description'],
                    'term_of_use'        => $validated['term_of_use'],
                    'how_to_use'         => $validated['how_to_use'],

                    'merchant_id'        => $validated['merchant_id'],
                    'reward_type' => $request->filled('reward_type') ? (int) $request->reward_type : 0,
                    'usual_price' => isset($validated['usual_price']) && $validated['usual_price'] !== '' ? (float) $validated['usual_price'] : 0,
                    'max_quantity' => isset($maxQty) && $maxQty !== '' ? (int) $maxQty : 0,

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

                    'ax_item_code'           => $request->ax_item_code ?? null,
                    'category_id'            => $request->filled('category_id') ? $request->category_id : null,
                    'club_classification_id' => $request->filled('club_classification_id') ? $request->club_classification_id : null,
                    'fabs_category_id'       => $request->filled('fabs_category_id') ? $request->fabs_category_id : null,
                    'smc_classification_id'  => $request->filled('smc_classification_id') ? $request->smc_classification_id : null,

                    'publish_independent'    => $request->publish_independent ?? 0,
                    'publish_inhouse'        => $request->publish_inhouse ?? 0,
                    'send_reminder'          => $request->send_reminder ?? 0,

                    // Digital
                    'expiry_type' => $request->expiry_type,
                    'voucher_validity' => $request->expiry_type === 'fixed' ? $request->voucher_validity : null,
                    'validity_month' => $request->expiry_type === 'validity'  ? $request->validity_month : null,
                    'where_use'                  => $request->where_use,
                    'inventory_type'            => $request->inventory_type ?? 0,
                    'inventory_qty'             => $request->inventory_qty ?? 0,
                    'voucher_value'             => $request->voucher_value ?? 0,
                    'voucher_set'               => $request->voucher_set ?? 0,
                    'set_qty'                    => $request->set_qty ?? 0,
                    'clearing_method'           => $request->clearing_method,
                    'participating_merchant_id' =>  $request->participating_merchant_id ?? 0,
                    'location_text'             => $locationTextId ?? '',
                    'max_order'                 => $request->max_order,
                    'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                    'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,
                    'is_featured' => $request->has('is_featured') ? 1 : 0,
                ]);


                /* ---------------------------------------------------
                * 6) UPDATE TIER RATES
                * ---------------------------------------------------*/
                RewardTierRate::where('reward_id', $reward->id)->delete();

                foreach ($tiers as $tier) {

                    $price = $request->input("tier_{$tier->id}");

                    // Optional safety: skip empty values
                    if ($price === null || $price === '') {
                        continue;
                    }

                    RewardTierRate::create([
                        'reward_id' => $reward->id,
                        'tier_id'   => $tier->id,
                        'price'     => $price,
                    ]);
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
                            'total_qty' => $locData['inventory_qty'] ?? 0,
                        ]);
                    }
                }

                /* ----------------------------------
                * DIGITAL → UPDATE PARTICIPATING MERCHANT OUTLETS
                * ---------------------------------- */
                if ($request->reward_type == 0 && $request->clearing_method == 2 && !empty($request->participating_merchant_locations) ) {

                    // Remove old mappings
                    ParticipatingLocations::where('reward_id', $reward->id)->delete();

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
                * DIGITAL → INVENTORY TYPE SWITCH (Merchant → Non-Merchant)
                * ---------------------------------------------------*/
                if ($request->inventory_type == 0 && $request->csvFile) {

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
        

                if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                    $file = $request->file('csvFile');
                    // $filename = time().'_'.$file->getClientOriginalName();
                    $filename = generateHashFileName($file);
                    $file->move(public_path('uploads/csv'), $filename);

                    $reward->csvFile = $filename;
                    $reward->save();

                    $filePath = public_path('uploads/csv/'.$filename);

                    // READ XLSX OR CSV
                    $rows = Excel::toArray([], $filePath);

                    $count = 0;

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

                        $count++; // ✅ count valid codes
                    }

                    // ✅ store count in inventory_qty
                    $reward->inventory_qty = $count;
                    $reward->save();
                }


                /* ---------------------------------------------------
                * SUCCESS
                * ---------------------------------------------------*/
                DB::commit();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Reward Updated Successfully'
                ]);
    
            }else{

    
            /* ---------------------------------------------------
            | 1) BASE RULES
            |---------------------------------------------------*/

            $rewardType    = (int) $request->input('reward_type');
            $inventoryType = (int) $request->input('inventory_type');
            $clearingMethod = (int) $request->input('clearing_method');

            $rules = [
                'voucher_image'      => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',

                'name'        => 'required|string|max:191',
                'description' => 'required|string',
                'term_of_use' => 'required|string',
                'how_to_use'  => 'nullable|string',

                'merchant_id' => 'required|exists:merchants,id',
                'reward_type' => 'required|in:0,1',
               
                'expiry_type' => 'required|in:fixed,validity,no_expiry',

                'voucher_validity' => [
                    'required_if:expiry_type,fixed',
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {

                        if ($request->expiry_type === 'fixed') {

                            $salesEndDate = \Carbon\Carbon::parse($request->sales_end)->startOfDay();
                            $validityDate = \Carbon\Carbon::parse($value)->startOfDay();

                            if ($validityDate->lt($salesEndDate)) {
                                $fail('Voucher expiry date must be after or equal to Redemption end date.');
                            }

                        }
                    }
                ],

                'validity_month' => 'required_if:expiry_type,validity|nullable|integer|min:1|max:24',
                'usual_price' => ['required','numeric','min:0','regex:/^\d+(\.\d{1,2})?$/'],                
                // 'publish_start'    => 'required|date',
                // 'publish_end'      => 'required|date|after_or_equal:publish_start',
                // 'sales_start'   => 'required|date|after_or_equal:publish_start',
                // 'sales_end'        => 'required|date|after_or_equal:sales_start',

                'publish_start' => 'required|date',
                'publish_end'   => 'required|date|after:publish_start',
                'sales_start'   => [
                    'required',
                    'date',
                    'after_or_equal:publish_start',
                    'before_or_equal:publish_end',
                ],
                'sales_end'     => [
                    'required',
                    'date',
                    'after_or_equal:sales_start',
                    'before_or_equal:publish_end',
                ],

                'low_stock_1' => 'nullable|integer|min:0',
                'low_stock_2' => 'nullable|integer|min:0',
                'ax_item_code'      => 'required',
                'friendly_url' => 'nullable|regex:/^(?!https?:\/\/)[a-zA-Z0-9-]+$/',
            ];

            $messages = [
                // Voucher name
                'name.required' => 'Voucher name is required.',
                'name.string'   => 'Voucher name must be valid text.',
                'name.max'      => 'Voucher name may not be greater than 191 characters.',

                //  // Publish Start
                // 'publish_start.required' => 'Publish Start Date & Time is required.',
                // 'publish_start.date'     => 'Publish Start Date must be a valid date and time.',

                // // Publish End
                // 'publish_end.required'        => 'Publish End Date & Time is required.',
                // 'publish_end.date'            => 'Publish End Date must be a valid date and time.',
                // 'publish_end.after_or_equal'  => 'Publish End Date must be equal to or after Publish Start Date.',


                // // Sales start date & time
                // 'sales_start.required' => 'Sales start date & time is required.',
                // 'sales_start.date'     => 'Sales start date & time must be a valid date.',
                // 'sales_start.after_or_equal' => 'Sales start date & time must be after or equal to Publish Start Date.',

                // // Sales end date & time
                // 'sales_end.required' => 'Sales end date & time is required.',
                // 'sales_end.date'     => 'Sales end date & time must be a valid date.',
                // 'sales_end.after_or_equal' => 'Sales end date & time must be after or equal to Sales start date & time.',

                // Publish Start
                'publish_start.required' => 'Publish start date & time is required.',
                'publish_start.date'     => 'Publish start date & time must be a valid date and time.',

                // Publish End
                'publish_end.required'        => 'Publish end date & time is required.',
                'publish_end.date'            => 'Publish end date & time must be a valid date and time.',
                'publish_end.after'           => 'Publish end date & time must be after Publish start date & time.',

                // Sales Start
                'sales_start.required'          => 'Sales start date & time is required.',
                'sales_start.date'              => 'Sales start date & time must be a valid date.',
                'sales_start.after_or_equal'    => 'Sales start date & time must be on or after Publish start date & time.',
                'sales_start.before_or_equal'   => 'Sales start date & time must be on or before Publish end date & time.',

                // Sales End
                'sales_end.required'            => 'Sales end date & time is required.',
                'sales_end.date'                => 'Sales end date & time must be a valid date.',
                'sales_end.after_or_equal'      => 'Sales end date & time must be on or after Sales start date & time.',
                'sales_end.before_or_equal'     => 'Sales end date & time must be on or before Publish end date & time.',

                 // Voucher Validity Date
                'expiry_type.required' => 'Please select voucher expiry type.',

                'voucher_validity.required_if' => 'Voucher expiry date is required when Fixed Expiry Date is selected.',
                'voucher_validity.date' => 'Voucher expiry date must be a valid date.',

                'validity_month.required_if' => 'Validity period is required when Validity Period is selected.',
                'validity_month.integer' => 'Validity period must be a number.',
                'validity_month.min' => 'Validity period must be at least 1 month.',
                'validity_month.max' => 'Validity period may not be greater than 24 months.',
                    
                'set_qty.required' => 'Voucher set quantity is required.',
                'set_qty.integer'  => 'Voucher set quantity must be a valid number.',
                'set_qty.min'      => 'Voucher set quantity must be at least 1.',
                'term_of_use.required' => 'Voucher T&C is required',
                'voucher_detail_img.required' => 'Voucher Detail Image is required',
                'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes',
                'friendly_url.regex' => 'Only letters allowed. URLs, numbers, spaces, and special characters like "https://" are not allowed.',
            ];


            /* ---------------------------------------------------
            | 2) TIER RULES
            |---------------------------------------------------*/

            $tiers = Tier::where('status', 'Active')->get();

            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'nullable|numeric|min:0';
                $messages["tier_{$tier->id}.nullable"] = "{$tier->tier_name} price is required";
            }


            /* ---------------------------------------------------
            | 3) CONDITIONAL RULES
            |---------------------------------------------------*/

            if ($rewardType === 1) {

                /* ==========================
                PHYSICAL REWARD
                ========================== */

                $rules['max_quantity_physical'] = 'required|integer|min:1';
                $rules['locations'] = 'required|array|min:1';

                foreach ($request->locations ?? [] as $locId => $locData) {
                    if (!empty($locData['selected'])) {
                        $rules["locations.$locId.inventory_qty"] = 'required|integer|min:1';
                        $messages["locations.$locId.inventory_qty.required"] = 'This location inventory quantity is required.';
                        $messages["locations.$locId.inventory_qty.integer"]  = 'This location inventory quantity must be a number.';
                        $messages["locations.$locId.inventory_qty.min"]      = 'This location inventory quantity must be at least 1.';
                    }
                }

            } else {

                /* ==========================
                DIGITAL REWARD
                ========================== */

                $rules['voucher_set']          = 'required|numeric|min:1';
                $rules['set_qty']              = 'required|numeric|min:1';
                $rules['max_quantity_digital'] = 'required|integer|min:1';
                // $rules['voucher_validity']     = 'required|date|after_or_equal:sales_end';
                $rules['inventory_type']       = 'required|in:0,1';
                $rules['voucher_value']        = 'required|numeric|min:1';
                $rules['clearing_method']      = 'required|in:0,1,2,3,4';
                $messages['set_qty.required'] = 'Voucher set quantity is required.';
                $messages['set_qty.numeric']  = 'Voucher set quantity must be a valid number.';
                $messages['set_qty.min']      = 'Voucher set quantity must be at least 1.';

                /* Inventory Type Based */
                $rules['inventory_qty'] = 'required_if:inventory_type,0|integer|min:1';
                $messages['inventory_qty.required_if'] = 'Internal/External is required';

                /* Clearing Method Based */
                if ($clearingMethod === 2) {

                    $existingMerchantId = $reward->participating_merchant_id ?? null;
                    $existingLocations  = $reward->participatingLocations ?? collect();
                    $locationsInput     = $request->input('participating_merchant_locations', []);

                    $hasLocations = false;

                    foreach ($locationsInput as $loc) {
                        if (!empty($loc['selected'])) {
                            $hasLocations = true;
                            break;
                        }
                    }

                    if (!$hasLocations && !$existingMerchantId) {
                        $rules['participating_merchant_id'] =
                            'required|exists:participating_merchants,id';
                    }

                    if (!$hasLocations && $existingLocations->isEmpty()) {
                        $rules['participating_merchant_locations'] =
                            'required|array|min:1';
                    }

                } else {

                    $rules['location_text'] = 'required|string';
                    $messages['location_text.required'] = 'Location is required';
                }
            }


            /* ---------------------------------------------------
            | 4) VALIDATOR
            |---------------------------------------------------*/

            $validator = Validator::make($request->all(), $rules, $messages);


            /* ---------------------------------------------------
            | 5) EXTRA CROSS VALIDATION
            |---------------------------------------------------*/

            $validator->after(function ($validator) use ($request, $tiers, $rewardType) {

                /* Physical must select at least one location */
                if ($rewardType === 1) {

                    $hasSelected = false;

                    foreach ($request->locations ?? [] as $locData) {
                        if (!empty($locData['selected'])) {
                            $hasSelected = true;
                            break;
                        }
                    }

                    if (!$hasSelected) {
                        $validator->errors()->add(
                            'locations',
                            'Please select at least one location.'
                        );
                    }
                }

                /* Tier price must not exceed usual price */
                foreach ($tiers as $tier) {
                    $price = $request->input("tier_{$tier->id}");
                    if ($price !== null && $price > $request->usual_price) {
                        $validator->errors()->add(
                            "tier_{$tier->id}",
                            "{$tier->tier_name} price cannot be greater than Usual Price"
                        );
                    }
                }
            });


            /* ---------------------------------------------------
            | 6) FAIL RESPONSE
            |---------------------------------------------------*/

            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "errors" => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

    
    
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
                    // $filename = time() . '_' . $file->getClientOriginalName();
                    $filename = generateHashFileName($file);
    
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
                    // $filename = time() . '_' . $file->getClientOriginalName();
                    $filename = generateHashFileName($file);
    
                    $file->move($uploadPath, $filename);
    
                    $validated['voucher_detail_img'] = $filename;
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
    
                $locationTextId = CustomLocation::getOrCreate(
                    $request->location_text ?? ''
                );
    
                // if ((int) $reward->is_draft != 1) {
                    $updateRequest = RewardUpdateRequest::updateOrCreate(
                        [
                            'reward_id' => $reward->id,
                            'status'    => 'pending',
                            'type'      => '0',
                        ],
                        [
                            'is_draft'             => 0, 
                            'cso_method'          => (int) ($request['cso_method'] ?? 0),
                            'request_by'          => auth()->id(),
                            'voucher_image'       => $validated['voucher_image'] ?? $reward->voucher_image,
                            'voucher_detail_img'  => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
                            'name'                => $validated['name'],
                            'description'         => $validated['description'],
                            'term_of_use'         => $validated['term_of_use'],
                            'how_to_use'          => $validated['how_to_use'],
    
                            'merchant_id'         => $validated['merchant_id'] ?? 0,
                            'reward_type' => isset($validated['reward_type']) && $validated['reward_type'] !== ''
                                ? (int) $validated['reward_type']
                                : 0,
    
                            'usual_price' => isset($validated['usual_price']) && $validated['usual_price'] !== ''
                                ? (float) $validated['usual_price']
                                : 0,
    
                            'max_quantity' => isset($maxQty) && $maxQty !== ''
                                ? (int) $maxQty
                                : 0,
    
    
                            'publish_start_date'  => $validated['publish_start_date'] ?? '',
                            'publish_start_time'  => $validated['publish_start_time'] ?? '',
                            'publish_end_date'    => $validated['publish_end_date'] ?? '',
                            'publish_end_time'    => $validated['publish_end_time'] ?? '',
    
                            'sales_start_date'    => $validated['sales_start_date'] ?? '',
                            'sales_start_time'    => $validated['sales_start_time'] ?? '',
                            'sales_end_date'      => $validated['sales_end_date'] ?? '',
                            'sales_end_time'      => $validated['sales_end_time'] ?? '',
    
                            'hide_quantity'       => $request->hide_quantity,
                            'low_stock_1'         => $request->low_stock_1,
                            'low_stock_2'         => $request->low_stock_2,
                            'friendly_url'        => $request->friendly_url,
    
                            'category_id'            => $request->category_id ?? null,
                            'club_classification_id' => $request->club_classification_id ?? null,
                            'fabs_category_id'       => $request->fabs_category_id ?? null,
                            'smc_classification_id'  => $request->smc_classification_id ?? null,
                            'ax_item_code'           => $request->ax_item_code ?? null,
    
                            'publish_independent' => $request->publish_independent ?? 0,
                            'publish_inhouse'     => $request->publish_inhouse ?? 0,
                            'send_reminder'       => $request->send_reminder ?? 0,
    
                             'expiry_type' => $request->expiry_type,
                            'voucher_validity' => $request->expiry_type === 'fixed' ? $request->voucher_validity : null,
                            'validity_month' => $request->expiry_type === 'validity'  ? $request->validity_month : null,
                            'where_use'           => $request->where_use,
                            'inventory_type'      => $request->inventory_type ?? 0,
                            'inventory_qty'       => $request->inventory_qty ?? 0,
                            'voucher_value'       => $request->voucher_value ?? 0,
                            'voucher_set'         => $request->voucher_set ?? 0,
                            'set_qty'             => $request->set_qty ?? 0,
                            'csvFile'             => $reward->csvFile ?? 0,
                            'clearing_method'     => $request->clearing_method,
                            'participating_merchant_id' => $request->participating_merchant_id ?? 0,
                            'location_text'       => $locationTextId ?? '',
                            'max_order'           => $request->max_order,
                            'suspend_deal'        => $request->has('suspend_deal') ? 1 : 0,
                            'suspend_voucher'     => $request->has('suspend_voucher') ? 1 : 0,
                            'is_featured' => $request->boolean('is_featured'),
                        ]
                    );
                    $reward->update(['is_draft' => 0]); // mark main reward as non-draft (it will be updated after approval)
    
    
                    if ($request->reward_type == 0 && $request->clearing_method == 2 && !empty($request->participating_merchant_locations) ) {
    
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
    
                    /* ---------------------------------------------------
                    * 7) UPDATE LOCATION DATA
                    * ---------------------------------------------------*/
                    if ($request->reward_type == '1') {
    
                        // Delete old rows
                        RewardLocationUpdate::where('reward_id', $reward->id)->delete();
    
                        // Insert fresh
                        foreach ($request->locations as $locId => $locData) {
    
                            if (!isset($locData['selected'])) {
                                continue;
                            }
    
                            RewardLocationUpdate::create([
                                'reward_id'     => $reward->id,
                                'merchant_id'   => $validated['merchant_id'],
                                'location_id'   => $locId,
                                'is_selected'   => 1,
                                'inventory_qty' => $locData['inventory_qty'] ?? 0,
                                'total_qty' => $locData['inventory_qty'] ?? 0,
                            ]);
                        }
                    }
    
    
                /* ---------------------------------------------------
                * 6) UPDATE TIER RATES
                * ---------------------------------------------------*/
    
                RewardTierRate::where('reward_id', $reward->id)->delete();
    
                foreach ($tiers as $tier) {
    
                    $price = $request->input("tier_{$tier->id}");
    
                    // Optional safety: skip empty values
                    if ($price === null || $price === '') {
                        continue;
                    }
    
                    RewardTierRate::create([
                        'reward_id' => $reward->id,
                        'tier_id'   => $tier->id,
                        'price'     => $price,
                    ]);
                }
    
                
            
    
                if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {
    
                    $file = $request->file('csvFile');
                    // $filename = time().'_'.$file->getClientOriginalName();
                    $filename = generateHashFileName($file);
                    $file->move(public_path('uploads/csv'), $filename);
    
    
                    $filePath = public_path('uploads/csv/'.$filename);
    
                    // READ XLSX OR CSV
                    $rows = Excel::toArray([], $filePath);
    
                    $count = 0;
    
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
    
                        $count++; // ✅ count valid codes
                    }
    
                
                    $updateRequest->csvFile = $filename;
                    $updateRequest->save();
                    
    
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
        
            $walletExists = UserWalletVoucher::where('reward_id', $reward->id)->exists();
            
            // if ($walletExists) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'This reward exists in user wallet. You cannot delete it.'
            //         ], 404);
            // }
                    
            // $cartitem = CartItem::where('voucher_id', $reward->id)->exists();
            // if ($cartitem) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'This reward exists in user cart. You cannot delete it.'
            //         ], 404);
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
           
            RewardTierRate::where('reward_id', $reward->id)->delete();          
            RewardLocation::where('reward_id', $reward->id)->delete();        
            ParticipatingLocations::where('reward_id', $reward->id)->delete();
            RewardVoucher::where('reward_id', $reward->id)->delete();
            RewardUpdateRequest::where('reward_id', $reward->id)->delete();
            RewardParticipatingMerchantLocationUpdate::where('reward_id', $reward->id)->delete();
            RewardLocationUpdate::where('reward_id', $reward->id)->delete();        
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


    /**
     * Get locations by company ID
     */
    public function getMerchantLocations($merchant_id)
    {
        $locations = ClubLocation::where('status','Active')
            ->select('id', 'name')
            ->get();

        return response()->json([
            'status' => 'success',
            'locations' => $locations
        ]);
    }

    public function getParticipatingMerchantLocations(Request $request)
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



   public function uploadCsv(Request $request)
    {
        $request->validate([
            'csvFile' => 'required|mimes:csv,xlsx,xls'
        ]);

        $file = $request->file('csvFile');

        $data = Excel::toArray([], $file)[0];

        if (count($data) <= 1) {
            return back()->with('error', 'File is empty');
        }

        $header = array_map('strtolower', $data[0]);
        $codeIndex = array_search('code', $header);

        if ($codeIndex === false) {
            return back()->with('error', 'Code column not found');
        }

        // 🔥 collect codes
        $fileCodes = collect(array_slice($data, 1))
            ->pluck($codeIndex)
            ->map(fn($c) => trim($c))
            ->filter()
            ->values();

        $duplicates = [];

        // 🔥 find duplicate codes in same file
        $counts = $fileCodes->countBy();

        foreach ($counts as $code => $count) {
            if ($count > 1) {
                $duplicates[] = [
                    'code'  => $code,
                    'error' => "Duplicate in file ({$count} times)"
                ];
            }
        }

        // ✅ if duplicates found → download file
        if (!empty($duplicates)) {

            return Excel::download(new class($duplicates) implements
                \Maatwebsite\Excel\Concerns\FromCollection,
                \Maatwebsite\Excel\Concerns\WithHeadings {

                protected $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function collection()
                {
                    return collect($this->data);
                }

                public function headings(): array
                {
                    return ['Code', 'Error'];
                }

            }, 'duplicate_codes_in_file.xlsx');
        }

        return back()->with('success', 'No duplicate codes in file');
    }
            
}
