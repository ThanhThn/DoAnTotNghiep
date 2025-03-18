<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\BaseRequest;

class UpdateEquipmentRequest extends BaseRequest
{
    public function rules(){
        return [
            'id' => 'required|uuid|exists:equipments,id',
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'type' => 'required|integer|in:1,2,3',
            'thumbnail' => 'required|string|mimetypes:image/jpeg,image/png,image/webp',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'room_ids' => 'required|array',
            'room_ids.*' => 'required|uuid|exists:rooms,id',
        ];
    }
}
