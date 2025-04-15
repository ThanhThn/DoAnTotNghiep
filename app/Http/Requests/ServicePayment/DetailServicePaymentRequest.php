<?php

namespace App\Http\Requests\ServicePayment;

use App\Http\Requests\BaseRequest;

class DetailServicePaymentRequest extends BaseRequest
{
    public function rules(){
        return [
            'servicePaymentId' => 'required|uuid|exists:service_payments,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'servicePaymentId' => $this->route('servicePaymentId'),
        ]);
    }
}
