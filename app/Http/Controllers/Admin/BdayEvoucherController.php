<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ClubLocation;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\Evoucher;
use App\Models\Reward;
use App\Models\RewardVoucher;
use App\Models\UserPurchasedReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct

class BdayEvoucherController extends Controller
{
    public function __construct()
    {

        $this->view_file_path = "admin.bday-voucher.";
        $permission_prefix    = $this->permission_prefix = 'bday-voucher';
        $this->layout_data    = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Birthday Voucher',
            'module_base_url'   => url('admin/bday-voucher'),
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
        $query = Reward::where('type',  '2');

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
                'month' => 'required',
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
                'club_location'     => 'required',
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
                'type'           => '2',
                'month'          => $validated['month'],
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

                'club_location'     => $validated['club_location'],
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
                        'type'      => '2',
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
        $this->layout_data['club_location'] = ClubLocation::get();
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
                'month' => 'required',
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

                'club_location'     => 'required',
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
                'month'      => $validated['month'] ?? $reward->month,
                'voucher_image'      => $validated['voucher_image'] ?? $reward->voucher_image,
                'name'               => $validated['name'],
                'description'        => $validated['description'],
                'term_of_use'        => $validated['term_of_use'],
                'how_to_use'         => $validated['how_to_use'],

                'merchant_id'        => $validated['merchant_id'],
                'reward_type'        => 0,
                'type'               => '2',

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
                'club_location'     => $validated['club_location'],
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
                        'type' => '2',
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

   public function getLocations(Request $request)
    {
        $merchantId = $request->merchant_id;

        $locations = ClubLocation::where('merchant_id', $merchantId)->get();
        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }


}
