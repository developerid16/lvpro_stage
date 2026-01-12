<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Tier;
use App\Models\Reward;
use App\Models\AppUser;
use App\Models\UserTier;
use App\Models\SaleBatch;
use Illuminate\Support\Str;
use App\Imports\SalesImport;
use App\Models\UserReferral;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Models\RefundSale;
use App\Models\KeyPassbookDebit;
use App\Models\ContentManagement;
use App\Models\KeyPassbookCredit;
use App\Models\UserPurchasedReward;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\ImportSales;
use App\Jobs\ImportSalesOld;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;


class SalesController extends Controller
{
    //
    public function __construct()
    {

        $this->view_file_path = "admin.sales.";
        $permission_prefix = $this->permission_prefix = 'sales';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Sales',
            'module_base_url' => url('admin/sales')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        return view($this->view_file_path . "index")->with($this->layout_data);
    }
    /**
     * Display a listing of the resource.
     */
    public function retiveTransaction()
    {

        $noTranscationThisYear = [];
        $transcationThisYear = [];
        $notEffected  = [];

        dd("==========");


        $sales = Sale::whereDate('date', '<', '2024-09-01')->whereDate('created_at', '>=', '2024-09-01')
            ->whereNotIn('sale_amount', ['30', '31'])
             ->groupBy('user_id')
             ->where('user_id','5451')
            ->get();
        // return $sales;
 
        // based on sales get users 
        foreach ($sales as $sale) {
            // Now we need to clean availablekes , remove key of milestone and spend amount and reset the tire
            $uid = $sale->user_id;
            $kpd =  KeyPassbookDebit::where('type', '!=', 'admin')->where('user_id', $uid)->delete();
            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $uid)->whereDate('expiry_date', '2025-11-30')->delete();
            // dd("COUNT",count($kpc), count($kpd));

            $user = AppUser::find($uid);
            $user->available_key = 0;
            $user->save();

            UserTier::where([['status', 'Active'], ['user_id', '=', $uid]])->update([
                "amount_spend" => 0,
                "meta_data" => null
            ]);





            // now we give user agian admin_creadit key of legancy only 

            $sumofnokey = KeyPassbookCredit::whereIn('earn_way', ['admin_credit', "admin_credit_legacy"])->where('user_id',$uid)->sum("no_of_key");

            // we giver first all key then deduct in next step becase we debit based on purchase
            $user = AppUser::find($uid);
            $user->available_key = (int) $sumofnokey;
            //dump($user->available_key);
            $user->save();

            // now first we need to clear all the key transactions that we given in past 
            Sale::whereDate('date', '>=', '2024-09-01')->whereDate('created_at', '>=', '2024-09-01')->where('user_id', $uid)->update([
                "limit_reach" => 0,
                "key_earn" => 0
            ]);


            // Now we give user again keyes based on the actule transaction
            $newsales = Sale::whereDate('date', '>=', '2024-09-01')->whereDate('created_at', '>=', '2024-09-01')
                ->selectRaw('*,sum(sale_amount) as totalsale')
                ->where('user_id', $uid)
                ->groupBy('ref')
                ->orderBy('date', 'asc')
                ->orderBy('system_time', 'asc')

                ->get();

            foreach ($newsales as $new) {
                SalesController::updateMileStone($new, (float)  $new['totalsale']);
            }

            // now we need to same thing for refund 

            // now first we need to clear all the key transactions that we given in past 
            RefundSale::whereDate('date', '>=', '2024-09-01')->whereDate('created_at', '>=', '2024-09-01')->where('user_id', $uid)->update([
                "key_earn" => 0
            ]);


            // Now we give user again keyes based on the actule transaction
            $newsales = RefundSale::whereDate('date', '>=', '2024-09-01')->whereDate('created_at', '>=', '2024-09-01')
                ->selectRaw('*,sum(sale_amount) as totalsale')
                ->where('user_id', $uid)
                ->orderBy('date', 'asc')
                ->orderBy('system_time', 'asc')

                ->groupBy('ref')
                ->get();
            foreach ($newsales as $new) {
                $key = SalesController::refundSale($new, (float)  $new['totalsale']);
                $new->key_earn = $key;
                $new->save();
            }



            // now deduct the key of user based on purchase the gift code


            $userPerReward = UserPurchasedReward::where('user_id', $uid)->get();
            foreach ($userPerReward as $pr) {
                // 

                $dk = $pr->key_use;
                // lets debit key on by one
                $availableKeys =  KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $uid]])->whereDate('expiry_date', '>=', Carbon::today())->orderBy('id', 'asc')->get();

                $user = AppUser::find($uid)->decrement('available_key', (int) $dk);


                $nokeys  = $dk;
                $i = 0;
                while ($nokeys > 0) {
                    $ak = $availableKeys[$i] ?? null;
                    if ($ak) {

                        if ($ak->remain_keys >= $nokeys) {
                            // first expred key find that full fill order no need to go further
                            $keyused = $nokeys;
                            $nokeys = 0;
                        } else {
                            // need to go further for next reward this reward dont have avaibaled key to fill order
                            $keyused = $ak->remain_keys;
                            $nokeys -= $ak->remain_keys;
                        }
                        KeyPassbookDebit::create([
                            'user_id' => $uid,
                            'purchase_id' => $pr->id,
                            'credit_id' => $ak->id ?? null,
                            'key_use' => $keyused,
                            'type' => 'purchased'
                        ]);
                        $ak->decrement('remain_keys', $keyused);

                        $i++;
                    } else {
                        KeyPassbookDebit::create([
                            'user_id' => $uid,
                            'purchase_id' => $pr->id,
                            'credit_id' => null,
                            'key_use' => $nokeys,
                            'type' => 'purchased'
                        ]);
                        $nokeys = 0;
                    }
                }
            }


            // 

        }
        dd("ALL DONR NEW");


        $activeUser = UserTier::where([['status', 'Active'], ['amount_spend', '>', '1']])->get();

        foreach ($activeUser as  $value) {
            $sales = Sale::whereDate('date', '>=', '2024-09-01')->where('user_id', $value->user_id)->groupBy('ref')
                ->selectRaw('*,sum(sale_amount) as totalsale')
                ->get();
            $totalSpend = 0;
            foreach ($sales as $key => $s) {
                $totalSpend +=  floor((float)$s->totalsale);
            }
            $refundsales = RefundSale::whereDate('date', '>=', '2024-09-01')->where('user_id', $value->user_id)->groupBy('ref')
                ->selectRaw('*,sum(sale_amount) as totalsale')
                ->get();
            foreach ($refundsales as $key => $s) {
                $totalSpend -=  floor((float)$s->totalsale);
            }
            // dump($value->user_id . " TOTAl SPE $value->amount_spend ==== $totalSpend" );


            if ($value->amount_spend ==  $totalSpend) {
                // Nothing to do here this user not effect
                //   dump("User is not effected " .  $value->user_id);
                $notEffected[] = $value->user_id;
            } else {
                // this user effect need to reset 


                if ($totalSpend === 0 && count($refundsales) == 0) {


                    // dump("User is effected " .  $value->user_id . " TOTAl SPE $value->amount_spend ==== $totalSpend" );
                    $noTranscationThisYear[] = $value->user_id;
                } else {
                    $transcationThisYear[] = $value->user_id;
                    // dump("User is effected " .  $value->user_id . " TOTAl SPE $value->amount_spend ==== $totalSpend" );


                }
                //TODO: here we need to reset his tire
            }
        }
        dd(count($notEffected), count($transcationThisYear), count($noTranscationThisYear));


        // Now find the user dont spent anything but still get keys and they use that one or not 
        // TODO Done
        $spentUser  = [];
        $nospentUser  = [];

        foreach ($noTranscationThisYear as $user) {
            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();
            $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
            if (count($debitskey) > 0) {
                $spentUser[] =  $user;
            } else {
                $nospentUser[] =  $user;
            }
        }

        // First remove the key from user that dont use them till now. so its easy 


        // foreach($nospentUser as $user){
        //     // Reset milestone bar and delete key and also remove from available key 00
        //     $ak = AppUser::find($user)->available_key;
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->sum('remain_keys');
        //     $lk = $ak - $kpc;
        //     if($lk >= 0){
        //         dump("$user User Still Remaining $lk total past $ak total earn $kpc");
        //     }else{
        //         dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");

        //     }
        // }
        // foreach($spentUser as $user){
        //     // Reset milestone bar and delete key and also remove from available key 
        //     $ak = AppUser::find($user)->available_key;
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->sum('no_of_key');
        //     $lk = $ak - $kpc;
        //     if($lk >= 0){
        //         dump("$user User Still Remaining $lk total past $ak total earn $kpc");
        //     }else{
        //         dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");

        //     }
        // }
        // TODO END
        // dd($spentUser,$nospentUser);


        // now check for the user have transcationThisYear and find the thigs 
        $nospentUser = [];
        $spentUser = [];
        foreach ($transcationThisYear as $user) {
            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();
            $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
            if (count($debitskey) > 0) {
                $spentUser[] =  $user;
            } else {
                $nospentUser[] =  $user;
            }
        }
        //dd($spentUser);


        foreach ($spentUser as $user) {
            // Reset milestone bar and delete key and also remove from available key 


            $ak = AppUser::find($user)->available_key;
            $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();

            $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
            $lkpc = $kpc->sum('remain_keys');

            if (count($debitskey) > 0) {

                // first find the key we give user as un-wanted


                $sales = Sale::whereDate('date', '<', '2024-09-01')->whereDate('created_at', '>=', "2024-09-01")->where([['user_id', $user], ['batch_id', '!=', '31'], ['batch_id', '!=', '30']])->groupBy('ref')
                    ->selectRaw('*,sum(key_earn) as totalsale')
                    ->get();
                $totalSpend = 0;
                foreach ($sales as $key => $s) {
                    $totalSpend +=  floor((float)$s->totalsale);
                }
                $refundsales = RefundSale::whereDate('date', '<', '2024-09-01')->whereDate('created_at', '>=', "2024-09-01")->where([['user_id', $user], ['batch_id', '!=', '31'], ['batch_id', '!=', '30']])->groupBy('ref')
                    ->selectRaw('*,sum(key_earn) as totalsale')
                    ->get();
                foreach ($refundsales as $key => $s) {
                    $totalSpend -=  floor((float)$s->totalsale);
                }

                if ($ak - $totalSpend < 0) {

                    dump("call here $user total key used by "  . $debitskey->sum('key_use') . " available key " . $ak . " total-key earn user " . $totalSpend . " Remove key from available key" .  $ak - $totalSpend);
                }
                // think in morning 
                // $lk = $ak - $lkpc;
                // if ($lk >= 0) {
                //     dump("$user User Still Remaining $lk total past $ak total earn $kpc");
                // } else {
                //     dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");
                // }
            } else {
                // so here we delete the all the key that given to user and reset tire in next step we again provide the key to users and also remove the available key based on remonain keys 

                // We get remian kes in case of user refund becase user spend nothing here 
                $lkpc = $kpc->sum('remain_keys');

                $lk = $ak - $lkpc;
                if ($lk >= 0) {
                    dump("$user User Still Remaining $lk total past $ak total earn $lkpc");
                } else {
                    dump("$user User has nagative key Remaining $lk total past $ak total earn $lkpc");
                }
            }
        }
        // foreach ($nospentUser as $user) {
        //     // Reset milestone bar and delete key and also remove from available key 


        //     $ak = AppUser::find($user)->available_key;
        //     $kpc = KeyPassbookCredit::whereIn('earn_way', ['milestone_reached', 'spending_amount'])->where('user_id', $user)->whereDate('expiry_date', '2025-11-30')->get();

        //     $debitskey =  KeyPassbookDebit::whereIn('credit_id', $kpc->pluck('id'))->where('type', 'purchased')->where('user_id', $user)->get();
        //     $lkpc = $kpc->sum('remain_keys');

        //     if (count($debitskey) > 0) {
        //         dump("call here $user");
        //         // think in morning 
        //         // $lk = $ak - $lkpc;
        //         // if ($lk >= 0) {
        //         //     dump("$user User Still Remaining $lk total past $ak total earn $kpc");
        //         // } else {
        //         //     dump("$user User has nagative key Remaining $lk total past $ak total earn $kpc");
        //         // }
        //     }else{
        //         // so here we delete the all the key that given to user and reset tire in next step we again provide the key to users and also remove the available key based on remonain keys 

        //         // We get remian kes in case of user refund becase user spend nothing here 
        //         $lkpc = $kpc->sum('remain_keys');

        //         $lk = $ak - $lkpc;
        //         if ($lk >= 0) {
        //             dump("$user User Still Remaining $lk total past $ak total earn $lkpc");
        //         } else {
        //             dump("$user User has nagative key Remaining $lk total past $ak total earn $lkpc");
        //         }

        //     }

        // }
    }

    public function datatable(Request $request)
    {
        $query = SaleBatch::query();
        $query->with('user');
        $query = $this->get_sort_offset_limit_query($request, $query, []);

        $final_data = [];
        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['file_name'] = $row->file_name;
            $final_data[$key]['user_name'] = $row->user->name;

            $final_data[$key]['created_at'] = $row->created_at->format(config('safra.date-format'));

            $final_data[$key]['status'] = $row->status;
            $action = "";

            if ($row->status === 'failed') {

                $action = "<div class='d-flex gap-3'>";

                $url = asset("report/$row->id-error.csv");
                $action .= "<a href='$url' download><i class='mdi mdi-download text-danger action-icon font-size-18' ></i></a>";
                $action . "</div>";
            }

            $final_data[$key]['action'] = $action;
        }
        $data = [];
        $data['items'] = $final_data;
        $data['count'] = $query['count'];
        return $data;
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
        $this->validate($request, [
            'file' => 'required|file',
        ]);

        // Processing , Completed and Fail
        if ($request->hasFile('file')) {
            $fileName = time() . rand() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('report'), $fileName);
        }

        $post_data = [
            'file_name' => $fileName,
            'upload_by' => Auth::id(),
        ];
        $sale = SaleBatch::create($post_data);

        $batchId = $sale->id;
        try {
            // Excel::queueImport(new SalesImport($batchId), request()->file('file'), null, \Maatwebsite\Excel\Excel::CSV);
            ImportSales::dispatch($batchId);
        } catch (\Exception $e) {
            throw $e;
        }


        return response()->json(['status' => 'success', 'message' => 'Sales Created Successfully']);
    }
    public function oldSales(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file',
        ]);

        // Processing , Completed and Fail
        if ($request->hasFile('file')) {
            $fileName = time() . rand() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('report'), $fileName);
        }

        $post_data = [
            'file_name' => 'livetra07.csv',
            'upload_by' => Auth::id(),
        ];
        $sale = SaleBatch::create($post_data);

        $batchId = $sale->id;
        try {
            // Excel::queueImport(new SalesImport($batchId), request()->file('file'), null, \Maatwebsite\Excel\Excel::CSV);
            ImportSalesOld::dispatch($batchId);
        } catch (\Exception $e) {
            throw $e;
        }


        return response()->json(['status' => 'success', 'message' => 'Sales Created Successfully']);
    }
    public function fileUploaded() {}

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
        $this->layout_data['data'] = Sale::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $exd = !isset($request->end_date) ? 1 : 0;

        $post_data = $this->validate($request, [
            'code' => 'required|max:191|unique:rewards,code,' . $id,
            'name' => 'required|max:191',
            'description' => 'required|max:500',
            'no_of_keys' => 'required|numeric|min:1',
            'quantity' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|required|date|after_or_equal:' . $request->start_date,
            'expiry_day' => 'required|numeric|min:' . $exd,
            'reward_type' => 'required',
            'product_name' => 'required_if:reward_type,1',
            'amount' => 'required_if:reward_type,0',
            'image_1' => 'sometimes|image',
            'image_2' => 'sometimes|required|image',
            'status' => 'required',
            'company_name' => 'required|max:191',
            'term_of_use' => 'required',
            'how_to_use' => 'required',
            'is_featured' => 'required',
            'labels' => 'sometimes',
        ]);

        $rd = Sale::find($id);
        if ($request->hasFile('image_1')) {
            $imageName = time() . rand() . '.' . $request->image_1->extension();
            $request->image_1->move(public_path('images'), $imageName);
            $post_data['image_1'] = $imageName;
            try {
                unlink(public_path("images/$rd->image_1"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        if ($request->hasFile('image_2')) {
            $imageName = time() . rand() . '.' . $request->image_2->extension();
            $request->image_2->move(public_path('images'), $imageName);
            $post_data['image_2'] = $imageName;
            try {
                unlink(public_path("images/$rd->image_2"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        $rd->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Sales Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Sale::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Sales Delete Successfully']);
    }


    public static function updateMileStoneOld($item, $amt)
    {
        $limit = 100000;

        $amt = floor($amt);
        $user = AppUser::where('unique_id', $item->loyalty)->first();
        if ($user) {
            if ($user->referral_code) {
                // this one run only ones for user lifetime
                $earnkeys = ContentManagement::whereIn('name', ['referral_by_keys', 'referral_to_keys'])->pluck('value', 'name');

                $ur = UserReferral::where('referral_to', $user->id)->first();

                KeyPassbookCredit::create([
                    'user_id' => $ur->referral_to,
                    'no_of_key' => $earnkeys['referral_to_keys'],
                    'remain_keys' => $earnkeys['referral_to_keys'],
                    'earn_way' => "referral_bounce",
                    'expiry_date' =>  keyExpiryDate(),
                ]);
                KeyPassbookCredit::create([
                    'user_id' => $ur->referral_by,
                    'no_of_key' => $earnkeys['referral_by_keys'],
                    'remain_keys' => $earnkeys['referral_by_keys'],
                    'earn_way' => "referral_bounce_earn",
                    'expiry_date' =>  keyExpiryDate(),
                ]);
                $ur->update([
                    'status' => 'Completed'
                ]);
                $user->update([
                    'referral_code' => null
                ]);
                //make code null because this not run in future versions and no load in progress
            }
            $userTire =   $user->myTire;

            // amount_spend
            $tier = Tier::get();

            $myTire =  $tier->firstWhere('id', $userTire->tier_id);
            $mmd = $userTire->meta_data;
            $lastamt = $userTire->amount_spend;
            $curamt = $userTire->amount_spend +  (float)$amt;

            // LIMIT CODE FOR USER

            if ($lastamt >= $limit) {
                // user reach limit nothing to do next 
                // mark limit in sale transcations 
                $userTire->update([
                    'amount_spend' => $curamt,
                ]);
                $item->increment('limit_reach', 1);
                return;
            } else {
                // now check whether user reach limit in this acount 
                if ($curamt >= $limit) {
                    // 10000  = 9000  (old) -  12000 (new)
                    $amt = $limit -  $lastamt;
                    $item->increment('limit_reach', 1);
                }
            }

            // END LIMIT CODE 


            Log::info($curamt . " CUR");
            Log::info($userTire->amount_spend . " amount_spend");

            $nextTire = $tier->skipUntil(function ($tire) use ($myTire) {
                return $tire->t_order > $myTire->t_order;
            });
            $nextTire = $nextTire->values()->all();
            $nxtSpend = null;
            if ($nextTire) {
                $nxtSpend = $nextTire[0]->spend_amount;
            } else {
                $nxtSpend =  null;
            }

            // milestone that newaly reach and forget about the last completed milestone. add this data to all realted DB and give reward to user if milestne readh
            $arr =  $mmd->pluck('id')->toArray();
            if (count($arr) === 0) {
                $arr =  [];
            }
            $milestones =   TierMilestone::where(function ($query) use ($userTire, $curamt, $arr) {
                $query->where([['tier_id', $userTire->tier_id], ['amount', '<=', $curamt]]);
                if (count($arr) > 0) {
                    $query->whereNotIn('id', $arr);
                }
            })->get();

            // $milestones
            if (count($milestones) > 0) {

                foreach ($milestones as $milestone) {
                    // user reach this milestone give him reward to user if milestne reach and update to DB so next time not give this reward to user
                    if ($milestone->type === "key") {
                        // keys add to account
                        $rk = $milestone->no_of_keys;
                        // check if user has already in negative value as key 

                        if ($user->available_key < 0) {
                            // remove nagetive value then add key to passbook
                            // dd($user->available_key, $rk, $user->available_key +  $rk);
                            $rk = $user->available_key +  $rk;
                            // $user->increment('available_key', $request->keys - $rk);

                            if ($rk <= 0) {

                                $user->available_key = $rk;
                                $user->save();
                            } else {
                                $user->available_key = 0;
                                $user->save();
                            }
                        }
                        KeyPassbookCredit::create([
                            'user_id' => $user->id,
                            'no_of_key' => $milestone->no_of_keys,
                            'remain_keys' => $rk < 0 ? 0 : $rk,
                            'earn_way' => "milestone_reached",
                            'meta_data' => $milestone->name,
                            'expiry_date' =>  keyExpiryDate(),
                        ]);
                        $user = $user->fresh();
                    } else {
                        $reward =  Reward::findOrFail($milestone->reward_id);
                        $uuid = (string) Str::ulid();

                        if ($reward->reward_type == 0) {
                            $sn = "SC";
                            $uuid  = $sn . Carbon::now()->format('ymjhisjmy');
                            $sn  = $uuid;
                        } else {
                            $sn = "SR";
                            $uuid  = $sn . $uuid;
                        }
                        $exd = null;
                        if ($reward->expiry_day == 0) {
                            // defalut is coupen expiry
                            $exd = $reward->end_date;
                        } else {
                            $exd = Carbon::now()->addDays($reward->expiry_day);
                        }
                        UserPurchasedReward::create([
                            'user_id' => $user->id,
                            'reward_id' => $reward->id,
                            'key_use' =>  "0",
                            'status' => "Purchased",
                            'get_from' => "milestone_reached",
                            'unique_no' =>  $uuid,
                            'voucher_serial' => $sn,
                            'expiry_date' => $exd,
                            'reward_type' => $reward->reward_type
                        ]);

                        $reward->increment('total_redeemed');
                    }
                    $miArr = $milestone->toArray();

                    $mmd->push($miArr);
                    $userTire->update([
                        'meta_data' => $mmd->toArray(),
                    ]);
                }
            }

            // update user spend amount to itire
            $userTire->update([
                'amount_spend' => $curamt,
            ]);
            // lets check if user pass the tire hope thay

            if ($nxtSpend != null && $nxtSpend <= $curamt) {
                // yes whooooooo
                // before leave give the remaining keys before tire change

                // 1000 900 200


                $diff = (float) $nxtSpend -  (float)$userTire->amount_spend;
                Log::info($diff . "---------");
                $keysuarn = abs($diff) * $myTire->instore_multiplier;
                // $diff
                // 100
                $rk = $keysuarn;
                // check if user has already in negative value as key 

                if ($user->available_key < 0) {
                    // remove nagetive value then add key to passbook
                    // dd($user->available_key, $rk, $user->available_key +  $rk);
                    $rk = $user->available_key +  $rk;
                    // $user->increment('available_key', $request->keys - $rk);

                    if ($rk <= 0) {

                        $user->available_key = $rk;
                        $user->save();
                    } else {
                        $user->available_key = 0;
                        $user->save();
                    }
                }
                $item->increment('key_earn', $keysuarn);
                KeyPassbookCredit::create([
                    'user_id' => $user->id,
                    'no_of_key' => $keysuarn,
                    'remain_keys' => $rk < 0 ? 0 : $rk,
                    'earn_way' => "spending_amount",
                    'expiry_date' =>  keyExpiryDate(),
                ]);
                $user = $user->fresh();


                // lots work do update all data and move to next tire
                $userTire->update([
                    'amount_spend' => $nxtSpend,
                    'status' => "Success",
                    'reach_at' => Carbon::now()
                ]);


                if ($nextTire) {
                    $ut =  UserTier::create([
                        'user_id' => $user->id,
                        'tier_id' => $nextTire[0]->id,
                        'status' => "Active",
                        'end_at' =>  yearEnd(),
                    ]);
                    $remainAMt = $curamt - $nxtSpend;
                    if ($remainAMt > 0) {

                        SalesController::updateMileStone($item, $remainAMt);
                        // whoooo still user have some amount to move further please check still made to new tire
                    } else {
                        // no its no chance to  move further so just return
                    }
                } else {
                    // all ready at best tire
                }
            } else {







                // give user to current tire spending bounce of key

                $keysuarn = $amt * $myTire->instore_multiplier;
                $rk = $keysuarn;
                // check if user has already in negative value as key 

                if ($user->available_key < 0) {
                    // remove nagetive value then add key to passbook
                    // dd($user->available_key, $rk, $user->available_key +  $rk);
                    $rk = $user->available_key +  $rk;
                    // $user->increment('available_key', $request->keys - $rk);

                    if ($rk <= 0) {

                        $user->available_key = $rk;
                        $user->save();
                    } else {
                        $user->available_key = 0;
                        $user->save();
                    }
                }


                $item->increment('key_earn', $keysuarn);
                KeyPassbookCredit::create([
                    'user_id' => $user->id,
                    'no_of_key' => $keysuarn,
                    'remain_keys' => $rk < 0 ? 0 : $rk,
                    'earn_way' => "spending_amount",
                    'expiry_date' =>  keyExpiryDate(),
                ]);
                $user = $user->fresh();
            }
        }
    }
    public static function refundSaleOld($item, $amt)
    {
        $amt = floor($amt);


        $user = AppUser::where('unique_id', $item->loyalty)->first();
        if ($user) {
            $userTire =   $user->myTire;
            $tier = Tier::get();
            $myTire =  $tier->firstWhere('id', $userTire->tier_id);
            $mmd = $userTire->meta_data;
            $curamt = $userTire->amount_spend;

            $keydebit = abs($amt) * $myTire->instore_multiplier;
            $keydebitOnlyAmt  = $keydebit;

            $remainAmt = $curamt -  $amt;
            $removeMilestone = $mmd->where('amount', '>', $remainAmt)->sortBy([['amount', 'desc']]);
            foreach ($removeMilestone as $milestone) {
                $keydebitOnlyMilestone = TierMilestone::find($milestone['id'])->no_of_keys;
                $keydebit += $keydebitOnlyMilestone;
                KeyPassbookDebit::create([
                    'user_id' => $user->id,
                    'key_use' => $keydebitOnlyMilestone,
                    'type' => 'milestone_abandoned',
                ]);
            }



            $filtered  =      $mmd->reject(function ($item, int $key) use ($remainAmt) {
                return $item['amount'] > $remainAmt;
            });

            // lets debit key on by one
            $availableKeys =  KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $user->id]])->whereDate('expiry_date', '>=', Carbon::today())->get();
            $nokeys  = $keydebit;
            $i = 0;
            while ($nokeys > 0) {
                $ak = $availableKeys[$i] ?? [];
                if ($ak) {

                    if ($ak->remain_keys >= $nokeys) {
                        // first expred key find that full fill order no need to go further
                        $keyused = $nokeys;
                        $nokeys = 0;
                    } else {
                        // need to go further for next reward this reward dont have avaibaled key to fill order
                        $keyused = $ak->remain_keys;
                        $nokeys -= $ak->remain_keys;
                    }
                    $ak->decrement('remain_keys', $keyused);
                } else {
                    $nokeys = 0;
                }

                $i++;
            }


            // $keysuarn = abs($amt) * $myTire->instore_multiplier;
            KeyPassbookDebit::create([
                'user_id' => $user->id,
                'key_use' => $keydebitOnlyAmt,
                'type' => 'refund_sale',
            ]);
            $user->decrement('available_key', $keydebit);

            // update miestone data so next time that not run again
            $userTire->meta_data =  $filtered->values()->all();
            $userTire->amount_spend =  $remainAmt;

            $userTire->save();
            // end
        }
        return $keydebitOnlyAmt;
    }


    public static function updateMileStone($item, $amt)
    {
        $limit = 100000;

        $amt = floor($amt);
        $user = AppUser::where('unique_id', $item->loyalty)->first();
        if ($user) {
            if ($user->referral_code) {
                // this one run only ones for user lifetime
                $earnkeys = ContentManagement::whereIn('name', ['referral_by_keys', 'referral_to_keys'])->pluck('value', 'name');

                $ur = UserReferral::where('referral_to', $user->id)->first();

                if ($ur) {

                    KeyPassbookCredit::create([
                        'user_id' => $ur->referral_to,
                        'no_of_key' => $earnkeys['referral_to_keys'],
                        'remain_keys' => $earnkeys['referral_to_keys'],
                        'earn_way' => "referral_bounce",
                        'expiry_date' =>  keyExpiryDate(),
                    ]);
                    KeyPassbookCredit::create([
                        'user_id' => $ur->referral_by,
                        'no_of_key' => $earnkeys['referral_by_keys'],
                        'remain_keys' => $earnkeys['referral_by_keys'],
                        'earn_way' => "referral_bounce_earn",
                        'expiry_date' =>  keyExpiryDate(),
                    ]);
                    $ur->update([
                        'status' => 'Completed'
                    ]);
                    $user->update([
                        'referral_code' => null
                    ]);
                }

                //make code null because this not run in future versions and no load in progress
            }
            $userTire =   $user->myTire;

            // amount_spend
            $tier = Tier::get();

            $myTire =  $tier->firstWhere('id', $userTire->tier_id);
            $mmd = $userTire->meta_data;
            $lastamt = $userTire->amount_spend;
            $curamt = $userTire->amount_spend +  (float)$amt;

            // LIMIT CODE FOR USER

            if ($lastamt >= $limit) {
                // user reach limit nothing to do next 
                // mark limit in sale transcations 
                $userTire->update([
                    'amount_spend' => $curamt,
                ]);
                $item->increment('limit_reach', 1);
                return;
            } else {
                // now check whether user reach limit in this acount 
                if ($curamt >= $limit) {
                    // 10000  = 9000  (old) -  12000 (new)
                    $amt = $limit -  $lastamt;
                    $item->increment('limit_reach', 1);
                }
            }

            // END LIMIT CODE 


            Log::info($curamt . " CUR");
            Log::info($userTire->amount_spend . " amount_spend");

            $nextTire = $tier->skipUntil(function ($tire) use ($myTire) {
                return $tire->t_order > $myTire->t_order;
            });
            $nextTire = $nextTire->values()->all();
            $nxtSpend = null;
            if ($nextTire) {
                $nxtSpend = $nextTire[0]->spend_amount;
            } else {
                $nxtSpend =  null;
            }

            // milestone that newaly reach and forget about the last completed milestone. add this data to all realted DB and give reward to user if milestne readh
            $arr =  $mmd->pluck('id')->toArray();
            if (count($arr) === 0) {
                $arr =  [];
            }
            $milestones =   TierMilestone::where(function ($query) use ($userTire, $curamt, $arr) {
                $query->where([['tier_id', $userTire->tier_id], ['amount', '<=', $curamt]]);
                if (count($arr) > 0) {
                    $query->whereNotIn('id', $arr);
                }
            })->get();

            // $milestones
            if (count($milestones) > 0) {

                foreach ($milestones as $milestone) {
                    // user reach this milestone give him reward to user if milestne reach and update to DB so next time not give this reward to user
                    if ($milestone->type === "key") {
                        // keys add to account
                        $rk = $milestone->no_of_keys;
                        // check if user has already in negative value as key 

                        if ($user->available_key < 0) {
                            // remove nagetive value then add key to passbook
                            // dd($user->available_key, $rk, $user->available_key +  $rk);
                            $rk = $user->available_key +  $rk;
                            // $user->increment('available_key', $request->keys - $rk);

                            if ($rk <= 0) {

                                $user->available_key = $rk;
                                $user->save();
                            } else {
                                $user->available_key = 0;
                                $user->save();
                            }
                        }
                        KeyPassbookCredit::create([
                            'user_id' => $user->id,
                            'no_of_key' => $milestone->no_of_keys,
                            'remain_keys' => $rk < 0 ? 0 : $rk,
                            'earn_way' => "milestone_reached",
                            'meta_data' => $milestone->name,
                            'expiry_date' =>  keyExpiryDate(),
                        ]);
                        $user = $user->fresh();
                    } else {
                        $reward =  Reward::findOrFail($milestone->reward_id);
                        $uuid = (string) Str::ulid();

                        if ($reward->reward_type == 0) {
                            $sn = "SC";
                            $uuid  = $sn . Carbon::now()->format('ymjhisjmy');
                            $sn  = $uuid;
                        } else {
                            $sn = "SR";
                            $uuid  = $sn . $uuid;
                        }
                        $exd = null;
                        if ($reward->expiry_day == 0) {
                            // defalut is coupen expiry
                            $exd = $reward->end_date;
                        } else {
                            $exd = Carbon::now()->addDays($reward->expiry_day);
                        }
                        UserPurchasedReward::create([
                            'user_id' => $user->id,
                            'reward_id' => $reward->id,
                            'key_use' =>  "0",
                            'status' => "Purchased",
                            'get_from' => "milestone_reached",
                            'unique_no' =>  $uuid,
                            'voucher_serial' => $sn,
                            'expiry_date' => $exd,
                            'reward_type' => $reward->reward_type
                        ]);

                        $reward->increment('total_redeemed');
                    }
                    $miArr = $milestone->toArray();

                    $mmd->push($miArr);
                    $userTire->update([
                        'meta_data' => $mmd->toArray(),
                    ]);
                }
            }

            // update user spend amount to itire
            $userTire->update([
                'amount_spend' => $curamt,
            ]);
            // lets check if user pass the tire hope thay

            if ($nxtSpend != null && $nxtSpend <= $curamt) {
                // yes whooooooo
                // before leave give the remaining keys before tire change

                // 1000 900 200


                $diff = (float) $nxtSpend -  (float)$userTire->amount_spend;
                Log::info($diff . "---------");
                $keysuarn = abs($diff) * $myTire->instore_multiplier;
                // $diff
                // 100
                $rk = $keysuarn;
                // check if user has already in negative value as key 

                if ($user->available_key < 0) {
                    // remove nagetive value then add key to passbook
                    // dd($user->available_key, $rk, $user->available_key +  $rk);
                    $rk = $user->available_key +  $rk;
                    // $user->increment('available_key', $request->keys - $rk);

                    if ($rk <= 0) {

                        $user->available_key = $rk;
                        $user->save();
                    } else {
                        $user->available_key = 0;
                        $user->save();
                    }
                }
                $item->increment('key_earn', $keysuarn);
                KeyPassbookCredit::create([
                    'user_id' => $user->id,
                    'no_of_key' => $keysuarn,
                    'remain_keys' => $rk < 0 ? 0 : $rk,
                    'earn_way' => "spending_amount",
                    'expiry_date' =>  keyExpiryDate(),
                ]);
                $user = $user->fresh();


                // lots work do update all data and move to next tire
                $userTire->update([
                    'amount_spend' => $nxtSpend,
                    'status' => "Success",
                    'reach_at' => Carbon::now()
                ]);


                if ($nextTire) {
                    $ut =  UserTier::create([
                        'user_id' => $user->id,
                        'tier_id' => $nextTire[0]->id,
                        'status' => "Active",
                        'end_at' =>  yearEnd(),
                    ]);
                    $remainAMt = $curamt - $nxtSpend;
                    if ($remainAMt > 0) {

                        SalesController::updateMileStone($item, $remainAMt);
                        // whoooo still user have some amount to move further please check still made to new tire
                    } else {
                        // no its no chance to  move further so just return
                    }
                } else {
                    // all ready at best tire
                }
            } else {







                // give user to current tire spending bounce of key

                $keysuarn = $amt * $myTire->instore_multiplier;
                $rk = $keysuarn;
                // check if user has already in negative value as key 

                if ($user->available_key < 0) {
                    // remove nagetive value then add key to passbook
                    // dd($user->available_key, $rk, $user->available_key +  $rk);
                    $rk = $user->available_key +  $rk;
                    // $user->increment('available_key', $request->keys - $rk);

                    if ($rk <= 0) {

                        $user->available_key = $rk;
                        $user->save();
                    } else {
                        $user->available_key = 0;
                        $user->save();
                    }
                }


                $item->increment('key_earn', $keysuarn);
                KeyPassbookCredit::create([
                    'user_id' => $user->id,
                    'no_of_key' => $keysuarn,
                    'remain_keys' => $rk < 0 ? 0 : $rk,
                    'earn_way' => "spending_amount",
                    'expiry_date' =>  keyExpiryDate(),
                ]);
                $user = $user->fresh();
            }
        }
    }
    public static function refundSale($item, $amt)
    {
        $amt = floor($amt);


        $user = AppUser::where('unique_id', $item->loyalty)->first();
        if ($user) {
            $userTire =   $user->myTire;
            $tier = Tier::get();
            $myTire =  $tier->firstWhere('id', $userTire->tier_id);
            $mmd = $userTire->meta_data;
            $curamt = $userTire->amount_spend;

            $keydebit = abs($amt) * $myTire->instore_multiplier;
            $keydebitOnlyAmt  = $keydebit;

            $remainAmt = $curamt -  $amt;
            $removeMilestone = $mmd->where('amount', '>', $remainAmt)->sortBy([['amount', 'desc']]);
            foreach ($removeMilestone as $milestone) {
                $keydebitOnlyMilestone = TierMilestone::find($milestone['id'])->no_of_keys;
                $keydebit += $keydebitOnlyMilestone;
                KeyPassbookDebit::create([
                    'user_id' => $user->id,
                    'key_use' => $keydebitOnlyMilestone,
                    'type' => 'milestone_abandoned',
                ]);
            }



            $filtered  =      $mmd->reject(function ($item, int $key) use ($remainAmt) {
                return $item['amount'] > $remainAmt;
            });

            // lets debit key on by one
            $availableKeys =  KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $user->id]])->whereDate('expiry_date', '>=', Carbon::today())->get();
            $nokeys  = $keydebit;
            $i = 0;
            while ($nokeys > 0) {
                $ak = $availableKeys[$i] ?? [];
                if ($ak) {

                    if ($ak->remain_keys >= $nokeys) {
                        // first expred key find that full fill order no need to go further
                        $keyused = $nokeys;
                        $nokeys = 0;
                    } else {
                        // need to go further for next reward this reward dont have avaibaled key to fill order
                        $keyused = $ak->remain_keys;
                        $nokeys -= $ak->remain_keys;
                    }
                    $ak->decrement('remain_keys', $keyused);
                } else {
                    $nokeys = 0;
                }

                $i++;
            }


            // $keysuarn = abs($amt) * $myTire->instore_multiplier;
            KeyPassbookDebit::create([
                'user_id' => $user->id,
                'key_use' => $keydebitOnlyAmt,
                'type' => 'refund_sale',
            ]);
            $user->decrement('available_key', $keydebit);

            // update miestone data so next time that not run again
            $userTire->meta_data =  $filtered->values()->all();
            $userTire->amount_spend =  $remainAmt;

            $userTire->save();
            // end
        }
        return $keydebitOnlyAmt;
    }

    public static function updateMileStoneAssign($item, $amt)
    {

        $amt = floor($amt);
        $user = AppUser::where('unique_id', $item->loyalty)->first();
        if ($user) {
            // if ($user->referral_code) {
            //     // this one run only ones for user lifetime
            //     $earnkeys = ContentManagement::whereIn('name', ['referral_by_keys', 'referral_to_keys'])->pluck('value', 'name');

            //     $ur = UserReferral::where('referral_to', $user->id)->first();

            //     KeyPassbookCredit::create([
            //         'user_id' => $ur->referral_to,
            //         'no_of_key' => $earnkeys['referral_to_keys'],
            //         'remain_keys' => $earnkeys['referral_to_keys'],
            //         'earn_way' => "referral_bounce",
            //         'expiry_date' =>  keyExpiryDate(),
            //     ]);
            //     KeyPassbookCredit::create([
            //         'user_id' => $ur->referral_by,
            //         'no_of_key' => $earnkeys['referral_by_keys'],
            //         'remain_keys' => $earnkeys['referral_by_keys'],
            //         'earn_way' => "referral_bounce_earn",
            //         'expiry_date' =>  keyExpiryDate(),
            //     ]);
            //     $ur->update([
            //         'status' => 'Completed'
            //     ]);
            //     $user->update([
            //         'referral_code' => null
            //     ]);
            //     //make code null because this not run in future versions and no load in progress
            // }
            $userTire =   $user->myTire;

            // amount_spend
            $tier = Tier::get();

            $myTire =  $tier->firstWhere('id', $userTire->tier_id);
            $mmd = $userTire->meta_data;
            $curamt = $userTire->amount_spend;
            $curamt += (float)$amt;

            Log::info($curamt . " CUR");
            Log::info($userTire->amount_spend . " amount_spend");

            $nextTire = $tier->skipUntil(function ($tire) use ($myTire) {
                return $tire->t_order > $myTire->t_order;
            });
            $nextTire = $nextTire->values()->all();
            $nxtSpend = null;
            if ($nextTire) {
                $nxtSpend = $nextTire[0]->spend_amount;
            } else {
                $nxtSpend =  null;
            }

            // milestone that newaly reach and forget about the last completed milestone. add this data to all realted DB and give reward to user if milestne readh
            $arr =  $mmd->pluck('id')->toArray();
            if (count($arr) === 0) {
                $arr =  [];
            }
            $milestones =   TierMilestone::where(function ($query) use ($userTire, $curamt, $arr) {
                $query->where([['tier_id', $userTire->tier_id], ['amount', '<=', $curamt]]);
                if (count($arr) > 0) {
                    $query->whereNotIn('id', $arr);
                }
            })->get();

            // $milestones
            if (count($milestones) > 0) {

                foreach ($milestones as $milestone) {
                    // user reach this milestone give him reward to user if milestne reach and update to DB so next time not give this reward to user
                    if ($milestone->type === "key") {
                        // keys add to account
                        $rk = $milestone->no_of_keys;
                        // check if user has already in negative value as key 

                        if ($user->available_key < 0) {
                            // remove nagetive value then add key to passbook
                            // dd($user->available_key, $rk, $user->available_key +  $rk);
                            $rk = $user->available_key +  $rk;
                            // $user->increment('available_key', $request->keys - $rk);

                            // if ($rk <= 0) {

                            //     $user->available_key = $rk;
                            //     $user->save();
                            // } else {
                            //     $user->available_key = 0;
                            //     $user->save();
                            // }
                        }
                        // KeyPassbookCredit::create([
                        //     'user_id' => $user->id,
                        //     'no_of_key' => $milestone->no_of_keys,
                        //     'remain_keys' => $rk < 0 ? 0 : $rk,
                        //     'earn_way' => "milestone_reached",
                        //     'meta_data' => $milestone->name,
                        //     'expiry_date' =>  keyExpiryDate(),
                        // ]);
                        // $user = $user->fresh();
                    } else {
                        $reward =  Reward::findOrFail($milestone->reward_id);
                        $uuid = (string) Str::ulid();

                        if ($reward->reward_type == 0) {
                            $sn = "SC";
                            $uuid  = $sn . Carbon::now()->format('ymjhisjmy');
                            $sn  = $uuid;
                        } else {
                            $sn = "SR";
                            $uuid  = $sn . $uuid;
                        }
                        $exd = null;
                        if ($reward->expiry_day == 0) {
                            // defalut is coupen expiry
                            $exd = $reward->end_date;
                        } else {
                            $exd = Carbon::now()->addDays($reward->expiry_day);
                        }
                        UserPurchasedReward::create([
                            'user_id' => $user->id,
                            'reward_id' => $reward->id,
                            'key_use' =>  "0",
                            'status' => "Purchased",
                            'get_from' => "milestone_reached",
                            'unique_no' =>  $uuid,
                            'voucher_serial' => $sn,
                            'expiry_date' => $exd,
                            'reward_type' => $reward->reward_type
                        ]);

                        $reward->increment('total_redeemed');
                    }
                    $miArr = $milestone->toArray();

                    $mmd->push($miArr);
                    $userTire->update([
                        'meta_data' => $mmd->toArray(),
                    ]);
                }
            }

            // update user spend amount to itire
            $userTire->update([
                'amount_spend' => $curamt,
            ]);
            // lets check if user pass the tire hope thay

            if ($nxtSpend != null && $nxtSpend <= $curamt) {
                // yes whooooooo
                // before leave give the remaining keys before tire change

                // 1000 900 200


                $diff = (float) $nxtSpend -  (float)$userTire->amount_spend;
                Log::info($diff . "---------");
                $keysuarn = abs($diff) * $myTire->instore_multiplier;
                // $diff
                // 100
                $rk = $keysuarn;
                // check if user has already in negative value as key 

                if ($user->available_key < 0) {
                    // remove nagetive value then add key to passbook
                    // dd($user->available_key, $rk, $user->available_key +  $rk);
                    $rk = $user->available_key +  $rk;
                    // $user->increment('available_key', $request->keys - $rk);

                    // if ($rk <= 0) {

                    //     $user->available_key = $rk;
                    //     $user->save();
                    // } else {
                    //     $user->available_key = 0;
                    //     $user->save();
                    // }
                }
                // $item->increment('key_earn', $keysuarn);
                // KeyPassbookCredit::create([
                //     'user_id' => $user->id,
                //     'no_of_key' => $keysuarn,
                //     'remain_keys' => $rk < 0 ? 0 : $rk,
                //     'earn_way' => "spending_amount",
                //     'expiry_date' =>  keyExpiryDate(),
                // ]);
                $user = $user->fresh();


                // lots work do update all data and move to next tire
                $userTire->update([
                    'amount_spend' => $nxtSpend,
                    'status' => "Success",
                    'reach_at' => Carbon::now()
                ]);


                if ($nextTire) {
                    $ut =  UserTier::create([
                        'user_id' => $user->id,
                        'tier_id' => $nextTire[0]->id,
                        'status' => "Active",
                        'end_at' =>  tireExpiryDate(),
                    ]);
                    $remainAMt = $curamt - $nxtSpend;
                    if ($remainAMt > 0) {

                        SalesController::updateMileStoneAssign($item, $remainAMt);
                        // whoooo still user have some amount to move further please check still made to new tire
                    } else {
                        // no its no chance to  move further so just return
                    }
                } else {
                    // all ready at best tire
                }
            } else {
                // give user to current tire spending bounce of key
                $keysuarn = $amt * $myTire->instore_multiplier;
                $rk = $keysuarn;
                // check if user has already in negative value as key 

                if ($user->available_key < 0) {
                    // remove nagetive value then add key to passbook
                    // dd($user->available_key, $rk, $user->available_key +  $rk);
                    $rk = $user->available_key +  $rk;
                    // $user->increment('available_key', $request->keys - $rk);

                    // if ($rk <= 0) {

                    //     $user->available_key = $rk;
                    //     $user->save();
                    // } else {
                    //     $user->available_key = 0;
                    //     $user->save();
                    // }
                }


                $item->increment('key_earn', $keysuarn);
                // KeyPassbookCredit::create([
                //     'user_id' => $user->id,
                //     'no_of_key' => $keysuarn,
                //     'remain_keys' => $rk < 0 ? 0 : $rk,
                //     'earn_way' => "spending_amount",
                //     'expiry_date' =>  keyExpiryDate(),
                // ]);
                $user = $user->fresh();
            }
        }
    }
    public static function refundSaleAssign($item, $amt)
    {
        $amt = floor($amt);
        $keydebitOnlyAmt = 0;

        $user = AppUser::where('unique_id', $item->loyalty)->first();
        if ($user) {
            $userTire =   $user->myTire;
            $tier = Tier::get();
            $myTire =  $tier->firstWhere('id', $userTire->tier_id);
            $mmd = $userTire->meta_data;
            $curamt = $userTire->amount_spend;

            $keydebit = abs($amt) * $myTire->instore_multiplier;
            $keydebitOnlyAmt  = $keydebit;

            $remainAmt = $curamt -  $amt;
            $removeMilestone = $mmd->where('amount', '>', $remainAmt)->sortBy([['amount', 'desc']]);
            foreach ($removeMilestone as $milestone) {
                // $keydebitOnlyMilestone = TierMilestone::find($milestone['id'])->no_of_keys;
                // $keydebit += $keydebitOnlyMilestone;
                // KeyPassbookDebit::create([
                //     'user_id' => $user->id,
                //     'key_use' => $keydebitOnlyMilestone,
                //     'type' => 'milestone_abandoned',
                // ]);
            }



            $filtered  =      $mmd->reject(function ($item, int $key) use ($remainAmt) {
                return $item['amount'] > $remainAmt;
            });

            // lets debit key on by one
            // $availableKeys =  KeyPassbookCredit::where([['remain_keys', '>', 0], ['user_id', $user->id]])->whereDate('expiry_date', '>=', Carbon::today())->get();
            // $nokeys  = $keydebit;
            // $i = 0;
            // while ($nokeys > 0) {
            //     $ak = $availableKeys[$i] ?? [];
            //     if ($ak) {

            //         if ($ak->remain_keys >= $nokeys) {
            //             // first expred key find that full fill order no need to go further
            //             $keyused = $nokeys;
            //             $nokeys = 0;
            //         } else {
            //             // need to go further for next reward this reward dont have avaibaled key to fill order
            //             $keyused = $ak->remain_keys;
            //             $nokeys -= $ak->remain_keys;
            //         }
            //         $ak->decrement('remain_keys', $keyused);
            //     } else {
            //         $nokeys = 0;
            //     }

            //     $i++;
            // }


            $keysuarn = abs($amt) * $myTire->instore_multiplier;
            // KeyPassbookDebit::create([
            //     'user_id' => $user->id,
            //     'key_use' => $keydebitOnlyAmt,
            //     'type' => 'refund_sale',
            // ]);
            Log::info("keydebit $keydebit");



            // $user->decrement('available_key', $keydebit);

            // update miestone data so next time that not run again
            Log::info("REdund");
            Log::info($filtered->values()->all());
            $userTire->meta_data =  $filtered->values()->all();
            $userTire->amount_spend =  $remainAmt;

            $userTire->save();
            // end
        }
        return $keydebitOnlyAmt;
    }
}
