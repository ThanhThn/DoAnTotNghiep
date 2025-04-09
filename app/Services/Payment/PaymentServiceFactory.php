<?php

namespace App\Services\Payment;

abstract class PaymentServiceFactory
{
    public function paymentByContract($data)
    {
        [$servicePayment, $relatedId, $extraData] = match ($data['payment_type']) {
          'rent' => [new RoomPaymentFactory(), $data['rental_history_id'] ?? "", ["type" => $data["rent_payment_type"]]],
          'service' => [new ServicePaymentFactory(), $data['service_payment_id'] ?? "", ["type" => $data["service_payment_type"]]],
        };

        return $servicePayment->processPaymentByContract($data['contract_id'], $relatedId, $data['amount'], $data['payment_method'] , $extraData);
    }

    abstract function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = []);
}
