<?php

namespace App\Services\RoomRentalHistory;

use App\Models\Room;
use App\Models\RoomRentalHistory;
use App\Models\RoomServiceUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RoomRentalHistoryService
{
    public function findHistory(Room $room, $monthBilling = null, $yearBilling = null)
    {
        $billingDay = $room->payment_date;
        $now = Carbon::now();

        if($monthBilling == null && $yearBilling == null){
            // Nếu hôm nay >= payment_date → tính cho tháng này (tiền phòng của tháng trước)
            if ($now->day > $billingDay) {
                $monthBilling = $now->month;
                $yearBilling = $now->year;

            } else {
                $monthBilling = $now->month - 1;
                $yearBilling = $now->year;

                if ($monthBilling == 0) {
                    $monthBilling += 12;
                    $yearBilling -= 1;
                }
            }
        }

        $roomUsage = RoomRentalHistory::where([
            'room_id' => $room->id,
            'month_billing' => $monthBilling,
            'year_billing' => $yearBilling
        ])->first();

        return [
            "usage" => $roomUsage,
            "month_billing" => $monthBilling,
            "year_billing" => $yearBilling
        ];
    }


    public function create($data)
    {
        $history = RoomRentalHistory::create($data);

        return $history;
    }

    function processRoomRentalHistory($room, $monthBilling = null, $yearBilling = null, $recursionCount = 0, $amountPaid = 0, $isFinalized = true)
    {
        if ($recursionCount > 1) {
            return false;
        }
        $now = Carbon::now();

        $history = $this->findHistory($room,$monthBilling, $yearBilling);

        if (!$history['usage']) {
            // Nếu chưa có, tạo mới
            $history['usage'] = $this->create([
                'room_id' => $room->id,
                'total_price' => $room->price,
                'amount_paid' => $amountPaid,
                'finalized' => !$isFinalized || ($now->day == $room->payment_date && $recursionCount < 1),
                'month_billing' => $history['month_billing'],
                'year_billing' => $history['year_billing'],
            ]);
            $remainPrice = $room->price;
        } else {

            // Nếu hôm nay là ngày lập hóa đơn và đã finalized -> Kiểm tra tháng tiếp theo
            if ($now->day == $room->payment_date && $history['usage']->finalized) {
                $nextMonth = $history['month_billing'] + 1;
                $nextYear = $history['year_billing'];

                if ($nextMonth > 12) {
                    $nextMonth = 1;
                    $nextYear++;
                }

                // Gọi đệ quy để kiểm tra tháng tiếp theo
                return $this->processRoomRentalHistory($room, $nextMonth, $nextYear, $recursionCount + 1, $amountPaid, $isFinalized);
            }

            // Nếu đã có, chỉ cập nhật finalized
            $history['usage']->finalized = !$isFinalized || ($now->day == $room->payment_date && $recursionCount < 1);
            $history['usage']->updated_at = $now;
            $history['usage']->amount_paid = $amountPaid;
            $history['usage']->save();

            if($history['usage']->total_price === $room->price){
                return false;
            }
            $remainPrice = $room->price - $history['usage']->total_price;
            $history['usage']->total_price = $room->price;
            $history['usage']->save();
        }

        return [
            'history' => $history['usage'],
            'price' => $remainPrice
        ];
    }
}
