<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;
use App\Models\Contract;

class ListContractRequest extends BaseRequest
{
    public function rules(): array{
        return [
            'lodging_id' => 'required|uuid|exists:lodging,id',
            'room_id' => 'nullable|uuid|exists:room,id',
            'status' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];
    }
}
