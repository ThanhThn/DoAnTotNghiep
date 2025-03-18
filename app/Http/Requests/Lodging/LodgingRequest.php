<?php

namespace App\Http\Requests\Lodging;

use App\Http\Requests\BaseRequest;

class LodgingRequest extends BaseRequest
{
    public function rules(){
        return [
            'lodgingId' => 'required|uuid|exists:lodgings,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'lodgingId' => $this->route('lodgingId'),
        ]);
    }
}
