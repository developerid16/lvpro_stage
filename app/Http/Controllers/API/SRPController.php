<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SafraAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SRPController extends Controller
{
    protected $safraAPIService;
    private $limit;
    private $last_modified;
    public function __construct(SafraAPIService $safraAPIService)
    {
        $this->SafraAPIService = $safraAPIService;
        $this->last_modified = Config::get('safra.last_modified');
        $this->limit = Config::get('safra.limit');
    }

    /**
     * Summary of GetBasicDetailInfoByModified
     */
    public function masterListParameter(Request $request)
    {
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

        try {
            $records = $this->SafraAPIService
                ->getSRPMasterListParameter();

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
        $limit = $request->limit ?? $this->limit;
        $lastModified = $request->last_modified ?? $this->last_modified;

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


}
