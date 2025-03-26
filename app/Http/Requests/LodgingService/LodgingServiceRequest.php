<?php

namespace App\Http\Requests\LodgingService;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class LodgingServiceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'id' => 'nullable|uuid|exists:lodging_services,id',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'name' => 'required_without:service_id|string|nullable',
            'service_id' => 'required_without:name|integer|nullable|exists:services,id',
            'late_days' => 'nullable|integer|min:0',
            'payment_date' => 'nullable|integer|between:1,28',
            'unit_id' => 'required|integer|exists:units,id',
            'price_per_unit' => 'required|numeric|min:0',
            'room_ids' => 'nullable|array',
            'room_ids.*' => [
                'nullable',
                'uuid',
                Rule::exists('rooms', 'id')->where(function ($query) {
                    return $query->where('lodging_id', $this->input('lodging_id'));
                }),
            ],
        ];
    }
}
