<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Helpers\DepartmentActivityLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fabs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FabsController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.fabs.";
        $permission_prefix = $this->permission_prefix = 'fabs';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Fab',
            'module_base_url' => url('admin/fabs')
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
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index(Request $request)
    {
        return view($this->view_file_path . "index")
            ->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * DATATABLE
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = Fabs::query();
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('active_department_id', $this->activeDeptId);
            $qb->where('active_club_location_id', $this->activeLocationId);
            $qb->where('active_role_id', $this->activeRoleId);
        }
        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'code',
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

            // $action = "<div class='d-flex gap-3'>";

            // if (Auth::user()->can($this->permission_prefix . '-edit')) {
            //     $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
            //                 <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
            //                 </a>";
            // }

            // $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
            //             <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            //             </a>";

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

            $action .= "<a target='_blank' href='" . url('admin/fabs/' . $row->id . '/activity-log') . "' 
                            class='activity-log text-primary' 
                            data-id='$row->id'
                            title='Fabs Activity Log'>
                            <i class='mdi mdi-history action-icon font-size-18'></i>
                        </a>";

            $final_data[$i] = [
                'sr_no'     => $index,
                'name'      => $row->name,
                'code'      => $row->code,
                'status'    => $row->status,
                'created_at'=> $row->created_at->format(config('safra.date-format')),
                'updated_at'=> $row->updated_at->format(config('safra.date-format')),
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
     * CREATE
     * ----------------------------------------------------- */
    public function create()
    {
        $this->layout_data['data'] = null;

        $html = view($this->view_file_path . 'add-edit-modal',
            $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {       

        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'code'   => 'required|string|max:255|unique:fabs,code',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();
        $post_data['active_department_id']      = $this->activeDeptId ?? NULL;
        $post_data['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $post_data['active_role_id']            = $this->activeRoleId ?? NULL;
        $post_data['added_by'] = Auth::user()->id;

        $fabs = Fabs::create($post_data);
        DepartmentActivityLogger::log(
            'create',
            'fab',
            $fabs->id,
            $fabs->name,
            [],
            $fabs->toArray(),
            "Fab '{$fabs->name}' Created Successfully."
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Fabs Created Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * EDIT
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Fabs::findOrFail($id);

        $html = view($this->view_file_path . 'add-edit-modal',
            $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $fab = Fabs::findOrFail($id);
        $oldData = $fab->toArray();
        
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:fabs,code,' . $id,
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();
        $post_data['active_department_id']      = $this->activeDeptId ?? NULL;
        $post_data['active_club_location_id']   = $this->activeLocationId ?? NULL;
        $post_data['active_role_id']            = $this->activeRoleId ?? NULL;

        $fab->update($post_data);

        DepartmentActivityLogger::log(
            'update',
            'fab',
            $fab->id,
            $fab->name,
            $oldData,
            $fab->fresh()->toArray(),
            "Fab '{$fab->name}' Updated Successfully."
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Fabs Updated Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * DELETE department
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        $fabs = Fabs::findOrFail($id);
        $fabs->delete();
        DepartmentActivityLogger::log(
            'delete',
            'fab',
            $fabs->id,
            $fabs->name,
            $fabs->toArray(),
            [],
            "Fab '{$fabs->name}' moved to trash."
        );
        AdminLogger::log('delete', Fabs::class, $id);
        return response()->json([
            'status' => 'success',
            'message' => 'Fabs Deleted Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * TRASH
     * ----------------------------------------------------- */
    public function trash(Request $request)
    {
        if ($request->ajax()) {
            $qb = Fabs::onlyTrashed();
            $result = $this->get_sort_offset_limit_query($request, $qb, [
                'id',
                'name',
                'code',
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

                $final_data[$i] = [
                    'sr_no'     => $index,
                    'name'      => $row->name,
                    'code'      => $row->code,
                    'status'    => $row->status,
                    'created_at'=> $row->created_at->format(config('safra.date-format')),
                    'updated_at'=> $row->updated_at->format(config('safra.date-format')),
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
        // Fabs::withTrashed()->findOrFail($id)->restore();
        $fab = Fabs::withTrashed()->findOrFail($id);
        $fab->restore();
        DepartmentActivityLogger::log(
            'restore',
            'fab',
            $fab->id,
            $fab->name,
            [],
            [],
            "fab '{$fab->name}' Restored Successfully."
        );
        return response()->json([
            'status'  => 'success',
            'message' => 'Fabs Restored Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * FORCE DELETE
     * ----------------------------------------------------- */
    public function forceDelete($id)
    {
        // Fabs::withTrashed()->findOrFail($id)->forceDelete();
        $fab = Fabs::withTrashed()->findOrFail($id);
        $fabName = $fab->name;
        $fabData = $fab->toArray();
        $fab->forceDelete();
        DepartmentActivityLogger::log(
            'force_delete',
            'fab',
            $id,
            $fabName,
            $fabData,
            [],
            "fab '{$fabName}' permanently deleted."
        );
        return response()->json([
            'status'  => 'success',
            'message' => 'Fabs Permanently Deleted'
        ]);
    }
}
