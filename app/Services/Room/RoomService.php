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
                // Lấy tất cả dịch vụ theo ID, tránh query từng dòng
                $serviceIds = collect($data['services'])->pluck('id')->toArray();
                $services = ModelLodgingService::whereIn('id', $serviceIds)->with('unit')->get()->keyBy('id');

                // Chuẩn bị dữ liệu để insert hàng loạt
                $roomServiceData = collect($data['services'])->map(function ($service) use ($newRoom, $services) {
                    $managerService = $services[$service['id']] ?? null;
                    return [
                        'room_id' => $newRoom->id,
                        'lodging_service_id' => $service['id'],
                        'last_recorded_value' => $service['value'] ?? ($managerService && $managerService->unit->is_fixed ? null : 0)
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
        $roomQuery = Room::where([
            'lodging_id' => $lodgingId,
            'is_enabled' => true
        ]);

        if (isset($data['status'])) {
            $roomQuery->where('status', $data['status']);
        }

        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $leaseDuration = $data['lease_duration'] ?? 1;
        $endDate = $startDate->copy()->addMonths($leaseDuration);

        // Loại bỏ các phòng có hợp đồng bị chồng lấn
        $roomQuery->whereDoesntHave('contracts', function ($query) use ($startDate, $endDate) {
            $query->where(function ($subQuery) use ($startDate, $endDate) {
                $subQuery->whereNotNull('end_date')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q2) use ($startDate, $endDate) {
                                $q2->where('start_date', '<', $startDate)
                                    ->where('end_date', '>', $endDate);
                            });
                    })
                    ->where('status', config('constant.contract.status.active'));
            })
                ->orWhere(function ($subQuery) use ($startDate) {
                    $subQuery->whereNull('end_date')
                        ->whereRaw('start_date + INTERVAL \'1 month\' * lease_duration >= ?', [$startDate])
                        ->where('status', config('constant.contract.status.active'));
                })
                ->orWhere(function ($subQuery) use ($startDate) {
                    $subQuery->where('start_date', '<=', $startDate)
                        ->where('status', config('constant.contract.status.pending'));
                });
        });

        $quantity = $data['quantity'] ?? 1;
//        $roomQuery->whereDoesntHave(['contracts' => function ($query) use ($quantity) {
//            $contracts = $query->whereIn('contracts.status', [config('constant.contract.status.active'), config('constant.contract.status.pending')])->sum('quantity');
//            return $contracts + $quantity <= $query->max_tennant;
//        }]);

        return $roomQuery->get();
    }

    static function isOwnerRoom($roomId, $userId)
    {
        $room = Room::find($roomId);
        if(!$room) return false;
        return LodgingService::isOwnerLodging($room->lodging_id, $userId);
    }


}
