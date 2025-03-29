<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Equipment\DeleteEquipmentRequest;
use App\Http\Requests\LodgingService\DeleteLodgingServiceRequest;
use App\Http\Requests\LodgingService\LodgingServiceRequest;
use App\Services\Lodging\LodgingService;
use App\Services\LodgingService\LodgingServiceManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LodgingServiceController extends Controller
{
    public function create(LodgingServiceRequest $request)
    {
        $data = $request->only('lodging_id', 'name', 'service_id', 'late_days', 'payment_date', 'unit_id', 'price_per_unit', 'room_ids');
        $userId = Auth::id();

        if(!LodgingService::isOwnerLodging($data['lodging_id'], $userId)) {
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }
        $service = new LodgingServiceManagerService();
        $result = $service->create($data);
        if(isset($result['errors'])) {
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

    public function listByLodging($lodgingId)
    {
        if(!isset($lodgingId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Lodging id is required',
                    'field' => 'lodging_id'
                ]]
            ]);
        }

        $lodging = (new LodgingService())->detailLodging($lodgingId);
        if(!$lodging){
            return response()->json([
                'status' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => [[
                    'message' => 'Lodging not found',
                    'field' => 'lodging_id'
                ]]
            ]);
        }

        $userId = Auth::id();

        if($lodging->user_id != $userId) {
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }
        $service = new LodgingServiceManagerService();

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->listByLodging($lodgingId)
            ]
        ]);
    }

    public function detail($id)
    {
        $service = new LodgingServiceManagerService();
        $result = $service->detail($id);
        if(isset($result['errors'])) {
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

    public function update(LodgingServiceRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        if(!LodgingService::isOwnerLodging($request['lodging_id'], $userId)) {
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }
        $service = new LodgingServiceManagerService();
        $result = $service->update($data['id'], $data);
        if(isset($result['errors'])) {
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

    public function list()
    {
        $service = new LodgingServiceManagerService();
        $result = $service->list();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function listByRoom(BaseRequest $request)
    {
        $request->validate([
            'room_id' => 'required|uuid|exists:rooms,id'
        ]);

        $service = new LodgingServiceManagerService();
        $result = $service->listByRoom($request['room_id']);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function delete(DeleteLodgingServiceRequest $request)
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

        $service = new LodgingServiceManagerService();
        $result = $service->softDelete($data['id']);
        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => 'Xoá dịch vụ thành công!'
            ]
        ]);
    }
}
