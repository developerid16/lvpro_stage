<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $qb = Department::query();

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
            'status' => 'required|in:Active,Inactive',
        ]);

        Department::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Department Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Department::findOrFail($id);
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
        Department::where('id', $id)->delete();
        AdminLogger::log('delete', Department::class, $id);
        return response()->json(['status' => 'success', 'message' => 'Department Deleted Successfully']);
    }
}
