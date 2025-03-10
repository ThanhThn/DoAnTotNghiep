<?php

namespace App\Http\Requests\RentalHistory;

use App\Http\Requests\BaseRequest;

class ListRentalHistoryRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'lodging_id' => 'required|uuid|exists:lodgings,id',
            'contract_id' => 'required|uuid|exists:contracts,id',
            'status' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'offset' => 'nullable|integer',
        ];
    }
}
