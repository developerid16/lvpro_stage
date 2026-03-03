<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\PaymentTransaction;
use App\Models\Purchase;
use App\Models\PushVoucherLog;
use App\Models\PushVoucherMember;
use App\Models\RewardVoucher;
use App\Models\UserWalletVoucher;
use App\Models\VoucherLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CsoIssuancePaidController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.cso-issuance-paid.";
        $permission_prefix = $this->permission_prefix = 'cso-issuance-paid';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'CSO Issuance (Paid)',
            'module_base_url' => url('admin/cso-issuance-paid')
        ]; 
        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable']]);
        
    }
  

    public function index(Request $request)
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '0')->where('is_draft', 0)->where('cso_method', 5);

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

         
            $final_data[$key]['image'] = imagePreviewHtml("uploads/image/{$row->voucher_image}");


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
            $methods = [
                0 => 'CSO Issuance',
                1 => 'Push by Member ID',
                2 => 'Push by Parameter',
                3 => 'Push by API SRP',
                4 => 'All Members',
                5 => 'Service Recovery',
            ];

            $final_data[$key]['cso_method'] = $methods[$row->cso_method] ?? '-';

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                // $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                // $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can('push-voucher-by-member-id')) {
                $action .= "<a href='javascript:void(0)' 
                                class='push_member_btn' 
                                data-id='{$row->id}' 
                                data-name=\"{$row->name}\">
                                <i class='mdi mdi-send text-success action-icon font-size-18'></i>
                            </a>";
            }
          
            $final_data[$key]['action'] = $action . "</div>";
        }
        $data          = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }  

    public function pushMemberVoucherByCsoIssuance(Request $request)
    {
        try {
            // ------------------------------------
            // VALIDATION
            // ------------------------------------
            $validator = Validator::make($request->all(), [
                'push_voucher'   => 'required',
                'reward_id'      => 'required|exists:rewards,id',
                'memberId'       => 'required|file',
                'method'   => 'required',
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
                // $fileName = time() . '_' . $file->getClientOriginalName();
                $fileName = generateHashFileName($file);
                $file->move(public_path('uploads/push_voucher'), $fileName);
            }

            $memberIdsArray = array_filter(
                array_map('trim', explode(',', $request->push_voucher)),
                fn($id) => $id !== ""
            );
            $memberIds = implode(',', $memberIdsArray); // <-- FINAL STRING TO STORE IN DB           

         
            if($request->input('method') == 'pushWallet'){
                $reward = Reward::where('id', $request->reward_id)->lockForUpdate()->first();
                $qty = 1;
                $now = now();

                if (!$reward) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Reward not found'
                    ], 404);
                }
    
                if ($reward->sales_start_date && $now->lt($reward->sales_start_date)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Voucher sale has not started yet'
                    ], 400);
                }
    
                if ($reward->sales_end_date && $now->gt($reward->sales_end_date)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Voucher sale has expired'
                    ], 400);
                }
    
                if ($reward->voucher_validity && $now->gt($reward->voucher_validity)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Voucher validity expired'
                    ], 400);
                }
    
                $existingMembers = [];
                  
                foreach ($memberIdsArray as $memberId) {
                    $alreadyExists = UserWalletVoucher::where('reward_id', $request->reward_id)->where('user_id', $memberId)->whereNotNull('push_voucher_member_id')->exists();

                  
                    if ($reward->purchased_qty > $reward->inventory_qty) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Voucher inventory exceeded'
                        ], 400);
                    }
                    if ($alreadyExists) {
                        $existingMembers[] = $memberId;
                        PushVoucherLog::create([
                            'user_id'   => $memberId,
                            'reward_id' => $request->reward_id,
                            'push_by'   => 'pushMemberId',
                            'type'      => 'pushWallet',
                            'status'    => 'already_assigned',
                            'message'   => 'Voucher already assigned to user'
                        ]);
                        continue;
                    }                    

                    $user = AppUser::where('session_id', $memberId)->first();
                    
                    if (!$user) {
                        PushVoucherLog::create([
                            'user_id'   => $memberId,
                            'reward_id' => $request->reward_id,
                            'type'      => $request->input('method'),
                            'status'    => 'user_not_found',
                            'push_by'   => 'pushMemberId',
                            'message'   => 'User not found in system'
                        ]);
                        continue;
                    }

                    $pushMemberId = PushVoucherMember::create([
                        'type' => '0',
                        'reward_id' => $request->reward_id,
                        'member_id' => $memberIds,   // stored comma-separated
                        'file'      => $fileName,
                        'method' => $request->input('method')
                    ]);

                    $userId = $user->session_id;

                    // ===== MAX QTY PER USER (SET LOGIC) =====
                    if (!is_null($reward->max_quantity)) {
        
                        $voucherSet = (int) ($reward->voucher_set ?: 1);
        
                        // Wallet qty → convert to sets
                        $currentQty = UserWalletVoucher::where('reward_id', $request->reward_id)
                            ->where('user_id', $userId)
                            ->sum('qty');
                            
                        $totalQty = $reward->max_quantity * $voucherSet;
                        $remainingEffectiveQty = $totalQty - $currentQty;
                        $remainingEffectiveQty = intdiv($remainingEffectiveQty, $voucherSet);
        
                        if ($remainingEffectiveQty <= 0) {
                            return response()->json([
                                'status' => false,
                                'message'    => 'Maximum limit per user exceeded',
                                'data'   => []
                            ], 422);
                        }
        
                        if ($remainingEffectiveQty <= 0) {
                            return response()->json([
                                'status' => false,
                                'message'    => 'Maximum limit per user exceeded',
                                'data'   => []
                            ], 422);
                        }
        
                        // Check current request
                        if ((int) $request->qty > $remainingEffectiveQty) {
                            return response()->json([
                                'status' => false,
                                'message' => "You can only claim {$remainingEffectiveQty} quantity"
                            ], 400);
                        }
                    }
                    
                    $receiptNo =   UserWalletVoucher::generateReceiptNo();
        
                    $voucherSet = (int) $reward->voucher_set * (int) $qty;   // ex: 5
                    $totalRecords = $voucherSet;
        
                    if($reward->inventory_type == '1'){ //merchant codes
                        $codes = RewardVoucher::where('reward_id', $reward->id)
                            ->where('is_used', 0)
                            ->limit($totalRecords)
                            ->lockForUpdate()
                            ->get();
        
                        $counter = 1;
                        foreach ($codes as $code) {
                            
                            $uniqueCode = UserWalletVoucher::generateUniqueVoucherCode();
                            $serialNo   = UserWalletVoucher::generateSerialNo($uniqueCode, $counter++);
                            UserWalletVoucher::create([
                                'user_id'            => $memberId,
                                'reward_voucher_id'  => $code->id,
                                'reward_id'   => $reward->id,                                        
                                'qty'           => 1,
                                'claimed_at'    => now(),
                                'status'         => 'Active',
                                'reward_status'  => 'purchased',
                                'receipt_no'    => $receiptNo,   
                                'unique_code'       => $uniqueCode,
                                'serial_no'         => $serialNo,                            
                                'push_voucher_member_id' => $pushMemberId->id,                            
                                
                            ]);
        
                            VoucherLog::create([
                                'user_id'           => $memberId,
                                'reward_id'         => $reward->id,
                                'reward_voucher_id' => $code->id,
                                'action'            => 'purchased',
                                'receipt_no'        => $uniqueCode,
                                'qty'               => 1,
                            ]);
        
                            // mark code used
                            $code->update(['is_used' => 1]);
                        }
                    }else{
                        for ($i = 0; $i < $totalRecords; $i++) {
                            
                            $uniqueCode = UserWalletVoucher::generateUniqueVoucherCode();
                            $serialNo   = UserWalletVoucher::generateSerialNo($uniqueCode, $i);
            
                            UserWalletVoucher::create([
                                'user_id'        => $memberId,
                                'reward_voucher_id' => null,
                                'reward_id'      => $reward->id,
                                'location_id'    => $request->location_id,
                                'location_type'  => $request->location_type,
                                'qty'            => 1, // ✅ always 1 per voucher
                                'claimed_at'     => now(),
                                'status'         => 'Active',
                                'reward_status'  => 'purchased',
                                'receipt_no'     => $receiptNo,
                                'unique_code'       => $uniqueCode,
                                'serial_no'         => $serialNo,  
                                'push_voucher_member_id' => $pushMemberId->id,      
                            ]);
            
                            VoucherLog::create([
                                'user_id'           => $memberId,
                                'reward_id'         => $reward->id,
                                'reward_voucher_id' => null,
                                'action'            => 'purchased',
                                'receipt_no'        => $uniqueCode,
                                'qty'               => 1,
                            ]);
                        }
                    }
                    $dataReq = $request->all();
                    // 🔥 Create Purchase entry for pushed voucher
                    $transactionId = strtoupper(Str::random(10));
                    $mid           = strtoupper(Str::random(10));
                    PaymentTransaction::updateOrCreate(
                        [
                            'transaction_id' => $transactionId,
                        ],
                        [
                            'user_id'           => $userId,
                            'mid'               => $mid ?? null,
                            'order_id'          => $reward->id ?? null,
                            'receipt_no'          => $receiptNo ?? null,
                            'rec'               => 2,
                            'request_amount'    =>  $reward->usual_price ?? 0,
                            'authorized_amount' => $reward->usual_price ?? 0,
                            'status'            => 'success',
                            'raw_response'      => json_encode($dataReq),
                        ]
                    );

                                        
                    PushVoucherLog::create([
                        'user_id'   => $memberId,
                        'reward_id' => $reward->id,
                        'push_voucher_member_id' => $pushMemberId->id ?? null,
                        'type'      => 'pushWallet',
                        'status'    => 'success',
                        'push_by'   => 'pushMemberId',
                        'message'   => 'Voucher pushed to wallet successfully'
                    ]);

                    $reward->purchased_qty = (int) $reward->purchased_qty + (int) $qty;
                    $reward->save();
                }

            }else {

                $existingMembers = [];
                $validMembers = [];

                foreach ($memberIdsArray as $memberId) {

                    $alreadyPushed = PushVoucherMember::where('reward_id', $request->reward_id)
                        ->where('method', 'pushCatalogue')
                        ->whereRaw("FIND_IN_SET(?, member_id)", [$memberId])
                        ->exists();

                    if ($alreadyPushed) {

                        $existingMembers[] = $memberId;

                        PushVoucherLog::create([
                            'user_id'   => $memberId,
                            'reward_id' => $request->reward_id,
                            'type'      => 'pushCatalogue',
                            'push_by'   => 'pushMemberId',
                            'status'    => 'already_assigned',
                            'message'   => 'Voucher already assigned to user'
                        ]);

                    } else {
                        $validMembers[] = $memberId;
                    }
                }

                // If no valid members, stop
                if (empty($validMembers)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'All selected members already assigned'
                    ], 400);
                }

                // ✅ Create PushVoucherMember FIRST
                $pushMemberId = PushVoucherMember::create([
                    'type' => '0',
                    'reward_id' => $request->reward_id,
                    'member_id' => implode(',', $validMembers),
                    'file'      => $fileName,
                    'method'    => 'pushCatalogue'
                ]);

                // ✅ Now log success using real push ID
                foreach ($validMembers as $memberId) {

                    PushVoucherLog::create([
                        'user_id'   => $memberId,
                        'reward_id' => $request->reward_id,
                        'push_voucher_member_id' => $pushMemberId->id,
                        'type'      => 'pushCatalogue',
                        'push_by'   => 'pushMemberId',
                        'status'    => 'success',
                        'message'   => 'Voucher pushed to catalogue'
                    ]);
                }
            }

            if (!empty($existingMembers)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Push completed. Already existed members: ' . implode(',', $existingMembers)
                ], 400);
            }
           
            return response()->json([
                'status'  => 'success',
                'message' => 'Push voucher saved successfully'
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
   
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(Tier $tier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
         
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
      
    }
  
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       
    }
}
