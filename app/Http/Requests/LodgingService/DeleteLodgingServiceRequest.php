<?php

namespace App\Http\Requests\LodgingService;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DeleteLodgingServiceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|uuid|exists:lodging_services,id',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
        ];
    }
}
