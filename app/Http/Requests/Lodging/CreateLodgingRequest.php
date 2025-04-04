<?php

namespace App\Http\Requests\Lodging;

use App\Http\Requests\BaseRequest;

class CreateLodgingRequest extends BaseRequest
{
    public function rules(){
        return [
            'user_id' => 'nullable|uuid|exists:users,id',
            'name' => 'required|string',
            'address' => 'nullable|string',
            'province_id' => 'required|integer|exists:provinces,id',
            'district_id' => 'required|integer|exists:districts,id',
            'ward_id' => 'required|integer|exists:wards,id',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'phone' => 'nullable|string|min:10|max:10',
            'email' => 'nullable|email',
            'phone_contact' => 'nullable|string|min:10|max:10',
            'email_contact' => 'nullable|email',
            'is_enabled' => 'nullable|boolean',
            'type_id' => 'required|integer|exists:lodging_types,id',
            'payment_date' => 'required|integer|between:1,28',
            'late_days' => 'required|integer|min:0',
            'area_room_default' => 'nullable|numeric|min:0',
            'price_room_default' => 'nullable|numeric|min:0'
        ];
    }
}
