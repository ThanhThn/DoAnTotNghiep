<?php

namespace App\Services\LodgingService;

use App\Models\Contract;
use App\Models\LodgingService;
use App\Models\PaymentHistory;
use App\Models\Room;
use App\Models\RoomServiceUsage;
use App\Models\ServicePayment;
use App\Services\Notification\NotificationService;
use App\Services\RoomUsageService\RoomUsageService;
use App\Services\Token\TokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

abstract class ServiceCalculatorFactory
{
    protected LodgingService $lodgingService;
    protected $now;
    protected $roomUsageService;

    public function __construct($lodgingService)
    {
        $this->lodgingService = $lodgingService->load(['service', 'unit', 'lodging']);
        $this->now = Carbon::now();
        $this->roomUsageService = new RoomUsageService();
    }

    abstract public function calculateCost();

    abstract public function processRoomUsageForContract(Room $room, Contract $contract, $usageAmount, $paymentMethod ,$currentValue = 0, $extractData = []);

    abstract function processRoomUsage($room, $totalPrice, $value, $monthBilling = null, $yearBilling = null, $recursionCount = 0);

    protected function findRoomUsage(Room $room, $monthBilling = null, $yearBilling = null)
    {
        $billingDay = $this->lodgingService->payment_date;

        if($monthBilling == null && $yearBilling == null){
            // Nếu hôm nay >= payment_date → tính cho tháng này (dịch vụ của tháng trước)
            if ($this->now->day > $billingDay) {
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

    protected function createPaymentAndNotify($room, $contract, $paymentAmount, $roomUsage, $monthBilling , $amountPaid = 0, $paymentMethod = null)
    {
        // Lưu thanh toán vào bảng ServicePayment
        $servicePayment = ServicePayment::create([
            'room_service_usage_id' => $roomUsage->id,
            'contract_id' => $contract->id,
            'payment_amount' => $paymentAmount,
            'amount_paid' => $amountPaid,
            'payment_date' => $this->now,
            'last_payment_date' => $this->now,
            'payment_method' => $paymentMethod,
            'due_date' => $this->now->clone()->addDays($this->lodgingService->late_days),
        ]);

        if($amountPaid > 0){
            PaymentHistory::create([
                'contract_id' => $contract->id,
                'room_id' => $contract->room_id,
                'lodging_id' => $room->lodging_id,
                'object_id' => $servicePayment->id,
                'object_type' => config('constant.object.type.service'),
                'amount' => $amountPaid,
                'payment_method' => config('constant.payment.method.system'),
                'paid_at' => Carbon::now(),
            ]);
        }


        if($paymentAmount > $amountPaid){
            // Lấy tên dịch vụ
            $nameService = isset($this->lodgingService->service) ? config("constant.service.name.{$this->lodgingService->service->name}") : $this->lodgingService->name;

            // Lấy thông tin nhà trọ
            $lodgingName = $this->lodgingService->lodging->name ?? 'Khu trọ không xác định';
            $lodgingType = $this->lodgingService->lodging->type->name ?? "";

            $paymentAmount = rtrim(rtrim(number_format($paymentAmount, 2, ',', '.'), '0'), ',');


            $message = [
                'title' => "Nhắc nhở thanh toán tiền $nameService tháng {$monthBilling} - $lodgingName",
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


    protected function getActiveRooms()
    {
        $lodgingService = $this->lodgingService;
        return Room::where('lodging_id', $this->lodgingService->lodging_id)
            ->whereHas('contracts', function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            })
            ->whereHas('roomServices', function ($query) use($lodgingService) {
                $query->where([
                    'lodging_service_id' => $lodgingService->id,
                    'is_enabled' => true]);
            })
            ->with(['contracts' => function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            }])
            ->get();
    }
}
