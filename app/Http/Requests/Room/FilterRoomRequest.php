<?php

namespace App\Http\Requests\Room;

use App\Http\Requests\BaseRequest;

class FilterRoomRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'start_date' => 'nullable|date',
            'lease_duration' => 'nullable|integer',
            'quantity' => 'nullable|integer',
            'status' => 'nullable|in:1,2,3,4',
        ];
    }
}
