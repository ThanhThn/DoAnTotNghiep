<?php

namespace App\Http\Requests\Room;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DeleteRoomRequest extends BaseRequest
{
    public function rules() : array {
        return [
            'room_id' => 'required|uuid|exists:rooms,id',
        ];
    }
}
