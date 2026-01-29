<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Models\RoleHasPermission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {

        $this->view_file_path = "admin.roles.";
        $permission_prefix = $this->permission_prefix = 'role';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Role',
            'module_base_url' => url('admin/roles')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $this->layout_data['permission'] = Permission::where('status','Active')->get();
        $this->layout_data['department'] = Department::where('status','Active')->get();


        return view($this->view_file_path . 'index')->with($this->layout_data);
    }

    public function datatable(Request $request)
    {

        $query = Role::query();
        $query->with(['permissions'])->where('name', '!=', 'super admin');
        $searched_from_relation = ['permissions' => ['name']];
        // $query = $this->get_sort_offset_limit_query($request, $query, ['status',], $searched_from_relation, ['user' => ['name', 'unique_id'], 'reward' => ['reward']], $relationSort);
        $query = $this->get_sort_offset_limit_query($request, $query, ['name'], $searched_from_relation, ['permissions' => ['permissions']] );

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['name'] = $row->name;

            $permission = null;
            if (!empty($row->permissions)) {
                foreach ($row->permissions as $permissions_row) {
                    $permission .= "<span class='badge badge-pill badge-soft-success font-size-11 me-1'>$permissions_row->name</span>";
                }
            }
            $final_data[$key]['permissions']  = $permission;

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            $final_data[$key]['action'] = $action . "</div>";
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'department' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::create(['name' => $request->input('name'), 'department' => $request->input('department'), 'status' => $request->input('status')]);
        $role->syncPermissions($request->input('permission'));
        return response()->json(['status' => 'success', 'message' => 'Role Created Successfully']);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->layout_data['data'] = Role::find($id);
        $this->layout_data['permission'] = Permission::where('status','Active')->get();
        $this->layout_data['department'] = Department::where('status','Active')->get();
        $this->layout_data['rolePermissions'] = RoleHasPermission::where("role_id", $id)->pluck('permission_id')->toArray();

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'department' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->department = $request->input('department');
        $role->status = $request->input('status');
        $role->save();
        $role->syncPermissions($request->input('permission'));
        return response()->json(['status' => 'success', 'message' => 'Role Update Successfully']);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Role::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Role Delete Successfully']);
    }
}
