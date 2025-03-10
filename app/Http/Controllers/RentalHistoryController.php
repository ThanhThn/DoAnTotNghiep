<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentalHistory\ListRentalHistoryRequest;
use App\Services\Contract\ContractService;
use App\Services\Lodging\LodgingService;
use App\Services\RentalHistory\RentalHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalHistoryController extends Controller
{
    public function listRentalHistory(ListRentalHistoryRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if(!LodgingService::isOwnerLodging($data['lodging_id'], $userId) || !ContractService::isContractOwner($data['contract_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new RentalHistoryService();
        $result = $service->listRentalHistory($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }
}
