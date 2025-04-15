<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentHistory\ListPaymentHistoryRequest;
use App\Services\PaymentHistory\PaymentHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    function list(ListPaymentHistoryRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        $service = new PaymentHistoryService();

        if(!$service->checkUserAccess($data['object_id'], $data['object_type'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $result = $service->list($data);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }
}
