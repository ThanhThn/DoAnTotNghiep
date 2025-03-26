<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\BaseRequest;

class DeleteEquipmentRequest extends BaseRequest
{
    public function rules(){
        return [
            'equipment_id' => 'required|uuid|exists:equipments,id',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
        ];
    }
}
