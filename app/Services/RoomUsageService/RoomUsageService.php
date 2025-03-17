<?php

namespace App\Services\RoomUsageService;

use App\Models\Contract;
use App\Models\RentalHistory;
use App\Models\RoomServiceUsage;
use App\Models\ServicePayment;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomUsageService
{
    public function createRoomUsage($data)
    {
        $insertData = [
            'room_id' => $data['room_id'],
            'lodging_service_id' => $data['lodging_service_id'],
            'total_price' => $data['total_price'],
            'amount_paid' => $data['amount_paid'],
            'value' => $data['value'],
            'finalized' => $data['finalized'],
            'month_billing' => $data['month_billing'],
            'year_billing' => $data['year_billing'],
            'is_need_close' => $data['is_need_close'] ?? false,
        ];

        return RoomServiceUsage::create($insertData);
    }

    function statisticalAmount($month, $year, $lodgingId)
    {
        $amount = RoomServiceUsage::whereHas('room.lodging', function ($query) use ($lodgingId) {
            $query->where('id', $lodgingId);
        })
            ->where([
                'month_billing' => $month,
                'year_billing' => $year,
            ])
            ->selectRaw('SUM(total_price) as total_payment, SUM(amount_paid) as total_paid')
            ->first();

        return $amount;
    }

    function listNeedCloseByLodging($lodgingId)
    {
        $usages = RoomServiceUsage::whereHas('room.lodging', function ($query) use ($lodgingId) {
            $query->where('id', $lodgingId);
        })->where([
            'finalized' => false,
            'is_need_close' => true,
        ])->get();

        return $usages;

    }


    function updateFinalRoomUsage($data)
    {
        $roomUsage = RoomServiceUsage::with(['lodgingService', 'room'])->find($data['room_usage_id']);

        try{
            DB::beginTransaction();
            if(isset($roomUsage->final_index)){
                $value = $data['final_index'] - $roomUsage->final_index;
            }else{
                $value = $data['final_index'] - $roomUsage->initial_index;
            }

           $totalPrice = $value * $roomUsage->lodgingService->price_per_unit;

            $roomUsage->update([
                'finalized' => true,
                'is_need_close' => false,
                'final_index' => $data['final_index'],
                'value' => $roomUsage->value + $value,
                'total_price' => $roomUsage->total_price + $totalPrice,
            ]);

            $roomUsage->refresh();

            $contracts = Contract::where([
                'room_id' => $roomUsage->room_id,
                'status' => config('constant.contract.status.active')
            ])->get();

            $now = Carbon::now();
            $amountPerPerson = $roomUsage->total_price / $roomUsage->room->current_tenants;
            foreach ($contracts as $contract) {
                $paymentAmount = $amountPerPerson * $contract->quantity;

                ServicePayment::create([
                    'room_service_usage_id' => $roomUsage->id,
                    'contract_id' => $contract->id,
                    'payment_amount' => $paymentAmount,
                    'amount_paid' => 0,
                    'payment_date' => $now,
                    'last_payment_date' => $now,
                    'due_date' => $now->clone()->addDays($roomUsage->lodgingService->late_days),
                ]);

                // Lấy tên dịch vụ
                $nameService = isset($roomUsage->lodgingService->service) ? config("constant.service.name.{$roomUsage->lodgingService->service->name}") : $roomUsage->lodgingService->name;

                // Lấy thông tin nhà trọ
                $lodgingName = $roomUsage->room->lodging->name ?? 'Khu trọ không xác định';
                $lodgingType = $roomUsage->room->lodging->type->name ?? "";

                $paymentAmount = rtrim(rtrim(number_format($paymentAmount, 2, ',', '.'), '0'), ',');

                $message = [
                    'title' => "Nhắc nhở thanh toán tiền $nameService tháng {$roomUsage->month_billing} - $lodgingName",
                    'body' => "Bạn cần thanh toán $paymentAmount đ cho phòng {$roomUsage->room->room_code}, $lodgingType $lodgingName. Vui lòng thanh toán sớm để tránh phí trễ hạn.",
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

            DB::commit();
            return $roomUsage;

        }catch (\Exception $exception){
            DB::rollBack();
            return ['errors' => [[
                'message' => $exception->getMessage(),
            ]]];
        }


    }
}
