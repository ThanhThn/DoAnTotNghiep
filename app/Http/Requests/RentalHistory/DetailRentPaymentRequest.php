<?php

namespace App\Http\Requests\RentalHistory;

use App\Http\Requests\BaseRequest;

class DetailRentPaymentRequest extends BaseRequest
{
    public function rules(){
        return [
            'rentalPaymentId' => 'required|uuid|exists:rent_payments,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'rentalPaymentId' => $this->route('rentalPaymentId'),
        ]);
    }
}
