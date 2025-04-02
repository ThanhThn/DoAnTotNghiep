<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

class ListUserRequest extends BaseRequest
{
    function rules(): array
    {
        return [
            "limit" => "nullable|integer|min:0",
            "offset" => "nullable|integer|min:0",
            "filters" => "nullable|array",

            "filters.name" => "nullable|string",
            "filters.email" => "nullable|email",
            "filters.gender" => "nullable|boolean",
            "filters.identity_card" => "nullable|string",
            "filters.phone" => "nullable|string",
            "filters.address" => "nullable|string",
            "filters.date_of_birth" => "nullable|date",
        ];
    }
}
