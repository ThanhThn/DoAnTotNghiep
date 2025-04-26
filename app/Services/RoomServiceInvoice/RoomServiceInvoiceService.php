<?php

namespace App\Services\RoomServiceInvoice;

use App\Models\Contract;
use App\Models\RentPayment;
use App\Models\RoomServiceInvoice;
use App\Models\ServicePayment;
use App\Services\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomServiceInvoiceService
{
    public function createRoomServiceInvoice($data)
    {
        $insertData = $data;

        if(!isset($data['is_need_close'])){
            $insertData['is_need_close'] = false;
        }

        return RoomServiceInvoice::create($insertData);
    }

    function statisticalAmount($month, $year, $lodgingId)
    {
        $amount = RoomServiceInvoice::whereHas('room.lodging', function ($query) use ($lodgingId) {
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
        $usages = RoomServiceInvoice::whereHas('room.lodging', function ($query) use ($lodgingId) {
            $query->where('id', $lodgingId);
        })->where([
            'finalized' => false,
            'is_need_close' => true,
        ])->with(['room:id,room_code,lodging_id', 'lodgingService:id,name,unit_id,service_id'])->get();

        return $usages;

    }


    function updateFinalRoomServiceInvoice($data)
    {
        $roomUsage = RoomServiceInvoice::with(['lodgingService', 'room', 'room.lodging'])->find($data['room_usage_id']);

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
            $amountPerPerson = ($roomUsage->total_price - $roomUsage->amount_paid) / $roomUsage->room->current_tenants;

            if($amountPerPerson > 0){
                foreach ($contracts as $contract) {
                    $paymentAmount = $amountPerPerson * $contract->quantity;

                    $result = ServicePayment::create([
                        'room_service_invoice_id' => $roomUsage->id,
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
                        'target_endpoint' => "/payment_history/service/$result->id?redirect_to=user",
                        'type' => config('constant.notification.type.important'),
                    ];

                    // Gửi thông báo
                    $notificationService = new NotificationService();
                    $notificationService->createNotification(
                        $message,
                        config('constant.object.type.user'),
                        $contract->user_id,
                        $contract->user_id,
                        config('constant.rule.user')
                    );
                }
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
