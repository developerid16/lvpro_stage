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
use App\Rules\SingleCodeColumnFile;
use App\Models\CustomLocation;
use App\Models\UserWalletVoucher;

class RewardController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.reward.";
        $permission_prefix    = $this->permission_prefix    = 'reward';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Treats & Deals',
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
        $this->layout_data['tiers'] = Tier::where('status', 'Active')->get();

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
            $final_data[$key]['amount'] = number_format($row->usual_price);

            $final_data[$key]['quantity'] = max(0, (int) $total_quantity);

            $purchased = UserWalletVoucher::where('reward_id', $row->id)
                ->where('reward_status', 'purchased')
                ->count();

            $final_data[$key]['purchased'] = max(0, $purchased);

            $final_data[$key]['balance'] = max(0, $total_quantity - $purchased);

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
                $final_data[$key]['image'] = '<img src="'.$imgUrl.'"
                            class="avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle"
                            alt="Voucher Image">'; // nothing shown
            }

           
            if ($row->publish_start_date && $row->publish_end_date) {
                $duration =
                    Carbon::parse($row->publish_start_date)->format(config('safra.date-only')) .
                    ' to ' .
                    Carbon::parse($row->publish_end_date)->format(config('safra.date-only'));

            } elseif ($row->publish_start_date) {
                $duration =
                    Carbon::parse($row->publish_start_date)->format(config('safra.date-only')) .
                    ' - No Expiry';
            } else {
                $duration = 'No Expiry';
            }

            $final_data[$key]['duration']   = $duration;
            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));

            $final_data[$key]['is_draft'] = $row->is_draft == 1 ? 'Yes' : 'No';
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
                    $filename = time() . '_' . $file->getClientOriginalName();
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
                    $filename = time() . '_' . $file->getClientOriginalName();
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
                    'is_draft'           => 1,
                    'voucher_image'      => $validated['voucher_image'] ?? '',
                    'voucher_detail_img' => $validated['voucher_detail_img'] ?? '',
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
                    'where_use'                  => $request->where_use,
                    'inventory_type'             => $request->inventory_type,
                    'inventory_qty'              => $request->inventory_qty,
                    'voucher_value'              => $request->voucher_value,
                    'voucher_set'                => $request->voucher_set,
                    'set_qty'                       => $request->set_qty,
                    'clearing_method'            => $request->clearing_method,
                    'participating_merchant_id'  => $request->participating_merchant_id ?? 0,
                    'location_text'              => $locationTextId ?? '',
                    'max_order'                   => $request->max_order,     
                    'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                    'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,           
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
                    $filename = time().'_'.$file->getClientOriginalName();
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


                 DB::commit();
                return response()->json(['status'=>'success','message'=>'Saved as draft successfully']);
            }

       
            $rules = [
                'voucher_image'      => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'name'               => 'required|string|max:191',
                'description'        => 'required|string',
                'term_of_use'        => 'required|string',
                'how_to_use'         => 'required|string',
                'merchant_id'        => 'required|exists:merchants,id',
                'reward_type'        => 'required|in:0,1',
                'voucher_validity'   => 'required',
                'usual_price'        => 'required|numeric|min:0',
                'publish_start'      => 'required',
                'publish_end'        => 'required',
                'sales_start'        => 'required',
                'sales_end'          => 'required',                
                'send_reminder'      => 'required|boolean',
            ];

            $messages = [
                'term_of_use.required' => 'Voucher T&C is required',
                'voucher_detail_img.required' => 'Voucher Detail Image is required',
                'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes',
            ];

            /* ---------------- TIER RULES ---------------- */

            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'required|numeric|min:0';
                $messages["tier_{$tier->id}.required"] = "{$tier->tier_name} price is required";
            }

            
            /* ---------------- RUN VALIDATOR ---------------- */
            $validator = Validator::make($request->all(), $rules, $messages);
            
            /* ---------------- CROSS FIELD CHECK ---------------- */
            $validator->after(function ($validator) use ($request, $tiers, &$rules) {
                /* ---------------- PHYSICAL ---------------- */
                if ($request->reward_type == 1) {
    
                    $rules['max_quantity_physical'] = 'required|integer|min:1';
                    $rules['locations'] = 'required|array|min:1';
    
                    $hasSelected = false;
    
                    foreach ($request->locations ?? [] as $locId => $locData) {
                        if (isset($locData['selected'])) {
                            $hasSelected = true;
                            $rules["locations.$locId.inventory_qty"] = 'required|integer|min:1';
                        }
                    }
    
                    if (!$hasSelected) {
                        return response()->json([
                            "status" => "error",
                            "errors" => [
                                "locations" => ["Please select at least one location."]
                            ]
                        ], 422);
                    }
                }
    
                /* ---------------- DIGITAL ---------------- */
                if ($request->reward_type == 0) {
    
                    $rules += [
                        'max_quantity_digital' => 'required|integer|min:1',
                        'voucher_validity'     => 'required|date',
                        'inventory_type'       => 'required|in:0,1',
                        'voucher_value'        => 'required|numeric|min:1',
                        'voucher_set'          => 'required|numeric|min:1',
                        'set_qty'          => 'required|numeric|min:1',
                        'clearing_method'      => 'required|in:0,1,2,3,4',
                    ];
    
                    if ($request->inventory_type == 0) {
                        $rules['inventory_qty'] = 'required|integer|min:1';
                    }
    
                    if ($request->inventory_type == 1) {
                        $rules['csvFile'] = ['required','file','mimes:csv,xlsx,xls', new SingleCodeColumnFile(),];
                    }
    
                    if ($request->clearing_method == 2) {
                        $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';
                        $rules['participating_merchant_locations'] = 'required|array|min:1';
                    }
                    if ($request->clearing_method != 2) {
                        $rules['location_text'] = 'required';
                        $messages = [
                            'location_text.required' => 'Location is required',
                        ];
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
            });

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
                'voucher_image'      => $validated['voucher_image'],
                'voucher_detail_img' => $validated['voucher_detail_img'],
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
                'where_use'                  => $request->where_use,
                'inventory_type'             => $request->inventory_type,
                'inventory_qty'              => $request->inventory_qty,
                'voucher_value'              => $request->voucher_value,
                'voucher_set'                => $request->voucher_set,
                'set_qty'                       => $request->set_qty,
                'clearing_method'            => $request->clearing_method,
                'participating_merchant_id'  => $request->participating_merchant_id ?? 0,
                'location_text'              => $locationTextId ?? '',
                'max_order'                   => $request->max_order, 
                 'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,               
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
                $filename = time().'_'.$file->getClientOriginalName();
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
        $this->layout_data['location_text'] = null;

        if (!empty($reward->location_text)) {
            $this->layout_data['location_text'] = CustomLocation::where('id', $reward->location_text)
                ->value('name');
        }
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        $this->layout_data['tiers'] = Tier::where('status', 'Active')->get();
        $this->layout_data['category'] = Category::get();
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
                    $filename = time() . '_' . $file->getClientOriginalName();

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
                    'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                    'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
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
                    'where_use'                  => $request->where_use,
                    'inventory_type'            => $request->inventory_type,
                    'inventory_qty'             => $request->inventory_qty,
                    'voucher_value'             => $request->voucher_value,
                    'voucher_set'               => $request->voucher_set,
                    'set_qty'                    => $request->set_qty,
                    'clearing_method'           => $request->clearing_method,
                    'participating_merchant_id' =>  $request->participating_merchant_id ?? 0,
                    'location_text'             => $locationTextId ?? '',
                    'max_order'                 => $request->max_order,
                     'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,
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
        

                if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                    $file = $request->file('csvFile');
                    $filename = time().'_'.$file->getClientOriginalName();
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
    
            }


            /* ---------------------------------------------------
            * 1) FIND REWARD
            * ---------------------------------------------------*/

            $rules = [
                'voucher_image'    => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'voucher_detail_img' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',

                'name'        => 'required|string|max:191',
                'description' => 'required|string',
                'term_of_use' => 'required|string',
                'how_to_use'  => 'required|string',

                'merchant_id' => 'required|exists:merchants,id',
                'reward_type' => 'required|in:0,1',
                'voucher_validity'   => 'required',
                'usual_price' => 'required|numeric|min:0',

                'publish_start' => 'required',
                'publish_end'   => 'required',
                'sales_start'   => 'required',
                'sales_end'     => 'required',
            ];

            $messages = [
                'term_of_use.required' => 'Voucher T&C is required',
                'voucher_detail_img.required' => 'Voucher Detail Image is required',
                'voucher_detail_img.image'    => 'Voucher Detail Image must be an image file',
                'voucher_detail_img.mimes'    => 'Voucher Detail Image must be a file of type: png, jpg, jpeg',
                'voucher_detail_img.max'      => 'Voucher Detail Image may not be greater than 2048 kilobytes', 
            ];

            /* ---------------- TIER RULES ---------------- */
            $tiers = Tier::where('status', 'Active')->get();

            foreach ($tiers as $tier) {
                $rules["tier_{$tier->id}"] = 'required|numeric|min:0';
                $messages["tier_{$tier->id}.required"] = "{$tier->tier_name} price is required";
            }
            
            /* ---------------- VALIDATOR ---------------- */
            $validator = Validator::make($request->all(), $rules, $messages);
            
            $validator->after(function ($validator) use ($request, $tiers, &$rules, $reward) {
                /* ---------------- PHYSICAL ---------------- */
                if ((int) $request->reward_type === 1) {
    
                    $rules = array_merge($rules, [
                        'max_quantity_physical' => 'required|integer|min:1',                       
                        'send_reminder'         => 'required|boolean',
    
                        'publish_independent'   => 'required_without_all:publish_inhouse|boolean',
                        'publish_inhouse'       => 'required_without_all:publish_independent|boolean',
    
                        'locations'             => 'required|array|min:1',
                    ]);
    
                    $hasSelected = false;
    
                    foreach ($request->locations ?? [] as $locId => $locData) {
                        if (!empty($locData['selected'])) {
                            $hasSelected = true;
                            $rules["locations.$locId.inventory_qty"] =
                                'required|integer|min:1';
                        }
                    }
    
                    if (!$hasSelected) {
                        return response()->json([
                            'status' => 'error',
                            'errors' => [
                                'locations' => ['Please select at least one location.']
                            ]
                        ], 422);
                    }
                }
    
                /* ---------------- DIGITAL ---------------- */
                if ((int) $request->reward_type === 0) {
    
                    $rules = array_merge($rules, [
                        'max_quantity_digital' => 'required|integer|min:1',
                        'voucher_validity'     => 'required|date',
                        'inventory_type'       => 'required|in:0,1',
                        'voucher_value'        => 'required|numeric|min:1',
                        'voucher_set'          => 'required|numeric|min:1',
                        'set_qty'              => 'required|numeric|min:1',
                        'clearing_method'      => 'required|in:0,1,2,3,4',
                    ]);
    
                    if ((int) $request->inventory_type == 0) {
                        $rules['inventory_qty'] = 'required|integer|min:1';
                    } 
    
                    if ($request->inventory_type == 1) {
                        if(!$reward->csvFile){
                            $rules['csvFile'] = ['required','file','mimes:xlsx,xls,csv', new SingleCodeColumnFile(),];
                        }
                    }
    
                    if ($request->clearing_method != 2) {
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
    
    
                }
                
                $usual = (float) $request->usual_price;

                foreach ($tiers as $tier) {
                    $price = (float) $request->input("tier_{$tier->id}");

                    if ($price > $usual) {
                        $validator->errors()->add(
                            "tier_{$tier->id}",
                            "{$tier->tier_name} price cannot be greater than Usual Price"
                        );
                    }
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

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

            $reward->update([
                'type'               => '0',
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'voucher_detail_img' => $validated['voucher_detail_img'] ?? $reward->voucher_detail_img,
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
                'where_use'                  => $request->where_use,
                'inventory_type'            => $request->inventory_type,
                'inventory_qty'             => $request->inventory_qty,
                'voucher_value'             => $request->voucher_value,
                'voucher_set'               => $request->voucher_set,
                'set_qty'                    => $request->set_qty,
                'clearing_method'           => $request->clearing_method,
                'participating_merchant_id' =>  $request->participating_merchant_id ?? 0,
                'location_text'             => $locationTextId ?? '',
                'max_order'                 => $request->max_order,
                 'suspend_deal'    => $request->has('suspend_deal') ? 1 : 0,
                'suspend_voucher' => $request->has('suspend_voucher') ? 1 : 0,
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
       

         if ($request->inventory_type == 1 && $request->hasFile('csvFile')) {

                $file = $request->file('csvFile');
                $filename = time().'_'.$file->getClientOriginalName();
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
    
}
