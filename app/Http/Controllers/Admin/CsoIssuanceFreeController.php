<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\PushVoucherLog;
use App\Models\PushVoucherMember;
use App\Models\UserWalletVoucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CsoIssuanceFreeController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.cso-issuance-free.";
        $permission_prefix = $this->permission_prefix = 'cso-issuance-free';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'CSO Issuance (Free)',
            'module_base_url' => url('admin/cso-issuance-free')
        ]; 
        $this->middleware("active.permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable']]);
        
        $this->middleware(function ($request, $next) {
            $activeDeptId = session('active_department_id');
            $user = Auth::user();

            $activeRoles = $user->roles->filter(function ($role) use ($activeDeptId) {
                return (string)$role->department === (string)$activeDeptId;
            });

            if ($activeRoles->isEmpty()) {
                $activeRoles = $user->roles;
            }

            $activeRole = $activeRoles->first();

            $this->activeDeptId     = $activeDeptId;
            $this->activeLocationId = session('active_club_location_id');
            $this->activeRoleId     = $activeRole?->id;

            return $next($request);
        });
    }
  

    public function index(Request $request)
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '1')->where('is_draft', 0)->where('cso_method', 0);
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('active_department_id', $this->activeDeptId);
            $query->where('active_club_location_id', $this->activeLocationId);
            $query->where('active_role_id', $this->activeRoleId);
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
        DB::beginTransaction();

        try {

            // ------------------------------------
            // VALIDATION
            // ------------------------------------
            $validator = Validator::make($request->all(), [
                'push_voucher' => 'required',
                'reward_id'    => 'required|exists:rewards,id',
                'memberId'     => 'required|file',
                'method'       => 'nullable|in:pushWallet',
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
                $fileName = generateHashFileName($file);
                $file->move(public_path('uploads/push_voucher'), $fileName);
            }

            // ------------------------------------
            // MEMBER IDS
            // ------------------------------------
            $memberIdsArray = array_filter(
                array_map('trim', explode(',', $request->push_voucher)),
                fn($id) => $id !== ""
            );

            $memberIds = implode(',', $memberIdsArray);

            // ------------------------------------
            // LOCK REWARD
            // ------------------------------------
            $reward = Reward::where('id', $request->reward_id)
                ->lockForUpdate()
                ->first();

            if (!$reward) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reward not found'
                ], 404);
            }

            $now = now();

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

            // ------------------------------------
            // CREATE PUSH RECORD ONCE
            // ------------------------------------
            $pushMember = PushVoucherMember::create([
                'type'      => 0,
                'reward_id' => $reward->id,
                'member_id' => $memberIds,
                'file'      => $fileName,
                'method'    => 'pushWallet'
            ]);

            $existingMembers = [];

            foreach ($memberIdsArray as $memberId) {

                $user = AppUser::where('session_id', $memberId)->first();

                if (!$user) {

                    PushVoucherLog::create([
                        'user_id'   => $memberId,
                        'reward_id' => $reward->id,
                        'push_by'   => 'pushMemberId',
                        'type'      => 'pushWallet',
                        'status'    => 'user_not_found',
                        'message'   => 'User not found'
                    ]);

                    continue;
                }

                // CHECK DUPLICATE
                $alreadyExists = UserWalletVoucher::where('reward_id', $reward->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyExists) {

                    $existingMembers[] = $memberId;

                    PushVoucherLog::create([
                        'user_id'   => $user->id,
                        'reward_id' => $reward->id,
                        'push_by'   => 'pushMemberId',
                        'type'      => 'pushWallet',
                        'status'    => 'already_assigned',
                        'message'   => 'Voucher already assigned'
                    ]);

                    continue;
                }

                // INVENTORY CHECK
                if ($reward->purchased_qty >= $reward->inventory_qty) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Voucher inventory exceeded'
                    ], 400);
                }

                // GENERATE CODES
                $receiptNo  = UserWalletVoucher::generateReceiptNo();
                $uniqueCode = UserWalletVoucher::generateUniqueVoucherCode();
                $serialNo   = UserWalletVoucher::generateSerialNo($uniqueCode, 1);

                UserWalletVoucher::create([
                    'user_id' => $user->id,
                    'reward_id' => $reward->id,
                    'qty' => 1,
                    'claimed_at' => now(),
                    'status' => 'Active',
                    'reward_status' => 'issued',
                    'receipt_no' => $receiptNo,
                    'unique_code' => $uniqueCode,
                    'serial_no' => $serialNo,
                    'push_voucher_member_id' => $pushMember->id,
                ]);

                PushVoucherLog::create([
                    'user_id'   => $user->id,
                    'reward_id' => $reward->id,
                    'push_voucher_member_id' => $pushMember->id,
                    'type'      => 'pushWallet',
                    'status'    => 'success',
                    'push_by'   => 'pushMemberId',
                    'message'   => 'Voucher pushed successfully'
                ]);

                $reward->purchased_qty += 1;
            }

            $reward->save();

            DB::commit();

            if (!empty($existingMembers)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Push completed. Already existed members: ' . implode(',', $existingMembers)
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Push voucher saved successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

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
