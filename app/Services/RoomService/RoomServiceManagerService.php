<?php

namespace App\Services\RoomService;

use App\Models\LodgingService;
use App\Models\Room;
use App\Models\RoomService;
use Illuminate\Support\Str;

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

    public function insert($data)
    {
        return RoomService::insert($data);
    }

    public function updateAndCreateByLodgingService(array $roomIds, $lodgingServiceId)
    {
        // Lấy danh sách các RoomService đã tồn tại trong CSDL
        $roomServiceExisted = RoomService::where('lodging_service_id', $lodgingServiceId)->get();

        $existedRoomIds = $roomServiceExisted->pluck('room_id')->toArray();

        RoomService::where('lodging_service_id', $lodgingServiceId)
            ->whereNotIn('room_id', $roomIds)
            ->update(['is_enabled' => false]);

        // Lấy danh sách room_id cần thêm mới
        $roomIdNotUsage = array_diff($roomIds, $existedRoomIds);

        if (!empty($roomIdNotUsage)) {
            $dataInsert = collect($roomIdNotUsage)->map(function ($roomId) use ($lodgingServiceId) {
                return [
                    'id' => Str::uuid(),
                    'room_id' => $roomId,
                    'lodging_service_id' => $lodgingServiceId,
                    'last_recorded_value' => 0,
                ];
            })->toArray();

            self::insert($dataInsert);
        }

        return true;
    }
}
