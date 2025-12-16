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
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\RewardVoucher;
use App\Models\UserPurchasedReward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct

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
            $final_data[$key]['month'] = Carbon::createFromFormat('Y-m',$row->month )->format(config('shilla.month-format'));
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
                'how_to_use'         => 'required|string',
                'term_of_use'        => 'required|string',
                'merchant_id'        => 'required|exists:merchants,id',
                'club_location'     => 'required',
                'voucher_validity'   => 'required|date',
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

                'voucher_validity'   => $validated['voucher_validity'],

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
            * SAVE PARTICIPATING LOCATIONS
            * ---------------------------------------------------*/
            if ($request->clearing_method == 2 && $request->participating_merchant_locations) {

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

            $reward = Reward::findOrFail($id);
            $currentMonth = now()->format('Y-m');

            /*
            |--------------------------------------------------------------------------
            | CASE 1: CURRENT MONTH
            |--------------------------------------------------------------------------
            */
            if ($reward->month === $currentMonth) {

                // Only manual inventory allowed
                if ($reward->inventory_type != 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Current month reward cannot be edited.'
                    ], 422);
                }

                // Inventory qty required
                if (!$request->filled('inventory_qty')) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => [
                            'inventory_qty' => ['Inventory quantity is required.']
                        ]
                    ], 422);
                }

                // ❌ No minus allowed
                if ($request->inventory_qty < $reward->inventory_qty) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => [
                            'inventory_qty' => [
                                'Inventory cannot be reduced for current month.'
                            ]
                        ]
                    ], 422);
                }

                // ✅ ONLY update inventory_qty
                $reward->update([
                    'inventory_qty' => $request->inventory_qty
                ]);

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Inventory updated successfully'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CASE 2: NON-CURRENT MONTH → STORE FOR ADMIN APPROVAL
            |--------------------------------------------------------------------------
            */

       
            $validated = $request->validate([
                'month' => 'required',
                'voucher_image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
                'name' => 'required|string',
                'description' => 'required|string',
                'term_of_use' => 'required|string',
                'how_to_use' => 'required|string',
                'merchant_id' => 'required|exists:merchants,id',
                'voucher_validity' => 'required|date',
                'club_location' => 'required',
                'inventory_type' => 'required|in:0,1',
                'inventory_qty' => 'nullable|integer|min:0',
                'voucher_value' => 'required|numeric|min:0',
                'voucher_set' => 'required|integer|min:1',
                'clearing_method' => 'required',
                'low_stock_1' => 'required|integer|min:0',
                'low_stock_2' => 'required|integer|min:0',
            ]);


            if ($request->hasFile('voucher_image')) {
                $file = $request->file('voucher_image');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/image'), $filename);
                $validated['voucher_image'] = $filename;
            }           

            $pendingRequest = RewardUpdateRequest::where('reward_id',  $reward->id) ->where('status', 'pending') ->first();

            if ($pendingRequest) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'month' => ['Your request is already pending approval.']
                    ]
                ], 422);
            }
            // $approvedRequest = RewardUpdateRequest::where('reward_id', $reward->id)->where('status', 'approved')->first();

            // if ($approvedRequest) {
            //     return response()->json([
            //         'status' => 'error',
            //         'errors' => [
            //             'month' => ['This reward has already been approved and cannot be updated again.']
            //         ]
            //     ], 422);
            // }

            /* ---------------------------------------------------
            * CREATE REWARD (e-Voucher only)
            * ---------------------------------------------------*/
            $reward = RewardUpdateRequest::create([
                'type'           => '2',
                'reward_id'      => $id,
                'month'          => $validated['month'],
                'voucher_image' => $validated['voucher_image'] ?? $reward->voucher_image,
                'name'           => $validated['name'],
                'description'    => $validated['description'],
                'term_of_use'    => $validated['term_of_use'],
                'how_to_use'     => $validated['how_to_use'],

                'merchant_id'    => $validated['merchant_id'],
                'reward_type'    => 0, // e-voucher fixed

                'voucher_validity'   => $validated['voucher_validity'],

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
                'status'        => 'pending',
            ]);
        


            /* ---------------------------------------------------
            * SAVE PARTICIPATING LOCATIONS
            * ---------------------------------------------------*/
            if ($request->clearing_method == 2 && $request->participating_merchant_locations) {

                foreach ($request->participating_merchant_locations as $locId => $locData) {
                    if (!isset($locData['selected'])) continue;

                    RewardParticipatingMerchantLocationUpdate::create([
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
