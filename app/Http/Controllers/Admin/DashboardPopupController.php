<?php

namespace App\Http\Controllers\Admin;

use App\Models\DashboardPopup;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardPopupController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.dashboardpopup.";
        $permission_prefix = $this->permission_prefix = 'dashboardpopup';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Dashboard popup',
            'module_base_url' => url('admin/dashboardpopup')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
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
        // Always order by display_order DESC (higher first)
        $query = DashboardPopup::query()
            ->orderBy('order', 'desc'); // or display_order if column name is that

        $result = $this->get_sort_offset_limit_query(
            $request,
            $query,
            ['code', 'name', 'status', 'frequency']
        );

        $rows  = $result['data'];
        $start = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $index = $start + $i + 1;

            $final_data[$i] = [
                // 🔥 REQUIRED for drag & drop
                'id'        => $row->id,

                'sr_no'     => $index,
                'code'      => $row->code,
                'name'      => $row->name,
                'order'     => $row->order,
                'status'    => $row->status,
                'frequency' => $row->frequency,
                'date'      =>
                    optional($row->start_date)->format(config('shilla.date-format')) .
                    ' to ' .
                    optional($row->end_date)->format(config('shilla.date-format')),
                'image'     => "<a href='" . asset("images/$row->image") . "' data-lightbox='set-$row->id'>
                                    <img src='" . asset("images/$row->image") . "'
                                        class='avatar-sm me-3 mx-lg-auto mb-3 mt-1
                                        float-start float-lg-none rounded-circle'
                                        alt='img'>
                                </a>",
            ];

            // ---------------- ACTIONS ----------------
            $action = "<div class='d-flex gap-3'>";
            $action .= "<span class='text-muted drag-indicator' title='Drag to reorder'>
                <i class='mdi mdi-drag'></i>
            </span>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                                <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                            </a>";
            }

            if (Auth::user()->can($this->permission_prefix . '-delete')) {
                $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                                <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                            </a>";
            }

            $final_data[$i]['action'] = $action . "</div>";

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'],
        ];
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
        $post_data = $request->validate([
            'name'        => 'required|string|max:25',
            'button'   => 'required|string|max:10',
            'order'         => 'required|numeric',
            'popup_type'    => 'required|in:once-a-day,always',           
            'start_date'    => 'required',
            'end_date'      => 'required|after_or_equal:start_date',
            'description'   => 'required|string',
        ]);



        if ($request->hasFile('image')) {
            $imageName = time() . rand() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $post_data['image'] = $imageName;
        }

        $post_data['frequency'] = $request->popup_type;
        DashboardPopup::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Poupup Created Successfully']);
    }

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
        $this->layout_data['data'] = DashboardPopup::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post_data = $this->validate($request, [
            'name' => 'required|max:25',
            'button' => 'required',
            'frequency' => 'required',
            'order' => 'required',
            'description' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'nullable|required|date|after_or_equal:' . $request->start_date,
        ]);


        $rd = DashboardPopup::find($id);

        if ($request->hasFile('image')) {
            $imageName = time() . rand() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $post_data['image'] = $imageName;
            try {
                unlink(filename: public_path("images/$rd->image"));
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        $rd->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Popup Update Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DashboardPopup::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Popup Delete Successfully']);
    }

     public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:dashboard_popups,id',
        ]);

        
        // Get current max display_order
        $maxOrder = DashboardPopup::max('order');

        // Start assigning from max → downward
        $currentOrder = $maxOrder;

        foreach ($request->order as $row) {

            DashboardPopup::where('id', $row['id'])
                ->update(['order' => $currentOrder]);

            $currentOrder--; // decrement for DESC order
        }

        return response()->json(['status' => 'success']);
    }


}
