<?php

namespace App\Services\LodgingService;

use App\Models\Room;
use App\Models\RoomServiceUsage;
use Carbon\Carbon;

class MonthlyService extends BaseServiceCalculator
{
    public function calculateCost()
    {
        $rooms = $this->getActiveRooms();

        foreach ($rooms as $room) {
            $totalPrice = $this->lodgingService->price_per_unit;
            $this->processRoomUsage($room, $totalPrice, 1);
        }
    }

    function processRoomUsage($room, $totalPrice, $value)
    {
        $roomUsage = $this->findRoomUsage($room);
        $remainPrice = $totalPrice;

        if (!$roomUsage['usage']) {
            // Nếu chưa có, tạo mới
            $roomUsage = RoomServiceUsage::create([
                'room_id' => $room->id,
                'lodging_service_id' => $this->lodgingService->id,
                'total_price' => $totalPrice,
                'amount_paid' => 0,
                'value' => $value,
                'finalized' => true,
                'month_billing' => $roomUsage['month_billing'],
                'year_billing' => $roomUsage['year_billing'],
            ]);
        } else {
            // Nếu đã có, chỉ cập nhật finalized
            $roomUsage['usage']->finalized = true;
            $roomUsage['usage']->update_at = $this->now;
            $roomUsage['usage']->save();

            if($roomUsage['usage']->total_price === $totalPrice){
                return;
            }
            $remainPrice = $totalPrice - $roomUsage['usage']->total_price;
            $roomUsage['usage']->total_price = $totalPrice;
            $roomUsage['usage']->save();
        }

        $amountPayment = ($remainPrice / $room->current_tenants);
        foreach ($room->contracts as $contract) {
            $this->createPaymentAndNotify($room, $contract, $amountPayment * $contract->quantity, $roomUsage);
        }
    }
}
