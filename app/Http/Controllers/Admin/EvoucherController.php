<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\Evoucher;
use App\Models\PushVoucherMember;
use App\Models\Reward;
use App\Models\RewardVoucher;
use App\Models\UserPurchasedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct

class EvoucherController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.evoucher.";
        $permission_prefix    = $this->permission_prefix    = 'evoucher';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Digital Voucher',
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
        $this->layout_data['rewards'] = Reward::get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '1');

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
                'voucher_image'      => 'required|image|mimes:png,jpg,jpeg|max:2048',
                'name'               => 'required|string|max:191',
                'description'        => 'required|string',
                'term_of_use'        => 'required|string',
                'how_to_use'         => 'required|string',

                'merchant_id'        => 'required|exists:merchants,id',

                'publish_start'      => 'required',
                'sales_start'        => 'required',

                'friendly_url'       => 'nullable',
                'direct_utilization'       => 'nullable',
                'max_quantity'       => 'required|integer|min:1',
                'voucher_validity'   => 'required|date',

                'category_id'     => 'nullable',
                'inventory_type'     => 'required|in:0,1',
                'voucher_value'      => 'required|numeric|min:0',
                'voucher_set'        => 'required|integer|min:1',

                'clearing_method'    => 'required|in:0,1,2,3,4',

                'low_stock_1'        => 'required|integer|min:0',
                'low_stock_2'        => 'required|integer|min:0',
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
                $rules['csvFile'] = 'required|file';
            }


            /* ---------------------------------------------------
            * CLEARING METHOD RULES
            * ---------------------------------------------------*/

            // External link → text required
             if (in_array($request->clearing_method, [0,1,3])) {
                $rules['location_text'] = 'required|string';
            }

            // External code + Merchant code → merchant + locations required
            if (in_array($request->clearing_method, [2])) {
                $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';
                $rules['participating_merchant_locations'] = 'required|array|min:1';
            }


            /* ---------------------------------------------------
            * VALIDATE
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
            * UPLOAD IMAGE
            * ---------------------------------------------------*/
            if ($request->hasFile('voucher_image')) {
                $file = $request->file('voucher_image');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/image'), $filename);
                $validated['voucher_image'] = $filename;
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


            /* ---------------------------------------------------
            * CREATE REWARD (e-Voucher only)
            * ---------------------------------------------------*/
            $reward = Reward::create([
                'type'  => '1',
                'voucher_image'  => $validated['voucher_image'],
                'name'           => $validated['name'],
                'description'    => $validated['description'],
                'term_of_use'    => $validated['term_of_use'],
                'how_to_use'     => $validated['how_to_use'],

                'merchant_id'    => $validated['merchant_id'],
                'reward_type'    => 0, // e-voucher fixed

                'direct_utilization'   => $validated['direct_utilization'] ?? 0,
                'max_quantity'   => $validated['max_quantity'],

                'publish_start_date' => $validated['publish_start_date'] ?? null,
                'publish_start_time' => $validated['publish_start_time'] ?? null,
                'publish_end_date'   => $validated['publish_end_date'] ?? null,
                'publish_end_time'   => $validated['publish_end_time'] ?? null,

                'sales_start_date'   => $validated['sales_start_date'] ?? null,
                'sales_start_time'   => $validated['sales_start_time'] ?? null,
                'sales_end_date'     => $validated['sales_end_date'] ?? null,
                'sales_end_time'     => $validated['sales_end_time'] ?? null,

                'voucher_validity'   => $validated['voucher_validity'],

                'inventory_type'     => $validated['inventory_type'],
                'inventory_qty'      => $validated['inventory_qty'] ?? null,

                'category_id'      => $validated['category_id'],
                'friendly_url'      => $validated['friendly_url'],
                'voucher_value'      => $validated['voucher_value'],
                'voucher_set'        => $validated['voucher_set'],
                'clearing_method'    => $validated['clearing_method'],

                'location_text'      => $request->location_text,
                'participating_merchant_id' => $request->participating_merchant_id,

                'hide_quantity'      => $request->hide_quantity ? 1 : 0,
                'low_stock_1'        => $validated['low_stock_1'],
                'low_stock_2'        => $validated['low_stock_2'],
            ]);


            /* ---------------------------------------------------
            * SAVE PARTICIPATING LOCATIONS
            * ---------------------------------------------------*/
            if (in_array($request->clearing_method, [2,4]) &&
                $request->participating_merchant_locations) {

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
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        $this->layout_data['participatingLocations'] = $reward ? $reward->participatingLocations->pluck('location_id')  : [];

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html' => $html,
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
            * 1) FETCH REWARD
            * ---------------------------------------------------*/
            $reward = Reward::findOrFail($id);

            /* ---------------------------------------------------
            * 2) VALIDATION RULES (same as store(), except image optional)
            * ---------------------------------------------------*/
            $rules = [
                'voucher_image'      => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'name'               => 'required|string|max:191',
                'description'        => 'required|string',
                'term_of_use'        => 'required|string',
                'how_to_use'         => 'required|string',

                'merchant_id'        => 'required|exists:merchants,id',
                'friendly_url'     => 'nullable',
                'category_id'     => 'nullable',
                'publish_start'      => 'required',
                'sales_start'        => 'required',

                'direct_utilization'       => 'nullable',
                'max_quantity'       => 'required|integer|min:1',
                'voucher_validity'   => 'required|date',

                'inventory_type'     => 'required|in:0,1',
                'voucher_value'      => 'required|numeric|min:0',
                'voucher_set'        => 'required|integer|min:1',

                'clearing_method'    => 'required|in:0,1,2,3,4',

                'low_stock_1'        => 'required|integer|min:0',
                'low_stock_2'        => 'required|integer|min:0',
            ];

            /* --------------------------------------------
            * INVENTORY RULES
            * --------------------------------------------*/
            if ($request->inventory_type == 0) {
                $rules['inventory_qty'] = 'required|integer|min:1';
            }

            if ($request->inventory_type == 1) {
                // require new CSV only if old doesn't exist
                if (!$reward->csvFile && !$request->hasFile('csvFile')) {
                    $rules['csvFile'] = 'required|file';
                }
            }

            /* --------------------------------------------
            * CLEARING METHOD RULES
            * --------------------------------------------*/
             if (in_array($request->clearing_method, [0,1,3])) {
                $rules['location_text'] = 'required|string';
            }

            if (in_array($request->clearing_method, [2])) {
                $rules['participating_merchant_id'] = 'required|exists:participating_merchants,id';
                $rules['participating_merchant_locations'] = 'required|array|min:1';
            }

            /* ---------------------------------------------------
            * 3) VALIDATE
            * ---------------------------------------------------*/
            $validated = $request->validate($rules);

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

            /* ---------------------------------------------------
            * 6) UPDATE REWARD
            * ---------------------------------------------------*/
            $reward->update([
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'name'               => $validated['name'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],

                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => 0,
                'type'               => '1',

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

                'voucher_validity'   => $validated['voucher_validity'],

                'category_id'     => $validated['category_id'],
                'friendly_url'     => $validated['friendly_url'],
                'inventory_type'     => $validated['inventory_type'],
                'inventory_qty'      => $validated['inventory_qty'] ?? null,

                'voucher_value'      => $validated['voucher_value'],
                'voucher_set'        => $validated['voucher_set'],
                'clearing_method'    => $validated['clearing_method'],

                'location_text'      => $request->location_text,
                'participating_merchant_id' => $request->participating_merchant_id,

                'hide_quantity'      => $request->hide_quantity ? 1 : 0,
                'low_stock_1'        => $validated['low_stock_1'],
                'low_stock_2'        => $validated['low_stock_2'],
            ]);


            /* ---------------------------------------------------
            * 7) UPDATE PARTICIPATING LOCATIONS
            * ---------------------------------------------------*/
            if ($request->clearing_method == 2 && $request->participating_merchant_locations) {


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

    public function getDates($id)
    {
        $reward = Reward::findOrFail($id);

        return response()->json([
            'publish_start' => $reward->publish_start_date ? $reward->publish_start_date . 'T' . $reward->publish_start_time : null,
            'publish_end'   => $reward->publish_end_date ? $reward->publish_end_date . 'T' . $reward->publish_end_time : null
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
                'age_from'           => 'nullable|date',
                'age_to'             => 'nullable|date',

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
