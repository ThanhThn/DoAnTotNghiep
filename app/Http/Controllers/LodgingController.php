<?php

namespace App\Http\Controllers;

use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LodgingController extends Controller
{
    function listByUser()
    {
        $service = new LodgingService();
        $lodgings = $service->listByUserID(Auth::id());
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $lodgings,
            ]
        ]);
    }
}
