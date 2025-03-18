<?php

namespace App\Http\Controllers;

use App\Services\Lodging\LodgingService;
use App\Services\Permission\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function listByUser(Request $request)
    {
        $lodgingId = $request->input('lodging_id');

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

        $userId = Auth::id();

        $service = new PermissionService($lodgingId);

        if(LodgingService::isOwnerLodging($lodgingId, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'body' => [
                    'data' => $service->listAll()
                ]
            ]);
        }

        $permissions = $service->listByUser($userId);

        if(!$permissions){
            return response()->json([
                'status' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => [
                    ['message' => 'Not permission']
                ]
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $permissions
            ]
        ]);
    }
}
