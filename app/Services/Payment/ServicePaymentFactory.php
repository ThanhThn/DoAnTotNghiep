<?php

namespace App\Services\Payment;

use App\Services\Payment\PaymentServiceFactory;
use App\Services\ServicePayment\ServicePaymentService;

class ServicePaymentFactory extends PaymentServiceFactory
{

    function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = [])
    {
        try {
            if (!$relatedId) {
                throw new \Exception("Thiếu thông tin quan trọng để thực hiện thanh toán.");
            }

            $service = new ServicePaymentService();
            $result = $service->paymentByContract($contractId, $relatedId, $amount, $paymentMethod);

            if(isset($result['errors'])){
                throw new \Exception($result['errors'][0]['message']);
            }

            return true;
        } catch (\Exception $exception) {
            return ['errors' => [['message' => $exception->getMessage()]]];
        }
    }
}
