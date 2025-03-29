<?php

namespace App\Services\Payment;

use App\Models\Contract;
use App\Models\RentalHistory;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomPayment extends PaymentService
{

    function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = [])
    {
        try {
            $rentalHistory = RentalHistory::where('contract_id', $contractId);

            if (!isset($extraData['type']) && !$relatedId) {
                throw new \Exception("Thiếu thông tin quan trọng để thực hiện thanh toán.");
            }

            if ($extraData['type'] == 'debt' && $relatedId) {
                $rentalHistory->where('id', $relatedId);
            }

            $rentalHistory = $rentalHistory->orderBy('payment_date', 'asc')->get();

            if ($rentalHistory->isEmpty()) {
                throw new \Exception("Không tìm thấy lịch sử thanh toán cho hợp đồng này.");
            }

            foreach ($rentalHistory as $history) {
                $amountToBePaid = $history->payment_amount - $history->amount_paid;

                $amountPaid = min($amountToBePaid, $amount);
                $amount = max(0, $amount - $amountToBePaid);

                $history->update([
                    'amount_paid' => $history->amount_paid + $amountPaid,
                    'payment_method' => $paymentMethod,
                    'last_payment_date' => Carbon::now()
                ]);

                if ($amount <= 0) {
                    break;
                }
            }

            if ($amount > 0) {
                Contract::where('id', $contractId)->update([
                    'remain_amount' => DB::raw('remain_amount + ' . $amount),
                ]);
            }

            return true;
        }catch (\Exception $exception){
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }
}
