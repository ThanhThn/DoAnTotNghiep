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
            "filter" => "nullable|array",

            "filter.name" => "nullable|string",
            "filter.email" => "nullable|email",
            "filter.gender" => "nullable|boolean",
            "filter.identity_card" => "nullable|string",
            "filter.phone" => "nullable|string",
            "filter.address" => "nullable|string",
            "filter.date_of_birth" => "nullable|date",
        ];
    }
}
