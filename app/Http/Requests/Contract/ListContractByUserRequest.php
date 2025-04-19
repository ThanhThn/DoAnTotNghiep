<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;
use App\Models\Contract;

class ListContractByUserRequest extends BaseRequest
{
    public function rules(): array{
        return [
            'status' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];
    }
}
