<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomLocation;
use App\Models\Merchant;
use App\Models\ParticipatingLocations;
use App\Models\ParticipatingMerchant;
use App\Models\ParticipatingMerchantLocation;
use App\Models\Reward;
use App\Models\RewardParticipatingMerchantLocationUpdate;
use App\Models\RewardUpdateRequest;
use App\Models\RewardVoucher;
use App\Models\UserWalletVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel; // THIS is correct
use App\Rules\SingleCodeColumnFile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EvoucherFeaturedController extends Controller
{
    
    public function __construct()
    {

        $this->view_file_path = "admin.evoucher-featured.";
        $permission_prefix = $this->permission_prefix = 'evoucher-featured';

       $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'E-Voucher Management Listing',
            'module_base_url'   => url('admin/evoucher-featured'),
        ];

        $this->middleware("permission:$permission_prefix|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $type = $request->type === 'campaign-voucher' ? 'campaign-voucher' : 'normal-voucher';
        $this->layout_data['type'] = $type;
        $this->layout_data['category'] = Category::get();
        $this->layout_data['merchants'] = Merchant::where('status', 'Active')->get();
        $this->layout_data['memberReward'] = Reward::where('cso_method',1)->get();
        $this->layout_data['parameterReward'] = Reward::where('cso_method',2)->get();
        $this->layout_data['participating_merchants'] = ParticipatingMerchant::where('status', 'Active')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '1');
        
        if (auth()->user()->role != 1) {
            $query->where('added_by', auth()->id());
            }
            
        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';

           $inventoryQty = (float) ($row->inventory_qty ?? 0);
            $purchasedQty = (float) ($row->purchased_qty ?? 0);
            $totalRedeemed = (float) ($row->total_redeemed ?? 0);

            $final_data[$key]['quantity'] = number_format($inventoryQty, 0, '.', '');
            $final_data[$key]['balance'] = number_format($inventoryQty - $purchasedQty, 0, '.', '');
            $final_data[$key]['total_redeemed'] = number_format($totalRedeemed, 0, '.', '');
            $redeemed = UserWalletVoucher::where('reward_id', $row->id)
            ->where('status', 'used')
            ->count();
            $issued = UserWalletVoucher::where('reward_id', $row->id)
            ->where('status', 'issued')
            ->count();
            $final_data[$key]['issuance']       = number_format($issued);

            $final_data[$key]['redeemed'] = max(0, $redeemed);
            $duration = $row->created_at->format(config('safra.date-format'));

            $final_data[$key]['image'] = imagePreviewHtml('uploads/image/' . $row->voucher_image);

            $start = $row->publish_start_date;
            $end   = $row->publish_end_date;

            $startDate = $start ? \Carbon\Carbon::parse($start) : null;
            $endDate   = $end ? \Carbon\Carbon::parse($end) : null;

            // block zero-date (-0001-11-30)
            $isValidStart = $startDate && $startDate->year > 0;
            $isValidEnd   = $endDate && $endDate->year > 0;

            if ($isValidStart && $isValidEnd) {
                $duration =
                    $startDate->format(config('safra.date-only')) .
                    ' to ' .
                    $endDate->format(config('safra.date-only'));
            } elseif ($isValidStart) {
                $duration = $startDate->format(config('safra.date-only'));
            } else {
                $duration = '-';
            }

            $final_data[$key]['duration'] = $duration;

            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));

            $action = "<div class='d-flex gap-3'>";
            // if (Auth::user()->can($this->permission_prefix . '-edit')) {
                // $action .= "<a href='javascript:void(0)' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            // }
            // if (Auth::user()->can($this->permission_prefix . '-delete')) {
                // $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            // }
            
            // if (Auth::user()->can($this->permission_prefix . '-stock-adjustment')) {

               $current_qty = ((float)($row->inventory_qty ?? 0)) - ((float)($row->purchased_qty ?? 0));

                $action .= "<a href='javascript:void(0)' 
                                class='stock-adjustment'  
                                data-id='{$row->id}'
                                data-name='{$row->name}'
                                data-inventory='{$row->inventory_qty}'
                                data-purchased='{$current_qty}'
                                data-hide='{$row->hide_catalogue}'
                                data-hide-time='{$row->hide_cat_time}'
                                title='Stock Adjustment'>
                                <i class='mdi mdi-warehouse text-info action-icon font-size-18'></i>
                            </a>";
            // }

            $final_data[$key]['hide_catalogue'] = '-';
            if (Auth::user()->can('hide-from-catalogue-voucher')) {
                $final_data[$key]['hide_catalogue'] = '
                    <div class="form-check form-switch m-0 text-center">
                        <input class="form-check-input hide-catalogue-switch" type="checkbox" data-id="'.$row->id.'" '.($row->hide_catalogue ? 'checked' : '').'>
                    </div>';
            }

            $final_data[$key]['is_featured'] = '-';

            if (Auth::user()->can('is-featured-voucher')) {
                $final_data[$key]['is_featured'] = '
                    <div class="form-check form-switch m-0 text-center">
                        <input class="form-check-input featured-toggle-switch" type="checkbox"  data-id="'.$row->id.'" '.($row->is_featured ? 'checked' : '').'>
                    </div>';
            }
        

            $final_data[$key]['action'] = $action . "</div>";
        }
        $data          = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
    }
    /**
     * Display a listing of the resource.
     */
   
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

    function normalizeTime($time)
    {
        if (!$time) return null;
        return substr($time, 0, 5);  // Keep only "HH:MM"
    }

    /**
     * Show the form for editing the specified resource.
     */
  


    public function toggleHideCatalogue(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:rewards,id',
            'status' => 'required|boolean'
        ]);

        $reward = Reward::findOrFail($request->id);

        $reward->update([
            'hide_catalogue' => $request->status,
            'hide_cat_time'  => now()
        ]);


        return response()->json([
            'status' => true,
            'message'    => 'Hide catalogue successfully updated'
        ]);
    }


    
    public function toggleFeatured(Request $request)
    {
        $reward = Reward::findOrFail($request->id);

        $reward->update([
            'is_featured' => $request->is_featured
        ]);

        return response()->json(['status' => true]);
    }

}
