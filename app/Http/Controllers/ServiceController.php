<?php

namespace App\Http\Controllers;

use App\Services\Service\ServiceManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function list(){
        $service = new ServiceManagerService();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->listAll()
            ]
        ]);
    }
}
