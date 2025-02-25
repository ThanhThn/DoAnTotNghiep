<?php

namespace App\Http\Controllers;

use App\Services\Client\ClientService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function listLodgingAndRooms()
    {
        $userId = Auth::id();
        $server = new ClientService();
        $result = $server->listLodgingAndRoomToContractByUser($userId);
        if(!$result){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Fail'
                ]]
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
