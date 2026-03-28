<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\ParticipatingMerchant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ParticipatingMerchantController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.participating-merchant.";
        $permission_prefix = $this->permission_prefix = 'participating-merchant';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Participating Merchant',
            'module_base_url' => url('admin/participating-merchant')
        ];

        
        $this->middleware("active.permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("active.permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("active.permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("active.permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }


    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->roles->contains('id', 1)) {
            $this->layout_data['departments'] = Department::orderBy('name', 'ASC')->get();
        } else {
            $departmentIds = $user->roles->pluck('department')->filter();

            $this->layout_data['departments'] = Department::whereIn('id', $departmentIds)->orderBy('name', 'ASC')->get();
        }
        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    /* -----------------------------------------------------
     * DATATABLE AJAX
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = ParticipatingMerchant::query();
        // ✅ Super Admin = all records, Other users = only their own records
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('added_by', Auth::user()->id);
        }
        if (auth()->user()->role != 1) { // not Super Admin
            $qb->where('added_by', auth()->id());
        }

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex       = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {

            $index = $startIndex + $i + 1;

            $createdAt =  $row->created_at->format(config('safra.date-format'));
            $updatedAt =  $row->updated_at->format(config('safra.date-format'));

            // ACTION BUTTONS
            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }

            // Participating Merchant Location Redirect
            if (Auth::user()->can('participating-merchant-location-list')) {
                $action .= "<a href='" . url('admin/participating-merchant/' . $row->id . '/location') . "'>
                    <i class='mdi mdi-map-marker-multiple text-primary action-icon font-size-18'></i>
                </a>";
            }
            $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
            </a>";

            $action .= "</div>";

            $final_data[$i] = [
                'sr_no'      => $index,
                'name'       => $row->name,
                'department' => $row->department ? $row->department->name : '-',
                'status'     => $row->status,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
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
     * CREATE MODAL
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
            'department_id'   => 'required|exists:departments,id',
            'status' => 'required|in:Active,Inactive',
        ]);       
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();
        $post_data['added_by'] = Auth::user()->id;
        $post_data['department_id'] = $post_data['department_id'] ?: null;
        ParticipatingMerchant::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Merchant Created Successfully']);
    }


    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = ParticipatingMerchant::findOrFail($id);
        $this->layout_data['departments'] = Department::orderBy('name', 'ASC')->get();
        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }


    /* -----------------------------------------------------
     * UPDATE MERCHANT
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $merchant = ParticipatingMerchant::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'department_id'   => 'required|exists:departments,id',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();
        $post_data['department_id'] = $post_data['department_id'] ?: null;
        $merchant->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Merchant Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE MERCHANT
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        $department = ParticipatingMerchant::findOrFail($id);
        $department->delete();
        AdminLogger::log('delete', ParticipatingMerchant::class, $id);
        return response()->json([
            'status' => 'success',
            'message' => 'Participating Merchant Deleted Successfully'
        ]);
    }
    /* -----------------------------------------------------
     * PARTICIPATING MERCHANT LOCATION PAGE
     * ----------------------------------------------------- */
    public function location($id)
    {
        $this->layout_data['merchant'] = ParticipatingMerchant::findOrFail($id);

        return view($this->view_file_path . "participating-location")->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * TRASH AJAX
     * ----------------------------------------------------- */
    public function trash(Request $request)
    {
        if ($request->ajax()) {
            $qb = ParticipatingMerchant::onlyTrashed();
            $result = $this->get_sort_offset_limit_query($request, $qb, [
                'id',
                'name',
                'status',
                'created_at',
                'updated_at',
            ]);

            $rowsQueryBuilder = $result['data'];
            $startIndex       = $result['offset'] ?? 0;

            $final_data = [];
            $i = 0;

            foreach ($rowsQueryBuilder->get() as $row) {

                $index = $startIndex + $i + 1;

                $createdAt =  $row->created_at->format(config('safra.date-format'));
                $updatedAt =  $row->updated_at->format(config('safra.date-format'));

                $final_data[$i] = [
                    'sr_no'      => $index,
                    'name'       => $row->name,
                    'department' => $row->department ? $row->department->name : '-',
                    'status'     => $row->status,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
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
        ParticipatingMerchant::withTrashed()->findOrFail($id)->restore();

        return response()->json([
            'status'  => 'success',
            'message' => 'Participating Merchant Restored Successfully'
        ]);
    }

    /* -----------------------------------------------------
     * FORCE DELETE
     * ----------------------------------------------------- */
    public function forceDelete($id)
    {
        ParticipatingMerchant::withTrashed()->findOrFail($id)->forceDelete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Participating Merchant Permanently Deleted'
        ]);
    }

}
