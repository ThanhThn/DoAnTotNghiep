<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\CreateRoomRequest;
use App\Http\Requests\Room\FilterRoomRequest;
use App\Models\Lodging;
use App\Services\Lodging\LodgingService;
use App\Services\Room\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function create(CreateRoomRequest $request){
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

        $service = new RoomService();
        $result = $service->createRoom($data);

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

    public function listByLodging($lodgingId, Request $request){
        $status = $request->input("status");
        if(!isset($lodgingId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Lodging id is required',
                    'field' => 'lodging_id'
                ]]
            ]);
        }

        if(!(new LodgingService())->get($lodgingId)){
            return response()->json([
                'status' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => [[
                    'message' => 'Lodging not found',
                    'field' => 'lodging_id'
                ]]
            ]);
        }
        $service = new RoomService();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->listRoomsByLodging($lodgingId, ['status' => $status])
            ]
        ]);
    }

    public function filter(FilterRoomRequest $request)
    {
        $data = $request->all();
        $service = new RoomService();
        return response()->json([
           'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->filterRooms($data, $data['lodging_id'])
            ]
        ]);
    }

    public function detail($id)
    {
        $service  = new RoomService();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->detail($id)
            ]
        ]);
    }
}
