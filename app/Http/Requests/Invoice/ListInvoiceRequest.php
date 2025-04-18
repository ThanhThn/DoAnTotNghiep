<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseRequest;

class ListInvoiceRequest extends BaseRequest
{
    public function rules()
    {
        return [
          'lodging_id' => 'required|uuid|exists:lodgings,id',
          'status' => 'required|string|in:paid,unpaid',
          'type' => 'required|string|in:rent,service',
          'room_code' => 'nullable|string',
          'offset' => 'numeric|integer|min:0',
          'limit' => 'nullable|integer|min:1',
          'month' => 'nullable|integer|between:1,12',
          'year'  => 'required_with:month|integer',
        ];
    }
}
