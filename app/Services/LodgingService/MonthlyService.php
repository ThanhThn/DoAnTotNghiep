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

        if (!$roomUsage) {
            // Nếu chưa có, tạo mới
            $roomUsage = RoomServiceUsage::create([
                'room_id' => $room->id,
                'lodging_service_id' => $this->lodgingService->id,
                'total_price' => $totalPrice,
                'amount_paid' => 0,
                'value' => $value,
                'finalized' => true,
            ]);
        } else {
            // Nếu đã có, chỉ cập nhật finalized
            $roomUsage->finalized = true;
            $roomUsage->update_at = $this->now;
            $roomUsage->save();

            if($roomUsage->total_price === $totalPrice){
                return;
            }
            $remainPrice = $totalPrice - $roomUsage->total_price;
            $roomUsage->total_price = $totalPrice;
            $roomUsage->save();
        }

        $amountPayment = ($remainPrice / $room->current_tenants);
        foreach ($room->contracts as $contract) {
            $this->createPaymentAndNotify($room, $contract, $amountPayment * $contract->quantity, $roomUsage);
        }
    }
}
