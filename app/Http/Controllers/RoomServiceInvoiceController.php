<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomUsage\CloseRoomServiceInvoiceRequest;
use App\Http\Requests\RoomUsage\ListRoomSerivceInvoiceRequest;
use App\Services\Lodging\LodgingService;
use App\Services\RoomServiceInvoice\RoomServiceInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomServiceInvoiceController extends Controller
{
    public function listRoomServiceNeedCloseByLodging(ListRoomSerivceInvoiceRequest $request)
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

        $service = new RoomServiceInvoiceService();
        $result = $service->listNeedCloseByLodging($lodgingId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' =>  [
                'data' => $result
            ]
        ]);
    }

    public function closeRoomService(CloseRoomServiceInvoiceRequest $request)
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

        $service = new RoomServiceInvoiceService();
        $result = $service->updateFinalRoomServiceInvoice($data);

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
