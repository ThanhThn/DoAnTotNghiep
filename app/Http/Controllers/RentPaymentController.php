<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\RentalHistory\DetailRentPaymentRequest;
use App\Http\Requests\RentalHistory\ListRentPaymentRequest;
use App\Services\Contract\ContractService;
use App\Services\Lodging\LodgingService;
use App\Services\RentPayment\RentPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentPaymentController extends Controller
{
    public function list(ListRentPaymentRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if((isset($data['lodging_id']) && !LodgingService::isOwnerLodging($data['lodging_id'], $userId)) && !ContractService::isContractOwner($data['contract_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $service = new RentPaymentService();
        $result = $service->listRentPayment($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function detail(DetailRentPaymentRequest $request, $rentalPaymentId)
    {

        $userId = Auth::id();

        $service = new RentPaymentService();
        if(!$service->checkAccessUser($rentalPaymentId, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }


        $result = $service->detail($rentalPaymentId);

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
