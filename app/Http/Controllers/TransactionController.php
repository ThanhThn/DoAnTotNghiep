<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\ListTransactionByWalletRequest;
use App\Services\Transaction\TransactionService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    function listByWallet(ListTransactionByWalletRequest $request)
    {
        $data = $request->all();

        $userId = Auth::id();

        if(!WalletService::isOwnerWallet($data['wallet_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => "Unauthorized",
                ]]
            ]);
        }

        $service = new TransactionService();

        $result = $service->listByWallet($data, $data['wallet_id']);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }
}
