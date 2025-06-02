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

    function messages(): array{
        return [
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.regex' => 'Số điện thoại phải có đúng 10 chữ số.',
            'phone.exists' => 'Số điện thoại chưa đăng ký.',
        ];
    }
}
