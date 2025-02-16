<?php

namespace App\Http\Requests\LodgingService;

use App\Http\Requests\BaseRequest;

class CreateLodgingServiceRequest extends BaseRequest
{
    public function rules(): array{
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'service_name' => 'required_without:service_id|string|nullable',
            'service_id' => 'required_without:service_name|integer|nullable|exists:services,id',
            'late_days' => 'nullable|integer|min:1',
            'payment_date' => 'nullable|integer|between:1,28',
            'unit_id' => 'required|integer|exists:units,id',
            'price_per_unit' => 'required|numeric|min:0',
            'room_ids' => 'nullable|array',
            'room_ids.*' => 'required_if:room_ids|uuid|exists:rooms,id',
        ];
    }
}
