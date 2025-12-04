<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tier;
use App\Models\Reward;
use Illuminate\Http\Request;
use App\Models\TierMilestone;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TierController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.tier.";
        $permission_prefix = $this->permission_prefix = 'tiers';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Tier',
            'module_base_url' => url('admin/tiers')
        ];

        // $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        // $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        // $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        // $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {

    //     $tm = TierMilestone::orderBy('max', 'desc')->first();

    //     $tire = Tier::get();
    //     $this->layout_data['tier'] = $tire;
    //     $this->layout_data['milestones'] = TierMilestone::orderBy('min','asc')->paginate(20);

    //     $this->layout_data['rewards'] =  Reward::where('status', 'Active')->get(['code', 'name', 'id']);
    //     $this->layout_data['last_milestone'] =   $tm;
    //     return view($this->view_file_path . "manage")->with($this->layout_data);
    // }

    public function index(Request $request)
    {
       return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function datatable(Request $request)
    {
        $qb = Tier::query();

        
        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id', // <-- replace with your actual custom Tier ID column name
            'tier_id', // <-- replace with actual Safra tier name column (or 'tier_name' if that's it)
            'tier_name', // <-- replace with actual Safra tier name column (or 'tier_name' if that's it)
            'alias_name',
            'created_at',
            'updated_at',
        ]);

        // $result['data'] should be a query builder limited to page results
        // If your helper returns the builder already executed (collection) adapt accordingly
        $rowsQueryBuilder = $result['data'];

        // Determine starting serial number (if your helper provides offset you can use it; fallback to 0)
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;
        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1; // sr_no

            $tierIdentifier = $row->id ?? $row->id; // replace tier_identifier if different
            $safraTierName  = $row->tier_name ?? $row->tier_name ?? ''; // replace column name if needed
            $tierId  = $row->tier_id ?? $row->tier_id ?? ''; // replace column name if needed
            $aliasName      = $row->alias_name ?? '';

            $createdAt = $row->created_at ? $row->created_at->format('d-m-Y h:i:s A') : '';
            $updatedAt = $row->updated_at ? $row->updated_at->format('d-m-Y h:i:s A') : '';


            // actions
            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}' title='Edit'><i class='mdi mdi-pencil text-primary action-icon font-size-18'></i></a>";
            }
            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}' title='Delete'><i class='mdi mdi-delete text-danger action-icon font-size-18'></i></a>";
            }

            // view/assign link (keeps your original pattern)

            $action .= "</div>";

            $final_data[$i] = [
                'sr_no'            => $index,
                'tier_id'       => $tierId,        // Alias Name column
                'alias_name'       => $aliasName,        // Alias Name column
                'tier_name'  => $safraTierName,    // Safra's Tier Name column
                'created_at'       => $createdAt,
                'updated_at'       => $updatedAt,
                'action'           => $action,
            ];

            $i++;
        }

        $data = [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];

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
         $post_data = $this->validate($request, [
            'tier_id' => ['required', 'regex:/^[A-Za-z0-9\-]+$/'],
            'alias_name' => 'required',
            'tier_name' => 'required',

        ]);

        Tier::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Tier Created Successfully']);
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
         //
        $this->layout_data['data'] = Tier::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
         //
        $post_data = $this->validate($request, [
            'tier_id' => ['required', 'regex:/^[A-Za-z0-9\-]+$/'],
            "alias_name" => "required",
            "tier_name" => "required",

        ]);
        Tier::find($id)->update($post_data);
        return response()->json(['status' => 'success', 'message' => 'Tier Update Successfully']);
    }
    function milestoneSave(Request $request)
    {         
        return redirect('admin/tiers');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
         Tier::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Tier Delete Successfully']);
    }
}
