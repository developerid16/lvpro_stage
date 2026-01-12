<?php

namespace App\Http\Controllers\Admin;

use App\Models\Announcement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    function __construct()
    {

        $this->view_file_path = "admin.announcement.";
        $permission_prefix = $this->permission_prefix = 'announcement';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Announcement',
            'module_base_url' => url('admin/announcement')
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
        // Always sort by display_order DESC (higher first)
        $query = Announcement::query()
            ->orderBy('display_order', 'desc');

        $result = $this->get_sort_offset_limit_query(
            $request,
            $query,
            ['title', 'display_order', 'start_date', 'end_date']
        );

        $rows  = $result['data'];
        $start = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $index = $start + $i + 1;

            // IMPORTANT: id must be at root level
            $final_data[$i] = [
                'id'    => $row->id,              // 🔥 REQUIRED for drag & drop
                'sr_no' => $index,
                'title' => $row->title,
                'date'  =>
                    optional($row->start_date)->format(config('safra.date-format')) .
                    ' to ' .
                    optional($row->end_date)->format(config('safra.date-format')),
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
        $rules = [
            'title'          => 'required|string|max:255',
            'display_order'  => 'required|numeric',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'description'    => 'required|string',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $messages = [
            'description.required' => 'Message field is required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Image upload
        if ($request->hasFile('image')) {
            $imageName = time() . rand() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $validated['image'] = $imageName;
        }

        // Map description → message
        $validated['message'] = $validated['description'];
        unset($validated['description']);

        Announcement::create($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Announcement created successfully'
        ]);
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
        $this->layout_data['data'] = Announcement::find($id);

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $rules = [
            'title'          => 'required|string|max:255',
            'display_order'  => 'required|numeric',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'description'    => 'required|string',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $messages = [
            'description.required' => 'Message field is required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $announcement = Announcement::findOrFail($id);

        // Map description → message
        $validated['message'] = $validated['description'];
        unset($validated['description']);

        // Image upload & replace
        if ($request->hasFile('image')) {
            $imageName = time() . rand() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $validated['image'] = $imageName;

            // delete old image safely
            if (!empty($announcement->image)) {
                $oldPath = public_path('images/' . $announcement->image);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }

        $announcement->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Announcement updated successfully'
        ]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Announcement::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Popup Delete Successfully']);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:announcements,id',
        ]);


        // Get current max display_order
        $maxOrder = Announcement::max('display_order');

        // Start assigning from max → downward
        $currentOrder = $maxOrder;

        foreach ($request->order as $row) {

            Announcement::where('id', $row['id'])
                ->update(['display_order' => $currentOrder]);

            $currentOrder--; // decrement for DESC order
        }

        return response()->json(['status' => 'success']);
    }



   
}
