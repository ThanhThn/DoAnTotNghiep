<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;

class UpdateContractRequest extends BaseRequest
{
    function rules(): array
    {
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'contract_id' => 'required|uuid|exists:contracts,id',
            'status' => 'required|integer',

            'identity_card' => 'nullable|string',
            'gender' => 'nullable|boolean',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'full_name' => 'nullable|string',

            'quantity' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'lease_duration' => 'required|integer',
            'monthly_rent' => 'nullable|numeric',
            'deposit_amount' => 'required|numeric',


            'relatives' => 'nullable|array',
            'relatives.*.full_name' => 'required_with:relative|string',
            'relatives.*.phone' => 'required_with:relative|string',
            'relatives.*.relationship' => 'required_with:relative|string',
        ];
    }
}
