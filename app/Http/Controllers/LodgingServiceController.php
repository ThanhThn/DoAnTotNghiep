<?php

namespace App\Http\Controllers;

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

        if(!(new LodgingService())->get($lodgingId)){
            return response()->json([
                'status' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => [[
                    'message' => 'Lodging not found',
                    'field' => 'lodging_id'
                ]]
            ]);
        }

        $userId = Auth::id();

        if(!LodgingService::isOwnerLodging( $lodgingId, $userId)) {
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
}
