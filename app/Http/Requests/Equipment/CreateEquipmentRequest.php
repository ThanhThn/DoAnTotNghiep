<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\BaseRequest;

class CreateEquipmentRequest extends BaseRequest
{
    public function rules(){
        return [
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'type' => 'required|integer|in:1,2,3',
            'thumbnail' => 'required|string',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'room_ids' => 'array|nullable',
            'room_ids.*' => 'required|uuid|exists:rooms,id',
        ];
    }
}
