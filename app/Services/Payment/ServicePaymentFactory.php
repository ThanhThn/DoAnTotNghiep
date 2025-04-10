<?php

namespace App\Services\Payment;

use App\Models\Contract;
use App\Models\ServicePayment;
use App\Services\Payment\PaymentServiceFactory;
use App\Services\ServicePayment\ServicePaymentService;
use Illuminate\Support\Facades\DB;

class ServicePaymentFactory extends PaymentServiceFactory
{

    function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = [])
    {
        try {
            $servicePayment = ServicePayment::where('contract_id', $contractId);
            if (!isset($extraData['type']) && !$relatedId) {
                throw new \Exception("Thiếu thông tin quan trọng để thực hiện thanh toán.");
            }

            if ($extraData['type'] == 'debt' && $relatedId) {
                $servicePayment->where('id', $relatedId);
            }
            $servicePayment = $servicePayment->orderBy('payment_date', 'asc')->get();
            if ($servicePayment->isEmpty()) {
                throw new \Exception("Không tìm thấy lịch sử thanh toán dịch vụ cho hợp đồng này.");
            }

            $service = new ServicePaymentService();
            foreach ($servicePayment as $payment) {
                $amountToBePaid = $payment->payment_amount - $payment->amount_paid;

                $result = $service->paymentByContract($contractId, $payment->id, min($amountToBePaid, $amount), $paymentMethod);

                if(isset($result['errors'])){
                    throw new \Exception($result['errors'][0]['message']);
                }

                $amount = max(0, $amount - $amountToBePaid);
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
        } catch (\Exception $exception) {
            return ['errors' => [['message' => $exception->getMessage()]]];
        }
    }
}
