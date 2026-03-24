<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Reward;
use App\Models\RewardUpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DataMigrateController extends Controller
{
    function __construct()
    {
        $this->view_file_path = "admin.data-migrate.";
        $permission_prefix = $this->permission_prefix = 'data-migrate';
        $this->layout_data = [
            'permission_prefix' => $permission_prefix,
            'title'             => 'Data Migrate',
            'module_base_url'   => url('admin/data-migrate')
        ];

        $this->middleware("permission:$permission_prefix-list|$permission_prefix-create|$permission_prefix-edit|$permission_prefix-delete", ['only' => ['index', 'datatable', 'store']]);
        $this->middleware("permission:$permission_prefix-create", ['only' => ['create', 'store']]);
        $this->middleware("permission:$permission_prefix-edit", ['only' => ['edit', 'update']]);
        $this->middleware("permission:$permission_prefix-delete", ['only' => ['destroy']]);
    }

    public function index()
    {
        return view($this->view_file_path . "index")->with($this->layout_data);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file     = $request->file('excel_file');
        $filePath = $file->getRealPath();

        // -----------------------------------------------
        // Step 1: Read raw bytes & fix encoding
        // -----------------------------------------------
        $rawContent = file_get_contents($filePath);

        $encoding = mb_detect_encoding($rawContent, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ASCII'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $rawContent = mb_convert_encoding($rawContent, 'UTF-8', $encoding);
        }

        // Strip BOM
        $rawContent = preg_replace('/^\xEF\xBB\xBF/', '', $rawContent);

        // -----------------------------------------------
        // Step 2: Parse CSV using str_getcsv on full content
        // This correctly handles:
        //   - quoted fields with embedded quotes like \" inside HTML
        //   - CRLF line endings
        //   - multiline quoted fields
        // -----------------------------------------------
        $dataRows = $this->parseCSV($rawContent);

        if (empty($dataRows)) {
            return back()->with('error', 'File has no data rows.');
        }

        $successCount = 0;
        $skipCount    = 0;
        $currentIndex = 0;

        DB::beginTransaction();

        try {
            foreach ($dataRows as $index => $row) {

                $currentIndex = $index;

                // Skip if title empty
                if (empty(trim($row['title'] ?? ''))) {
                    $skipCount++;
                    continue;
                }

                // -----------------------------------------------
                // 1) CATEGORY — getOrCreate
                // -----------------------------------------------
                $categoryId   = null;
                $categoryName = trim($row['category'] ?? '');

                if (!empty($categoryName)) {
                    $category   = Category::firstOrCreate(['name' => $categoryName]);
                    $categoryId = $category->id;
                }

                // -----------------------------------------------
                // 2) MERCHANT — fixed dummy id
                // -----------------------------------------------
                $itemCode   = trim($row['item_code'] ?? '');
                $merchantId = 82;

                // -----------------------------------------------
                // 3) PARSE DATES  (format: "1/30/2026 18:00")
                // -----------------------------------------------
                $publishStart = $this->parseDateTime($row['publish_start'] ?? null);
                $publishEnd   = $this->parseDateTime($row['publish_end'] ?? null);
                $salesStart   = $this->parseDateTime($row['sale_start'] ?? null);
                $salesEnd     = $this->parseDateTime($row['sale_end'] ?? null);

                // -----------------------------------------------
                // 4) REWARD TYPE
                //    is_evoucher=1 → digital (reward_type=0)
                //    is_evoucher=0 → physical (reward_type=1)
                // -----------------------------------------------
                $isEvoucher = (int) ($row['is_evoucher'] ?? 0);
                $rewardType = $isEvoucher === 1 ? 0 : 1;

                // -----------------------------------------------
                // 5) VOUCHER VALIDITY
                // -----------------------------------------------
                $voucherValidity = null;
                $expiryType      = 'no_expiry';

                $rawValidity = trim($row['evoucher_validity'] ?? '');
                if (!empty($rawValidity) && strtoupper($rawValidity) !== 'NULL') {
                    try {
                        $voucherValidity = Carbon::parse($rawValidity)->format('Y-m-d');
                        $expiryType      = 'fixed';
                    } catch (\Exception $e) {
                        $voucherValidity = null;
                        $expiryType      = 'no_expiry';
                    }
                }

                // -----------------------------------------------
                // 6) OTHER FIELDS
                // -----------------------------------------------
                $usualPrice         = is_numeric($row['UsualPrice'] ?? '')          ? (float) $row['UsualPrice']          : 0;
                $evoucherPrice      = is_numeric($row['evoucher_price'] ?? '')       ? (float) $row['evoucher_price']       : 0;
                $setQty             = is_numeric($row['evoucher_qty_per_set'] ?? '') ? (int)   $row['evoucher_qty_per_set'] : 1;
                $maxOrder           = is_numeric($row['max_order'] ?? '')            ? (int)   $row['max_order']            : 0;
                $friendlyUrl        = trim($row['FriendlyURL'] ?? '');
                $sendReminder       = (int) ($row['ToSentEmailReminder'] ?? 0);
                $publishInhouse     = (int) ($row['inhouse'] ?? 0);
                $publishIndependent = (int) ($row['internet'] ?? 0);

                $approved = (int) ($row['approved'] ?? 0);
                $isDraft  = $approved === 1 ? 2 : 1;

                $description = $this->cleanText($row['description'] ?? '');
                $termOfUse   = $this->cleanText($row['tnc'] ?? '');

                // -----------------------------------------------
                // 7) CREATE REWARD
                // -----------------------------------------------
                $reward = Reward::create([
                    'type'                => '0',
                    'name'                => $this->cleanText(trim($row['title'])),
                    'description'         => $description,
                    'term_of_use'         => $termOfUse,
                    'how_to_use'          => null,

                    'merchant_id'         => $merchantId,
                    'category_id'         => $categoryId,

                    'reward_type'         => $rewardType,
                    'usual_price'         => $usualPrice,
                    'max_order'           => $maxOrder,
                    'ax_item_code'        => $itemCode,

                    'publish_start_date'  => $publishStart['date'] ?? null,
                    'publish_start_time'  => $publishStart['time'] ?? null,
                    'publish_end_date'    => $publishEnd['date'] ?? null,
                    'publish_end_time'    => $publishEnd['time'] ?? null,

                    'sales_start_date'    => $salesStart['date'] ?? null,
                    'sales_start_time'    => $salesStart['time'] ?? null,
                    'sales_end_date'      => $salesEnd['date'] ?? null,
                    'sales_end_time'      => $salesEnd['time'] ?? null,

                    'expiry_type'         => $expiryType,
                    'voucher_validity'    => $voucherValidity,
                    'validity_month'      => null,

                    'voucher_value'       => $evoucherPrice,
                    'set_qty'             => $setQty,
                    'voucher_set'         => 1,
                    'inventory_type'      => 0,
                    'inventory_qty'       => 0,
                    'clearing_method'     => 0,

                    'friendly_url'        => $friendlyUrl ?: null,
                    'send_reminder'       => $sendReminder,
                    'publish_inhouse'     => $publishInhouse,
                    'publish_independent' => $publishIndependent,

                    'is_draft'            => $isDraft,
                    'status'              => 'pending',
                    'data_migrate_records'=> '1'
                ]);

                // -----------------------------------------------
                // 8) CREATE RewardUpdateRequest if approved=1
                // -----------------------------------------------
                if ($approved === 1) {
                    RewardUpdateRequest::create([
                        'reward_id'           => $reward->id,
                        'type'                => '0',
                        'status'              => 'pending',
                        'request_by'          => auth()->id(),

                        'name'                => $reward->name,
                        'description'         => $reward->description,
                        'term_of_use'         => $reward->term_of_use,
                        'how_to_use'          => null,

                        'merchant_id'         => $reward->merchant_id,
                        'category_id'         => $reward->category_id,
                        'reward_type'         => $reward->reward_type,
                        'usual_price'         => $reward->usual_price,
                        'max_order'           => $reward->max_order,
                        'ax_item_code'        => $reward->ax_item_code,

                        'publish_start_date'  => $reward->publish_start_date,
                        'publish_start_time'  => $reward->publish_start_time,
                        'publish_end_date'    => $reward->publish_end_date,
                        'publish_end_time'    => $reward->publish_end_time,

                        'sales_start_date'    => $reward->sales_start_date,
                        'sales_start_time'    => $reward->sales_start_time,
                        'sales_end_date'      => $reward->sales_end_date,
                        'sales_end_time'      => $reward->sales_end_time,

                        'expiry_type'         => $reward->expiry_type,
                        'voucher_validity'    => $reward->voucher_validity,
                        'validity_month'      => null,

                        'voucher_value'       => $reward->voucher_value,
                        'set_qty'             => $reward->set_qty,
                        'voucher_set'         => $reward->voucher_set,
                        'inventory_type'      => $reward->inventory_type,
                        'inventory_qty'       => $reward->inventory_qty,
                        'clearing_method'     => $reward->clearing_method,

                        'friendly_url'        => $reward->friendly_url,
                        'send_reminder'       => $reward->send_reminder,
                        'publish_inhouse'     => $reward->publish_inhouse,
                        'publish_independent' => $reward->publish_independent,

                        'is_draft'            => 2,
                    ]);
                }

                $successCount++;
            }

            DB::commit();

            return back()->with('success', "Migration complete! {$successCount} records imported" . ($skipCount > 0 ? ", {$skipCount} skipped." : "."));

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Migration failed at row ' . ($currentIndex + 2) . ': ' . $e->getMessage());
        }
    }

    // -----------------------------------------------
    // CSV Parser — handles:
    //   - quoted fields with embedded " (like href=\"...\")
    //   - CRLF and LF line endings
    //   - multiline quoted fields
    // -----------------------------------------------
    private function parseCSV(string $content): array
    {
        // Normalize CRLF → LF only OUTSIDE quoted fields
        // We use str_getcsv on the whole content split by real row boundaries

        // Split into rows respecting quoted fields
        $rows = [];
        $current = '';
        $inQuotes = false;
        $len = strlen($content);

        for ($i = 0; $i < $len; $i++) {
            $ch = $content[$i];

            if ($ch === '"') {
                // Toggle quote state, handle "" escaped quotes
                if ($inQuotes && isset($content[$i + 1]) && $content[$i + 1] === '"') {
                    $current .= '"';
                    $i++; // skip next quote
                } else {
                    $inQuotes = !$inQuotes;
                    $current .= $ch;
                }
            } elseif (($ch === "\n" || ($ch === "\r" && isset($content[$i+1]) && $content[$i+1] === "\n")) && !$inQuotes) {
                // Real row boundary
                if ($ch === "\r") $i++; // skip \n after \r
                $rows[] = $current;
                $current = '';
            } elseif ($ch === "\r" && !$inQuotes) {
                // Lone \r
                $rows[] = $current;
                $current = '';
            } else {
                $current .= $ch;
            }
        }

        // Last row without trailing newline
        if ($current !== '') {
            $rows[] = $current;
        }

        // Remove empty rows
        $rows = array_values(array_filter($rows, fn($r) => trim($r) !== ''));

        if (empty($rows)) return [];

        // Parse header
        $headers = array_map('trim', str_getcsv($rows[0], ',', '"'));

        $dataRows = [];
        foreach (array_slice($rows, 1) as $rowStr) {
            $cols = str_getcsv($rowStr, ',', '"');

            // Normalize column count
            if (count($cols) < count($headers)) {
                $cols = array_pad($cols, count($headers), '');
            } elseif (count($cols) > count($headers)) {
                $cols = array_slice($cols, 0, count($headers));
            }

            $dataRows[] = array_combine($headers, $cols);
        }

        return $dataRows;
    }

    // -----------------------------------------------
    // Helper: Parse "1/30/2026 18:00" → date + time
    // -----------------------------------------------
    private function parseDateTime(?string $value): ?array
    {
        if (empty($value) || strtoupper(trim($value)) === 'NULL') {
            return null;
        }
        try {
            $dt = Carbon::parse(trim($value));
            return [
                'date' => $dt->format('Y-m-d'),
                'time' => $dt->format('H:i:s'),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    // -----------------------------------------------
    // Helper: Clean Windows-1252 / invalid UTF-8 chars
    // -----------------------------------------------
    private function cleanText(?string $value): string
    {
        if ($value === null) return '';

        $search  = ["\x95", "\x96", "\x97", "\x91", "\x92", "\x93", "\x94", "\x85", "\x99"];
        $replace = ['•',    '–',    '—',    "'",    "'",    '"',    '"',    '…',    '™'   ];
        $value   = str_replace($search, $replace, $value);

        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        $value = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $value);

        return $value;
    }
}