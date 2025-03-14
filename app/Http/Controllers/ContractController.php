<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contract\CreateContractRequest;
use App\Http\Requests\Contract\DetailContractRequest;
use App\Http\Requests\Contract\ListContractRequest;
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

    public function list(ListContractRequest $request){
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

        $service  = new ContractService();
        $result = $service->listContract($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function detail(DetailContractRequest $request, $contractId)
    {
        $userId = Auth::id();
        if(!LodgingService::isOwnerLodging($contractId, $userId) && !ContractService::isContractOwner($contractId, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service  = new ContractService();
        $result = $service->detail($contractId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->only(['lodging_id', 'contract_id', 'status', 'start_date', 'lease_duration', 'remain_amount', 'deposit_amount', 'monthly_rent', 'quantity', 'gender', 'address', 'identity_card', 'date_of_birth', 'full_name', "relatives"]);
        $userId = Auth::id();
        if(!LodgingService::isOwnerLodging($data['lodging_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors'
            ]);
        }

        $service  = new ContractService();
        $result = $service->update($data);

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
