<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Support\Facades\Auth;

class VoucherListController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.voucher-list.";
        $permission_prefix = $this->permission_prefix = 'voucher-list';

       $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Voucher List',
            'reward_base_url'   => url('admin/voucher-list'),
            'module_base_url'   => url('admin/reward'),
        ];

        
        $this->middleware("active.permission:$permission_prefix|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable']]);
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
        return view($this->view_file_path . "index")->with($this->layout_data);
    }


    /* -----------------------------------------------------
     * DATATABLE AJAX
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $query = Reward::where('is_draft',0);
        if (!Auth::user()->hasRole('Super Admin')) {
            $query->where('active_department_id', $this->activeDeptId);
            $query->where('active_club_location_id', $this->activeLocationId);
            $query->where('active_role_id', $this->activeRoleId);
        }
        $query = $this->get_sort_offset_limit_query($request, $query, ['name','status']);

        $final_data = [];
       foreach ($query['data']->get() as $key => $row) {
        $action = '<div class="d-flex justify-content-center align-items-center gap-2">';

        // VIEW (always)
        $action .= '
        <button type="button"
            class="btn btn-link p-0 view"
            data-id="'.$row->id.'"
            title="View">
            <i class="mdi mdi-eye text-info action-icon font-size-18"></i>
        </button>';

        // EDIT
        if (Auth::user()->can($this->permission_prefix . '-edit')) {
           
        $url = $row->type == 0 
            ? url('admin/reward/' . $row->id . '/edit') 
            : ($row->type == 1 
                ? url('admin/evoucher/' . $row->id . '/edit') 
                : ($row->type == 2 
                    ? url('admin/birthday-voucher/' . $row->id . '/edit') 
                    : '#'
                )
            );
            $action .= "<a href='javascript:void(0)' class='edit' data-url='$url' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";

        }

        // SUSPEND
        if (Auth::user()->can($this->permission_prefix . '-edit')) {
            $action .= '
            <div class="form-check form-switch m-0">
                <input class="form-check-input suspend-switch"
                    type="checkbox"
                    data-id="'.$row->id.'"
                    '.($row->suspend_voucher ? 'checked' : '').'
                    title="Suspend Deal">
            </div>';
        }

        // DELETE
        if (Auth::user()->can($this->permission_prefix . '-delete')) {
            $action .= '
            <button type="button"
                class="btn btn-link p-0 delete_btn"
                data-id="'.$row->id.'"
                title="Delete">
                <i class="mdi mdi-delete text-danger action-icon font-size-18"></i>
            </button>';
        }

        $action .= '</div>';
        
            $final_data[] = [
                'sr_no'       => $key + 1,
                'name'        => $row->name,
                'type'        => match ((string)$row->type) {
                    '0' => 'Treats & Deals','1' => 'E-Voucher','2' => 'Birthday Voucher', default => '-',
                },
                'reward_type' => match ((string)$row->reward_type) {
                    '0' => 'Digital','1' => 'Physical', default => '-',
                },
                'created_at'  => $row->created_at->format(config('safra.date-format')),
                'updated_at'  => $row->updated_at->format(config('safra.date-format')),
                'action' => $action

            ];
        }

        return [
            'items' => $final_data,
            'count' => $query['count'] ?? 0,
        ];
    }

    

  
    // DELETE
    public function destroy($id)
    {
        // Reward::where('id', $id)->delete();
        AdminLogger::log('delete', Reward::class, $id);

        return response()->json([
            'status' => true,
            'msg'    => 'Voucher deleted'
        ]);
    }

    // VIEW (MODAL DATA)
    public function show($id)
    {
        $reward = Reward::findOrFail($id);
        return response()->json([
            'status' => true,
            'data'   => $reward
        ]);
    }
    // SUSPEND / UNSUSPEND
    public function toggleSuspend(Request $request)
    {
        Reward::where('id', $request->id)
            ->update(['suspend_voucher' => $request->status ? 1 : 0]);

        return response()->json([
            'status' => true,
            'msg'    => 'Voucher status updated'
        ]);
    }


}
