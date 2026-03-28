<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Helpers\DepartmentActivityLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClubLocation;
use App\Models\RewardLocation;
use App\Models\RewardLocationUpdate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClubLocationController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.club-location.";
        $permission_prefix = $this->permission_prefix = 'club-location';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Club Location',
            'module_base_url' => url('admin/club-location')
        ];

       

        $this->middleware("active.permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("active.permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("active.permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("active.permission:$permission_prefix-delete", ['only' => ['destroy']]);

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
     * LIST PAGE (Merchant → Club Location list)
     * ----------------------------------------------------- */
    public function index()
    {
        // $this->layout_data['merchant_id'] = $merchant;

        return view($this->view_file_path . "index")
            ->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * DATATABLE
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        // $qb = ClubLocation::where('merchant_id', $request->merchant_id);
        $qb = ClubLocation::query();
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('active_department_id', $this->activeDeptId);
            $qb->where('active_club_location_id', $this->activeLocationId);
            $qb->where('active_role_id', $this->activeRoleId);
        }
        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
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

            $action .= "<a target='_blank' href='" . url('admin/club-location/' . $row->id . '/activity-log') . "' 
                            class='activity-log text-primary' 
                            data-id='$row->id'
                            title='Club Location Activity Log'>
                            <i class='mdi mdi-history action-icon font-size-18'></i>
                        </a>";
            $final_data[$i] = [
                'sr_no'      => $index,
                'name'       => $row->name,
                'status'     => $row->status,
                'code'     => $row->code,
                'created_at' =>  $row->created_at->format(config('safra.date-format')),
                'updated_at' =>  $row->updated_at->format(config('safra.date-format')),

                'action' => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }

    /* -----------------------------------------------------
     * CREATE FORM
     * ----------------------------------------------------- */
    public function create($merchant)
    {
        $this->layout_data['merchant_id'] = $merchant;
        $this->layout_data['data'] = null;

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255',
            'status'      => 'required|in:Active,Inactive',
        ], [
            'merchant_id.required' => 'Merchant is required',
            'merchant_id.exists'   => 'Invalid merchant selected',
            'name.required'        => 'Name is required',
            'code.required'        => 'Code is required',
            'status.required'      => 'Status is required',
            'status.in'            => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // validated data
        $post_data = $validator->validated();
        $post_data['added_by'] = Auth::user()->id;
        $post_data['active_department_id']      = $this->activeDeptId ?? NULL;
        $post_data['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $post_data['active_role_id']            = $this->activeRoleId ?? NULL;
        $clublocation = ClubLocation::create($post_data);
        DepartmentActivityLogger::log(
            'create',
            'club-location',
            $clublocation->id,
            $clublocation->name,
            [],
            $clublocation->toArray(),
            "Club Location '{$clublocation->name}' Created Successfully."
        );

        return response()->json(['status' => 'success', 'message' => 'Location Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT FORM
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $location = ClubLocation::findOrFail($id);

        $this->layout_data['data'] = $location;
        $this->layout_data['merchant_id'] = $location->merchant_id; // FIX

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json([
            'status' => 'success',
            'html'   => $html
        ]);
    }


    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'code'   => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ], [
            'name.required'   => 'Name is required',
            'code.required'   => 'Code is required',
            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // validated data
        $post_data = $validator->validated();
        $post_data['active_department_id']      = $this->activeDeptId ?? NULL;
        $post_data['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $post_data['active_role_id']            = $this->activeRoleId ?? NULL;
        $clubLocation = ClubLocation::findOrFail($id);
        $oldData = $clubLocation->toArray();
        $clubLocation->update($post_data);
        DepartmentActivityLogger::log(
            'update',
            'club-location',
            $clubLocation->id,
            $clubLocation->name,
            $oldData,
            $clubLocation->fresh()->toArray(),
            "Club Location '{$clubLocation->name}' Updated Successfully."
        );

        return response()->json(['status' => 'success', 'message' => 'Location Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        $location = ClubLocation::findOrFail($id);
        $location->delete();
        DepartmentActivityLogger::log(
            'delete',
            'club-location',
            $location->id,
            $location->name,
            $location->toArray(),
            [],
            "Club Location '{$location->name}' moved to trash."
        );
        AdminLogger::log('delete', ClubLocation::class, $id);
        return response()->json([
            'status' => 'success',
            'message' => 'Location Deleted Successfully'
        ]);
    }

    public function trash(Request $request)
    {
        if ($request->ajax()) {

            // ✅ ONLY deleted records
            $qb = ClubLocation::onlyTrashed();

            $result = $this->get_sort_offset_limit_query($request, $qb, [
                'id',
                'name',
                'status',
                'created_at',
                'updated_at',
                'deleted_at'
            ]);

            $rowsQueryBuilder = $result['data'];
            $startIndex = $result['offset'] ?? 0;

            $final_data = [];
            $i = 0;

            foreach ($rowsQueryBuilder->get() as $row) {

                $index = $startIndex + $i + 1;

                $final_data[$i] = [
                    'sr_no' => $index,
                    'name'  => $row->name,
                    'code'  => $row->code,
                    'status'=> $row->status,
                    'deleted_at' => $row->deleted_at 
                        ? $row->deleted_at->format(config('safra.date-format')) 
                        : '',

                    // ✅ Restore + Permanent Delete
                    'action' => "
                        <div class='d-flex gap-3'>
                            <a href='javascript:void(0)' class='restore_btn' data-id='{$row->id}'>
                                <i class='mdi mdi-restore text-success action-icon font-size-18'></i>
                            </a>
                            <a href='javascript:void(0)' class='force_delete_btn' data-id='{$row->id}'>
                                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                            </a>
                        </div>
                    ",
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

    public function restore($id)
    {
        // ClubLocation::withTrashed()->findOrFail($id)->restore();

        $clublocation = ClubLocation::withTrashed()->findOrFail($id);
        $clublocation->restore();
        DepartmentActivityLogger::log(
            'restore',
            'club-location',
            $clublocation->id,
            $clublocation->name,
            [],
            [],
            "Club Location '{$clublocation->name}' Restored Successfully."
        );
        return response()->json([
            'status' => 'success',
            'message' => 'Location Restored Successfully'
        ]);
    }
    public function forceDelete($id)
    {
        // ClubLocation::withTrashed()->findOrFail($id)->forceDelete();
        $clublocation = ClubLocation::withTrashed()->findOrFail($id);
        $clublocationName = $clublocation->name;
        $clublocationData = $clublocation->toArray();
        $clublocation->forceDelete();
        DepartmentActivityLogger::log(
            'force_delete',
            'clublocation',
            $id,
            $clublocationName,
            $clublocationData,
            [],
            "Club Location '{$clublocationName}' permanently deleted."
        );
        return response()->json([
            'status' => 'success',
            'message' => 'Location Permanently Deleted'
        ]);
    }




}
