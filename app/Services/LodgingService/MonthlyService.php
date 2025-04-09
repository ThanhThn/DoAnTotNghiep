<?php

namespace App\Services\LodgingService;

use App\Helpers\Helper;
use App\Models\Contract;
use App\Models\Room;
use App\Models\RoomServiceUsage;
use App\Services\RentalHistory\RentalHistoryService;
use App\Services\RoomService\RoomServiceManagerService;
use App\Services\RoomUsageService\RoomUsageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyService extends ServiceCalculatorFactory
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
                'unit_id' => $this->lodgingService->unit_id,
                'service_id' => $this->lodgingService->service_id,
                'service_name' => $this->lodgingService->name
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
            $this->createPaymentAndNotify($room, $contract, $amountPayment * $contract->quantity, $roomUsage['usage'], $roomUsage['month_billing']);
        }
        return 0;
    }

    public function processRoomUsageForContract(Room $room, Contract $contract, $usageAmount, $paymentMethod, $currentValue = 0, $extractData = [])
    {
        try{
            DB::beginTransaction();


            $roomUsage = $this->findRoomUsage($room, $extractData['month_billing'] ?? null, $extractData['year_billing'] ?? null);


            $paymentDateLast = Carbon::create($roomUsage['year_billing'], $roomUsage['month_billing'], $this->lodgingService->payment_date);


            // Tính toán thời gian thuê (tháng, ngày)
            $durationService = Helper::calculateDuration($paymentDateLast, $this->now, $paymentDateLast->isSameDay($this->now));

            // Tính số tiền thuê phòng
            if (!empty($extractData['is_monthly_billing'])) {
                $paymentAmountService = $this->lodgingService->price_per_unit * $durationService['months'];

                // Nếu số ngày dương, tính thêm một tháng tiền thuê
                if ($durationService['days'] > 0) {
                    $paymentAmountService += $room->price;
                }
            } else {
                $dailyRate = $this->lodgingService->price_per_unit / $this->now->daysInMonth;
                $paymentAmountService = ($this->lodgingService->price_per_unit * $durationService['months']) + ($dailyRate * $durationService['days']);
            }

            $tenants = max($room->current_tenants, 1);
            $paymentAmount = ($paymentAmountService / $tenants) * $contract->quantity;
            $amountPaid = max(0, min($paymentAmount, $usageAmount));

            if(!$roomUsage['usage']) {
                $roomUsageService = new RoomUsageService();
                $dataRoomUsage = [
                    'room_id' => $room->id,
                    'lodging_service_id' => $this->lodgingService->id,
                    'total_price' => $this->lodgingService->price_per_unit,
                    'amount_paid' => $amountPaid,
                    'value' => 1,
                    'finalized' => false,
                    'month_billing' => $this->now->month,
                    'year_billing' => $this->now->year,
                    'is_need_close' => false,
                    'final_index' => $currentValue,
                    'unit_id' => $this->lodgingService->unit_id,
                    'service_id' => $this->lodgingService->service_id,
                    'service_name' => $this->lodgingService->name
                ];

                $roomUsage['usage']=$roomUsageService->createRoomUsage($dataRoomUsage);
            }else{
                $roomUsage['usage']->update([
                    'amount_paid' => $roomUsage['usage']->amount_paid + $amountPaid,
                ]);
            }

            $this->createPaymentAndNotify($room, $contract, $paymentAmount, $roomUsage['usage'], $roomUsage['month_billing'], $amountPaid, $paymentMethod);

            DB::commit();

            return $usageAmount - $amountPaid;
        }catch (\Exception $exception){
            DB::rollBack();
            return ["errors" => [[
                'message' => $exception->getMessage(),
            ]]];
        }
    }
}
