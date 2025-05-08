<?php

namespace App\Http\Requests\Lodging;

use App\Http\Requests\BaseRequest;

class ConfigLodgingRequest extends BaseRequest
{
    public function rules(){
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'config' => 'required|array',
            'config.password_for_client' => 'required|string',
        ];
    }
}
