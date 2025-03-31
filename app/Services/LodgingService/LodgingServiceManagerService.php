<?php

namespace App\Services\LodgingService;

use App\Models\Room;
use App\Models\RoomService;
use App\Models\Unit;
use App\Services\Lodging\LodgingService;
use App\Models\LodgingService as Model;
use App\Services\RoomService\RoomServiceManagerService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Mockery\Exception;

class LodgingServiceManagerService
{
    public function create($data)
    {
        $lodging = (new LodgingService())->detailLodging($data['lodging_id']);
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
            $lodgingService = Model::create($insertData);
            $roomService = new RoomServiceManagerService();

            if(isset($data['room_ids'])){
                $insertData= array_map(fn ($roomId) => [
                    "id" => Str::uuid(),
                    "room_id" => $roomId,
                    'lodging_service_id' => $lodgingService->id,
                    'last_recorded_value' => 0,
                ], $data['room_ids']);
                $roomService->insert($insertData);
            }

            return $lodgingService;
        }
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
        $service = Model::with(['service', 'unit', 'roomServices' => function ($query) {
            $query->where('is_enabled', true)->with('room');
        }])->find($id);
        return $service;
    }

    public function update($id, $data)
    {
        try {
            $service = Model::find($id);

            // Chuẩn bị dữ liệu cập nhật
            $updateData = array_filter([
                'service_id' => $data['service_id'] ?? null,
                'name' => $data['name'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'payment_date' => $data['payment_date'] ?? null,
                'late_days' => $data['late_days'] ?? null,
                'price_per_unit' => $data['price_per_unit'] ?? null,
            ], fn($value) => !is_null($value));

            $updateData = array_filter($updateData, function ($value, $key) use ($service) {
                return $service->$key !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (!empty($updateData)) {
                $service->update($updateData);
            }


            // Nếu có room_ids, đồng bộ danh sách
            if (isset($data['room_ids'])) {
                $roomService = new RoomServiceManagerService();
                $roomService->updateAndCreateByLodgingService($data['room_ids'], $id);
            }

            return $service->refresh();
        } catch (\Exception $exception) {
            return [
                'errors' => [['message' => $exception->getMessage()]]
            ];
        }
    }


    public function listByRoom($roomId)
    {
        $service = Model::whereHas('roomServices', function ($query) use ($roomId) {
            $query->where(['room_id' => $roomId, 'is_enabled' => true]);
        })->with(['service', 'unit', 'roomServices' => function ($query) use ($roomId){
            $query->where(['room_id' => $roomId, 'is_enabled' => true]);
        }])->get();
        return $service;
    }

    public function list()
    {
        $results = Model::whereHas('roomServices', function ($query) {
            $query->where('is_enabled', true)
                ->whereHas('room.contracts', function ($queryInner) {
                    $queryInner->where('status', config('constant.contract.status.active'));
                });
        })->with(['service', 'unit'])->get();
        return $results;
    }

    public function softDelete($id)
    {
        try {
            $model = Model::findOrFail($id);
            $model->delete();
            return true;
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'errors' => [
                    [
                        'message' => $exception->getMessage(),
                    ]
                ]
            ];
        }
    }

    public function getServiceCalculator(Model $lodgingService): ?ServiceCalculatorFactory
    {
        $lodgingService->load('unit');
        return match ($lodgingService->unit->name) {
            'month' => new MonthlyService($lodgingService),
            'person' => new PersonService($lodgingService),
            'kwh', 'cubic_meter' => new IndexedService($lodgingService),
            default => throw new \InvalidArgumentException("Unknown service type: " . $lodgingService->type->name),
        };
    }

}
