<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;

class DetailContractRequest extends BaseRequest
{
    public function rules(){
        return [
            'contractId' => 'required|uuid|exists:contracts,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'contractId' => $this->route('contractId'),
        ]);
    }
}
