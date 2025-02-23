<?php

namespace App\Http\Requests\Room;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends BaseRequest
{
    public function rules() : array {
        return [
            'id' => 'nullable|uuid|exists:room,id',
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
            'services.*' => 'required_with:services|array',
            'services.*.id' => ['required_with:services','uuid',
                Rule::exists('lodging_services', 'id')->where(function ($query) {
                    return $query->where('lodging_id', $this->input('lodging_id'));
                }),],
            'services.*.value' => 'nullable|numeric|min:0',
        ];
    }
}
