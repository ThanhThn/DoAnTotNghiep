<?php

namespace App\Http\Requests\Room;

use App\Http\Requests\BaseRequest;

class CreateRoomRequest extends BaseRequest
{
    public function rules() : array {
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'room_code' => 'required|string',
            'max_tenants' => 'required|integer',
            'price' => 'nullable|numeric|min:0',
            'status' => 'nullable|integer|in:1,2,3',
            'area' => 'nullable|numeric|min:0',
            'priority' => 'nullable|array',
            'payment_date' => 'nullable|integer|between:1,28',
            'late_days' => 'nullable|integer|min:0',

            'services' => 'nullable|array',
            'services.*' => 'required_if:services|array',
            'services.*.id' => 'required_if:services|uuid|exists:lodging_services,id',
            'services.*.value' => 'nullable|numeric|min:0',
        ];
    }
}
