<?php

namespace App\Http\Requests\RentalHistory;

use App\Http\Requests\BaseRequest;

class DetailRentalHistoryRequest extends BaseRequest
{
    public function rules(){
        return [
            'rentalHistoryId' => 'required|uuid|exists:rental_histories,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'rentalHistoryId' => $this->route('rentalHistoryId'),
        ]);
    }
}
