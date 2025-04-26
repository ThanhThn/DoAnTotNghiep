<?php

namespace App\Http\Requests\RoomUsage;

use App\Http\Requests\BaseRequest;

class CloseRoomUsageRequest extends BaseRequest
{
    public function rules()
    {
        return [
          'lodging_id' => 'required|uuid|exists:lodgings,id',
          'room_usage_id' => 'required|uuid|exists:room_service_invoices,id',
          'final_index' => 'required|numeric'
        ];
    }
}
