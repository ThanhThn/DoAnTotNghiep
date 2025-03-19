<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lodging\CreateLodgingRequest;
use App\Http\Requests\Lodging\LodgingRequest;
use App\Http\Requests\Lodging\OverviewRequest;
use App\Http\Requests\Lodging\UpdateLodgingRequest;
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
                "payment_date", "late_days", "area_room_default", "price_room_default"
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

    function overview(OverviewRequest $request)
    {
        $data= $request->all();
        $userId = Auth::id();
        if(!LodgingService::isOwnerLodging($data['lodging_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new LodgingService();
        return [
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->overview($data)
            ]
        ];
    }

    function detail(LodgingRequest $request, $lodgingId)
    {
        $service = new LodgingService();
        $result = $service->detailLodging($lodgingId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }


    function update(UpdateLodgingRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        if(!LodgingService::isOwnerLodging($data['id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new LodgingService();
        $result = $service->updateLodging($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }


    function softDelete(LodgingRequest $request, $lodgingId)
    {
        $useId = Auth::id();
        if(!LodgingService::isOwnerLodging($lodgingId, $useId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [
                    [
                        'message' => 'Unauthorized'
                    ]
                ]
            ]);
        }

        $service = new LodgingService();
        $result = $service->softDelete($lodgingId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => "Xoá nhà cho thuê thành công"
            ]
        ]);
    }
}
