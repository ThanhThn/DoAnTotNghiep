<?php

namespace App\Services\LodgingService;

use App\Models\LodgingService;
use App\Models\Room;
use App\Models\RoomServiceUsage;
use App\Models\ServicePayment;
use App\Services\Notification\NotificationService;
use App\Services\Token\TokenService;
use Carbon\Carbon;

abstract class BaseServiceCalculator
{
    protected LodgingService $lodgingService;

    public function __construct($lodgingService)
    {
        $this->lodgingService = $lodgingService->load(['service', 'unit', 'lodging']);
    }

    abstract public function calculateCost();

    protected function processRoomUsage($room, $totalPrice, $value)
    {
        $now = Carbon::today();

        // Kiểm tra xem đã có bản ghi trong tháng này chưa
        $roomUsage = RoomServiceUsage::where([
            'room_id' => $room->id,
            'lodging_service_id' => $this->lodgingService->id,
        ])->whereMonth('created_at', $now->month) // Chỉ lấy dữ liệu của tháng hiện tại
        ->whereYear('created_at', $now->year)
            ->first();

        if (!$roomUsage) {
            // Nếu chưa có, tạo mới
            $roomUsage = RoomServiceUsage::create([
                'room_id' => $room->id,
                'lodging_service_id' => $this->lodgingService->id,
                'total_price' => $totalPrice,
                'amount_paid' => 0,
                'value' => $value,
                'finalized' => true,
                'created_at' => $now, // Đảm bảo thời gian chính xác
                'updated_at' => $now
            ]);
        } else {
            // Nếu đã có, chỉ cập nhật finalized
            $roomUsage->update([
                'finalized' => true,
                'updated_at' => $now
            ]);
        }

        foreach ($room->contracts as $contract) {
            $this->createPaymentAndNotify($room, $contract, $now, $totalPrice, $roomUsage);
        }
    }

    protected function createPaymentAndNotify($room, $contract, $now, $paymentAmount, $roomUsage)
    {
        // Lưu thanh toán vào bảng ServicePayment
        ServicePayment::create([
            'room_service_usage_id' => $roomUsage->id,
            'contract_id' => $contract->id,
            'payment_amount' => $paymentAmount,
            'amount_paid' => 0,
            'payment_date' => $now,
            'last_payment_date' => $now,
            'due_date' => $now->clone()->addDays($this->lodgingService->late_days),
        ]);

        // Lấy tên dịch vụ
        $nameService = isset($this->lodgingService->service) ? config("constant.service.name.{$this->lodgingService->service->name}") : $this->lodgingService->name;

        // Lấy thông tin nhà trọ
        $lodgingName = $this->lodgingService->lodging->name ?? 'Khu trọ không xác định';
        $lodgingType = $this->lodgingService->lodging->type->name ?? "";

        $paymentAmount = rtrim(rtrim(number_format($paymentAmount, 2, ',', '.'), '0'), ',');
        // Nội dung thông báo rõ ràng hơn
        $message = [
            'title' => "Nhắc nhở thanh toán tiền $nameService tháng {$now->month} - $lodgingName",
            'body' => "Bạn cần thanh toán $paymentAmount đ cho phòng {$room->room_code}, $lodgingType $lodgingName. Vui lòng thanh toán sớm để tránh phí trễ hạn.",
            'target_endpoint' => '/rental_history/list',
            'type' => config('constant.notification.type.important'),
        ];

        // Gửi thông báo
        $notificationService = new NotificationService();
        $notificationService->createNotification(
            $message,
            config('constant.object.type.user'),
            $contract->user_id,
            $contract->user_id
        );
    }
}
