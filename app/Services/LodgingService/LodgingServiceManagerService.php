<?php

namespace App\Services\LodgingService;

use App\Models\Room;
use App\Models\RoomService;
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
            'name' => $data['name'] ?? null,
            'unit_id' => $data['unit_id'],
            'payment_date' => $data['payment_date'] ?? $lodging->payment_date,
            'late_days' => $data['late_days'] ?? $lodging->late_days,
            'price_per_unit' => $data['price_per_unit'],
        ];
        try{
        return Model::create($insertData);}
        catch (\Exception $exception){
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    public function listByLodging($lodgingId){
        return Model::with(['service', 'unit'])
            ->select('id', 'service_id', 'name', 'unit_id', 'price_per_unit')
            ->where(['lodging_id' => $lodgingId, 'is_enabled' => true])->get();
    }


    public function detail($id)
    {
        $service = Model::with(['service', 'unit'])->find($id);

        $rooms = Room::where('lodging_id', $service->lodging_id)
            ->withExists([
                'services as is_usage' => function ($query) use ($id) {
                    $query->where('lodging_service_id', $id);
                }
            ])
            ->get();

        $service->setRelation('rooms', $rooms);

        return $service;

    }
}
