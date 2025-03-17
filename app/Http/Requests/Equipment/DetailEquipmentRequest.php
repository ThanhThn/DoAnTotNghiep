<?php

namespace App\Http\Requests\Equipment;

use App\Http\Requests\BaseRequest;

class DetailEquipmentRequest extends BaseRequest
{
    public function rules(){
        return [

            'equipmentId' => 'required|uuid|exists:equipments,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'equipmentId' => $this->route('equipmentId'),
        ]);
    }
}
