<?php

namespace App\Http\Controllers;

use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function tests\data;

class RealtimeController extends Controller
{
    public function auth()
    {
        $userId = Auth::id();
        $service = new RealtimeService();
        $token = $service->getToken($userId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => [
                    'token' => $token,
                ]
            ]
        ]);
    }
}
