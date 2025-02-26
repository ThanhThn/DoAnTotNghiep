<?php

namespace App\Services\Feedback;

use App\Jobs\UploadImageToStorage;
use App\Models\Feedback;
use App\Models\Lodging;
use App\Models\Room;
use App\Models\Token;
use App\Services\Notification\NotificationService;
use App\Services\Token\TokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedbackService
{
    public function createFeedback($data, $userId)
    {
        try {
            DB::beginTransaction();
            $roomId = $data['room_id'];
            $lodgingId = $data['lodging_id'];
            $insertData = [
                'room_id' => $roomId,
                'lodging_id' => $lodgingId,
                'user_id' => $userId,
                'status' => config('constant.feedback.status.submitted'),
                'title' => $data['title'],
                'body' => [
                    'content'  => $data['content']
                ],
            ];
            $flag = false;
            if(isset($data['images'])){
                $flag = true;
            }
            $feedback = Feedback::create($insertData);
            DB::commit();
            if($flag){
                UploadImageToStorage::dispatch($feedback->id, config('constant.type.feedback'), $data['images']);
            }

            $lodging = Lodging::with(['type'])->find($lodgingId);
            $room = Room::find($roomId);

            $tokens = TokenService::getTokens($lodging->user_id, config('constant.token.type.notify'));

            if(count($tokens) > 0){
                $mess = [
                    'title' => "Ý kiến mới tại {$lodging->type->name} {$lodging->name}",
                    'body' => "Phòng {$room->room_code} tại {$lodging->type->name} {$lodging->name} vừa có góp ý mới!",
                    'target_endpoint' => '/feedback'
                ];

                NotificationService::sendNotificationRN($mess, $tokens);
            }


            return $feedback;
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }
}
