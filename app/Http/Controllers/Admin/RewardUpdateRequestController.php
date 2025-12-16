<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\NewAdminRegister;
use App\Models\ParticipatingLocations;
use App\Models\Reward;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\User;
use App\Models\UserAccessRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class RewardUpdateRequestController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.reward-update-request.";
        $permission_prefix = $this->permission_prefix = 'reward-update-request';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Reward Update Request',
            'module_base_url' => url('admin/reward-update-request')
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

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = RewardUpdateRequest::query()
            ->orderBy('created_at', 'desc');

        $result = $this->get_sort_offset_limit_query(
            $request,
            $query,
            [
                'name',
                'status',
                'month',
                'inventory_qty',
                'voucher_value',
                'created_at'
            ]
        );

        $rows  = $result['data'];
        $start = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $index = $start + $i + 1;

            $final_data[$i] = [
                'id'                => $row->id,
                'sr_no'             => $index,

                // BASIC INFO
                'reward_id'         => $row->reward_id,
                'month'             => $row->month,
                'name'              => $row->name,
                'description'       => $row->description ?? '-',

                // INVENTORY
                'inventory_type'    => $row->inventory_type == 0 ? 'Inventory Quantity' : 'File',
                'inventory_qty'     => $row->inventory_qty ?? '-',

                // VALUE
                'voucher_value'     => $row->voucher_value,
                'voucher_set'       => $row->voucher_set,

                // CLEARING
                'clearing_method'   => $this->getClearingMethodLabel($row->clearing_method),
                'location_text'     => $row->location_text ?? '-',

                // FLAGS
                'hide_quantity'     => $row->hide_quantity ? 'Yes' : 'No',
                'low_stock_1'       => $row->low_stock_1,
                'low_stock_2'       => $row->low_stock_2,

                // STATUS
                'status'            => ucfirst($row->status),

                // META
                'created_at'        => optional($row->created_at)->format(config('shilla.date-format')),
            ];

            // ---------------- ACTIONS ----------------
            $action = "<div class='d-flex gap-2 justify-content-center'>";

            if ($row->status === 'pending') {

                $action .= "<a href='javascript:void(0)' 
                    class='approve_btn'
                    data-id='{$row->id}'
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


    private function getClearingMethodLabel($method)
    {
        return match ((int) $method) {
            0 => 'QR',
            1 => 'Barcode',
            2 => 'External Code',
            3 => 'External Link',
            4 => 'Merchant Code',
            default => '-',
        };
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
        DB::beginTransaction();

        try {

            $update = RewardUpdateRequest::findOrFail($request->id);
            $reward = Reward::findOrFail($update->reward_id);

            $reward->update([
                'month'              => $update->month,
                'voucher_image'      => $update->voucher_image,
                'name'               => $update->name,
                'description'        => $update->description,
                'term_of_use'        => $update->term_of_use,
                'how_to_use'         => $update->how_to_use,

                'merchant_id'        => $update->merchant_id,
                'reward_type'        => $update->reward_type,

                'voucher_validity'   => $update->voucher_validity,
                'club_location'      => $update->club_location,
                'inventory_type'     => $update->inventory_type,
                'inventory_qty'      => $update->inventory_qty,

                'voucher_value'      => $update->voucher_value,
                'voucher_set'        => $update->voucher_set,
                'clearing_method'    => $update->clearing_method,

                'location_text'      => $update->location_text,
                'participating_merchant_id' => $update->participating_merchant_id,
                'hide_quantity'      => $update->hide_quantity,
                'low_stock_1'        => $update->low_stock_1,
                'low_stock_2'        => $update->low_stock_2,
            ]);

            $update->update([
                'status'      => 'approved',
            ]);


            if ($update->clearing_method == 2) {

                // Remove old actual locations
                ParticipatingLocations::where('reward_id', $reward->id)->delete();
                // Copy from pending table
                $pendingLocations = RewardParticipatingMerchantLocationUpdate::where('reward_id', $reward->id)->get();

                foreach ($pendingLocations as $loc) {
                    ParticipatingLocations::create([
                        'reward_id'                 => $reward->id,
                        'participating_merchant_id' => $loc->participating_merchant_id,
                        'location_id'               => $loc->location_id,
                        'is_selected'               => 1,
                    ]);
                }

                // Optional cleanup
                RewardParticipatingMerchantLocationUpdate::where('reward_id',$reward->id)->delete();
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reward updated successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function reject($id)
    {
        RewardUpdateRequest::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Request rejected');
    }
   
}
