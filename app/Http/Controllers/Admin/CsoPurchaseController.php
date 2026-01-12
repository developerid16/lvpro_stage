<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CsoPurchaseController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.cso-purchase.";
        $permission_prefix = $this->permission_prefix = 'cso-purchase';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'CSO - Purchase',
            'module_base_url' => url('admin/cso-purchase')
        ];       
    }
  

    public function index(Request $request)
    {
        $query = Reward::where('type','0')->withTrashed(); 

        // IMPORTANT: allow 0
        if ($request->filled('reward_type')) {
            $query->where('reward_type', (int) $request->reward_type);
        }

        $this->layout_data['rewards'] = $query->get();
        $this->layout_data['selected_type'] = $request->reward_type;

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function getMemberDetails(Request $request)
    {
        // $member = Member::where('member_id', $request->member_id)->first();

        // if (!$member) {
        //     return response()->json([
        //         'message' => 'Invalid Member ID'
        //     ], 404);
        // }

        $reward = Reward::findOrFail($request->reward_id);

        if ($reward->sales_end_date && $reward->sales_end_time) {
            $salesEnd = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $reward->sales_end_date . ' ' . $reward->sales_end_time
            )->format(config('safra.date-format'));
        }
        return response()->json([
            'member' => [
                'id'     => $member->id ?? 1,
                'name'   => $member->name ?? 'test',
                'email'  => $member->email ?? 'test',
                'mobile' => $member->mobile ?? 'test',
            ],            
            'reward' => [
                'id'    => $reward->id,
                'image' => asset('uploads/image/'.$reward->voucher_image),
                'reward_type'  => $reward->inventory_type,
                'type'  => $reward->inventory_type == 0 ? 'Digital' : 'Physical',
                'name'  => $reward->name,
                'offer' => $reward->description,
                'sales_end' => $salesEnd,
                'remaining_qty' => 12,
                'rates' => [
                    'member' => '1.00',
                    'movie'  => '1.00',
                    'bitez'  => '1.00',
                    'travel' => '1.00',
                ]
            ],
            'pricing' => [
                'subtotal'  => '1.00',
                'admin_fee'=> '0.00',
                'total'    => '1.00'
            ]
        ]);

    }

    public function checkout(Request $request)
    {
        DB::beginTransaction();

        try {

            $reward = Reward::findOrFail($request->reward_id);

            // -----------------------------------
            // DUPLICATE CHECK (IMPORTANT)
            // -----------------------------------
            $alreadyExists = Purchase::where('member_id', $request->member_id)
                ->where('reward_id', $request->reward_id)
                ->exists();

            if ($alreadyExists) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'This reward is already purchased by this member.'
                ], 422);
            }

            $receiptNo = 'RCPT-' . now()->format('YmdHis');

            $purchase = Purchase::create([
                'receipt_no'        => $receiptNo,
                'reward_id'         => $request->reward_id,
                'member_id'         => $request->member_id,
                'member_name'       => $request->member_name,
                'member_email'      => $request->member_email,
                'qty'               => $request->qty,
                'status'            => 1, // PENDING (numeric)
                'payment_mode'      => $request->payment_mode,
                'note'              => $request->note,
                'update_membership' => $request->has('update_membership') ? 1 : 0,
                'collection'        => $request->collection,
                'subtotal'          => $request->subtotal,
                'admin_fee'         => $request->admin_fee,
                'total'             => $request->total,
            ]);

            DB::commit();

            return response()->json([
                'purchase_id' => $purchase->id,
                'receipt_no'  => $receiptNo,
                'date'        => now()->format(config('safra.date-format')),
                'name'        => $reward->name,
                'type'        => $reward->reward_type == 0 ? 'Digital' : 'Physical',
                'qty'         => $purchase->qty,
                'price'       => $purchase->total,
                'payment_mode'       => $purchase->payment_mode,
                'total'       => $purchase->total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    public function complete(Request $request)
    {
        Purchase::where('id', $request->purchase_id)
            ->update(['status' => 'completed']);

        return response()->json(['status' => 'ok']);
    }

    public function cancel(Request $request)
    {
        Purchase::where('id', $request->purchase_id)
            ->update(['status' => 'cancelled']);

        return response()->json(['status' => 'ok']);
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
