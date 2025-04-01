<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class CreateUserRequest extends BaseRequest
{
    function rules(): array
    {
        return [
            'full_name' => 'required|string',
            'identity_card' => 'required|string',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email',
            'password' => 'required|string',
            'gender' => 'required|boolean',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'relatives' => 'nullable|array',
            'relatives.*.full_name' => 'required_with:relative|string',
            'relatives.*.phone' => 'required_with:relative|string',
            'relatives.*.relationship' => 'required_with:relative|string',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_completed' => 'nullable|boolean',
        ];
    }
}
