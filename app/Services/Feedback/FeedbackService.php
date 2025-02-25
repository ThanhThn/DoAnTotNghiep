<?php

namespace App\Services\Feedback;

use App\Jobs\UploadImageToStorage;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedbackService
{
    public function createFeedback($data, $userId)
    {
        try {
            DB::beginTransaction();
            $insertData = [
                'object_to_id' => $data['object_to_id'],
                'object_to_type' => $data['object_to_type'],
                'object_from_type' => $data['object_from_type'],
                'object_from_id' => $data['object_from_id'],
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
