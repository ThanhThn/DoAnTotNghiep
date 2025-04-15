<?php

namespace App\Services\PaymentHistory;

use App\Models\PaymentHistory;
use App\Services\RentalHistory\RentalHistoryService;
use App\Services\ServicePayment\ServicePaymentService;

class PaymentHistoryService
{

    public function list($data)
    {
        $histories = PaymentHistory::on('pgsqlReplica')->where([
            'object_id' => $data['object_id'],
            'object_type' => $data['object_type'],
        ]);

        if(isset($data['from'])){
            $histories->where('created_at', '>=', $data['from']);
        }
        if(isset($data['to'])){
            $histories->where('created_at', '<=', $data['to']);
        }

        $total = $histories->count();

        if(isset($data['offset'])){
            $histories->offset($data['offset']);
        }
        if(isset($data['limit'])){
            $histories->limit($data['limit']);
        }

        $histories = $histories->orderBy('created_at', 'desc')->get();

        return [
            'total' => $total,
            'data' => $histories,
        ];
    }
    public function checkUserAccess($objectId, $objectType, $userId): bool{
        try{
            $service = match ($objectType){
                config('constant.object.type.rent') => new RentalHistoryService(),
                config('constant.object.type.service') => new ServicePaymentService(),
                default => throw new \Exception('Type not supported')
            };

            return $service->checkAccessUser($objectId, $userId);
        }catch (\Exception $exception){
            return false;
        }
    }
}
