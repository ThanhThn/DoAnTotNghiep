<?php

namespace App\Http\Controllers;

use App\Events\NewNotification;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Notification\DetailNotificationRequest;
use App\Http\Requests\Notification\ListNotificationRequest;
use App\Models\Lodging;
use App\Services\Lodging\LodgingService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request){
        $userId = Auth::id();
        $data = $request->input('data');
        $type = $request->input('type');
        event(new NewNotification($userId, $type, $data));
        return response()->json(['success' => true]);
    }

    public function list(ListNotificationRequest $request)
    {
        $data = $request->all();

        $userId =  Auth::id();

        $data['object_id'] = $data['object_type'] == config('constant.object.type.user') ? $userId : $data['object_id'];


        if($data['object_type'] == config('constant.object.type.lodging') && !LodgingService::isOwnerLodging($data['object_id'], $userId)){
            return response()->json([
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
                'errors' => [[
                    'message' => 'Unauthorized'
                ]]
            ]);
        }

        $notificationServer = new NotificationService();
        $result = $notificationServer->list($data);
        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => $result
        ]);
    }

    public function toggleRead(DetailNotificationRequest $request, $notificationId){

        $service = new NotificationService();
        $result = $service->toggleRead($notificationId);

        if(isset($result['errors'])){
            return response()->json([
                'status' => JsonResponse::HTTP_BAD_REQUEST,
                'errors' => $result['errors']
            ]);
        }

        return response()->json([
            'status' => JsonResponse::HTTP_OK,
            'body' => [
                'data' => $result
            ]
        ]);
    }
}
