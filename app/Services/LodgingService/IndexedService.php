<?php

namespace App\Services\LodgingService;

use App\Models\RoomService;
use App\Models\RoomServiceUsage;
use App\Services\LodgingService\BaseServiceCalculator;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;

class IndexedService extends BaseServiceCalculator
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
            $roomUsage = RoomServiceUsage::create([
                'room_id' => $room->id,
                'lodging_service_id' => $this->lodgingService->id,
                'total_price' => 0,
                'amount_paid' => 0,
                'value' => $roomService ? $roomService->last_recorded_value : 0,
                'finalized' => false,
                'month_billing' => $roomUsage['month_billing'],
                'year_billing' => $roomUsage['year_billing'],
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
        }


        // Lấy tên dịch vụ
        $nameService = isset($this->lodgingService->service) ? config("constant.service.name.{$this->lodgingService->service->name}") : $this->lodgingService->name;

        $notificationService = new NotificationService();
        $message = [
            'title' => "Cập nhật chỉ số dịch vụ $nameService",
            'body' => "Vui lòng cập nhật chỉ số mới cho dịch vụ $nameService phòng {$room->room_code}.",
            'target_endpoint' => '/service_update',
            'type' => config('constant.notification.type.important'),
        ];
        $notificationService->createNotification($message, config('constant.object.type.lodging'), $this->lodgingService->lodging->id, $this->lodgingService->lodging->user_id);

        return 0;
    }
}
