<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feedback\CreateFeedbackRequest;
use App\Http\Requests\Feedback\FeedbackIdRequest;
use App\Http\Requests\Feedback\ListFeedbackRequest;
use App\Http\Requests\Feedback\UpdateFeedbackRequest;
use App\Services\Feedback\FeedbackService;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new FeedbackService();
    }

    public function create(CreateFeedbackRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        $result = $this->service->createFeedback($data, $userId);

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

    public function list(ListFeedbackRequest $request)
    {
        $data = $request->all();
        $userId = Auth::id();

        if($data['scope'] != config('constant.rule.user') && !LodgingService::isOwnerLodging($data['lodging_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $result = $this->service->list($data, $userId);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function detail(FeedbackIdRequest $request , $feedbackId)
    {
        $result = $this->service->detail($feedbackId);
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

        if(!LodgingService::isOwnerLodging($data['lodging_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'User is not allowed to update feedback.'
                ]]
            ]);
        }

        $result = $this->service->updateStatus($data['feedback_id'], $data['status']);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }

    public function delete(FeedbackIdRequest $request, $feedbackId)
    {
        $userId = Auth::id();

        $result = $this->service->delete($feedbackId, $userId);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => "Xoá phản hồi thành công !"
            ]
        ]);
    }

}
