<?php

namespace App\Http\Requests\RoomUsage;

use App\Http\Requests\BaseRequest;

class ListRoomUsageRequest extends BaseRequest
{
    function rules()
    {
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
        ];
    }
}
