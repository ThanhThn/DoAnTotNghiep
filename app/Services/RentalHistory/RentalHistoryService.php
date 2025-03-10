<?php

namespace App\Services\RentalHistory;

use App\Models\Contract;
use App\Models\RentalHistory;
use App\Services\Notification\NotificationService;
use App\Services\Token\TokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RentalHistoryService
{

    function createRentalHistory($data)
    {
        $insertData = [
            'contract_id' => $data['contract_id'],
            'payment_amount' => $data['payment_amount'],
            'amount_paid' => $data['amount_paid'],
            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_date' => $data['payment_date'],
            'last_payment_date' => $data['last_payment_date'],
            'due_date' => $data['due_date'],
        ];

        try {
            DB::beginTransaction();
            $rentalHistory = RentalHistory::create($insertData);

            if ($data['amount_paid'] < $data['payment_amount']) {
                $contract = Contract::find($data['contract_id']);
                $tokens = TokenService::getTokens($contract->user_id, config('constant.token.type.notify'));
                $currentMonth = Carbon::today()->month;

                $dif = $data['payment_amount'] - $data['amount_paid'];

                if (count($tokens) > 0) {
                    $contract->load('room');
                    $formattedDif = number_format($dif, 2, ',', '.');

                    $roomName = $contract->room->room_code ?? 'Phòng không xác định';
                    $lodging = $contract->room->lodging;
                    $lodgingName = $lodging->name ?? "Nhà trọ không xác định";
                    $lodgingType = strtolower($lodging->type->name ?? "");

                    $notificationService = new NotificationService();
                    $mess = [
                        'title' => "Nhắc nhở thanh toán tiền trọ tháng $currentMonth",
                        'body' => "Bạn còn thiếu $formattedDif đ tiền trọ tháng $currentMonth cho phòng $roomName, $lodgingType $lodgingName. Vui lòng thanh toán sớm để tránh phát sinh phí trễ hạn.",
                        'target_endpoint' => '/rental_history/list',
                        'type' => config('constant.notification.type.important')
                    ];
                    $notificationService->createNotification($mess, config('constant.object.type.user'),$contract->user_id, $tokens);
                }
            }
            DB::commit();
            return $rentalHistory;
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }
}
