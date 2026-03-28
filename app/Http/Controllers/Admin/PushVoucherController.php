<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PushVoucherMember;
use Illuminate\Support\Facades\Auth;

class PushVoucherController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.push-voucher.";
        $permission_prefix = $this->permission_prefix = 'push-voucher';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Push Voucher',
            'module_base_url' => url('admin/push-voucher')
        ];


        $this->middleware("active.permission:$permission_prefix-log", ['only' => ['index', 'datatable']]);
        // $this->middleware("active.permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        // $this->middleware("active.permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        // $this->middleware("active.permission:$permission_prefix-delete", ['only' => ['destroy']]);

        $this->middleware(function ($request, $next) {
            $activeDeptId = session('active_department_id');
            $user = Auth::user();

            $activeRoles = $user->roles->filter(function ($role) use ($activeDeptId) {
                return (string)$role->department === (string)$activeDeptId;
            });

            if ($activeRoles->isEmpty()) {
                $activeRoles = $user->roles;
            }

            $activeRole = $activeRoles->first();

            $this->activeDeptId     = $activeDeptId;
            $this->activeLocationId = session('active_club_location_id');
            $this->activeRoleId     = $activeRole?->id;

            return $next($request);
        });
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
        $qb = PushVoucherMember::query()
            ->with(['reward']); // Load reward relation
        if (!Auth::user()->hasRole('Super Admin')) {
            $qb->where('active_department_id', $this->activeDeptId);
            $qb->where('active_club_location_id', $this->activeLocationId);
            $qb->where('active_role_id', $this->activeRoleId);
        }
        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'type',
            'reward_id',
            'member_id',
            'created_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

           
            $createdAt =  $row->created_at->format(config('safra.date-format'));
          

            // -------------------------
            // FINAL OUTPUT ROW
            // -------------------------
            $final_data[$i] = [
                'sr_no'        => $index,
                'type'         => $row->type == 0 ? 'Push Voucher By Member ID' : 'Push Voucher By Parameter',
                'reward_name'  => $row->reward?->name ?? '-',      // Reward relation
                'member_id'    => $row->member_id,
                'created_at'   => $createdAt,
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }


    /* -----------------------------------------------------
     * SHOW CREATE FORM MODAL
     * ----------------------------------------------------- */
    public function create()
    {
       
    }


    /* -----------------------------------------------------
     * STORE category
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
       
    }


    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
       
    }


    /* -----------------------------------------------------
     * UPDATE category
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
    }


    /* -----------------------------------------------------
     * DELETE category
     * ----------------------------------------------------- */
    public function destroy($id)
    {
       
    }

}
