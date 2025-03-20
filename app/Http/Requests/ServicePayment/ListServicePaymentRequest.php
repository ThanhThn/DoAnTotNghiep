<?php

namespace App\Http\Requests\ServicePayment;

use App\Http\Requests\BaseRequest;

class ListServicePaymentRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'lodging_id' => 'nullable|uuid|exists:lodgings,id',
            'contract_id' => 'required|uuid|exists:contracts,id',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];
    }
}
