<?php

namespace App\Services\Feedback;

use App\Events\ActiveFeedback;
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

            $feedback->load(['lodging', 'room']);

            $lodging = $feedback->lodging;
            $room = $feedback->room;

            $notificationService = new NotificationService();
            $mess = [
                'title' => "Ý kiến mới tại {$lodging->type->name} {$lodging->name}",
                'body' => "Phòng {$room->room_code} tại {$lodging->type->name} {$lodging->name} vừa có góp ý mới!",
                'target_endpoint' => '/feedback/list',
                'type' => config('constant.notification.type.normal')
            ];

            $notificationService->createNotification($mess, config('constant.object.type.lodging'), $lodging->id, $lodging->user_id, config('constant.rule.manager'));

            event(new ActiveFeedback($lodging->id, config('constant.object.type.lodging'), $feedback, "new"));

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

    public function listByUser($data, $userId)
    {
        $feedback = Feedback::with(['room', 'lodging'])->where('user_id', $userId)->orderBy('created_at', 'desc');

        if(isset($data['status'])){
            $feedback->where('status', $data['status']);
        }
        return $feedback->get();
    }

    public function list($data, $userId)
    {
        $feedbacks = Feedback::on('pgsqlReplica')->with(['user', 'room', 'lodging']);
        if(isset($data['lodging_id'])){
            $feedbacks->where('lodging_id', $data['lodging_id']);
        }

        if(isset($data['room_id'])){
            $feedbacks->where('room_id', $data['room_id']);
        }

        if($data['scope'] == config('constant.rule.user')){
            $feedbacks->where('user_id', $userId);
        }

        if(isset($data['status'])){
            $feedbacks->where('status', $data['status']);
        }

        if(isset($data['search'])){
            $feedbacks->where('title', 'ilike', '%' . $data['search'] . '%');
        }

        $total = $feedbacks->count();

        $feedbacks->orderBy('created_at', 'desc')->limit($data['limit'] ?? 10)->offset($data['offset'] ?? 0);
        return [
            'total' => $total,
            'data' => $feedbacks->get()
        ];
    }

    public function detail($id)
    {
        return Feedback::with(['room', 'lodging'])->find($id);
    }

    public function updateStatus($id, $status)
    {
        $feedback = $this->detail($id);

        $oldStatus = $feedback->status;
        $newStatus = $status;

        $feedback->status = $status;
        $feedback->save();

        if($oldStatus != $newStatus){
            $notificationService = new NotificationService();

            $lodging = $feedback->lodging;
            $room = $feedback->room;

            $status =  [
                config('constant.feedback.status.submitted') => "Đã gửi",
                config('constant.feedback.status.received') => "Đã nhận" ,
                config('constant.feedback.status.in_progress') => "Đang xử lý" ,
                config('constant.feedback.status.resolved') => "Đã giải quyết",
                config('constant.feedback.status.closed') => "Đã đóng"];

            $mess = [
                'title' => "Cập nhật phản hồi tại {$lodging->type->name} {$lodging->name}",
                'body' => "Phòng {$room->room_code} có phản hồi lúc " . date('H:i d/m/Y', strtotime($feedback->created_at)) . " vừa được cập nhật trạng thái thành: {$status[$newStatus]}.",
                'target_endpoint' => "/feedback/detail/{$feedback->id}",
                'type' => config('constant.notification.type.normal')
            ];

            $notificationService->createNotification($mess, config('constant.object.type.user'), $feedback->user_id, $feedback->user_id, config('constant.rule.user'));

            event(new ActiveFeedback($feedback->user_id, config('constant.object.type.user'), $feedback, "update"));
        }
        return $feedback;
    }
}
