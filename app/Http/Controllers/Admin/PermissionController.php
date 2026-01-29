<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.permission.";
        $permission_prefix = $this->permission_prefix = 'permission';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Permission',
            'module_base_url' => url('admin/permissions')
        ];
    }

    /* LIST */
    public function index()
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    /* DATATABLE */
    public function datatable(Request $request)
    {
        $qb = Permission::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id', 'name', 'slug', 'status', 'created_at'
        ]);

        $rows = $result['data']->get();
        $start = $result['offset'] ?? 0;

        $data = [];
        foreach ($rows as $i => $row) {
            $action = "<div class='d-flex gap-2'>
                <a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary'></i></a>
                <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'><i class='mdi mdi-delete text-danger'></i></a>
            </div>";

            $data[] = [
                'sr_no' => $start + $i + 1,
                'name'  => $row->name,
                'status'=> $row->status ? 'Active' : 'Inactive',
                'action'=> $action,
            ];
        }

        return [
            'items' => $data,
            'count' => $result['count'] ?? $qb->count(),
        ];
    }

    /* CREATE */
    public function create()
    {
        $this->layout_data['data'] = null;
        return response()->json([
            'status' => 'success',
            'html' => view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render()
        ]);
    }

    /* STORE */
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name'   => 'required|string|max:255|unique:permissions,name',
            'status' => 'required|in:Active,Inactive',
        ]);

        Permission::create($data);

        return response()->json(['status' => 'success', 'message' => 'Permission Created']);
    }

    /* EDIT */
    public function edit($id)
    {
        $this->layout_data['data'] = Permission::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'html' => view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render()
        ]);
    }

    /* UPDATE */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $data = $this->validate($request, [
            'name'   => 'required|string|max:255|unique:permissions,name,' . $id,
            'status' => 'required|in:Active,Inactive',
        ]);

        $permission->update($data);

        return response()->json(['status' => 'success', 'message' => 'Permission Updated']);
    }

    /* DELETE */
    public function destroy($id)
    {
        Permission::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Permission Deleted']);
    }
}
