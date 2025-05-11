<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;

class ListFeedbackRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'scope' => 'required|string|in:user,owner',
            'lodging_id' => 'required_if:scope,owner|uuid|exists:lodgings,id',
            'room_id' => 'nullable|uuid|exists:rooms,id',
            'status' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'search' => 'nullable|string',
        ];
    }
}
