<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomUsage\CloseRoomUsageRequest;
use App\Http\Requests\RoomUsage\ListRoomUsageRequest;
use App\Services\Lodging\LodgingService;
use App\Services\RoomUsageService\RoomUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomUsageController extends Controller
{
    public function listUsageNeedCloseByLodging(ListRoomUsageRequest $request)
    {
        $lodgingId = $request->input('lodging_id');
        $userId = Auth::id();

        if(!LodgingService::isOwnerLodging($lodgingId, $userId)){
            return response()->json([
               'status' => JsonResponse::HTTP_UNAUTHORIZED,
               'errors' => [[
                   'message' => 'Unauthorized'
               ]]
            ]);
        }

        $service = new RoomUsageService();
        $result = $service->listNeedCloseByLodging($lodgingId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' =>  [
                'data' => $result
            ]
        ]);
    }

    public function closeRoomUsage(CloseRoomUsageRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if(!LodgingService::isOwnerLodging($data['lodging_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new RoomUsageService();
        $result = $service->updateFinalRoomUsage($data);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }
}
