<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Helpers\DepartmentActivityLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Reward;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
class MerchantController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.merchant.";
        $permission_prefix = $this->permission_prefix = 'merchant';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Merchant',
            'module_base_url' => url('admin/merchants')
        ];


        $this->middleware("active.permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("active.permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("active.permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("active.permission:$permission_prefix-delete", ['only' => ['destroy']]);
        $this->middleware("active.permission:$permission_prefix-activity-log", ['only' => ['activityLog']]);

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


    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index(Request $request)
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    /* -----------------------------------------------------
     * DATATABLE AJAX
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = Merchant::query();
        // ✅ Super Admin = all records, Others = only their own
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('active_department_id', $this->activeDeptId);
            $qb->where('active_club_location_id', $this->activeLocationId);
            $qb->where('active_role_id', $this->activeRoleId);
        }
        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'logo',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

            $createdAt =  $row->created_at->format(config('safra.date-format'));
            $updatedAt =  $row->updated_at->format(config('safra.date-format'));

            // -------------------------
            // ACTION BUTTONS
            // -------------------------
            // $action = "<div class='d-flex gap-3'>";

            // if (Auth::user()->can($this->permission_prefix . '-edit')) {
            //     $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            // }
           
            // $action .= "
            // <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
            //     <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            // </a>";
            
            // $action .= "</div>";
            
            $activePermissions = session('active_permissions', []);

            $canEdit   = in_array($this->permission_prefix . '-edit',   $activePermissions) || Auth::user()->hasRole('Super Admin');
            $canDelete = in_array($this->permission_prefix . '-delete', $activePermissions) || Auth::user()->hasRole('Super Admin');

            $action = "<div class='d-flex gap-3'>";

            if ($canEdit) {
                $action .= "<a href='javascript:void(0)' 
                    class='edit' 
                    data-id='$row->id'
                    title='Edit'>
                    <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                </a>";
            }

            if ($canDelete) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'>
                                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                            </a>";
            }

            $action .= "<a target='_blank' href='" . url('admin/merchants/' . $row->id . '/activity-log') . "' 
                            class='activity-log text-primary' 
                            data-id='$row->id'
                            title='Merchants Activity Log'>
                            <i class='mdi mdi-history action-icon font-size-18'></i>
                        </a>";


            $final_data[$i] = [             
                'sr_no'     => $index,
                'name'      => $row->name,
                'logo' => imagePreviewHtml("uploads/image/{$row->logo}"),
                'status'    => $row->status,
                'created_at'=> $createdAt,
                'updated_at'=> $updatedAt,
                'action'    => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }

    /* -----------------------------------------------------
     * SHOW CREATE FORM MODAL
     * ----------------------------------------------------- */
    public function create()
    {
        $this->layout_data['data'] = null;

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * STORE MERCHANT
     * ----------------------------------------------------- */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'logo'   => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'   => 'Merchant name is required',
            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
            'logo.required'   => 'Logo is required',
            'logo.image'      => 'Logo must be an image',
            'logo.mimes'      => 'Allowed formats: jpg, jpeg, png, webp',
            'logo.max'        => 'Logo size must be less than 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // -------------------------
        // Upload logo
        // -------------------------
        $path = public_path('uploads/image');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file = $request->file('logo');
        // $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filename = generateHashFileName($file);
        $file->move($path, $filename);

        // -------------------------
        // Store merchant
        // -------------------------
        $merchant = Merchant::create([
            'name'   => $request->name,
            'status' => $request->status,
            'logo'   => $filename,
            'active_department_id'      => $this->activeDeptId ?? NULL,
            'active_club_location_id'   => $this->activeLocationId ?? NULL,
            'active_role_id'            => $this->activeRoleId ?? NULL,
        ]);
        DepartmentActivityLogger::log(
            'create',
            'merchant',
            $merchant->id,
            $merchant->name,
            [],
            $merchant->toArray(),
            "Merchant '{$merchant->name}' Created Successfully."
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Merchant Created Successfully'
        ]);
    }


    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Merchant::findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * UPDATE MERCHANT
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $merchant = Merchant::findOrFail($id);
        $oldData = $merchant->toArray();
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'logo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'   => 'Merchant name is required',
            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
            'logo.image'      => 'Logo must be an image',
            'logo.mimes'      => 'Allowed formats: jpg, jpeg, png, webp',
            'logo.max'        => 'Logo size must be less than 2MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // -------------------------
        // Prepare update data
        // -------------------------
        $post_data = [
            'name'   => $request->name,
            'status' => $request->status,
            'active_department_id'      => $this->activeDeptId ?? NULL,
            'active_club_location_id'   => $this->activeLocationId ?? NULL,
            'active_role_id'            => $this->activeRoleId ?? NULL,
        ];

        // -------------------------
        // Upload logo if provided
        // -------------------------
        if ($request->hasFile('logo')) {

            $path = public_path('uploads/image');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // delete old image
            if ($merchant->logo && file_exists(public_path($merchant->logo))) {
                unlink(public_path($merchant->logo));
            }

            $file = $request->file('logo');
            // $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filename = generateHashFileName($file);

            $file->move($path, $filename);

            $post_data['logo'] = $filename;
        }

        // -------------------------
        // Update merchant
        // -------------------------
        $merchant->update($post_data);
        DepartmentActivityLogger::log(
            'update',
            'merchant',
            $merchant->id,
            $merchant->name,
            $oldData,
            $merchant->fresh()->toArray(),
            "Merchant '{$merchant->name}' Updated Successfully."
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Merchant Updated Successfully'
        ]);
    }


    /* -----------------------------------------------------
     * DELETE MERCHANT
     * ----------------------------------------------------- */
    // public function destroy($id)
    // {
    //     // suspend rewards using this merchant
    //     Reward::where('merchant_id', $id)->update([
    //         'suspend_voucher' => 1,
    //         'suspend_deal' => 1,
    //         'merchant_id' => null
    //     ]);

    //     Merchant::where('id', $id)->delete();

    //     AdminLogger::log('delete', Merchant::class, $id);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Merchant Deleted Successfully'
    //     ]);
    // }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $merchant = Merchant::find($id);

            if (!$merchant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Merchant not found'
                ], 404);
            }

            // Suspend rewards and detach merchant
            Reward::where('merchant_id', $id)->update([
                'suspend_voucher' => 1,
                'suspend_deal' => 1,
                'merchant_id' => null
            ]);

            // Soft delete merchant
            $merchant->delete();
            DepartmentActivityLogger::log(
                'delete',
                'merchant',
                $merchant->id,
                $merchant->name,
                $merchant->toArray(),
                [],
                "Merchant '{$merchant->name}' moved to trash."
            );

            AdminLogger::log('delete', Merchant::class, $id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Merchant moved to trash successfully'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /* -----------------------------------------------------
     * LOCATION PAGE
     * ----------------------------------------------------- */
    public function location($id)
    {
        $this->layout_data['merchant'] = Merchant::findOrFail($id);
        return view($this->view_file_path . "location")->with($this->layout_data);
    }


    public function trash(Request $request)
    {
        if ($request->ajax()) {
            $qb = Merchant::query()->onlyTrashed();
            $result = $this->get_sort_offset_limit_query($request, $qb, [
                'id',
                'name',
                'logo',
                'status',
                'created_at',
                'updated_at',
            ]);
    
            $rowsQueryBuilder = $result['data'];
            $startIndex = $result['offset'] ?? 0;
    
            $final_data = [];
            $i = 0;
    
            foreach ($rowsQueryBuilder->get() as $row) {
                $index = $startIndex + $i + 1;
    
                $createdAt =  $row->created_at->format(config('safra.date-format'));
                $updatedAt =  $row->updated_at->format(config('safra.date-format'));
    
                $final_data[$i] = [             
                    'sr_no'     => $index,
                    'name'      => $row->name,
                    'logo' => imagePreviewHtml("uploads/image/{$row->logo}"),
                    'status'    => $row->status,
                    'created_at'=> $createdAt,
                    'updated_at'=> $updatedAt,
                    'action'    => "<div class='d-flex gap-3'>
                                            <a href='javascript:void(0)' class='restore_btn' data-id='{$row->id}'>
                                                <i class='mdi mdi-restore text-success action-icon font-size-18'></i>
                                            </a>
                                            <a href='javascript:void(0)' class='force_delete_btn' data-id='{$row->id}'>
                                                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                                            </a>
                                        </div>",
                ];
    
                $i++;
            }
    
            return [
                'items' => $final_data,
                'count' => $result['count'] ?? $rowsQueryBuilder->count(),
            ];
        }

        return view($this->view_file_path . "trash")->with($this->layout_data);

    }


    /* -----------------------------------------------------
     * RESTORE
     * ----------------------------------------------------- */
    public function restore($id)
    {
        // Merchant::withTrashed()->findOrFail($id)->restore();
        $merchant = Merchant::withTrashed()->findOrFail($id);
        $merchant->restore();
        DepartmentActivityLogger::log(
            'restore',
            'merchant',
            $merchant->id,
            $merchant->name,
            [],
            [],
            "Merchant '{$merchant->name}' Restored Successfully."
        );
        return response()->json([
            'status'  => 'success',
            'message' => 'Merchant Restored Successfully'
        ]);
    }

    public function forceDelete($id)
    {
        DB::beginTransaction();

        try {
            $merchant = Merchant::withTrashed()->findOrFail($id);

            // Optional: check if still linked somewhere
            $hasRewards = Reward::where('merchant_id', $id)->exists();

            if ($hasRewards) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Merchant still linked to rewards.'
                ], 400);
            }

            // 👉 Add future cleanup here if needed
            $merchantName = $merchant->name;
            $merchantData = $merchant->toArray();
            $merchant->forceDelete();

            AdminLogger::log('force_delete', Merchant::class, $id);
            DepartmentActivityLogger::log(
                'force_delete',
                'merchant',
                $id,
                $merchantName,
                $merchantData,
                [],
                "Merchant '{$merchantName}' permanently deleted."
            );
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Merchant permanently deleted'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function activityLog($record_id)
    {
        $department_activity_logs = DB::table('department_activity_logs')
            ->where('record_id', $record_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->layout_data['logs']      = $department_activity_logs;
        $this->layout_data['record_id'] = $record_id;

        return view("admin.activity-log")->with($this->layout_data);
    }
}
