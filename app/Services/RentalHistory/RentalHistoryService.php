<?php

namespace App\Services\RentalHistory;

use App\Models\Contract;
use App\Models\RentalHistory;
use App\Services\Notification\NotificationService;
use App\Services\Token\TokenService;
use Carbon\Carbon;

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
        ];

        $rentalHistory = RentalHistory::create($insertData);

        if($data['amount_paid'] < $data['payment_amount']){
            $contract = Contract::find($data['contract_id']);
            $tokens = TokenService::getTokens($contract->user_id, config('constant.token.type.notify'));
            $today = Carbon::today()->day;

            $dif =  $data['payment_amount'] - $data['amount_paid'];

            if(count($tokens) > 0){
                $notificationService = new NotificationService();
                $mess = [
                    'title' => "Thông báo đóng tiền trọ tháng $today",
                    'body' => "Tiền trọ tháng $today là $dif đ"
                ];
                $notificationService->createNotification($mess, $contract->user_id, config('constant.object.type.user'), $tokens);
            }
        }
    }
}
