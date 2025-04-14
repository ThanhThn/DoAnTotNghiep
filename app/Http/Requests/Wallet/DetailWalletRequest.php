<?php

namespace App\Http\Requests\Wallet;

use App\Http\Requests\BaseRequest;

class DetailWalletRequest extends BaseRequest
{
    public function rules(){
        return [

            'walletId' => 'required|uuid|exists:wallets,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'walletId' => $this->route('walletId'),
        ]);
    }
}
