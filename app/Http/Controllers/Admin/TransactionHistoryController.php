<?php

namespace App\Http\Controllers\Admin;

use App\Models\TransactionHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Reward;
use App\Models\UserWalletVoucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionHistoryController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.transaction-history.";
        $permission_prefix = $this->permission_prefix = 'transaction-history';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Transaction History',
            'module_base_url' => url('admin/transaction-history')
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
        $qb = TransactionHistory::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'transaction_id',
            'order_id',
            'payment_mode',
            'status',
            'created_at',
        ]);

        $rows = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {
            $index = $startIndex + $i + 1;

            $paymentMode = match ((int)$row->payment_mode) {
                1 => 'Card',
                default => 'Other',
            };

           $action = "
            <a href='javascript:void(0)' class='view_vouchers' data-receipt='{$row->receipt_no}'>
                <i class='mdi mdi-eye text-info font-size-18'></i>
            </a>
        ";



            $final_data[] = [
                'sr_no'            => $index,
                'transaction_id'   => $row->transaction_id,
                'user'              => $row->user_id,
                'receipt_no'              => $row->receipt_no,
                'payment_mode'     => $paymentMode,
                'request_amount'   => number_format($row->request_amount, 2) . ' ' . $row->request_ccy,
                'authorized_amount'=> $row->authorized_amount
                                        ? number_format($row->authorized_amount, 2) . ' ' . $row->authorized_ccy
                                        : '-',
                'status'           => $row->status,
                'created_at'       => $row->created_at
                                        ? $row->created_at->format(config('safra.date-format'))
                                        : '-',
                'action' => $action,
              
            ];


            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rows->count(),
        ];
    }

    public function voucherDetail($receipt_no)
    {
        $vouchers = UserWalletVoucher::with(['reward.merchant'])
            ->where('receipt_no', $receipt_no)
            ->get();

        if ($vouchers->isEmpty()) {
            return response()->json([
                'status' => false,
                'html' => '<div class="p-3">No vouchers found</div>'
            ]);
        }

        $html = view($this->view_file_path . 'voucher-detail-modal', [
            'vouchers' => $vouchers
        ])->render();

        return response()->json([
            'status' => true,
            'html' => $html
        ]);
    }


   
}
