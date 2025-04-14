<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\DetailWalletRequest;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    function detail(DetailWalletRequest $request, $walletId)
    {
        $service = new WalletService();
        $userId = Auth::id();


        if(!WalletService::isOwnerWallet($walletId, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => "Unauthorized",
                ]]
            ]);
        }
        $result = $service->detail($walletId);
        if(isset($result['errors'])){
            return [
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ];
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }
}
