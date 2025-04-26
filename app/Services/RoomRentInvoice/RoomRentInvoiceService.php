<?php

namespace App\Services\RoomRentInvoice;

use App\Models\Room;
use App\Models\RoomRentInvoice;
use App\Models\RoomServiceInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RoomRentInvoiceService
{
    public function detail($id)
    {
        return RoomRentInvoice::find($id);
    }

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

        $roomUsage = RoomRentInvoice::where([
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
        $history = RoomRentInvoice::create($data);

        return $history;
    }

    function processRoomRentInvoice($room, $monthBilling = null, $yearBilling = null, $recursionCount = 0, $amountPaid = 0, $paymentAmount = 0, $isFinalized = true, $isFinalizedEarly = false)
    {
        if ($recursionCount > 1) {
            return false;
        }
        $now = Carbon::now();

        $history = $this->findHistory($room,$monthBilling, $yearBilling);

        if (!$history['usage'] || $history['usage']->is_finalized_early) {
            // Nếu chưa có, tạo mới
            $history['usage'] = $this->create([
                'room_id' => $room->id,
                'total_price' => $isFinalizedEarly ? $paymentAmount : $room->price,
                'amount_paid' => $amountPaid,
                'finalized' => !$isFinalized || ($now->day == $room->payment_date && $recursionCount < 1),
                'month_billing' => $history['month_billing'],
                'year_billing' => $history['year_billing'],
                'is_finalized_early' => $isFinalizedEarly
            ]);
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
                return $this->processRoomRentInvoice($room, $nextMonth, $nextYear, $recursionCount + 1, $amountPaid, $paymentAmount, $isFinalized, $isFinalizedEarly);
            }


            $amountPaid = $history['usage']->amount_paid + $amountPaid;
            // Nếu đã có, chỉ cập nhật finalized
            $history['usage']->update([
                'finalized' => !$isFinalized || ($now->day == $room->payment_date && $recursionCount < 1),
                'amount_paid' => $amountPaid,
                'total_price' => $room->price,
                'is_finalized_early' => $isFinalizedEarly
            ]);
        }
        $remainPrice = $room->price - $amountPaid;
        return [
            'history' => $history['usage']->refresh(),
            'price' => $remainPrice
        ];
    }
}
