<?php

namespace App\Services\LodgingService;

use App\Models\Room;
use App\Models\RoomServiceUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PersonService extends BaseServiceCalculator
{
    public function calculateCost()
    {
        $rooms = $this->getActiveRooms();

        foreach ($rooms as $room) {
            if ($room->current_tenants == 0) {
                continue;
            }

            $totalPrice = $this->lodgingService->price_per_unit * $room->current_tenants;
            $this->processRoomUsage($room, $totalPrice, $room->current_tenants);
        }
    }

    function processRoomUsage($room, $totalPrice, $value)
    {

        $roomUsage = $this->findRoomUsage($room);


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
            $roomUsage->update([
                'total_price' => $roomUsage->total_price + $totalPrice,
                'value' => $roomUsage->value + $value,
                'finalized' => true,
                'updated_at' => $this->now
            ]);
        }

        foreach ($room->contracts as $contract) {
            $amountPayment = $this->lodgingService->price_per_unit * $contract->quantity;
            $this->createPaymentAndNotify($room, $contract, $amountPayment, $roomUsage);
        }
    }
}
