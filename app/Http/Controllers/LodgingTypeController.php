<?php

namespace App\Http\Controllers;

use App\Models\LodgingType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LodgingTypeController extends Controller
{
    public function list()
    {
        $types = LodgingType::all();
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $types
            ]
        ]);
    }
}
