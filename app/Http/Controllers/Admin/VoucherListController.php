<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Reward;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
           
            $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";

        }

        // SUSPEND
        if (Auth::user()->can($this->permission_prefix . '-edit')) {
            $action .= '
            <div class="form-check form-switch m-0">
                <input class="form-check-input suspend-switch"
                    type="checkbox"
                    data-id="'.$row->id.'"
                    '.($row->suspend_voucher ? 'checked' : '').'
                    title="Suspend">
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
