<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\BaseRequest;

class ListEquipmentRequest extends BaseRequest
{
    function rules(): array
    {
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'offset' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1',
        ];
    }
}
