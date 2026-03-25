<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {


        $this->view_file_path = "admin.user.";
        $permission_prefix = $this->permission_prefix = 'cms-user';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'User',
            'module_base_url' => url('admin/user')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
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
        // $query->with("roles")->whereHas('roles', function($query) {
        //     $query->where('name', '!=', 'Super Admin');
        // });
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('added_by', Auth::user()->id);
        }

        $searched_from_relation = ['roles' => ['name']];
        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'email', 'phone', 'status'], $searched_from_relation, ['roles' => ['roles']]);

        $rows = $query['data']->get();

        $deptIds = $rows->flatMap(fn($user) => $user->roles->pluck('department'))
                        ->filter()
                        ->unique()
                        ->values();

        $departments = \App\Models\Department::whereIn('id', $deptIds)->pluck('name', 'id');

        $final_data = [];
        foreach ($rows as $key => $row) {
            $final_data[$key]['sr_no']   = $key + 1;
            $final_data[$key]['name']    = $row->name;
            $final_data[$key]['email']   = $row->email;
            $final_data[$key]['phone']   = $row->phone;
            $final_data[$key]['status']  = $row->status;

            // Roles badges
            $roles = $row->roles->pluck('name')->toArray();
            $roles = implode("</span><span class='badge badge-pill badge-soft-success font-size-11 me-1 mt-2'>", $roles);
            $final_data[$key]['roles'] = (!empty($roles))
                ? "<span class='badge badge-pill badge-soft-success font-size-11 me-1 mt-2'>" . $roles . "</span>"
                : null;

            $userDeptIds = $row->roles->pluck('department')->filter()->unique();
            $deptBadges  = $userDeptIds->map(function($id) use ($departments) {
                $name = $departments[$id] ?? null;
                return $name
                    ? "<span class='badge badge-pill badge-soft-info font-size-11 me-1 mt-2'>$name</span>"
                    : null;
            })->filter()->implode('');

            $final_data[$key]['department'] = !empty($deptBadges) ? $deptBadges : '-';

            $action = "<div class='d-flex gap-3'>";
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }
            $final_data[$key]['action'] = $action . "</div>";
        }

        $data          = [];
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
        $validator = Validator::make($request->all(), [

            'name'   => 'required|string|max:191',

            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],

            'phone'  => 'required|unique:users,phone',
            'status' => 'required',
            'role'   => 'required|array|min:1',

           'password' => [
                'required',
                function ($attribute, $value, $fail) {

                    $errors = [];

                    if (strlen($value) < 8) {
                        $errors[] = 'at least 8 characters';
                    }

                    if (!preg_match('/[A-Z]/', $value)) {
                        $errors[] = 'one uppercase letter';
                    }

                    if (!preg_match('/[a-z]/', $value)) {
                        $errors[] = 'one lowercase letter';
                    }

                    if (!preg_match('/[0-9]/', $value)) {
                        $errors[] = 'one number';
                    }

                    if (!preg_match('/[@$!%*#?&]/', $value)) {
                        $errors[] = 'one special character';
                    }

                    if (!empty($errors)) {
                        $fail('Password must contain ' . implode(', ', $errors) . '.');
                    }
                }
            ],

        ], [

            // Required Messages
            'name.required'   => 'Name is required.',
            'email.required'  => 'Email is required.',
            'phone.required'  => 'Phone Number is required.',
            'status.required' => 'Status is required.',
            'role.required'   => 'Role is required.',
            'password.required' => 'Password is required.',

            // Unique Messages
            'email.unique' => 'Email already exists.',
            'phone.unique' => 'Phone Number already exists.',

            // Password Strength
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.mixed'     => 'Password must contain uppercase and lowercase letters.',
            'password.letters'   => 'Password must contain letters.',
            'password.numbers'   => 'Password must contain numbers.',
            'password.symbols'   => 'Password must contain special characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        $roles = $data['role'];
        unset($data['role']);

        $user = User::create($data);
        $user->assignRole($roles);

        return response()->json([
            'status'  => true,
            'message' => 'User Created Successfully'
        ]);
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => [
                'required',
                Rule::unique('users', 'email')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],

            'phone' => [
                'required',
                Rule::unique('users', 'phone')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'status' => 'required',
            'role' => 'required',
            
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'Email already exists.',
            'phone.required' => 'Phone is required.',
            'phone.unique' => 'Phone already exists.',
            'status.required' => 'Status is required.',
            'role.required' => 'Role is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $request->validate([
            'password' => [
                'nullable',
                function ($attribute, $value, $fail) {

                    $errors = [];

                    if (strlen($value) < 8) {
                        $errors[] = 'at least 8 characters';
                    }

                    if (!preg_match('/[A-Z]/', $value)) {
                        $errors[] = 'one uppercase letter';
                    }

                    if (!preg_match('/[a-z]/', $value)) {
                        $errors[] = 'one lowercase letter';
                    }

                    if (!preg_match('/[0-9]/', $value)) {
                        $errors[] = 'one number';
                    }

                    if (!preg_match('/[@$!%*#?&]/', $value)) {
                        $errors[] = 'one special character';
                    }

                    if (!empty($errors)) {
                        $fail('Password must contain ' . implode(', ', $errors) . '.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();

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

        $user = User::findOrFail($id);
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

    public function trash(Request $request)
    {
        if ($request->ajax()) {
            $query = User::query()->onlyTrashed();
    
            $searched_from_relation = ['roles' => ['name']];
            $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'email', 'phone', 'status'], $searched_from_relation, ['roles' => ['roles']]);
    
            $rows = $query['data']->get();
    
            $deptIds = $rows->flatMap(fn($user) => $user->roles->pluck('department'))
                            ->filter()
                            ->unique()
                            ->values();
    
            $departments = \App\Models\Department::whereIn('id', $deptIds)->pluck('name', 'id');
    
            $final_data = [];
            foreach ($rows as $key => $row) {
                $final_data[$key]['sr_no']   = $key + 1;
                $final_data[$key]['name']    = $row->name;
                $final_data[$key]['email']   = $row->email;
                $final_data[$key]['phone']   = $row->phone;
                $final_data[$key]['status']  = $row->status;
    
                // Roles badges
                $roles = $row->roles->pluck('name')->toArray();
                $roles = implode("</span><span class='badge badge-pill badge-soft-success font-size-11 me-1 mt-2'>", $roles);
                $final_data[$key]['roles'] = (!empty($roles))
                    ? "<span class='badge badge-pill badge-soft-success font-size-11 me-1 mt-2'>" . $roles . "</span>"
                    : null;
    
                $userDeptIds = $row->roles->pluck('department')->filter()->unique();
                $deptBadges  = $userDeptIds->map(function($id) use ($departments) {
                    $name = $departments[$id] ?? null;
                    return $name
                        ? "<span class='badge badge-pill badge-soft-info font-size-11 me-1 mt-2'>$name</span>"
                        : null;
                })->filter()->implode('');
    
                $final_data[$key]['department'] = !empty($deptBadges) ? $deptBadges : '-';

                $final_data[$key]['action'] = "<div class='d-flex gap-3'>
                                        <a href='javascript:void(0)' class='restore_btn' data-id='{$row->id}'>
                                            <i class='mdi mdi-restore text-success action-icon font-size-18'></i>
                                        </a>
                                        <a href='javascript:void(0)' class='force_delete_btn' data-id='{$row->id}'>
                                            <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                                        </a>
                                    </div>";
            }
    
            $data          = [];
            $data['items'] = $final_data;
            $data['count'] = $query['count'];
            return $data;
        }
        return view($this->view_file_path . "trash")->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * RESTORE
     * ----------------------------------------------------- */
    public function restore($id)
    {
        User::withTrashed()->findOrFail($id)->restore();
 
        return response()->json([
            'status'  => 'success',
            'message' => 'User Restored Successfully'
        ]);
    }
 
    /* -----------------------------------------------------
     * FORCE DELETE
     * ----------------------------------------------------- */
    public function forceDelete($id)
    {
        User::withTrashed()->findOrFail($id)->forceDelete();
 
        return response()->json([
            'status'  => 'success',
            'message' => 'User Permanently Deleted'
        ]);
    }

    
}
