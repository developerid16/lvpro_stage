<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.notification.";
        $permission_prefix = $this->permission_prefix = 'notification';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Notification',
            'module_base_url' => url('admin/notification')
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
        $qb = Notification::query();

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'title',
            'type',
            'date',
            'created_at',
        ]);

        $rowsQueryBuilder = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rowsQueryBuilder->get() as $row) {
            $index = $startIndex + $i + 1;

            $action = "<div class='d-flex gap-3'>";

            if (Auth::user()->can($this->permission_prefix . '-edit')) {
                $action .= "<a href='javascript:void(0)' class='edit' data-id='{$row->id}'><i class='mdi mdi-pencil text-primary font-size-18'></i></a>";
            }

            $action .= "<a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'><i class='mdi mdi-delete text-danger font-size-18'></i></a>";
            $action .= "</div>";

            $final_data[$i] = [
                'sr_no'      => $index,
                'title'      => $row->title,
                'type'       => $row->type,
                'date'       => $row->date,
                'created_at' => $row->created_at?->format(config('safra.date-format')),
                'action'     => $action,
            ];
            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rowsQueryBuilder->count(),
        ];
    }

    /* -----------------------------------------------------
     * CREATE MODAL
     * ----------------------------------------------------- */
    public function create()
    {
        $this->layout_data['data'] = null;
        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'title'      => 'required|string|max:255',
            'short_desc' => 'required|string|max:255',
            'desc'       => 'required|string',
            'date'       => 'required|date',
            'type'       => 'required|string|max:100',
            'img'        => $request->id ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();


        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $name = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('uploads/image'), $name);
            $post_data['img'] = $name;
        }


        Notification::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Notification Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT
     * ----------------------------------------------------- */
    public function edit($id)
    {
        $this->layout_data['data'] = Notification::findOrFail($id);
        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    public function show($id)
    {
       
    }
    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

       
        $validator = Validator::make($request->all(), [
            'title'      => 'required|string|max:255',
            'short_desc' => 'required|string|max:255',
            'desc'       => 'required|string',
            'date'       => 'required|date',
            'type'       => 'required|string|max:100',
            'img'        => $request->id ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();


        if ($request->hasFile('img')) {

          if ($notification->img && file_exists(public_path($notification->img))) {
              unlink(public_path($notification->img));
          }

          $image = $request->file('img');
          $name = time().'_'.$image->getClientOriginalName();
          $image->move(public_path('uploads/image'), $name);
          $post_data['img'] = $name;
        }


        $notification->update($post_data);

        return response()->json(['status' => 'success', 'message' => 'Notification Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        AdminLogger::log('delete', Notification::class, $id);
        if ($notification->img && file_exists(public_path($notification->img))) {
            unlink(public_path($notification->img));
        }

        $notification->delete();

        return response()->json(['status' => 'success', 'message' => 'Notification Deleted Successfully']);
    }
}
