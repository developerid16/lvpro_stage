<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AppUser;
use Illuminate\Http\Request;
use App\Models\KeyPassbookDebit;
use App\Models\KeyPassbookCredit;
use App\Models\PassholderCompany;
use Spatie\Permission\Models\Role;
use App\Models\UserPurchasedReward;
use App\Http\Controllers\Controller;
use App\Models\RefundSale;
use App\Models\Sale;
use App\Models\TransactionHistory;
use App\Models\UserPurchasedRewardLogs;
use App\Models\UserWalletVoucher;
use App\Services\SafraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AppUserController extends Controller
{
    /**
     * UserController constructor.
     */
    function __construct()
    {


        $this->view_file_path = "admin.app-user.";
        $permission_prefix = $this->permission_prefix = 'app-user';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Application User',
            'module_base_url' => url('admin/app-user')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        // $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update', 'editUser']]);
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
        $query = AppUser::query();



        // • Create:
        //  ‣Customer Type(Airport Pass Holder, Aircrew), APHID, Name, Email, Password, Mobile Number, Gender, Date of Birth, APH expiry date, Company, Employment ID, Referral Code.
        $searched_from_relation = [];
        $query = $this->get_sort_offset_limit_query($request, $query, ['name', 'email',  'status', 'user_type', 'unique_id', 'gender', 'phone_number'], $searched_from_relation, []);

        $final_data = [];


        foreach ($query['data']->get() as $key => $row) {
            $final_data[$key]['sr_no'] = $key + 1;
            $final_data[$key]['unique_id'] = $row->unique_id;
            $final_data[$key]['gender'] = $row->gender;
            $final_data[$key]['phone_number'] = $row->country_code . ' ' . $row->phone_number;
            $final_data[$key]['date_of_birth'] = $row->date_of_birth ? $row->date_of_birth->format(config('safra.date-format')) : '';
            $final_data[$key]['created_at'] = $row->created_at ? $row->created_at->format(config('safra.date-format')) : '';
            $final_data[$key]['expiry_date'] = $row->expiry_date ? $row->expiry_date->format(config('safra.date-format')) : '';
            $final_data[$key]['my_code'] = $row->my_code;
            $final_data[$key]['name'] = $row->name;
            $final_data[$key]['email'] = $row->email;
            $final_data[$key]['status'] = $row->status;
            if ($row->status === 'Inactive' || $row->status === 'Blacklist') {
                $final_data[$key]['status'] .= "<i class='mdi mdi-information text-primary action-icon font-size-18' title='$row->blacklist_reason'></i>";
            }

            $url = route('admin.app-user.show', ['app_user' => $row->id]);
            $showurl = route('admin.app-user-show', ['id' => $row->id]);

            $editurl = route('admin.app-user-edit', ['id' => $row->id]);
            $transactionsurl = route('admin.app-user-transactions', ['id' => $row->id]);
            $action = "<div class='d-flex gap-3'>";
            

            $action .= "<a href='$showurl' class='show' data-id='$row->id'><i class='mdi mdi-eye text-primary action-icon font-size-18'></i></a>";

            $action .= "<a href='$editurl' class='edit' data-id='$row->id'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            $action .= "<a href='$transactionsurl' class='edit' data-id='$row->id'><i class='mdi mdi-credit-card text-primary action-icon font-size-18'></i></a>";
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='$row->id'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }

            $final_data[$key]['action'] = $action . "</div>";
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
     * Show the form for creating a new resource.
     */
    public function editUser($id)
    {
        $data = Appuser::findOrFail($id);

        // dd( $data );
        return view($this->view_file_path . "edit", compact('data',) + $this->layout_data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
   public function show(string $id)
    {
        $user = AppUser::findOrFail($id);

        $rewards = UserWalletVoucher::with('reward')
            ->where('user_id', $user->id)
            ->whereHas('reward')   // 👈 this removes null relations
            ->get();

        return view($this->view_file_path . "show",
            compact('user', 'rewards') + $this->layout_data
        );
    }
    public function userTransactions(string $id, Request $request)
    {
        $user = Appuser::findOrFail($id);

        $filter   = $request->filter;
        $lastYear = Carbon::today()->subYears(2);

        $masterData = collect([]);

        /*
        |--------------------------------------------------------------------------
        | USER WALLET VOUCHER (CLAIM / REDEEM)
        |--------------------------------------------------------------------------
        */
        $wallets = UserWalletVoucher::with('reward:id,name')
            ->where('user_id', $id)
            ->whereDate('created_at', '>=', $lastYear)
            ->get();

        foreach ($wallets as $wallet) {

            if ($wallet->claimed_at) {
                $masterData->push([
                    'text'  => 'Voucher Claimed - '.$wallet->reward?->name.' ('.$wallet->receipt_no.')',
                    'keys'  => $wallet->qty,
                    'total' => 0,
                    'type'  => 'Amount Used',
                    'date' => Carbon::parse($wallet->claimed_at),

                ]);
            }

            if ($wallet->redeemed_at) {
                $masterData->push([
                    'text'  => 'Voucher Redeemed - '.$wallet->reward?->name.' ('.$wallet->receipt_no.')',
                    'keys'  => $wallet->qty,
                    'total' => 0,
                    'type'  => 'negative',
                    'date' => Carbon::parse($wallet->redeemed_at),

                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT TRANSACTION HISTORY
        |--------------------------------------------------------------------------
        */
        $transactions = TransactionHistory::where('user_id', $id)
            ->whereDate('created_at', '>=', $lastYear)
            ->get();

        foreach ($transactions as $txn) {

            $paymentMode = match ((int)$txn->payment_mode) {
                1 => 'Card',
                default => 'Other',
            };

            $masterData->push([
                'text'  => 'Payment '.$txn->status.' | '.$paymentMode.' | Receipt '.$txn->receipt_no,
                'keys'  => 0,
                'total' => $txn->authorized_amount ?? $txn->request_amount,
                'type'  => $txn->status === 'SUCCESS' ? 'positive' : 'negative',
                'date' => Carbon::parse($txn->created_at),

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        */
        $masterData = ($request->sort === 'asc')
            ? $masterData->sortBy('date')
            : $masterData->sortByDesc('date');

        $masterData = $masterData->values()->all();

        return view(
            $this->view_file_path . 'transactions',
            compact('user', 'masterData', 'filter')
        );
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->layout_data['data'] = User::with('roles')->find($id);
        $this->layout_data['role'] = Role::where('name', '!=', 'Super Admin')->get();
        $this->layout_data['assign_roles'] = $this->layout_data['data']->roles->pluck('name');

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {


        $user = AppUser::find($id);
        // 'email' => 'regex:/^.+@.+$/i'.
        if ($user->user_type === 'Aircrew') {
            $post_data = $this->validate(
                $request,
                [
                    'name' => 'required',
                    'gender' => 'required',
                    'email' => "required|email|unique:app_users,email,$id",
                    'phone_number' => "required|numeric|unique:app_users,phone_number,$id",
                    'date_of_birth' => "required|date",
                    'country_code' => 'required',

                    'status' => 'required',
                    'blacklist_reason' => 'required_if:status,Inactive,Blacklist',
                    'password' => 'nullable|min:8',
                    'password_reason' => 'required_with:password',
                ],

            );
        } else {
            $post_data = $this->validate($request, [
                'name' => 'required',
                'gender' => 'required',
                'email' => "required|email|unique:app_users,email,$id",
                'phone_number' => "required|numeric|unique:app_users,phone_number,$id",
                'date_of_birth' => "required|date",
                'country_code' => 'required',

                'status' => 'required',
                'blacklist_reason' => 'required_if:status,Inactive,Blacklist',
                'password' => 'nullable|min:8',
                'password_reason' => 'required_with:password',

            ]);
        }

        if ($request->company_id) {
            unset($post_data['c_code']);
            unset($post_data['c_name']);
        }


        if ($post_data['status'] !== 'Blacklist' && $post_data['status'] !== 'Inactive') {

            $post_data['blacklist_reason'] = '';
            if (isset($post_data['expiry_date'])) {
                $exd = Carbon::parse($post_data['expiry_date']);

                if ($exd->gt(Carbon::now()) && $user->status === 'Expired') {
                    $post_data['status'] = 'Active';
                }
            }
        }
        if (isset($post_data['password']) && $post_data['password']) {
            $post_data['password'] = Hash::make($request->password);
            $post_data['password_reset'] = 1;
        } else {
            unset($post_data['password']);
        }

        $user->update($post_data);
        return redirect('admin/app-user');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            
            // finally delete app user
            AppUser::where('id', $id)->delete();
            AdminLogger::log('delete', AppUser::class, $id);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'App user and all related data deleted successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
   


    public function toggleSuspend(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:user_wallet_vouchers,id',
            'suspend_voucher' => 'required|in:0,1'
        ]);

        $voucher = UserWalletVoucher::findOrFail($request->id);

        $voucher->suspend_voucher = $request->suspend_voucher;
        $voucher->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Suspend status updated'
        ]);
    }
}
