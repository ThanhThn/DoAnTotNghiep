<?php

namespace App\Services\Equipment;

use App\Jobs\UploadImageToStorage;
use App\Models\Equipment;
use App\Models\Room;
use App\Models\RoomSetup;
use App\Services\Image\ImageService;
use App\Services\RoomSetup\RoomSetupService;
use Illuminate\Http\UploadedFile;
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

        if (isset($data['room_ids']) && count($data['room_ids']) > $data['quantity']) {
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

    public function detail($equipmentId)
    {
        $equipment = Equipment::with(['roomSetups.room'])->find($equipmentId);
        return $equipment;
    }

    public function update($equipmentId, $data)
    {
        $equipment = Equipment::find($equipmentId);

        $type = $data['type'] ?? $equipment->type;

        try{
            DB::beginTransaction();
            $roomSetupService = new RoomSetupService();
            if($type == config('constant.equipment.type.public')){
                $roomSetupService->softDeleteAll([
                    'equipment_id' => $equipmentId,
                ]);

                $remainingQuantity = $data['quantity'];
            }else{
                $roomIds = $data['room_ids'] ?? [];
                $result = $roomSetupService->syncRoomSetupsForEquipment($equipmentId, $roomIds);

                $remainingQuantity = $data['quantity'] - $result['used_quantities'];
            }

            if($remainingQuantity < 0){
                throw new \Exception("Số lượng lưu trữ bé hơn số lượng cần dùng");
            }

            $oldThumbnail = $equipment->thumbnail;
            $newThumbnail = $data['thumbnail'];


            if ($oldThumbnail != $newThumbnail && is_string($newThumbnail)) {
                UploadImageToStorage::dispatch($equipment->id, config('constant.type.equipment'), $newThumbnail);
            }

            if($newThumbnail instanceof UploadedFile){
                $newThumbnail = ImageService::uploadImage($newThumbnail, config('constant.type.equipment'), $equipment->id);
            }
            $equipment->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'quantity' => $data['quantity'],
                'type' => $type,
                'thumbnail' => $newThumbnail,
                'lodging_id' => $data['lodging_id'],
                'remaining_quantity' => $remainingQuantity
            ]);

            DB::commit();

            return $equipment->refresh()->load('roomSetups.room');
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception->getMessage()
                ]]
            ];
        }

    }

    public function softDelete($id)
    {
        try {
            $model = Equipment::findOrFail($id);
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

    public function listByLodging($data, $lodgingId)
    {
        $equipments = Equipment::where(['lodging_id' => $lodgingId]);

        $total = $equipments->count();

        $equipments = $equipments->offset($data['offset'] ?? 0)
            ->limit($data['limit'] ?? 20)
            ->get();

        return [
            'total' => $total,
            'data' => $equipments
        ];
    }
}
