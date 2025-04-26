<?php

namespace App\Http\Requests\PaymentHistory;

use App\Http\Requests\BaseRequest;

class ListPaymentHistoryRequest extends BaseRequest
{
    public function rules()
    {
        $table = match ($this->object_type){
            'rent' => 'rent_payments',
            'service' => 'service_payments',
            default => null
        };
        return [
            'object_id' => ['required', 'uuid', $table ? "exists:$table,id" : ""],
            'object_type' => "required|string|in:service,rent",
            'offset' => "nullable|numeric|min:0",
            'limit' => "nullable|numeric|min:1",
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ];
    }

}
