<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contract\CreateContractRequest;
use App\Models\Lodging;
use App\Services\Contract\ContractService;
use App\Services\Lodging\LodgingService;
use App\Services\Room\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    public function create(CreateContractRequest $request)
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

        $service  = new ContractService();
        $result = $service->createContract($data);
        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' =>  [
                'data' => $result
            ]
        ]);
    }
}
