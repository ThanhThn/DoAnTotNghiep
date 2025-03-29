<?php

namespace App\Services\Payment;

abstract class PaymentService
{
    public function paymentByContract($data)
    {
        [$servicePayment, $relatedId, $extraData] = match ($data['payment_type']) {
          'rent' => [new RoomPayment(), $data['rental_history_id'] ?? "", ["type" => $data["rent_payment_type"]]],
          'service' => [new ServicePayment(), $data['service_payment_id'] ?? "", []],
        };

        return $servicePayment->processPaymentByContract($data['contract_id'], $relatedId, $data['amount'], $data['payment_method'] , $extraData);
    }

    abstract function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = []);
}
