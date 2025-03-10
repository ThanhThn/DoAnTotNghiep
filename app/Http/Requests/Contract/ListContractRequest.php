<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;
use App\Models\Contract;

class ListContractRequest extends BaseRequest
{
    public function rules(): array{
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'room_id' => 'nullable|uuid|exists:rooms,id',
            'status' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];
    }
}
