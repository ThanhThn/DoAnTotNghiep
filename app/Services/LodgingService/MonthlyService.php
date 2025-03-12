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

    function processRoomUsage($room, $totalPrice, $value, $monthBilling = null, $yearBilling = null, $recursionCount = 0)
    {
        if ($recursionCount > 1) {
            return 0;
        }

        $roomUsage = $this->findRoomUsage($room,$monthBilling, $yearBilling);
        $remainPrice = $totalPrice;

        if (!$roomUsage['usage']) {
            // Nếu chưa có, tạo mới
            $roomUsage = $this->roomUsageService->createRoomUsage([
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

            // Nếu đã có, chỉ cập nhật finalized
            $roomUsage['usage']->finalized = $this->now->day == $this->lodgingService->payment_date && $recursionCount < 1;
            $roomUsage['usage']->updated_at = $this->now;
            $roomUsage['usage']->save();

            if($roomUsage['usage']->total_price === $totalPrice){
                return 0;
            }
            $remainPrice = $totalPrice - $roomUsage['usage']->total_price;
            $roomUsage['usage']->total_price = $totalPrice;
            $roomUsage['usage']->save();
        }

        $amountPayment = ($remainPrice / $room->current_tenants);
        foreach ($room->contracts as $contract) {
            $this->createPaymentAndNotify($room, $contract, $amountPayment * $contract->quantity, $roomUsage, $roomUsage['month_billing']);
        }
        return 0;
    }
}
