<?php

namespace App\Http\Requests\Lodging;

use App\Http\Requests\BaseRequest;

class ListLodgingRequest extends BaseRequest
{
    public function rules(){
        return [
            "limit" => "nullable|integer|min:0",
            "offset" => "nullable|integer|min:0",
            "is_trash" => "required|boolean",
            "filters" => "nullable|array",

            "filters.name" => "nullable|string",
            "filters.address" => "nullable|string",
            "filters.type_id" => "nullable|integer|exists:lodging_types,id"
        ];
    }
}
