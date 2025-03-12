<?php

namespace App\Services\LodgingService;

use App\Models\LodgingService;
use App\Models\Room;
use App\Models\RoomServiceUsage;
use App\Models\ServicePayment;
use App\Services\Notification\NotificationService;
use App\Services\Token\TokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class BaseServiceCalculator
{
    protected LodgingService $lodgingService;
    protected $now;

    public function __construct($lodgingService)
    {
        $this->lodgingService = $lodgingService->load(['service', 'unit', 'lodging']);
        $this->now = Carbon::now();
    }

    abstract public function calculateCost();

    abstract function processRoomUsage($room, $totalPrice, $value);

    protected function findRoomUsage(Room $room)
    {
        $billingDay = $this->lodgingService->payment_date;

        // Xác định ngày bắt đầu chu kỳ thanh toán của tháng này
        $billingStartDate = Carbon::create($this->now->year, $this->now->month, $billingDay);

        // Nếu hôm nay >= payment_date → tính cho tháng này (dịch vụ của tháng trước)
        if ($this->now > $billingStartDate) {
            $monthBilling = $this->now->month;
            $yearBilling = $this->now->year;

        } else {
            $monthBilling = $this->now->month - 1;
            $yearBilling = $this->now->year;

            if ($monthBilling == 0) {
                $monthBilling += 12;
                $yearBilling -= 1;
            }
        }

        $roomUsage = RoomServiceUsage::where([
            'room_id' => $room->id,
            'lodging_service_id' => $this->lodgingService->id,
            'month_billing' => $monthBilling,
            'year_billing' => $yearBilling
        ])->first();

        return [
            "usage" => $roomUsage,
            "month_billing" => $monthBilling,
            "year_billing" => $yearBilling
        ];
    }

    protected function createPaymentAndNotify($room, $contract, $paymentAmount, $roomUsage)
    {
        // Lưu thanh toán vào bảng ServicePayment
        ServicePayment::create([
            'room_service_usage_id' => $roomUsage->id,
            'contract_id' => $contract->id,
            'payment_amount' => $paymentAmount,
            'amount_paid' => 0,
            'payment_date' => $this->now,
            'last_payment_date' => $this->now,
            'due_date' => $this->now->clone()->addDays($this->lodgingService->late_days),
        ]);

        // Lấy tên dịch vụ
        $nameService = isset($this->lodgingService->service) ? config("constant.service.name.{$this->lodgingService->service->name}") : $this->lodgingService->name;

        // Lấy thông tin nhà trọ
        $lodgingName = $this->lodgingService->lodging->name ?? 'Khu trọ không xác định';
        $lodgingType = $this->lodgingService->lodging->type->name ?? "";

        $paymentAmount = rtrim(rtrim(number_format($paymentAmount, 2, ',', '.'), '0'), ',');
        // Nội dung thông báo rõ ràng hơn
        $message = [
            'title' => "Nhắc nhở thanh toán tiền $nameService tháng {$this->now->month} - $lodgingName",
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


    protected function getActiveRooms()
    {
        return Room::where('lodging_id', $this->lodgingService->lodging_id)
            ->whereHas('contracts', function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            })
            ->with(['contracts' => function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            }])
            ->get();
    }
}
