<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class LoginUserRequest extends BaseRequest
{
    function rules(): array
    {
        return [
            'phone' => 'required|string|regex:/[0-9]{10}/|exists:users,phone',
            'password' => 'required|string',
            'rule' => 'required|string|in:user,manager'
        ];
    }
}
