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
            $records = $this->SafraAPIService
                ->getSRPMasterListParameter($request->all());

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
     * Summary of merchandiseItemList
     */ 
    public function merchandiseItemList(Request $request)
    {
        try {
            $records = $this->SafraAPIService
                ->getMerchandiseItemList();

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

    public function getEmailNotification(Request $request)
    {
        try {
            $records = $this->SafraAPIService
                ->getEmailNotification([
                    'Token_list' => $request->get('Token_list') // Capital T
                ]);

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
