<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ClubLocation;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.department.";
        $permission_prefix = $this->permission_prefix = 'department';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Department',
            'module_base_url' => url('admin/departments')
        ];
        
        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index(Request $request)
    {
        $query = ClubLocation::orderBy('name', 'ASC');
        // ✅ Super Admin = all records, Others = only their own
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('added_by', Auth::user()->id);
        }
        $this->layout_data['club_locations'] = $query->get();
        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    /* -----------------------------------------------------
     * DATATABLE AJAX
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = Department::query();
        // ✅ Super Admin = all records, Other users = only their own records
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('added_by', Auth::user()->id);
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

            $createdAt = $row->created_at->format(config('safra.date-format'));
            $updatedAt = $row->updated_at->format(config('safra.date-format'));

            $status = $row->status == 'Active'
                ? "<span class='badge bg-success'>Active</span>"
                : "<span class='badge bg-danger'>Inactive</span>";

            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary font-size-18'></i></a>";
            }

            $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'><i class='mdi mdi-delete text-danger font-size-18'></i></a>";
            $action .= "</div>";

            $final_data[$i] = [
                'sr_no'      => $index,
                'name'       => $row->name,
                'club_location' => $row->clubLocation ? $row->clubLocation->name : '-',
                'status'     => $status,
                'created_at' => $createdAt,
                'action'     => $action,
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
        $this->layout_data['club_locations'] = ClubLocation::orderBy('name', 'ASC')->get();
        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * STORE department
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
            'club_location_id' => 'nullable|exists:club_locations,id',
            'status' => 'required|in:Active,Inactive',
        ]);
        $post_data['added_by'] = Auth::user()->id;
        Department::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Department Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Department::findOrFail($id);
        $this->layout_data['club_locations'] = ClubLocation::orderBy('name', 'ASC')->get();
        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * UPDATE department
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $post_data = $this->validate($request, [
            'name'   => 'required|string|max:255',
            'club_location_id' => 'nullable|exists:club_locations,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        $department->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Department Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE department
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->delete();
        AdminLogger::log('delete', Department::class, $id);
        return response()->json([
            'status' => 'success',
            'message' => 'Department Deleted Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * TRASH
     * ----------------------------------------------------- */
    public function trash(Request $request)
    {
        if ($request->ajax()) {

            $qb = Department::onlyTrashed();

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

                $createdAt = $row->created_at->format(config('safra.date-format'));
                $updatedAt = $row->updated_at->format(config('safra.date-format'));

                $status = $row->status == 'Active'
                    ? "<span class='badge bg-success'>Active</span>"
                    : "<span class='badge bg-danger'>Inactive</span>";

                $action = "<div class='d-flex gap-3'>";

                if (Auth::user()->can($this->permission_prefix . '-edit')) {
                    $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary font-size-18'></i></a>";
                }

                $final_data[$i] = [
                    'sr_no'      => $index,
                    'name'       => $row->name,
                    'club_location' => $row->clubLocation ? $row->clubLocation->name : '-',
                    'status'     => $status,
                    'created_at' => $createdAt,
                    'action'     => "<div class='d-flex gap-3'>
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
        Department::withTrashed()->findOrFail($id)->restore();
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Department Restored Successfully'
        ]);
    }
 
    /* -----------------------------------------------------
     * FORCE DELETE
     * ----------------------------------------------------- */
    public function forceDelete($id)
    {
        Department::withTrashed()->findOrFail($id)->forceDelete();
 
        return response()->json([
            'status'  => 'success',
            'message' => 'Department Permanently Deleted'
        ]);
    }
}
