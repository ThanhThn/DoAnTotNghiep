<?php

namespace App\Services\Equipment;

use App\Jobs\UploadImageToStorage;
use App\Models\Equipment;
use App\Services\RoomSetup\RoomSetupService;
use Illuminate\Support\Facades\DB;

class EquipmentService
{
    public function create($data){
        $insertData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'quantity' => $data['quantity'],
            'type' => $data['type'],
            'thumbnail' => $data['thumbnail'],
            'lodging_id' => $data['lodging_id'],
            'remaining_quantity' => isset($data['room_ids']) ? $data['quantity'] - count($data['room_ids']) : $data['quantity'],
        ];

        if (isset($data['room_ids']) && count($data['room_ids']) < $data['needed_rooms']) {
            return [
                'errors' => [[
                    'message' => "Số lượng lưu trữ bé hơn số lượng cần dùng"
                ]]
            ];
        }

        try {
            DB::beginTransaction();

            $equipment = Equipment::create($insertData);

            $roomSetupData = array_map(fn ($roomId) => [
                "room_id" => $roomId,
                'equipment_id' => $equipment->id,
                'quantity' => 1,
            ], $data['room_ids']);
            $roomSetupService = new RoomSetupService();
            $result = $roomSetupService->insert($roomSetupData);

            if (!$result) {
                throw new \Exception("Lỗi khi chèn dữ liệu room setup");
            }

            UploadImageToStorage::dispatch($equipment->id, config('constant.type.equipment'), $data['thumbnail']);
            DB::commit();
            return $equipment;
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception
                ]]
            ];
        }
    }
}
