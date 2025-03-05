<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\BaseRequest;

class ListNotificationRequest extends BaseRequest
{
    function rules(): array{
        return [
            'object_type' => 'required|string|in:user,lodging',
            'object_id' => 'required_if:object_type,lodging|uuid',
            'offset' => 'nullable|int',
            'limit' => 'nullable|int',
        ];
    }
}
