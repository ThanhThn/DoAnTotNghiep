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

    function processRoomUsage($room, $totalPrice, $value, $monthBilling = null, $yearBilling = null, $recursionCount = 0)
    {
        if ($recursionCount > 1) {
            return 0;
        }

        $roomUsage = $this->findRoomUsage($room, $monthBilling, $yearBilling);

        if (!$roomUsage['usage']) {

            $roomUsage['usage'] = $this->roomUsageService->createRoomUsage([
                'room_id' => $room->id,
                'lodging_service_id' => $this->lodgingService->id,
                'total_price' => $totalPrice,
                'amount_paid' => 0,
                'value' => $value,
                'finalized' => $this->now->day == $this->lodgingService->payment_date && $recursionCount < 1,
                'month_billing' => $roomUsage['month_billing'],
                'year_billing' => $roomUsage['year_billing'],
            ]);
        } else {

            // Nếu hôm nay là ngày lập hóa đơn và đã finalized -> Kiểm tra tháng tiếp theo
            if ($this->now->day == $this->lodgingService->payment_date && $roomUsage['usage']->finalized) {
                $nextMonth = $roomUsage['month_billing'] + 1;
                $nextYear = $roomUsage['year_billing'];

                if ($nextMonth > 12) {
                    $nextMonth = 1;
                    $nextYear++;
                }

                // Gọi đệ quy để kiểm tra tháng tiếp theo
                return $this->processRoomUsage($room, $totalPrice, $value, $nextMonth, $nextYear, $recursionCount + 1);
            }

            // Nếu đã có, chỉ cập nhật nếu không bị finalized
            $roomUsage['usage']->update([
                'total_price' => $roomUsage['usage']->total_price + $totalPrice,
                'value' => $roomUsage['usage']->value + $value,
                'finalized' => $this->now->day == $this->lodgingService->payment_date && $recursionCount < 1,
                'updated_at' => $this->now
            ]);
        }

        // Tạo hóa đơn cho từng hợp đồng thuê
        foreach ($room->contracts as $contract) {
            $amountPayment = $this->lodgingService->price_per_unit * $contract->quantity;
            $this->createPaymentAndNotify($room, $contract, $amountPayment, $roomUsage['usage'], $roomUsage['month_billing']);
        }
        return 0;
    }

}
