<?php

namespace App\Services\LodgingService;

use App\Models\Room;
use App\Models\RoomService;
use App\Models\Unit;
use App\Services\Lodging\LodgingService;
use App\Models\LodgingService as Model;
use App\Services\RoomService\RoomServiceManagerService;

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

    public function update($id, $data)
    {
        try {
            $service = Model::findOrFail($id);

            // Chuẩn bị dữ liệu cập nhật
            $updateData = array_filter([
                'service_id' => $data['service_id'] ?? null,
                'name' => $data['name'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'payment_date' => $data['payment_date'] ?? null,
                'late_days' => $data['late_days'] ?? null,
                'price_per_unit' => $data['price_per_unit'] ?? null,
            ], fn($value) => !is_null($value)); // Loại bỏ giá trị null để tránh ghi đè không cần thiết

            // Cập nhật dữ liệu nếu có thay đổi
            if (!empty($updateData)) {
                $service->update($updateData);
            }

            // Nếu có danh sách room_ids
            if (!empty($data['room_ids'])) {
                // Lấy danh sách phòng đã sử dụng dịch vụ
                $roomUsage = RoomService::where('lodging_service_id', $id)
                    ->pluck('room_id')
                    ->toArray();

                // Lọc ra những phòng chưa sử dụng dịch vụ
                $roomNotUsage = array_diff($data['room_ids'], $roomUsage);

                // Nếu không có phòng mới, bỏ qua
                if (!empty($roomNotUsage)) {
                    $unit = Unit::find($data['unit_id']);

                    // Chuẩn bị dữ liệu để chèn hàng loạt (bulk insert)
                    $roomServiceData = array_map(fn($room) => [
                        'room_id' => $room,
                        'lodging_service_id' => $id,
                        'last_recorded_value' => $unit?->is_fixed ? null : 0,
                    ], $roomNotUsage);

                    // Chèn hàng loạt để tối ưu hiệu suất
                    RoomService::insert($roomServiceData);
                }
            }

            return $service->refresh();
        } catch (\Exception $exception) {
            return [
                'success' => false,
                'errors' => [['message' => $exception->getMessage()]]
            ];
        }
    }

}
