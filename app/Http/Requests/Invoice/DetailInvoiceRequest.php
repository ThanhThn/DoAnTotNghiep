<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\BaseRequest;

class DetailInvoiceRequest extends BaseRequest
{
    public function rules()
    {
        return [
          'id' => 'required|uuid',
          'lodging_id' => 'required|uuid|exists:lodgings,id',
          'type' => 'required|string|in:rent,service',
        ];
    }
}
