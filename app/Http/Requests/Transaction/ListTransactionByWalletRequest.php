<?php

namespace App\Http\Requests\Transaction;

use App\Http\Requests\BaseRequest;

class ListTransactionByWalletRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'wallet_id' => "required|uuid|exists:wallets,id",
            'offset' => "nullable|numeric|min:0",
            'limit' => "nullable|numeric|min:1",
            'type' => 'nullable|string|in:payment,transfer_out,transfer_in,withdraw,deposit',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ];
    }
}
