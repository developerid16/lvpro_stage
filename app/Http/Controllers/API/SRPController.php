<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SafraAPIService;
use Illuminate\Http\Request;

class SRPController extends Controller
{
    protected $safraAPIService;
    public function __construct(SafraAPIService $safraAPIService)
    {
        $this->SafraAPIService = $safraAPIService;
    }

    /**
     * Summary of GetBasicDetailInfoByModified
     */
    public function masterListParameter(Request $request)
    {
    
        try {
            // $records = $this->SafraAPIService->GetSRPMerchandiseItemList($request->all());
            $records = $this->SafraAPIService
                ->getMerchandiseItemList();

            return response()->json([
                // 'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function merchandiseItemList(Request $request)
    {
        try {
            $records = $this->SafraAPIService
                ->getMerchandiseItemList();

            return response()->json([
                // 'status' => 'success',
                'data' => $records
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getTokenByMemberId(Request $request)
    {
        try {
            $records = $this->SafraAPIService
                ->getTokenByMemberId($request->all());

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
