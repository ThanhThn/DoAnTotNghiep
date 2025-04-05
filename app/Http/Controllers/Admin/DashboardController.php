<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BaseRequest;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    function overview(BaseRequest $request)
    {
        $request->validate([
            'section' => 'required|string|in:total,latest_users',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $data = $request->all();
        $service = new DashboardService();

        $result = $service->overview($data);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }
}
