<?php

namespace App\Services\Payment;

use App\Models\Contract;
use App\Models\PaymentHistory;
use App\Models\RentPayment;
use App\Services\Contract\ContractService;
use App\Services\Payment\PaymentServiceFactory;
use App\Services\RoomRentInvoice\RoomRentInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomPaymentFactory extends PaymentServiceFactory
{

    function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = [])
    {
        try {
            $roomRentalHistoryService = new RoomRentInvoiceService();
            $rentalHistory = RentPayment::where('contract_id', $contractId);

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

            $contract = (new ContractService())->detail($contractId);

            foreach ($rentalHistory as $history) {
                $amountToBePaid = $history->payment_amount - $history->amount_paid;

                $amountPaid = $history->amount_paid + min($amountToBePaid, $amount);

                $status = match (true) {
                    $amountPaid == 0 => config('constant.payment.status.unpaid'),
                    $amountPaid < $history->payment_amount => config('constant.payment.status.partial'),
                    default => config('constant.payment.status.paid'),
                };

                $history->update([
                    'status' => $status,
                    'amount_paid' => $amountPaid,
                    'payment_method' => $paymentMethod,
                    'last_payment_date' => Carbon::now()
                ]);


                if($history->room_rent_invoice_id){
                    $roomRental = $roomRentalHistoryService->detail($history->room_rent_invoice_id);

                    if ($roomRental) {
                        $roomRental->amount_paid += min($amountToBePaid, $amount);
                        $roomRental->save();
                    }
                }


                PaymentHistory::create([
                    'contract_id' => $contractId,
                    'room_id' => $contract->room_id,
                    'lodging_id' => $contract->room->lodging_id,
                    'object_id' => $history->id,
                    'object_type' => config('constant.object.type.rent'),
                    'amount' => min($amountToBePaid, $amount),
                    'payment_method' => $paymentMethod,
                    'paid_at' => Carbon::now(),
                ]);
                $amount = max(0, $amount - $amountToBePaid);

                if ($amount <= 0) {
                    break;
                }
            }

            if ($amount > 0) {
                $contract->update([
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
