<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipment\CreateEquipmentRequest;
use App\Http\Requests\Equipment\DetailEquipmentRequest;
use App\Http\Requests\Equipment\ListEquipmentRequest;
use App\Http\Requests\Equipment\UpdateEquipmentRequest;
use App\Services\Equipment\EquipmentService;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    public function create(CreateEquipmentRequest $request)
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

        $service = new EquipmentService();

        $result = $service->create($data);

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

    public function detail(DetailEquipmentRequest $request,$equipmentId)
    {
        $service = new EquipmentService();
        $result = $service->detail($equipmentId);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function update(UpdateEquipmentRequest $request)
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

        $service = new EquipmentService();

        $result = $service->update($data['id'],$data);

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

    public function listByLodging(ListEquipmentRequest $request)
    {
        $data = $request->all();

        $service = new EquipmentService();
        $result = $service->listByLodging($data, $data['lodging_id']);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }
}
