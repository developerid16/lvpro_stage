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
            'title' => 'Voucher List',
            'module_base_url' => url('admin/voucher-list')
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
        $qb = Reward::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'type',
            'reward_type',
            'created_at',
            'updated_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

            $typeLabel = match ((string)$row->type) {
                '0' => 'Treats & Deals',
                '1' => 'E-Voucher',
                '2' => 'Birthday Voucher',
                default => '-',
            };

            $rewardTypeLabel = match ((string)$row->reward_type) {
                '0' => 'Digital',
                '1' => 'Physical',
                default => '-',
            };

            $createdAt = $row->created_at->format(config('safra.date-format'));
            $updatedAt = $row->updated_at->format(config('safra.date-format'));

            // ACTIONS
            $action = "<div class='d-flex gap-3'>";

            // VIEW
            $action .= "<a href='javascript:void(0)' class='view' data-id='{$row->id}'>
                            <i class='mdi mdi-eye text-info font-size-18'></i>
                        </a>";

            // EDIT
            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                                <i class='mdi mdi-pencil text-primary font-size-18'></i>
                            </a>";
            }

            // DELETE
            $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";

            // SUSPEND SWITCH
            $checked = $row->suspend_voucher == 1 ? 'checked' : '';
            $action .= "
                <div class='form-check form-switch'>
                    <input class='form-check-input suspend-switch'
                        type='checkbox'
                        data-id='{$row->id}'
                        {$checked}>
                </div>
            ";

            $action .= "</div>";

            $final_data[] = [
                'sr_no'       => $index,
                'name'        => $row->name,
                'type'        => $typeLabel,
                'reward_type' => $rewardTypeLabel,
                'created_at'  => $createdAt,
                'updated_at'  => $updatedAt,
                'action'      => $action,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
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
