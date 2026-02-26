<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\VoucherLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CsoPhysicalController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.cso-physical.";
        $permission_prefix = $this->permission_prefix = 'cso-physical';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'CSO - Physical Collection',
            'module_base_url' => url('admin/cso-physical')
        ];       
    }
  

    public function index(Request $request)
    {
       return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $qb = Purchase::query()
            ->with('reward') // relation needed
            ->select('purchases.*');

        // --------------------------------
        // FILTER: Member ID / Receipt No
        // --------------------------------
        if ($request->filled('filter_by') && $request->filled('filter_value')) {
            if ($request->filter_by === 'member_id') {
                $qb->where('member_id', $request->filter_value);
            }

            if ($request->filter_by === 'receipt_no') {
                $qb->where('receipt_no', 'like', '%' . $request->filter_value . '%');
            }
        }

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'receipt_no',
            'member_id',
            'created_at',
            'status',
        ]);

        $rows = $result['data']->get();
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows as $row) {

            $index = $startIndex + $i + 1;

            // -------------------------
            // STATUS LABEL
            // -------------------------
            $statusData = $this->purchaseStatus($row->status);

            $status = "<span class='badge bg-{$statusData['class']}'>
                            {$statusData['label']}
                    </span>";


            // -------------------------
            // ACTIONS
            // -------------------------
          $action = "<div class='d-flex gap-2'>";

            $action .= "
                <a href='javascript:void(0)'
                class='view-btn'
                data-id='{$row->id}'
                title='View'>
                    <i class='mdi mdi-eye text-primary action-icon font-size-18'></i>
                </a>";

            if ($row->status === 'completed') {
                $action .= "
                    <button 
                        type='button'
                        class='btn btn-sm btn-warning issue-btn'
                        data-id='{$row->id}'
                        data-receipt='{$row->receipt_no}'>
                        Issue
                    </button>";
            }

            // ✅ Show View File if file exists
            if (!empty($row->file)) {

                $fileUrl = asset($row->file);

                $action .= "
                    <a href='{$fileUrl}'
                        target='_blank'
                        class='view-btn'
                        title='View PDF'>
                        <span class='mdi mdi-file-pdf-box  font-size-18'></span>
                    </a>";
            }

            $action .= "</div>";

            
            $rewardType = match ((int) ($row->reward->reward_type ?? 0)) {
                0 => 'Digital',
                1 => 'Physical',
                default => '-',
            };

            $final_data[] = [
                'sr_no'               => $index,
                'receipt_no'          => $row->receipt_no,
                'reward_name'         => $row->reward->name ?? '-',
                'member_id'           => $row->member_id,
                'qty'                 => $row->qty,
                'payment_mode'        => strtoupper($row->payment_mode),
                'reward_type'          => $rewardType,
                'status'              => $status,
                'receipt_datetime'    => optional($row->created_at)->format(config('safra.date-format')),
                'redeemed_datetime' => $row->redeemed_at ? $row->redeemed_at->format(config('safra.date-format')) : '-',
                'action'              => $action,
                'remark'              => $row->remark,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $qb->count(),
        ];
    }

    public function view($id)
    {
        $purchase = Purchase::with('reward')->findOrFail($id);
        $statusData = $this->purchaseStatus($purchase->status);
    
        $rewardType = match ((int) ($purchase->reward->reward_type ?? 0)) {
            0 => 'Digital',
            1 => 'Physical',
            default => '-',
        };

        return response()->json([
            'receipt_no'        => $purchase->receipt_no,
            'reward_name'       => $purchase->reward->name ?? '-',
            'member_id'         => $purchase->member_id,
            'qty'               => $purchase->qty,
            'payment_mode'      => strtoupper($purchase->payment_mode),
            'reward_type' => $rewardType,
             'status_badge' => "<span class='badge bg-{$statusData['class']}'>
                        {$statusData['label']}
                   </span>",
            'receipt_datetime'  => optional($purchase->created_at)->format(config('safra.date-format')),
            'redeemed_datetime' => $purchase->redeemed_at ? $purchase->redeemed_at->format(config('safra.date-format')) : '-',
            'remark'            => $purchase->remark,
        ]);
    }


    public function issue(Request $request)
    {
        $request->validate([
            'remark' => 'nullable|string|max:500',
            'file'   => 'nullable|file|mimes:pdf|max:2048', // 2MB max
        ]);

        $purchase = Purchase::findOrFail($request->purchase_id);

        $pdfPath = null;

        // ✅ Upload PDF
        if ($request->hasFile('file')) {

            $file = $request->file('file');

            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

            $destinationPath = public_path('uploads/pdf');

            // create folder if not exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $fileName);

            $pdfPath = 'uploads/pdf/'.$fileName;
        }

        $purchase->update([
            'remark'      => $request->remark,
            'status'      => 'redeemed',
            'redeemed_at' => now(),
            'file'        => $pdfPath, // make sure column exists
        ]);

        VoucherLog::create([
            'user_id'    => $purchase->member_id,
            'reward_id'  => $purchase->reward_id,
            'action'     => 'redeemed',
            'receipt_no' => $purchase->unique_code,
            'qty'        => 1,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Purchase issued successfully.'
        ]);
    }

    // helper / controller private method / config
    function purchaseStatus($status)
    {
        return match ($status) {
            'pending' => ['label' => 'pending',   'class' => 'warning'],
            'completed' => ['label' => 'completed', 'class' => 'success'],
            'cancelled' => ['label' => 'cancelled', 'class' => 'danger'],
            'redeemed' => ['label' => 'redeemed', 'class' => 'info'],
            default => ['label' => 'Unknown', 'class' => 'secondary'],
        };
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
