<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\NewAdminRegister;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchantLocation;
use App\Models\Reward;
use App\Models\RewardLocation;
use App\Models\RewardLocationUpdate;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\User;
use App\Models\UserAccessRequest;
use Carbon\Carbon;
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
      
        $query = RewardUpdateRequest::with('requester')->orderBy('created_at', 'desc');

        $filters = json_decode($request->get('filter'), true) ?? [];

        if (!empty($filters['requester'])) {
            $query->whereHas('requester', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['requester'] . '%');
            });
        }

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

     

        foreach ($rows->get() as $key => $row) {

            $index = $start + $i + 1;

            $fromMonth = $row->from_month
                ? Carbon::createFromFormat('Y-m', $row->from_month)
                    ->format(config('safra.month-format'))
                : null;

            $toMonth = $row->to_month
                ? Carbon::createFromFormat('Y-m', $row->to_month)
                    ->format(config('safra.month-format'))
                : null;

            if ($fromMonth && $toMonth) {
                $month = $fromMonth . ' To ' . $toMonth;
            } elseif ($fromMonth) {
                $month = $fromMonth;
            } else {
                $month = '-';
            }
            $final_data[$i] = [
                'id'                => $row->id,
                'sr_no'             => $index,

                'reward_type' => Reward::getRewardTypeLabel($row->type),

                'reward_id'         => $row->reward_id,
                'month'            =>  $month,
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
                'requester' => optional($row->requester)->name ?? '-',
                'created_at'        => optional($row->created_at)->format(config('safra.date-format')),
            ];

            // ---------------- ACTIONS ----------------
            $action = "<div class='d-flex gap-2 justify-content-center'>";

            $action .= "<a href='javascript:void(0)'
                class='view_btn'
                data-id='{$row->id}'
                title='View'>
                <i class='mdi mdi-eye text-primary font-size-18'></i>
            </a>";

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
            4 => 'External Code',
            3 => 'External Link',
            2 => 'Merchant Code',
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
    public function show($id)
    {
        $data = RewardUpdateRequest::with([
            'requester',
            'reward',
            'tierRates.tier:id,tier_name','rewardLocations','participatingLocations','customLocation:id,name'
        ])->findOrFail($id);

        $data['locations'] = RewardLocationUpdate::with('location:id,name')
            ->where('reward_id', $data->reward_id)
            ->get()
            ->map(function ($loc) {
                return [
                    'id'            => $loc->location?->id,
                    'name'          => $loc->location?->name,
                    'inventory_qty' => $loc->inventory_qty,
                    'total_qty'     => $loc->total_qty,
                ];
            });

        if ($data['locations']->isEmpty()) {

            $data['locations'] = RewardLocation::with('location:id,name')
                ->where('reward_id', $data->reward_id)
                ->get()
                ->map(function ($loc) {
                    return [
                        'id'            => $loc->location?->id,
                        'name'          => $loc->location?->name,
                        'inventory_qty' => $loc->inventory_qty,
                        'total_qty'     => $loc->total_qty,
                    ];
                });
        }

            
        $pendingLocations = RewardParticipatingMerchantLocationUpdate::where('reward_id', $data->reward_id)->get();

        if ($pendingLocations->isNotEmpty()) {

            $data['merchant_locations'] = ParticipatingMerchantLocation::whereIn( 'id', $pendingLocations->pluck('location_id'))
            ->select('id', 'name')->get()
            ->map(function ($loc) {
                return [
                    'id'   => $loc->id,
                    'name' => $loc->name,
                ];
            });

        } else {
            $pendingLocations = ParticipatingLocations::where('reward_id', $data->reward_id)->get();
            $data['merchant_locations'] = ParticipatingMerchantLocation::whereIn( 'id', $pendingLocations->pluck('location_id') )
            ->select('id', 'name')->get()
            ->map(function ($loc) {
                return [
                    'id'   => $loc->id,
                    'name' => $loc->name,
                ];
            });
        }


        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
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
            if (!$request->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID is missing'
                ], 422);
            }

            $update = RewardUpdateRequest::findOrFail($request->id);
            $reward = Reward::findOrFail($update->reward_id);
            $type = (int) $update->type; // 0  = treat&deals, 1 = eVoucher, 2 = Birthday

          
            if ($type == '1') {//evocuher

                $reward->update([
                    'voucher_image'        => $update->voucher_image ?? $reward->voucher_image,
                    'voucher_detail_img'   => $update->voucher_detail_img ?? $reward->voucher_detail_img,

                    'name'                 => $update->name,
                    'description'          => $update->description,
                    'term_of_use'          => $update->term_of_use,
                    'how_to_use'           => $update->how_to_use,

                    'merchant_id'          => $update->merchant_id,
                    'reward_type'          => $update->reward_type,
                    'type'                 => 1,

                    'direct_utilization'   => $update->direct_utilization,
                    
                    'publish_start_date'   => $update->publish_start_date,
                    'publish_start_time'   => $update->publish_start_time,
                    'publish_end_date'     => $update->publish_end_date,
                    'publish_end_time'     => $update->publish_end_time,
                    
                    'sales_start_date'     => $update->sales_start_date,
                    'sales_start_time'     => $update->sales_start_time,
                    'sales_end_date'       => $update->sales_end_date,
                    'sales_end_time'       => $update->sales_end_time,
                    
                    'voucher_validity'     => $update->voucher_validity,
                    'friendly_url'         => $update->friendly_url,
                    
                    'max_quantity'    => (int) ($update->max_quantity ?? 0),
                    'category_id'     => (int) ($update->category_id ?? 0),
                    'inventory_type'  => (int) ($update->inventory_type ?? 0),
                    'inventory_qty'   => $update->inventory_qty !== null ? (int)$update->inventory_qty : 0,
                    'voucher_value'   => (float) ($update->voucher_value ?? 0),
                    'voucher_set'     => (int) ($update->voucher_set ?? 0),
                    'set_qty'         => (int) ($update->set_qty ?? 0),
                    'clearing_method' => (int) ($update->clearing_method ?? 0),


                    'location_text'        => $update->location_text,
                    'participating_merchant_id' => $update->participating_merchant_id,

                    'hide_quantity'        => $update->hide_quantity,
                    'low_stock_1'          => $update->low_stock_1,
                    'low_stock_2'          => $update->low_stock_2,

                    'suspend_deal'         => $update->suspend_deal,
                    'suspend_voucher'      => $update->suspend_voucher,
                    'csvFile'               => $update->csvFile,
                    'is_featured' =>        $update->is_featured,
                    'is_draft'             => 0,
                ]);
                $update->update([
                    'status' => 'approved',
                ]);


            }else if($type == '2'){//birthday

                $reward->update([
                    'from_month' => $update->from_month,
                    'to_month' => $update->to_month,
                    'voucher_image'        => $update->voucher_image ?? $reward->voucher_image,
                    'voucher_detail_img'   => $update->voucher_detail_img ?? $reward->voucher_detail_img,
                    'name' => $update->name,
                    'description' => $update->description,
                    'term_of_use' => $update->term_of_use,
                    'how_to_use' => $update->how_to_use,
                    'merchant_id' => $update->merchant_id,
                    'reward_type' => $update->reward_type,
                    'voucher_validity' => $update->voucher_validity,
                    'inventory_type'  => (int) ($update->inventory_type ?? 0),
                    'inventory_qty'   => $update->inventory_qty !== null ? (int)$update->inventory_qty : 0,
                    'voucher_value'   => (float) ($update->voucher_value ?? 0),
                    'voucher_set'     => (int) ($update->voucher_set ?? 0),
                    'set_qty'         => (int) ($update->set_qty ?? 0),
                    'clearing_method' => (int) ($update->clearing_method ?? 0),
                    'location_text' => $update->location_text,
                    'participating_merchant_id' => $update->participating_merchant_id,
                    'hide_quantity' => $update->hide_quantity,
                    'low_stock_1' => $update->low_stock_1,
                    'low_stock_2' => $update->low_stock_2,
                ]);

                $update->update([
                    'status' => 'approved',
                ]);
                
            }else if($type == '0'){//treat&deals
                
                $reward->update([
                    'voucher_image'        => $update->voucher_image ?? $reward->voucher_image,
                    'voucher_detail_img'   => $update->voucher_detail_img ?? $reward->voucher_detail_img,
                    'name'                 => $update->name,
                    'description'          => $update->description,
                    'term_of_use'          => $update->term_of_use,
                    'how_to_use'           => $update->how_to_use,
                    'merchant_id'          => $update->merchant_id,
                    'reward_type'          => $update->reward_type,
                    'type'                 => 0,
                    'direct_utilization'   => $update->direct_utilization,
                    'publish_start_date'   => $update->publish_start_date,
                    'publish_start_time'   => $update->publish_start_time,
                    'publish_end_date'     => $update->publish_end_date,
                    'publish_end_time'     => $update->publish_end_time,
                    'sales_start_date'     => $update->sales_start_date,
                    'sales_start_time'     => $update->sales_start_time,
                    'sales_end_date'       => $update->sales_end_date,
                    'sales_end_time'       => $update->sales_end_time,
                    'voucher_validity'     => $update->voucher_validity,
                    
                    'friendly_url'         => (int) ($update->friendly_url),
                    'club_classification_id' => (int) ($update->club_classification_id),
                    'fabs_category_id'       => (int) ($update->fabs_category_id),
                    'smc_classification_id'  => (int) ($update->smc_classification_id),
                    'ax_item_code'           => (int) ($update->ax_item_code),
                    'usual_price'         => (int) ($update->usual_price),
                    
                    'max_quantity'    => (int) ($update->max_quantity ?? 0),
                    'category_id'     => (int) ($update->category_id ?? 0),
                    'inventory_type'  => (int) ($update->inventory_type ?? 0),
                    'inventory_qty'   => $update->inventory_qty !== null ? (int)$update->inventory_qty : 0,
                    'voucher_value'   => (float) ($update->voucher_value ?? 0),
                    'voucher_set'     => (int) ($update->voucher_set ?? 0),
                    'set_qty'         => (int) ($update->set_qty ?? 0),
                    'clearing_method' => (int) ($update->clearing_method ?? 0),

                    'location_text'        => $update->location_text,
                    'participating_merchant_id' => $update->participating_merchant_id,
                    'hide_quantity'        => $update->hide_quantity,
                    'low_stock_1'          => $update->low_stock_1,
                    'low_stock_2'          => $update->low_stock_2,
                    'suspend_deal'         => $update->suspend_deal,
                    'suspend_voucher'      => $update->suspend_voucher,
                    'is_featured' =>        $update->is_featured,
                    'publish_independent'    => $update->publish_independent ?? 0,
                    'publish_inhouse'        => $update->publish_inhouse ?? 0,
                    'send_reminder'          => $update->send_reminder ?? 0,        
                    'where_use'          => $update->where_use,
                    'is_draft'             => 0,   // 🔑 important
                    'csvFile'               => $update->csvFile,

                ]);

                if ($reward->reward_type == '1') {

                    // Delete old rows
                    $pendingLocations = RewardLocationUpdate::where('reward_id', $reward->id)->get();
                    

                    RewardLocation::where('reward_id', $reward->id)->delete();

                    foreach ($pendingLocations as $loc) {
                        RewardLocation::create([                           

                            'reward_id'     => $reward->id,
                            'merchant_id'   => $loc['merchant_id'],
                            'location_id'   => $loc['location_id'],
                            'is_selected'   => $loc['is_selected'] ?? 1,
                            'inventory_qty' => $loc['inventory_qty'] ?? 0,
                            'total_qty' => $loc['inventory_qty'] ?? 0,
                        ]);
                    }

                    RewardLocationUpdate::where(
                        'reward_id',
                        $reward->id
                    )->delete();
                }

                $update->update([
                    'status' => 'approved',
                ]);


            }

            if ($update->clearing_method == '2') {
    
                $pendingLocations = RewardParticipatingMerchantLocationUpdate::where(
                    'reward_id',
                    $reward->id
                )->get();

                ParticipatingLocations::where('reward_id', $reward->id)->delete();

                foreach ($pendingLocations as $loc) {
                    ParticipatingLocations::create([
                        'reward_id' => $reward->id,
                        'participating_merchant_id' => $loc->participating_merchant_id,
                        'location_id' => $loc->location_id,
                        'is_selected' => $loc->is_selected ?? 1,
                    ]);
                }

                RewardParticipatingMerchantLocationUpdate::where(
                    'reward_id',
                    $reward->id
                )->delete();
            }        

            if($reward->is_draft != 1){
                $update->update([
                    'status' => 'approved',
                ]);
                $reward->update([
                    'is_draft' => 0,
                ]);    
            }   
            
            if ($reward->hide_catalogue == 1) {

                if (!$reward->hide_cat_time) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Hide time not found'
                    ], 400);
                }

                $allowTime = Carbon::parse($reward->hide_cat_time)->addMinutes(60);

                if (now()->lt($allowTime)) {

                    $remainingMinutes = now()->diffInMinutes($allowTime);

                    // return response()->json([
                    //     'status'  => false,
                    //     'message' => "You can update inventory after {$remainingMinutes} minutes"
                    // ], 400);
                }else{
                    // After 60 minutes → allow update and reset hide
                    $reward->update([
                        'hide_catalogue' => 0,
                        'hide_cat_time'  => null
                    ]);
                }

            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Reward updated successfully',
                'data' => $update
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),   // 👈 ACTUAL ERROR
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }


    public function reject($id)
    {
        RewardUpdateRequest::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Request rejected');
    }
   
}
