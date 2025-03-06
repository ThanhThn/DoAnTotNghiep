<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feedback\CreateFeedbackRequest;
use App\Http\Requests\Feedback\DetailFeedbackRequest;
use App\Http\Requests\Feedback\ListFeedbackRequest;
use App\Http\Requests\Feedback\UpdateFeedbackRequest;
use App\Services\Feedback\FeedbackService;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function create(CreateFeedbackRequest $request)
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

    public function list(ListFeedbackRequest $request)
    {
        $data = $request->all();
//        $userId = Auth::id();
        $service = new FeedbackService();
        $result = $service->list($data);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function detail(DetailFeedbackRequest $request ,$feedbackId)
    {
        $service = new FeedbackService();
        $result = $service->detail($feedbackId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function updateStatus(UpdateFeedbackRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if(!LodgingService::isOwnerLodging($data['lodgingId'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'User is not allowed to update feedback.'
                ]]
            ]);
        }

        $service = new FeedbackService();
        $result = $service->updateStatus($data['feedback_id'], $data['status']);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

}
