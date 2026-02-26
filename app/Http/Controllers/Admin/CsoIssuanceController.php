<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\UserWalletVoucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CsoIssuanceController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.cso-issuance.";
        $permission_prefix = $this->permission_prefix = 'cso-issuance';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'CSO Issuance',
            'module_base_url' => url('admin/cso-issuance')
        ];       
    }
  

    public function index(Request $request)
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $query = Reward::where('type',  '1')->where('is_draft', 0)->where('cso_method', 0);

        $query = $this->get_sort_offset_limit_query($request, $query, ['code', 'name', 'no_of_keys', 'quantity', 'status', 'total_redeemed']);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no']      = $key + 1;
            $final_data[$key]['code']       = $row->code;
            $final_data[$key]['name']       = $row->name;
            $final_data[$key]['reward_type'] = ($row->reward_type == 1) ? 'Physical' : 'Digital';

            $final_data[$key]['quantity']       = number_format($row->inventory_qty);
            $final_data[$key]['total_redeemed'] = number_format($row->total_redeemed);


            $redeemed = UserWalletVoucher::where('reward_id', $row->id)
                ->where('status', 'used')
                ->count();

            $final_data[$key]['redeemed'] = max(0, $redeemed);
            $duration = $row->created_at->format(config('safra.date-format'));

            // if (!empty($row->voucher_image)) {
            //     $imgUrl = asset("uploads/image/{$row->voucher_image}");

            //     $final_data[$key]['image'] = '
            //         <a href="'.$imgUrl.'" target="_blank">
            //             <img src="'.$imgUrl.'"
            //                 class="avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle"
            //                 alt="Voucher Image">
            //         </a>';
            // } else {
            //     $imgUrl = asset("uploads/image/no-image.png");
            //     $final_data[$key]['image'] = '<img src="'.$imgUrl.'" class="avatar-sm me-3 mx-lg-auto mb-3 mt-1 float-start float-lg-none rounded-circle" alt="Voucher Image">';
            // }
            $final_data[$key]['image'] = imagePreviewHtml("uploads/image/{$row->voucher_image}");


            if ($row->publish_start_date && $row->publish_end_date) {
                $duration =
                    Carbon::parse($row->publish_start_date)->format(config('safra.date-only')) .
                    ' to ' .
                    Carbon::parse($row->publish_end_date)->format(config('safra.date-only'));

            } elseif ($row->publish_start_date) {
                $duration =
                    Carbon::parse($row->publish_start_date)->format(config('safra.date-only')) .
                    ' - No Expiry';
            } else {
                $duration = 'No Expiry';
            }

            $final_data[$key]['duration']   = $duration;
            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));
            $final_data[$key]['is_draft'] = $row->is_draft == 1 ? 'Yes' : 'No';

            $final_data[$key]['status'] = $row->status;
            $methods = [
                0 => 'CSO Issuance',
                1 => 'Push by Member ID',
                2 => 'Push by Parameter',
                3 => 'Push by API SRP',
                4 => 'App/Web',
            ];

            $final_data[$key]['cso_method'] = $methods[$row->cso_method] ?? '-';

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
        //
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
    public function show(Tier $tier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
         
      
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
      
    }
  
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       
    }
}
