<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class RegisterUserRequest extends BaseRequest
{
    public function rules(){
        return [
          'phone' => 'required|unique:users,phone',
          'email' => 'required|email',
          'password' => 'required|string',
        ];
    }
}
