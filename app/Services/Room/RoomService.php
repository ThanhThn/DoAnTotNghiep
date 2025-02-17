<?php

namespace App\Services\Room;

use App\Models\Lodging;
use App\Models\Room;
use App\Services\Lodging\LodgingService;
use App\Services\RoomService\RoomServiceManagerService;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function createRoom($data)
    {
        $lodging = (new LodgingService())->get($data['lodging_id']);

        $roomData = [
            'lodging_id' => $data['lodging_id'],
            'room_code' => $data['room_code'],
            'max_tenants' => $data['max_tenants'],
            'price' => $data['price'] ?? $lodging->price_room_default ?? null,
            'area' => $data['area'] ?? $lodging->area_room_default ?? null,
            'status' => $data['status'] ?? config('constant.room.status.unfilled'),
            'priority' => $data['priority'] ?? null,
        ];

        try {
            DB::beginTransaction();

            $newRoom = Room::create($roomData);

            if (isset($data['services'])) {
                $roomServiceManager = new RoomServiceManagerService();

                foreach ($data['services'] as $selectedService) {
                    $serviceCreationResult = $roomServiceManager->create([
                        'room_id' => $newRoom->id,
                        'lodging_service_id' => $selectedService['id'],
                        'last_recorded_value' => $selectedService['value'] ?? null
                    ]);

                    if (isset($serviceCreationResult['errors'])) {
                        throw new \Exception($serviceCreationResult['errors']);
                    }
                }
            }

            DB::commit();
            return $newRoom;
        } catch (\Exception $exception) {
            DB::rollback();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    public function listRoomsByLodging($lodgingId)
    {
        return Room::where('lodging_id', $lodgingId)->get();
    }

}
