<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\BaseRequest;

class UpdateEquipmentRequest extends BaseRequest
{
    public function rules()
    {
        $rules = [
            'id' => 'required|uuid|exists:equipments,id',
            'name' => 'required|string',
            'quantity' => 'required|integer',
            'type' => 'required|integer|in:1,2,3',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'room_ids' => 'nullable|array',
            'room_ids.*' => 'required_with:room_ids|uuid|exists:rooms,id',
        ];

        if ($this->hasFile('thumbnail')) {
            $rules['thumbnail'] = 'file|mimes:jpeg,png,webp|max:5120';
        } else {
            $rules['thumbnail'] = 'string';
        }

        return $rules;
    }

}
