<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\PaymentContractRequest;
use App\Services\Contract\ContractService;
use App\Services\Lodging\LodgingService;
use App\Services\Payment\ServicePaymentFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    function paymentByContract(PaymentContractRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        $contract = (new ContractService())->detail($data['contract_id'], "pgsqlReplica");

        if(!LodgingService::isOwnerLodging($contract->room->lodging_id, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new ServicePaymentFactory();
        $result = $service->paymentByContract($data);

        if(!$result){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Thanh toán thất bại'
                ]]
            ]);
        }

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => 'Thanh toán thành công!'
            ]
        ]);
    }
}
