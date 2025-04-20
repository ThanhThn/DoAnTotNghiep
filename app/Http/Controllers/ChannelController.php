<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest;
use App\Services\Channel\ChannelService;
use App\Services\ChannelMember\ChannelMemberService;
use App\Services\Lodging\LodgingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    public function list(Request $request){
        $data = $request->only(['member_id','member_type', 'limit', 'offset']);
        $userId = Auth::id();

        $limit = $data['limit'] ?? 10;
        $offset = $data['offset'] ?? 0;
        $memberType = $data['member_type'] ?? config('constant.object.type.user');
        $memberId = $data['member_id'] ?? '';
        $memberId = ($memberType == 'lodging' ? $memberId : $userId);

        if(!$memberId){
            $response = [
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => [[
                    'message' => 'Chỗ ở này không tồn tại hoặc không thuộc về tài khoản của bạn.'
                ]]
            ];

            return response()->json($response, JsonResponse::HTTP_OK);
        }

        if($memberType == config('constant.object.type.lodging') && !LodgingService::isOwnerLodging($memberId, $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized access.'
                ]]
            ]);
        }

        $service = new ChannelService($memberId, $memberType);
        $result = $service->list($limit, $offset);

        // Số channel chưa xem
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function leaveChannel(BaseRequest $request)
    {
        $request->validate([
            'channel_id' => 'required|uuid|exists:channels,id',
            'member_id' => 'nullable|uuid',
            'member_type' => 'required|in:lodging,user',
        ]);

        $data = $request->all();
        $userId = Auth::id();

        $memberType = $data['member_type'] ?? config('constant.object.type.user');
        $memberId = $data['member_id'] ?? '';
        $memberId = ($memberType == 'lodging' ? $memberId : $userId);

        if(!ChannelMemberService::isMemberOfChannel($memberId, $memberType, $data['channel_id'])){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    "message" => 'Unauthorized access.'
                ]]
            ]);
        }

        $service = new ChannelService($memberId, $memberType);
        $service->leaveChannel($data['channel_id']);

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body'   => [
                'data' => 'Leave channel successfully.'
            ]
        ]);
    }
}
