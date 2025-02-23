<?php

namespace App\Services\Room;

use App\Models\Lodging;
use App\Models\Room;
use App\Services\Lodging\LodgingService;
use App\Models\LodgingService as ModelLodgingService;
use App\Services\LodgingService\LodgingServiceManagerService;
use App\Services\RoomService\RoomServiceManagerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function createRoom($data)
    {
        try {
            DB::beginTransaction();

            $lodging = Lodging::find($data['lodging_id']);

            // Chuẩn bị dữ liệu phòng
            $roomData = [
                'lodging_id' => $data['lodging_id'],
                'room_code' => $data['room_code'],
                'max_tenants' => $data['max_tenants'],
                'price' => $data['price'] ?? $lodging->price_room_default,
                'area' => $data['area'] ?? $lodging->area_room_default,
                'status' => $data['status'] ?? config('constant.room.status.unfilled'),
                'priority' => $data['priority'] ?? null,
                'payment_date' => $data['payment_date'] ?? $lodging->payment_date,
                'late_days' => $data['late_days'] ?? $lodging->late_days,
            ];

            // Tạo phòng mới
            $newRoom = Room::create($roomData);

            if (!empty($data['services'])) {

                $serviceIds = collect($data['services'])->pluck('id')->toArray();
                $services = ModelLodgingService::whereIn('id', $serviceIds)->with('unit')->get()->keyBy('id');

                // Chuẩn bị dữ liệu để insert hàng loạt
                $roomServiceData = collect($data['services'])->map(function ($service) use ($newRoom, $services) {
                    $managerService = $services[$service['id']] ?? null;
                    return [
                        'room_id' => $newRoom->id,
                        'lodging_service_id' => $service['id'],
                        'last_recorded_value' => $service['value'] ?? 0
                    ];
                })->toArray();

                // Bulk insert dữ liệu
                (new RoomServiceManagerService())::insert($roomServiceData);
            }

            DB::commit();
            return $newRoom;
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    public function listRoomsByLodging($lodgingId, $data = [])
    {
        $roomQuery = Room::where(['lodging_id'=> $lodgingId, 'is_enabled' => true]);

        // Lọc theo trạng thái phòng nếu có
        if (isset($data['status'])) {
            $roomQuery->where('status', $data['status']);
        }

        return $roomQuery->get();
    }

    public function filterRooms($data, $lodgingId)
    {
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $leaseDuration = $data['lease_duration'] ?? 1;
        $endDate = $startDate->copy()->addMonths($leaseDuration);
        $quantity = $data['quantity'] ?? 1;

        $roomQuery = Room::where([
            'lodging_id' => $lodgingId,
            'is_enabled' => true
        ])->whereDoesntHave('contracts', function ($query) use ($startDate, $endDate, $quantity) {
            $query->select('room_id')
                ->selectRaw('COALESCE(SUM(quantity), 0) as total_tenants')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<', $startDate)
                                ->where('end_date', '>', $endDate);
                        });
                })
                ->groupBy('room_id')
                ->havingRaw('(COALESCE(SUM(quantity), 0) + ?) > max_tenants', [$quantity]);
        });

        if (isset($data['status'])) {
            $roomQuery->where('status', $data['status']);
        }

        return $roomQuery->get();
    }

    public function detail($id)
    {
        $room = Room::with('roomServices')->find($id);
//        $service = ModelLodgingService::where('lodging_id', $room->lodging_id)->get();
//
//        $room->setRelation('services', $service);
        return $room;
    }

    static function isOwnerRoom($roomId, $userId)
    {
        return Room::whereHas('lodging', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('id', $roomId)->exists();
    }

}
