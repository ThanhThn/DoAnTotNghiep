<?php

namespace App\Services\LodgingService;

use App\Services\Lodging\LodgingService;
use App\Models\LodgingService as Model;

class LodgingServiceManagerService
{
    public function create($data)
    {
        $lodging = (new LodgingService())->get($data['lodging_id']);
        $insertData = [
            'lodging_id' => $data['lodging_id'],
            'service_id' => $data['service_id'] ?? null,
            'service_name' => $data['service_name'] ?? null,
            'unit_id' => $data['unit_id'],
            'payment_date' => $data['payment_date'] ?? $lodging->payment_date,
            'late_days' => $data['late_days'] ?? $lodging->late_days,
            'price_per_unit' => $data['price_per_unit'],
        ];
        try{
        return Model::create($insertData);}
        catch (\Exception $exception){
            return [
                'error' => [
                    'message' => $exception->getMessage(),
                ]
            ];
        }
    }
}
