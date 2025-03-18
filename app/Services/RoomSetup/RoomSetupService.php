<?php

namespace App\Services\RoomSetup;

use App\Models\RoomSetup;

class RoomSetupService
{
    public function insert(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $dataInsert = array_map(fn($item) => [
            ...$item,
            'status' => 1,
            'installation_date' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ], $data);

        RoomSetup::insert($dataInsert);

        return true;
    }

    public function softDeleteAll($data)
    {
        $roomSetups = RoomSetup::query();

        if(isset($data['equipment_id'])){
            $roomSetups = $roomSetups->where('equipment_id', $data['equipment_id']);
        }
        if(isset($data['room_id'])){
            $roomSetups = $roomSetups->where('room_id', $data['room_id']);
        }

        $roomSetups->delete();
    }

    public function syncRoomSetupsForEquipment($equipmentId,  array $roomIds)
    {
        RoomSetup::where('equipment_id', $equipmentId)
            ->whereNotIn('room_id', $roomIds)
            ->delete();

        $existingRoomSetups = RoomSetup::withTrashed()
            ->where('equipment_id', $equipmentId)
            ->whereIn('room_id', $roomIds)
            ->get()->keyBy('room_id');


        $roomSetupData = [];
        foreach ($roomIds as $roomId) {
            $existingRoomSetup = $existingRoomSetups->get($roomId);

            if($existingRoomSetup){

                if($existingRoomSetup->trashed()){
                    $existingRoomSetup->restore();
                }
            }else{
                $roomSetupData[] = [
                    'equipment_id' => $equipmentId,
                    'room_id' => $roomId,
                    'quantity' => 1,
                ];
            }
        }

        if(!empty($roomSetupData)){
            $this->insert($roomSetupData);
        }

        $usedQuantities = RoomSetup::where('equipment_id', $equipmentId)
            ->whereNull('deleted_at')
            ->sum('quantity');

        return [
            'used_quantities' => $usedQuantities,
        ];
    }

}
