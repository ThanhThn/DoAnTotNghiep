<?php

namespace App\Http\Controllers;

use App\Services\Service\ServiceManagerService;
use App\Services\Unit\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitServiceController extends Controller
{
    public function unitsByService(Request $request)
    {
        $serviceId = $request->service_id;
        if(!isset($serviceId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Service id not provided',
                    'field' => 'service_id'
                ]]
            ]);
        }

        if(!(new ServiceManagerService())->findById($serviceId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Service not found',
                    'field' => 'service_id'
                ]]
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body'  => [
                'data' => (new UnitService())->listByService($serviceId)
            ]
        ]);
    }
}
