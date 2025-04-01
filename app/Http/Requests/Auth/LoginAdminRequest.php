<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class LoginAdminRequest extends BaseRequest
{
    function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
