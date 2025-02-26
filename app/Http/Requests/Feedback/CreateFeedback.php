<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;

class CreateFeedback extends BaseRequest
{
    public function rules() {
        return [
            'room_id' => 'required|uuid|exists:rooms,id',
            'object_from_id' => 'required|uuid',
            'object_from_type' => 'required|string|in:lodging,room,user',
            'title' => 'required|string',
            'content' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'required_with:images:string',
        ];
    }
}
