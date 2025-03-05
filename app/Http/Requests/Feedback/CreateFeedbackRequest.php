<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;

class CreateFeedbackRequest extends BaseRequest
{
    public function rules() {
        return [
            'room_id' => 'required|uuid|exists:rooms,id',
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'title' => 'required|string',
            'content' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'required_with:images:string',
        ];
    }
}
