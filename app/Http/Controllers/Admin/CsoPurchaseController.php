<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentTransaction;
use App\Models\Tier;
use App\Models\Reward;
use App\Models\VoucherLog;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Purchase;
use App\Models\RewardLocation;
use App\Models\RewardTierRate;
use App\Models\RewardVoucher;
use App\Models\UserWalletVoucher;
use App\Models\VoucherLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $query = Reward::with('merchant:id,name,logo')
            ->whereNull('deleted_at')
            ->where('type', '0')
            ->where('is_draft', 0)
            ->where('hide_catalogue', 0)
            ->orderByRaw("
                CASE 
                    WHEN CONCAT(sales_end_date,' ',IFNULL(sales_end_time,'23:59:59')) >= NOW() 
                    THEN 0 
                    ELSE 1 
                END
            ")
            ->orderBy('sales_end_date', 'asc');


        if ($request->filled('reward_type')) {
            $query->where('reward_type', (int) $request->reward_type);
        }

        $rewards = $query->get();

        $totalCount = $rewards->count(); // ✅ total records

        foreach ($rewards as $reward) {

            if ((int)$reward->reward_type === 1) {

                $reward->pending_collection = UserWalletVoucher::where('reward_id', $reward->id)
                    ->where('reward_status', 'purchased')
                    ->where('status', 'Active')
                    ->count();

                $reward->club_total_qty = RewardLocation::where('reward_id', $reward->id)
                    ->sum('inventory_qty');

                $reward->club_total_stock = RewardLocation::where('reward_id', $reward->id)
                    ->sum('total_qty');

                $reward->total_sold = UserWalletVoucher::where('reward_id', $reward->id)->count();

                $reward->left_qty = $reward->club_total_qty - $reward->total_sold;
            }

            if ((int)$reward->reward_type === 0) {

                $reward->club_total_qty = $reward->inventory_qty ?? 0;
                $reward->total_sold     = $reward->purchased_qty ?? 0;
                $reward->left_qty       = $reward->club_total_qty - $reward->total_sold;
                $reward->pending_collection = 0;
            }
        }

        $this->layout_data['rewards'] = $rewards;
        $this->layout_data['selected_type'] = $request->reward_type;
        $this->layout_data['totalCount'] = $totalCount; // ✅ send to view

        return view($this->view_file_path . "index")
            ->with($this->layout_data);
    }


    public function getMemberDetails(Request $request)
    {
        $reward = Reward::findOrFail($request->reward_id);

        $expired = reward_expired($reward);

        if ($expired) {
            return response()->json([
                'success' => false,
                'message' => 'Reward has expired.'
            ], 400); 
        }
        /*
        |--------------------------------------------------------------------------
        | SALES END FORMAT
        |--------------------------------------------------------------------------
        */
        $salesEnd = null;

        if ($reward->sales_end_date && $reward->sales_end_time) {
            $salesEnd = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $reward->sales_end_date . ' ' . $reward->sales_end_time
            )->format(config('safra.date-format'));
        }

        /*
        |--------------------------------------------------------------------------
        | REMAINING QTY
        |--------------------------------------------------------------------------
        */
        if ((int)$reward->reward_type === 1) {
            // Physical
            $totalQty = RewardLocation::where('reward_id', $reward->id)
                ->sum('inventory_qty');
            $remainingQty = RewardLocation::where('reward_id', $reward->id)
                ->sum('total_qty');

            // $soldQty = UserWalletVoucher::where('reward_id', $reward->id)->count();
        } else {
            // Digital
            $totalQty = $reward->inventory_qty ?? 0;
            $soldQty  = $reward->purchased_qty ?? 0;
            $remainingQty = $totalQty - $soldQty;
        }

        /*
        |--------------------------------------------------------------------------
        | TIER RATES
        |--------------------------------------------------------------------------
        */
        $tierRates = RewardTierRate::with('tier:id,tier_name')
            ->where('reward_id', $reward->id)
            ->get();

        $rates = [];

        foreach ($tierRates as $rate) {
            if ($rate->tier) {
                $rates[strtolower($rate->tier->tier_name)] = number_format($rate->price, 2);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PRICING CALCULATION
        |--------------------------------------------------------------------------
        */
        // Example: take first tier price as subtotal
        $subtotal = $tierRates->first()->price ?? 0;
        $adminFee = 0;
        $total    = $subtotal + $adminFee;

        return response()->json([

            'member' => [
                'id'     => $request->member_id ?? 1,
                'name'   => 'test',
                'email'  => 'test',
                'mobile' => 'test',
            ],

            'reward' => [
                'id'            => $reward->id,
                // 'image'         => $reward->voucher_image
                //     ? asset('uploads/image/'.$reward->voucher_image)
                //     : asset("uploads/image/no-image.png"),
                'image'         => imageExists('uploads/image/'.$reward->voucher_image),
                'reward_type'   => $reward->inventory_type,
                'type'          => $reward->inventory_type == 0 ? 'Digital' : 'Physical',
                'name'          => $reward->name,
                'offer'         => strip_tags($reward->description),
                'sales_end'     => $salesEnd,
                'remaining_qty' => max($remainingQty, 0),
                'rates'         => $rates,
                'usual_price' => $reward->usual_price ?? 0,
            ],

            'pricing' => [
                'subtotal'  => number_format($subtotal, 2),
                'admin_fee' => number_format($adminFee, 2),
                'total'     => number_format($total, 2),
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
                // return response()->json([
                //     'status'  => 'error',
                //     'message' => 'This reward is already purchased by this member.'
                // ], 422);
            }

            $receiptNo =   UserWalletVoucher::generateReceiptNo();
            $purchase = Purchase::create([
                'receipt_no'        => $receiptNo,
                'reward_id'         => $request->reward_id,
                'member_id'         => $request->member_id,
                'member_name'       => $request->member_name,
                'member_email'      => $request->member_email,
                'qty'               => $request->qty,
                'status'            => 'pending', // PENDING (numeric)
                'payment_mode'      => $request->payment_mode,
                'note'              => $request->note,
                'update_membership' => $request->has('update_membership') ? 1 : 0,
                'collection'        => $request->remain_qty,
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
        DB::beginTransaction();

        try {

            $purchase = Purchase::where('id', $request->purchase_id)
                ->where('status', '!=', 'completed')
                ->lockForUpdate()
                ->first();

            if (!$purchase) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Purchase not found or already completed'
                ]);
            }

            $userId   = $purchase->member_id;
            $voucher  = Reward::find($purchase->reward_id);

            if (!$voucher) {
                throw new \Exception('Reward not found');
            }

            /*
            |--------------------------------------------------------------------------
            | 1️⃣ UPDATE PURCHASE STATUS
            |--------------------------------------------------------------------------
            */
            $purchase->update([
                'status' => 'completed'
            ]);

            /*
            |--------------------------------------------------------------------------
            | 2️⃣ STORE PAYMENT TRANSACTION (ALLOW BLANK)
            |--------------------------------------------------------------------------
            */
            $dataReq = $request->all();

            $transactionId = strtoupper(Str::random(10));
            $mid           = strtoupper(Str::random(10));
            PaymentTransaction::updateOrCreate(
                [
                    'transaction_id' => $transactionId,
                ],
                [
                    'user_id'           => $userId,
                    'mid'               => $mid ?? null,
                    'order_id'          => $voucher->id ?? null,
                    'receipt_no'          => $purchase->receipt_no ?? null,
                    'rec'                       => 2,
                    'request_amount'    =>  $voucher->usual_price ?? 0,
                    'authorized_amount' => $voucher->usual_price ?? 0,
                    'status'            => 'success',
                    'raw_response'      => json_encode($dataReq),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | 3️⃣ CREATE WALLET VOUCHERS
            |--------------------------------------------------------------------------
            */

            // PHYSICAL REWARD
            if ((int)$voucher->reward_type === 1) {

                for ($i = 0; $i < (int)$purchase->qty; $i++) {

                    $uniqueCode = UserWalletVoucher::generateUniqueVoucherCode();
                    $serialNo   = UserWalletVoucher::generateSerialNo($uniqueCode, $i);

                    UserWalletVoucher::create([
                        'user_id'       => $userId,
                        'reward_id'     => $voucher->id,
                        'qty'           => 1,
                        'claimed_at'    => now(),
                        'status'        => 'Active',
                        'reward_status' => 'purchased',
                        'receipt_no'    => $purchase->receipt_no,
                        'unique_code'   => $uniqueCode,
                        'serial_no'     => $serialNo,
                    ]);

                    VoucherLog::create([
                        'user_id'   => $userId,
                        'reward_id' => $voucher->id,
                        'action'    => 'purchased',
                        'receipt_no'=> $uniqueCode,
                        'qty'       => 1,
                    ]);
                }
            }

            // DIGITAL REWARD
            if ((int)$voucher->reward_type === 0) {
                $voucherSet = (int) $voucher->voucher_set;   // ex: 5
                $total = $voucherSet * (int) $purchase->qty;
                
                if($voucher->inventory_type == '1'){ //merchant codes
                    $codes = RewardVoucher::where('reward_id', $voucher->id)
                        ->where('is_used', 0)
                        ->limit($total)
                        ->lockForUpdate()
                        ->get();        
                    
                    $counter = 1;

                    foreach ($codes as $code) {

                        $uniqueCode = UserWalletVoucher::generateUniqueVoucherCode();
                        $serialNo   = UserWalletVoucher::generateSerialNo($uniqueCode, $counter++);

                        UserWalletVoucher::create([
                            'user_id'       => $userId,
                            'reward_id'     => $voucher->id,
                            'reward_voucher_id'  => $code->id,
                            'qty'           => 1,
                            'claimed_at'    => now(),
                            'status'        => 'Active',
                            'reward_status' => 'purchased',
                            'receipt_no'    => $purchase->receipt_no,
                            'unique_code'   => $uniqueCode,
                            'serial_no'     => $serialNo,
                        ]);

                        VoucherLog::create([
                            'user_id'   => $userId,
                            'reward_id' => $voucher->id,
                            'action'    => 'purchased',
                            'receipt_no'=> $uniqueCode,
                            'qty'       => 1,
                        ]);
                    }
                }else{

                    for ($i = 0; $i < $total; $i++) {
    
                        $uniqueCode = UserWalletVoucher::generateUniqueVoucherCode();
                        $serialNo   = UserWalletVoucher::generateSerialNo($uniqueCode, $i);
    
                        UserWalletVoucher::create([
                            'user_id'       => $userId,
                            'reward_id'     => $voucher->id,
                            'qty'           => 1,
                            'claimed_at'    => now(),
                            'status'        => 'Active',
                            'reward_status' => 'purchased',
                            'receipt_no'    => $purchase->receipt_no,
                            'unique_code'   => $uniqueCode,
                            'serial_no'     => $serialNo,
                        ]);
    
                        VoucherLog::create([
                            'user_id'   => $userId,
                            'reward_id' => $voucher->id,
                            'action'    => 'purchased',
                            'receipt_no'=> $uniqueCode,
                            'qty'       => 1,
                        ]);
                    }
                }


                // increment purchased qty
                $voucher->increment('purchased_qty', (int)$purchase->qty);

            }

            /*
            |--------------------------------------------------------------------------
            | 4️⃣ NOTIFICATION
            |--------------------------------------------------------------------------
            */
            Notification::create([
                'user_id'    => $userId,
                'reward_id'  => $voucher->id,
                'title'      => $voucher->name,
                'img'        => $voucher->voucher_image,
                'short_desc' => 'You purchased ' . $purchase->qty . ' Qty of ' . $voucher->name,
                'desc'       => $voucher->how_to_use,
                'date'       => now(),
                'type'       => 'purchased'
            ]);

            DB::commit();

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
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
