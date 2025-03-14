<?php

namespace App\Http\Requests\Contract;

use App\Http\Requests\BaseRequest;

class CreateContractRequest extends BaseRequest
{
    public function rules(){
        return [
            'status' => 'required|in:1,2',
            'room_id' => 'required|uuid|exists:rooms,id',
            'identity_card' => 'required_if:status,2|string',
            'phone' => 'required|string',
            'gender' => 'required_if:status,2|boolean',
            'date_of_birth' => 'required_if:status,2|date',
            'address' => 'required_if:status,2|string',
            'full_name' => 'required|string',

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
