<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\DeleteRoomRequest;
use App\Http\Requests\Room\RoomRequest;
use App\Http\Requests\Room\FilterRoomRequest;
use App\Models\Lodging;
use App\Services\Lodging\LodgingService;
use App\Services\Room\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function create(RoomRequest $request){
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

        if(!(new LodgingService())->detailLodging($lodgingId)){
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

    public function update(RoomRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if(!isset($data['id'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Id is required',
                ]]
            ]);
        }

        if(!RoomService::isOwnerRoom($data['id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new RoomService();
        $result = $service->update($data, $data['id']);

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

    public function delete(DeleteRoomRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if(!RoomService::isOwnerRoom($data['room_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new RoomService();
        $result = $service->softDelete($data['room_id']);
        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => 'Xoá phòng thành công!'
            ]
        ]);
    }
}
