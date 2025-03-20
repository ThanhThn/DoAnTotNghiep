<?php

namespace App\Services\ServicePayment;

use App\Models\RoomServiceUsage;
use App\Models\ServicePayment;

class ServicePaymentService
{
    public function listServicePaymentByContract($data)
    {
        $servicePayments = ServicePayment::with('roomServiceUsage:id,month_billing,year_billing,service_id,service_name,unit_id,initial_index,final_index')->where('contract_id', $data['contract_id']);


        $total = $servicePayments->count();

        $servicePayments = $servicePayments
            ->orderBy(
                RoomServiceUsage::select('year_billing')
                    ->whereColumn('room_service_usages.id', 'service_payments.room_service_usage_id'),
                'desc'
            )
            ->orderBy(
                RoomServiceUsage::select('month_billing')
                    ->whereColumn('room_service_usages.id', 'service_payments.room_service_usage_id'),
                'desc'
            )
            ->orderBy('payment_date', 'desc')
            ->offset($data['offset'] ?? 0)
            ->limit($data['limit'] ?? 20)
            ->get();

        return [
            'total' => $total,
            'data' => $servicePayments,
        ];
    }
}
