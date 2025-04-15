<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServicePayment\DetailServicePaymentRequest;
use App\Http\Requests\ServicePayment\ListServicePaymentRequest;
use App\Services\Contract\ContractService;
use App\Services\Lodging\LodgingService;
use App\Services\RentalHistory\RentalHistoryService;
use App\Services\ServicePayment\ServicePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicePaymentController extends Controller
{
    function list(ListServicePaymentRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if((isset($data['lodging_id']) && !LodgingService::isOwnerLodging($data['lodging_id'], $userId)) || !ContractService::isContractOwner($data['contract_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new ServicePaymentService();
        $result = $service->listServicePaymentByContract($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function detail(DetailServicePaymentRequest $request, $servicePaymentId)
    {

        $userId = Auth::id();

        $service = new ServicePaymentService();
        if(!$service->checkAccessUser($servicePaymentId, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }


        $result = $service->detail($servicePaymentId);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result,

            ]
        ]);
    }
}
