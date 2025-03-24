<?php

namespace App\Http\Requests\ChatHistory;

use App\Http\Requests\BaseRequest;

class ListChatHistoryRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'channel_id' => 'required|uuid|exists:channels,id',
            'member_type' => 'required|in:lodging,user',
            'member_id' => 'required_if:member_type,lodging|uuid|nullable',
            'offset' => 'nullable|numeric|min:0',
            'limit' => 'nullable|numeric|min:1',
        ];
    }
}
