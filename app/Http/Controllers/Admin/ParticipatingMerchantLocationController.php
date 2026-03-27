<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminLogger;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ParticipatingMerchantLocation;
use App\Models\ParticipatingMerchant;
use App\Models\ClubLocation;
use Illuminate\Support\Facades\Validator;
// use Endroid\QrCode\Builder\Builder;
// use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
class ParticipatingMerchantLocationController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.participating-merchant-location.";
        $permission_prefix = $this->permission_prefix = 'participating-merchant-location';

        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title' => 'Participating Merchant Outlet',
            'module_base_url' => url('admin/participating-merchant-location')
        ];

        
        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    /* -----------------------------------------------------
     * LIST PAGE
     * ----------------------------------------------------- */
    public function index($merchant)
    {
        ParticipatingMerchantLocation::expireLocations();
        // Participating merchant (NOT Merchant)
        $pm = ParticipatingMerchant::findOrFail($merchant);

        $this->layout_data['participating_merchant_id'] = $pm->id;
        $this->layout_data['participating_merchant']   = $pm;

        // Club Locations dropdown depends on participating merchant
        $this->layout_data['locations'] = ClubLocation::orderBy('name', 'asc')->get();

        // Participating merchant list dropdown
        $this->layout_data['merchants'] = ParticipatingMerchant::where('id',$merchant)->orderBy('name', 'asc')->get();

        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    /* -----------------------------------------------------
     * DATATABLE
     * ----------------------------------------------------- */
    public function datatable(Request $request)
    {
        $qb = ParticipatingMerchantLocation::where(
            'participating_merchant_id',
            $request->participating_merchant_id
        );
        if (auth()->user()->role != 1) { // not Super Admin
            $qb->where('added_by', auth()->id());
        }

        $result = $this->get_sort_offset_limit_query($request, $qb, [
            'id',
            'name',
            'code',
            'start_date',
            'end_date',
            'club_location_id',
            'participating_merchant_id',
            'status',
            'created_at',
            'updated_at',
        ]);

        $rows = $result['data'];
        $startIndex = $result['offset'] ?? 0;

        $final_data = [];
        $i = 0;

        foreach ($rows->get() as $row) {

            $qrUrl = $row->qrcode ? asset('uploads/qrcode/'.$row->qrcode) : '';
            $qrBtn = '';

            if (!empty($row->qrcode)) {
                $qrUrl = asset('uploads/qrcode/'.$row->qrcode);

                $qrBtn = "<a href='{$qrUrl}' download>
                            <i class='mdi mdi-qrcode text-success action-icon font-size-18'></i>
                        </a>";
            }
            $final_data[$i] = [
                'sr_no'        => $startIndex + $i + 1,
                'name'         => $row->name,
                'code'         => $row->code,
                'start_date'   => $row->start_date->format(config('safra.date-format')),
                'end_date'     => $row->end_date->format(config('safra.date-format')),
                'club_location'=> optional($row->clubLocation)->name,
                'status'       => $row->status,
                'created_at'   => $row->created_at->format(config('safra.date-format')),
                'updated_at'   => $row->updated_at->format(config('safra.date-format')),

                'action' =>
                "<div class='d-flex gap-3 justify-content-center'>

                    <a href='javascript:void(0)' class='edit' data-id='{$row->id}'>
                        <i class='mdi mdi-pencil text-primary action-icon font-size-18'></i>
                    </a>

                    <a href='javascript:void(0)' class='delete_btn' data-id='{$row->id}'>
                        <i class='mdi mdi-delete text-danger action-icon font-size-18'></i>
                    </a>

                    {$qrBtn}
                </div>",
            ];

            $i++;
        }

        return [
            'items' => $final_data,
            'count' => $result['count'] ?? $rows->count(),
        ];
    }


    /* -----------------------------------------------------
     * CREATE MODAL
     * ----------------------------------------------------- */
    public function create($merchant)
    {
        $this->layout_data['participating_merchant_id'] = $merchant;
        $this->layout_data['data'] = null;

        $this->layout_data['locations'] = ClubLocation::all();
        $this->layout_data['merchants'] = ParticipatingMerchant::where('id',$merchant)->get();

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();
        return response()->json(['status' => 'success', 'html' => $html]);
    }

    
   
    /* -----------------------------------------------------
     * STORE
     * ----------------------------------------------------- */
    public function store(Request $request)
    {
       $validator = Validator::make($request->all(), [
           'name'              => 'required|string|max:255',           
           'participating_merchant_id' => 'required|exists:participating_merchants,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'club_location_id'  => 'nullable|exists:club_locations,id',
            'status'            => 'required|in:Active,Inactive',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('participating_merchant_location', 'code')
                    ->where(function ($query) use ($request) {
                        return $query->where('participating_merchant_id', $request->participating_merchant_id);
                    }),
            ],
            ],

         [
            'participating_merchant_id.required' => 'Participating merchant is required',
            'participating_merchant_id.exists'   => 'Invalid participating merchant',

            'name.required' => 'Name is required',
            'code.required' => 'Redemption code is required',
            'code.unique' => 'This code already exists for the selected merchant',

            'start_date.required' => 'Lease start date is required',
            'start_date.date'     => 'Lease start date must be a valid date',

            'end_date.required' => 'Lease end date is required',
            'end_date.after_or_equal' => 'Lease end date must be after or equal to start date',

            'club_location_id.exists'   => 'Invalid club location',

            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $post_data = $validator->validated();

        $qrName = 'qr_' . Str::random(8) . '.png';
        $encryptedCode  = ParticipatingMerchantLocation::encryptCode($post_data['code']);

        $path = public_path('uploads/qrcode');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // $result = Builder::create()
        //     ->writer(new PngWriter())
        //     ->data($encryptedCode)
        //     ->size(300)
        //     ->margin(10)
        //     ->build();

        $qrCode = QrCode::create($encryptedCode)
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $result->saveToFile($path.'/'.$qrName);

        $post_data['qrcode'] = $qrName;
        $post_data['encrypted_code'] = $encryptedCode;
        $post_data['club_location_id'] = !empty($request->club_location_id)  ? $request->club_location_id  : null;
        ParticipatingMerchantLocation::create($post_data);

        return response()->json(['status' => 'success', 'message' => 'Participating Merchant Location Created Successfully']);
    }

    /* -----------------------------------------------------
     * EDIT MODAL
     * ----------------------------------------------------- */
    public function edit($id)
    {
        
        $row = ParticipatingMerchantLocation::findOrFail($id);

        $this->layout_data['data'] = $row;
        $this->layout_data['participating_merchant_id'] = $row->participating_merchant_id;

        $this->layout_data['locations'] = ClubLocation::all();
        $this->layout_data['merchants'] = ParticipatingMerchant::where('id',$row->participating_merchant_id)->get();

        $html = view($this->view_file_path . 'add-edit-modal', $this->layout_data)->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /* -----------------------------------------------------
     * UPDATE
     * ----------------------------------------------------- */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'club_location_id'  => 'nullable|exists:club_locations,id',
            'status'            => 'required|in:Active,Inactive',
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('participating_merchant_location', 'code')
                    ->where(fn ($q) => $q->where('participating_merchant_id', $request->participating_merchant_id))
                    ->ignore($id), // ✅ correct
            ],
            
        ], [
            'name.required' => 'Name is required',
            'code.required' => 'Redemption code is required',
            'code.unique' => 'This code already exists for the selected merchant',

            'start_date.required' => 'Lease start date is required',
            'start_date.date'     => 'Lease start date must be a valid date',

            'end_date.required' => 'Lease end date is required',
            'end_date.after_or_equal' => 'Lease end date must be after or equal to start date',

            'club_location_id.exists'   => 'Invalid club location',

            'status.required' => 'Status is required',
            'status.in'       => 'Invalid status value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $record = ParticipatingMerchantLocation::findOrFail($id);

        // Check if code changed
        if ($record->code != $data['code']) {

           $qrName = 'qr_' . Str::random(8) . '.png';
            $encryptedCode  = ParticipatingMerchantLocation::encryptCode($data['code']);

            $path = public_path('uploads/qrcode');

            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            // $result = Builder::create()
            //     ->writer(new PngWriter())
            //     ->data($encryptedCode)
            //     ->size(300)
            //     ->margin(10)
            //     ->build();

            // $result->saveToFile($path.'/'.$qrName);

            $qrCode = QrCode::create($encryptedCode)
                ->setSize(300)
                ->setMargin(10);

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            $result->saveToFile($path.'/'.$qrName);

                $data['encrypted_code'] = $encryptedCode;

                $data['qrcode'] = $qrName;
            }

        $data['club_location_id'] = !empty($request->club_location_id)  ? $request->club_location_id  : null;

        ParticipatingMerchantLocation::findOrFail($id)->update($data);

        return response()->json(['status' => 'success', 'message' => 'Participating Merchant Location Updated Successfully']);
    }

    /* -----------------------------------------------------
     * DELETE
     * ----------------------------------------------------- */
    public function destroy($id)
    {
        ParticipatingMerchantLocation::destroy($id);
        AdminLogger::log('delete', ParticipatingMerchantLocation::class, $id);

        return response()->json(['status' => 'success', 'message' => 'Participating Merchant Location Deleted Successfully']);
    }


    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx|max:2048',
            'participating_merchant_id' => 'required|exists:participating_merchants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $merchantId = $request->participating_merchant_id;

        // Read file
        $rows = Excel::toArray([], $request->file('file'))[0];

        if (empty($rows)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File is empty'
            ]);
        }

        unset($rows[0]); // remove header

        $errors = [];
        $insertData = [];

        DB::beginTransaction();

        try {

            foreach ($rows as $index => $row) {

                $rowNumber = $index + 2;

                $name  = $row[0] ?? null;
                $code  = $row[1] ?? null;
                $start = $row[2] ?? null;
                $end   = $row[3] ?? null;
                $clubLocationName = $row[4] ?? null;

                // ✅ Basic validation
                if (!$name || !$code || !$start || !$end) {
                    $errors[] = "Row {$rowNumber}: Missing required fields";
                    continue;
                }

                // ✅ Check duplicate in DB (same merchant)
                $exists = ParticipatingMerchantLocation::where('code', $code)
                    ->where('participating_merchant_id', $merchantId)
                    ->exists();

                if ($exists) {
                    $errors[] = "Row {$rowNumber}: Code '{$code}' already exists";
                    continue;
                }

                // ✅ Optional: check duplicate inside file
                if (collect($insertData)->where('code', $code)->count()) {
                    $errors[] = "Row {$rowNumber}: Duplicate code in file '{$code}'";
                    continue;
                }
                $clubLocationId = null;

                if (!empty($clubLocationName)) {

                    $clubLocation = \App\Models\ClubLocation::where('name', $clubLocationName)->first();

                    if (!$clubLocation) {
                        $errors[] = "Row {$rowNumber}: Club location '{$clubLocationName}' not found";
                        continue;
                    }

                    $clubLocationId = $clubLocation->id;
                }

                // ✅ Generate QR
                $qrName = 'qr_' . Str::random(8) . '.png';
                $encryptedCode = ParticipatingMerchantLocation::encryptCode($code);

                $path = public_path('uploads/qrcode');

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }

                $qrCode = QrCode::create($encryptedCode)
                    ->setSize(300)
                    ->setMargin(10);

                $writer = new PngWriter();
                $result = $writer->write($qrCode);

                $result->saveToFile($path . '/' . $qrName);

                // ✅ Prepare data
                $insertData[] = [
                    'name' => $name,
                    'code' => $code,
                    'start_date' => $start,
                    'end_date' => $end,
                    'participating_merchant_id' => $merchantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'qrcode' => $qrName,
                    'club_location_id' => $clubLocationId,

                ];
            }

            // ❌ If any error → rollback
            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'file' => $errors
                    ]
                ], 422);
            }

            // ✅ Insert
            ParticipatingMerchantLocation::insert($insertData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded & data stored successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample_participating_merchant.csv"',
        ];

        $columns = ['name', 'code', 'start_date', 'end_date','club_location'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, $columns);

            // Sample data
            fputcsv($file, [
                'Shop 1',
                'ABC123',
                '2025-01-01',
                '2025-12-31',
                'Main Branch',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
