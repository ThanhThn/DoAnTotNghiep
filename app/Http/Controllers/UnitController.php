<?php

namespace App\Http\Controllers;

use App\Services\Unit\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function list(){
        $service = new UnitService();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $service->listAll()
            ]
        ]);
    }
}
