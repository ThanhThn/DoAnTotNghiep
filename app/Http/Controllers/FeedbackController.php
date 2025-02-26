<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feedback\CreateFeedback;
use App\Services\Feedback\FeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function create(CreateFeedback $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        $service = new FeedbackService();
        $result = $service->createFeedback($data, $userId);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }
        return response()->json([
            'status' => JsonResponse::HTTP_CREATED,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function listByUser(Request $request)
    {
        $data = $request->all();
        $userId = Auth::id();
        $service = new FeedbackService();
        $result = $service->listByUser($data, $userId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }
}
