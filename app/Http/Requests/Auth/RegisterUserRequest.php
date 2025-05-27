<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class RegisterUserRequest extends BaseRequest
{
    public function rules(){
        return [
          'phone' => 'required|unique:users,phone',
          'email' => 'nullable|email',
          'password' => 'required|string',
          'token' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.unique'   => 'Số điện thoại này đã được đăng ký.',
        ];
    }
}
