<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseRequest;

class PaymentContractRequest extends BaseRequest
{
    public function rules(): array {
        return [
          'contract_id' => 'required|uuid|exists:contracts,id',
          'amount' => 'required|numeric|min:1',
          'payment_method' => 'required|string|in:cash,bank',
          'payment_type' => 'required|in:rent,service',
          'service_payment_id' => 'nullable|required_if:payment_type,service|exists:service_payments,id',
          'rent_payment_type' => 'nullable|required_if:payment_type,rent|in:full,debt',
          'rental_history_id' => 'nullable|required_if:rent_payment_type,debt|exists:rental_histories,id'
        ];
    }
}
