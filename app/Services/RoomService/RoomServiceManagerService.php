<?php

namespace App\Services\RoomService;

use App\Models\Room;
use App\Models\RoomService;

class RoomServiceManagerService
{
    public function create($data)
    {
        $createData = [
            'room_id' => $data['room_id'],
            'lodging_service_id' => $data['lodging_service_id'],
            'last_recorded_value' => $data['last_recorded_value'] ?? 0,
        ];
        try {
            return RoomService::create($createData);
        }catch (\Exception $exception){
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }
}
