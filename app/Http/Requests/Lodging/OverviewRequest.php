<?php

namespace App\Http\Requests\Lodging;

use App\Http\Requests\BaseRequest;

class OverviewRequest extends BaseRequest
{
    public function rules(){
        return [
            'section' => 'required|string|in:statistical,room',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer',
        ];
    }
}
