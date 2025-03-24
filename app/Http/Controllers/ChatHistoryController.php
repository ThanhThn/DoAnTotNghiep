<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatHistory\CreateChatHistoryRequest;
use App\Http\Requests\ChatHistory\ListChatHistoryRequest;
use App\Services\Channel\ChannelService;
use App\Services\ChannelMember\ChannelMemberService;
use App\Services\ChatHistory\ChatHistoryService;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatHistoryController extends Controller
{
    function list(ListChatHistoryRequest $request)
    {
        $data = $request->all();
        $limit = $data['limit'] ?? 10;
        $offset = $data['offset'] ?? 0;
        $memberType = $data['member_type'] ?? config('constant.object.type.user');
        $memberId = $data['member_id'] ?? '';
        $memberId = ($memberType == 'lodging' ? $memberId : Auth::id());

        if(!$memberId){
            $response = [
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Chỗ ở này không tồn tại hoặc không thuộc về tài khoản của bạn.'
                ]]
            ];

            return response()->json($response, JsonResponse::HTTP_OK);
        }


        if(!ChannelMemberService::isMemberOfChannel($memberId, $memberType, $data['channel_id'])){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized access.'
                ]]
            ]);
        }

        $service = new ChatHistoryService($memberId, $memberType);
        $result = $service->list($data['channel_id'] ,$limit, $offset);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);

    }


    function create(CreateChatHistoryRequest $request)
    {
        $data = $request->all();

        $memberType = $data['member_type'] ?? config('constant.object.type.user');
        $memberId = $data['member_id'] ?? '';
        $memberId = ($memberType == 'lodging' ? $memberId : Auth::id());

        if(!$memberId){
            $response = [
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Chỗ ở này không tồn tại hoặc không thuộc về tài khoản của bạn.'
                ]]
            ];

            return response()->json($response, JsonResponse::HTTP_OK);
        }


        if(!ChannelMemberService::isMemberOfChannel($memberId, $memberType, $data['channel_id'])){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized access.'
                ]]
            ]);
        }

        $service = new ChatHistoryService($memberId, $memberType);
        $result = $service->create($data['channel_id'] ,$data['message']);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);

    }
}
