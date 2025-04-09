<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;

class CreateFinalBillRequest extends BaseRequest
{
    function rules()
    {
        return [
            'contract_id' => 'required|exists:contracts,id',
            'room_id' => 'required|exists:rooms,id',
            'deposit_amount_refund' => 'required|numeric|min:0',
            'end_date' => 'required|date',
            'is_monthly_billing' => 'nullable|boolean',
            'services' => 'nullable|array',
            'services.*.id' => 'required_with:services|exists:lodging_services,id',
            'services.*.value' => 'nullable|numeric|min:0',
        ];
    }
}
