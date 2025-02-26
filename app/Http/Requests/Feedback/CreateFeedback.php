<?php

namespace App\Http\Requests\Feedback;

use App\Http\Requests\BaseRequest;

class CreateFeedback extends BaseRequest
{
    public function rules() {
        return [
            'object_to_id' => 'required|uuid',
            'object_to_type' => 'required|string|in:lodging,room,user',
            'object_from_id' => 'required|uuid',
            'object_from_type' => 'required|string|in:lodging,room,user',
            'title' => 'required|string',
            'content' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'required_with:images:string',
        ];
    }
}
