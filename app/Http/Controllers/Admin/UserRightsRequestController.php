<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\NewAdminRegister;
use App\Models\User;
use App\Models\UserAccessRequest;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class UserRightsRequestController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.user-rights.";
        $permission_prefix = $this->permission_prefix = 'user-rights';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'User Rights Request',
            'module_base_url' => url('admin/user-rights')
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
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = UserAccessRequest::query()
            ->orderBy('created_at', 'desc');

        $result = $this->get_sort_offset_limit_query(
            $request,
            $query,
            ['name', 'email', 'status', 'created_at']
        );

        $rows  = $result['data'];
        $start = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $index = $start + $i + 1;

            $final_data[$i] = [
                'id'          => $row->id,
                'sr_no'       => $index,
                'name'        => $row->name,
                'email'       => $row->email,
                'description' => $row->description ?? '-',
                'status'      => ucfirst($row->status),
                'created_at'  => optional($row->created_at)
                                    ->format(config('shilla.date-format')),
            ];

            // ---------------- ACTIONS ----------------
            $action = "<div class='d-flex gap-2 justify-content-center'>";

            if ($row->status === 'pending') {

               $action .= "<a href='javascript:void(0)' 
                    class='approve_btn'
                    data-id='{$row->id}'
                    data-name='" . e($row->name) . "'
                    data-email='" . e($row->email) . "'
                    title='Approve'>
                    <i class='mdi mdi-check-circle text-success font-size-18'></i>
                </a>";

                $action .= "<a href='javascript:void(0)' 
                    class='reject_btn'
                    data-id='{$row->id}'
                    title='Reject'>
                    <i class='mdi mdi-close-circle text-danger font-size-18'></i>
                </a>";
            } else {
                $action .= "<span class='text-muted'>—</span>";
            }

            $final_data[$i]['action'] = $action . "</div>";

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'],
        ];
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
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       
    }

    public function reorder(Request $request)
    {
       
    }


    public function approve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:user_access_requests,id',
            'name'       => 'required|string|max:255',
            'status'     => 'required|in:Active,Disabled,Lockout',
            'role'       => 'required|array|min:1',
        ], [
            'name.required'   => 'Name is required',
            'status.required' => 'Status is required',
            'role.required'   => 'Please select at least one role',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $accessRequest = UserAccessRequest::findOrFail($request->request_id);

        // CREATE USER
        $password = Str::random(10);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $accessRequest->email,
            'password' => Hash::make($password),
            'status'   => $request->status,
        ]);

        // ASSIGN ROLES
        $user->assignRole($request->role);

        // UPDATE REQUEST STATUS
        $accessRequest->update(['status' => 'approved']);

        // SEND EMAIL
        try {
            Mail::to($user->email)->send(
                new NewAdminRegister([
                    'name'     => $user->name,
                    'password' => $password
                ])
            );
        } catch (\Throwable $e) {
            // optional: log error
        }

        return response()->json(['status' => 'success']);
    }


    public function reject($id)
    {
        UserAccessRequest::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Request rejected');
    }
   
}
