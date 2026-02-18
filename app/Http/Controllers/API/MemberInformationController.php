<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SafraServiceAPI;
use Illuminate\Http\Request;

class MemberInformationController extends Controller
{
    protected $safraServiceAPI;
    private $limit;
    public function __construct(SafraServiceAPI $safraServiceAPI)
    {
        $this->safraServiceAPI = $safraServiceAPI;
        $this->limit = 5; // Default limit
    }

    /**
     * Summary of GetBasicDetailInfoByModified
     */
    public function GetBasicDetailInfoByModified(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? '2025-09-17';

        try {
            $records = $this->safraServiceAPI
                ->basicDetailInfoModified($lastModified, $limit);

            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of get basic detail info by ig
     */
    public function GetBasicDetailIg(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? '2025-09-17';

        try {
            $records = $this->safraServiceAPI
                ->getIGbasicdetail($lastModified, $limit);

            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of GetLatestTransaction
     */
    public function GetLatestTransaction(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? '2025-09-17';

        try {
            $records = $this->safraServiceAPI
                ->getLatestTransaction($lastModified, $limit);
            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of GetCustomerZone
     */
    public function GetCustomerZone(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? '2025-09-17';

        try {
            $records = $this->safraServiceAPI
                ->getCustomerZone($lastModified, $limit);
            return response()->json([
                'status' => 'success',
                'count' => count($records),
                'last_sync_time' => $lastModified,
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of infoByMethod
     */
    public function infoByMethod()
    {
        $memberid = $request->member_id ?? 'A100063879';
        if (empty($memberid)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member ID is required'
            ], 400);
        }
        try {
            $lastModified = $request->last_modified ?? '2025-09-17';
            $records = $this->safraServiceAPI->getInfoByMethod($lastModified, $memberid);
            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /** get shopping cart */
    public function getShoppingCart(Request $request)
    {
        $memberid = $request->member_id ?? 'A100063879';
        if (empty($memberid)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member ID is required'
            ], 400);
        }
        try {
            $lastModified = $request->last_modified ?? '2025-09-17';
            $records = $this->safraServiceAPI->getShoppingCartNo($lastModified, $memberid);
            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of clearShoppingCart
     */
    public function clearShoppingCart(Request $request)
    {
        $memberid = $request->member_id ?? 'A100063879';
        if (empty($memberid)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member ID is required'
            ], 400);
        }
        try {
            $lastModified = $request->last_modified ?? '2025-09-17';
            $records = $this->safraServiceAPI->clearShoppingCart($lastModified, $memberid);
            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}