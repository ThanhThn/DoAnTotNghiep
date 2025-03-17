<?php

namespace App\Services\RoomUsageService;

use App\Models\RentalHistory;
use App\Models\RoomServiceUsage;
use Illuminate\Support\Facades\Log;

class RoomUsageService
{
    public function createRoomUsage($data)
    {
        $insertData = [
            'room_id' => $data['room_id'],
            'lodging_service_id' => $data['lodging_service_id'],
            'total_price' => $data['total_price'],
            'amount_paid' => $data['amount_paid'],
            'value' => $data['value'],
            'finalized' => $data['finalized'],
            'month_billing' => $data['month_billing'],
            'year_billing' => $data['year_billing'],
            'is_need_close' => $data['is_need_close'] ?? false,
        ];

        return RoomServiceUsage::create($insertData);
    }

    function statisticalAmount($month, $year, $lodgingId)
    {
        $amount = RoomServiceUsage::whereHas('room.lodging', function ($query) use ($lodgingId) {
            $query->where('id', $lodgingId);
        })
            ->where([
                'month_billing' => $month,
                'year_billing' => $year,
            ])
            ->selectRaw('SUM(total_price) as total_payment, SUM(amount_paid) as total_paid')
            ->first();

        return $amount;
    }
}
