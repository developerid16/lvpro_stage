<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Models\User;

use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Mail\NewAdminRegister;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {


        $this->view_file_path = "admin.user.";
        $permission_prefix = $this->permission_prefix = 'user';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'User',
            'module_base_url' => url('admin/user')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {


        $this->layout_data['role'] = Role::where('name', '!=', 'Super Admin')->get();
        // $this->layout_data['role'] = Role::get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = User::query();
        $query->with("roles")->whereHas('roles', function($query) {
            $query->where('name', '!=', 'Super Admin');
         });



        $searched_from_relation = ['roles' => ['name']];
        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'email', 'phone', 'status',], $searched_from_relation, ['roles' => ['roles']]);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['email'] = $row->email;
            $final_data[$key]['phone'] = $row->phone;
            $final_data[$key]['status'] = $row->status;

            $roles = $row->roles->pluck('name')->toArray();
            $roles = implode("</span><span class='badge badge-pill badge-soft-success font-size-11 me-1'>", $roles);
            $final_data[$key]['roles']  = (!empty($roles)) ? "<span class='badge badge-pill badge-soft-success font-size-11 me-1'>" . $roles : null;

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
        $post_data = $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users,email',
           'password' => 'nullable|string|min:6|max:20',
            'phone' => 'required|unique:users,phone',
            'status' => 'required',
            'role' => 'required',
        ]);


        $password = $request->password;
        $post_data['password'] = Hash::make($password);
        $role = $post_data['role'];
        unset($post_data['role']);

        $user = User::create($post_data);

        try {
            //code...
            $data['password'] = $password;
            $data['name'] =  $user->name;
            Mail::to($user->email)->send(
                new NewAdminRegister($data)
            );
        } catch (\Throwable $th) {
            //throw $th;
            // return response()->json(['status' => false, "msg" => "Something went wrong.",]);
        }

        $user->assignRole($role);

        return response()->json(['status' => 'success', 'message' => 'User Created Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->layout_data['data'] = User::with('roles')->find($id);
        $this->layout_data['role'] = Role::where('name', '!=', 'Super Admin')->get();
        $this->layout_data['assign_roles'] = $this->layout_data['data']->roles->pluck('name');

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post_data = $this->validate($request, [
            'name' => 'required',
            'email' => "required|unique:users,email,$id",
            'phone' => "required|unique:users,phone,$id",
            'status' => 'required',
            'role' => 'required',
            'password' => 'nullable|string|min:6|max:20',
        ]);

         // Update password only if provided
        if (!empty($post_data['password'])) {
            $post_data['password'] = Hash::make($post_data['password']);
        } else {
            unset($post_data['password']);
        }
        // if (!empty($post_data['password'])) {
        //     $post_data['password'] = Hash::make($post_data['password']);
        // } else {
        //     unset($post_data['password']);
        // }
        $role = $post_data['role'];
        unset($post_data['role']);

        $user = User::find($id);
        $user->update($post_data);
        $user->syncRoles($role);

        return response()->json(['status' => 'success', 'message' => 'User Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::where('id', $id)->delete();
        AdminLogger::log('delete', User::class, $id);
        return response()->json(['status' => 'success', 'message' => 'User Delete Successfully']);
    }

    
}
