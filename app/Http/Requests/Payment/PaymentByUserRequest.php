<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseRequest;

class PaymentByUserRequest extends BaseRequest
{
    public function rules(): array {
        return [
          'contract_id' => 'required|uuid|exists:contracts,id',
          'amount' => 'required|numeric|min:1',
          'object_type' => 'required|in:rent,service',
          'object_id' => 'required|uuid',
        ];
    }
}
