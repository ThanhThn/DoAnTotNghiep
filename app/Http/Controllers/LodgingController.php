<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lodging\CreateLodgingRequest;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LodgingController extends Controller
{
    function listByUser()
    {
        $service = new LodgingService();
        $lodgings = $service->listByUserID(Auth::id());
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $lodgings,
            ]
        ]);
    }

    function create(CreateLodgingRequest $request)
    {
        $data = $request->only([
                "name", "address", "province_id", "district_id", "ward_id",
                "latitude", "longitude", "phone", "email", "type_id",
                "payment_date", "late_date", "area_room_default", "price_room_default"
            ]
        );
        $service = new LodgingService();
        $result = $service->create($data, Auth::id());

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result,
            ]
        ]);
    }
}
