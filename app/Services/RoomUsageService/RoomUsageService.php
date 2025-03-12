<?php

namespace App\Services\RoomUsageService;

use App\Models\RoomServiceUsage;
use Illuminate\Support\Facades\Log;

class RoomUsageService
{
    public function createRoomUsage($data)
    {

        Log::info($data);
        $insertData = [
            'room_id' => $data['room_id'],
            'lodging_service_id' => $data['lodging_service_id'],
            'total_price' => $data['total_price'],
            'amount_paid' => $data['amount_paid'],
            'value' => $data['value'],
            'finalized' => $data['finalized'],
            'month_billing' => $data['month_billing'],
            'year_billing' => $data['year_billing'],
        ];

        return RoomServiceUsage::create($insertData);
    }
}
