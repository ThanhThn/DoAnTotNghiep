<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;

class ListFeedbackRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'lodging_id' => 'nullable|uuid|exists:lodgings,id',
            'room_id' => 'nullable|uuid|exists:rooms,id',
            'status' => 'nullable|integer'
        ];
    }
}
