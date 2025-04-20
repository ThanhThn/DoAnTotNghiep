<?php

namespace App\Services\LodgingService;

use App\Models\Contract;
use App\Models\Room;
use App\Models\RoomService;
use App\Models\RoomServiceUsage;
use App\Services\LodgingService\ServiceCalculatorFactory;
use App\Services\Notification\NotificationService;
use App\Services\RoomService\RoomServiceManagerService;
use App\Services\RoomUsageService\RoomUsageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndexedService extends ServiceCalculatorFactory
{
    public function calculateCost()
    {
        $rooms = $this->getActiveRooms();

        foreach ($rooms as $room) {
            $this->processRoomUsage($room, 0, 0);
        }
    }

    function processRoomUsage($room, $totalPrice, $value, $monthBilling = null, $yearBilling = null, $recursionCount = 0)
    {

        $roomUsage = $this->findRoomUsage($room);
        $roomService = RoomService::where([
            'room_id' => $room->id,
            'lodging_service_id' => $this->lodgingService->id,
        ])->first();


        if (!$roomUsage['usage']) {
            // Nếu chưa có, tạo mới
            $roomUsage = $this->roomUsageService->createRoomUsage([
                'room_id' => $room->id,
                'lodging_service_id' => $this->lodgingService->id,
                'total_price' => 0,
                'amount_paid' => 0,
                'initial_index' => $roomService ? $roomService->last_recorded_value : 0,
                'value' => 0,
                'finalized' => false,
                'is_need_close' => true,
                'month_billing' => $roomUsage['month_billing'],
                'year_billing' => $roomUsage['year_billing'],
                'unit_id' => $this->lodgingService->unit_id,
                'service_id' => $this->lodgingService->service_id,
                'service_name' => $this->lodgingService->name
            ]);
        }else{
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

            $roomUsage['usage']->is_need_close = true;
            $roomUsage['usage']->updated_at = $this->now;
            $roomUsage['usage']->save();
        }


        // Lấy tên dịch vụ
        $nameService = isset($this->lodgingService->service) ? config("constant.service.name.{$this->lodgingService->service->name}") : $this->lodgingService->name;

        $notificationService = new NotificationService();
        $message = [
            'title' => "Cập nhật chỉ số dịch vụ $nameService",
            'body' => "Vui lòng cập nhật chỉ số mới cho dịch vụ $nameService phòng {$room->room_code}.",
            'target_endpoint' => "/lodging/$room->lodging_id/service_usage/create",
            'type' => config('constant.notification.type.important'),
        ];
        $notificationService->createNotification($message, config('constant.object.type.lodging'), $this->lodgingService->lodging->id, $this->lodgingService->lodging->user_id, config('constant.rule.manager'));

        return 0;
    }

    public function processRoomUsageForContract(Room $room, Contract $contract, $usageAmount, $paymentMethod, $currentValue = 0, $extractData = [])
    {
        try{
            DB::beginTransaction();

            $roomService = new RoomServiceManagerService();

            $service = $roomService->detailByRoomAndService($room->id, $this->lodgingService->id);


            if($service->last_recorded_value >= $currentValue) {
                return $usageAmount;
            }

            $roomUsage = $this->findRoomUsage($room);

            $difIndex = $currentValue - $service->last_recorded_value;

            $totalPrice = $this->lodgingService->price_per_unit * $difIndex;
            $paymentAmount = ($totalPrice / $room->current_tenants) * $contract->quantity;
            $amountPaid = max(0, min($paymentAmount, $usageAmount));

            if(!$roomUsage['usage']) {
                $roomUsageService = new RoomUsageService();
                $dataRoomUsage = [
                    'room_id' => $room->id,
                    'lodging_service_id' => $this->lodgingService->id,
                    'total_price' => $totalPrice,
                    'amount_paid' => $amountPaid,
                    'value' => $difIndex,
                    'finalized' => false,
                    'month_billing' => $roomUsage['month_billing'],
                    'year_billing' => $roomUsage['year_billing'],
                    'is_need_close' => false,
                    'initial_index' => $service->last_recorded_value ?? 0,
                    'final_index' => $currentValue,
                    'unit_id' => $this->lodgingService->unit_id,
                    'service_id' => $this->lodgingService->service_id,
                    'service_name' => $this->lodgingService->name
                ];
                $roomUsage['usage'] = $roomUsageService->createRoomUsage($dataRoomUsage);
            }else{
                $roomUsage['usage']->update([
                    'total_price' => $roomUsage['usage']->total_price + $totalPrice,
                    'value' => $roomUsage['usage']->value + $difIndex,
                    'amount_paid' => $roomUsage['usage']->amount_paid + $amountPaid,
                    'final_index' => $currentValue,
                ]);
            }

            $service->update([
                "last_recorded_value" => $currentValue,
            ]);

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
