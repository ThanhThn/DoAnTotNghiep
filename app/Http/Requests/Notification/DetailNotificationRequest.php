<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseRequest;

class DetailNotificationRequest extends BaseRequest
{
    public function rules(){
        return [
            'notificationId' => 'required|uuid|exists:notifications,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'notificationId' => $this->route('notificationId'),
        ]);
    }
}
