<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class UpdateUserAdminRequest extends BaseRequest
{

    function rules(): array
    {
        $id = $this->input('id');
        return [
            'id' => 'required|uuid|exists:users,id',
            'full_name' => 'required|string',
            'identity_card' => 'required|string',
            'phone' => 'required|string|unique:users,phone,'. $id,
            'email' => 'nullable|email',
            'password' => 'nullable|string',
            'gender' => 'nullable|boolean',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
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
