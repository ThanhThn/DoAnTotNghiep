<?php

namespace App\Services\Room;

use App\Models\Lodging;
use App\Models\LodgingService as Model;
use App\Models\Room;
use App\Models\RoomService as ModelsRoomService;
use App\Services\RoomService\RoomServiceManagerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                // Chuẩn bị dữ liệu để insert hàng loạt
                $roomServiceData = collect($data['services'])->map(function ($service) use ($newRoom) {
                    return [
                        'id' => Str::uuid(),
                        'room_id' => $newRoom->id,
                        'lodging_service_id' => $service['id'],
                        'last_recorded_value' => $service['value'] ?? 0
                    ];
                })->toArray();

                (new RoomServiceManagerService())->insert($roomServiceData);
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

        return $roomQuery->orderBy("created_at", 'asc')->get();
    }

    /**
     * Lọc danh sách các phòng đủ điều kiện để tạo hợp đồng mới tại một khu trọ cụ thể.
     *
     * @param array $data Dữ liệu đầu vào bao gồm:
     *   - start_date (string|Carbon|null): Ngày bắt đầu thuê (mặc định là ngày hiện tại nếu không truyền).
     *   - lease_duration (int): Thời hạn thuê (tính theo tháng, mặc định là 1 tháng).
     *   - quantity (int): Số lượng người dự kiến ở (mặc định là 1 người).
     *   - status (string|null): Trạng thái phòng cần lọc (tùy chọn).
     * @param int $lodgingId ID của khu trọ cần kiểm tra phòng.
     *
     * @return \Illuminate\Support\Collection Danh sách các phòng thỏa điều kiện, đã được lọc và sắp xếp.
     *
     * ✅ Điều kiện lọc chính:
     * - Phòng thuộc khu trọ (`lodging_id`) và đang được bật (`is_enabled = true`).
     * - Không có hợp đồng (đang chờ duyệt hoặc đang hoạt động) bị **giao thoa thời gian** với khoảng thời gian thuê mới.
     * - Tổng số người ở hiện tại + số người dự kiến không vượt quá `max_tenants` của phòng.
     *
     * 📌 Lưu ý:
     * - Hợp đồng được coi là giao thoa nếu:
     *     - `start_date` hoặc `end_date` nằm trong khoảng thời gian thuê mới, hoặc
     *     - hợp đồng bao trùm toàn bộ khoảng thuê mới.
     * - Sau khi lọc, các phòng thỏa điều kiện sẽ được **loại bỏ thông tin hợp đồng** để nhẹ dữ liệu trả về.
     */
    public function filterRooms($data, $lodgingId)
    {
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $leaseDuration = $data['lease_duration'] ?? 1;
        $endDate = $startDate->copy()->addMonths($leaseDuration);
        $quantity = $data['quantity'] ?? 1;

        $roomQuery = Room::where([
            'lodging_id' => $lodgingId,
            'is_enabled' => true
        ])
            ->with('contracts', function ($query) use ($startDate, $endDate, $quantity) {
                $query->whereIn('status', [
                    config('constant.contract.status.pending'),
                    config('constant.contract.status.active')
                ])
                    ->where(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereRaw('
                        (COALESCE(end_date, start_date + (lease_duration || \' months\')::INTERVAL) BETWEEN ? AND ?)
                        OR (start_date < ? AND COALESCE(end_date, start_date + (lease_duration || \' months\')::INTERVAL) > ?)
                    ', [$startDate, $endDate, $startDate, $endDate]);
                    });
            });

        if (isset($data['status'])) {
            $roomQuery->where('status', $data['status']);
        }

        $rooms =  $roomQuery->orderBy("created_at", 'asc')->get();

        $rooms = $rooms->filter(function ($room) use ($quantity) {
            $totalQuantity = $room->contracts->sum('quantity') + $quantity;
            return $totalQuantity <= $room->max_tenants;
        })->map(function ($room) {
            unset($room->contracts);
            return $room;
        })->values();
        return $rooms;
    }

    public function detail($id)
    {
        $room = Room::with('roomServices')->find($id);
        return $room;
    }

    public function update($data, $id)
    {
        $room = Room::where(['id' => $id, 'lodging_id' => $data['lodging_id']])->first();
        if (!$room) {
            return [
                'errors' => [['message' => 'Room not found']]
            ];
        }

        try {
            DB::beginTransaction();

            // Cập nhật thông tin phòng
            $roomData = [
                'room_code' => $data['room_code'],
                'max_tenants' => $data['max_tenants'],
                'current_tenants' => $data['current_tenants'] ?? $room->current_tenants,
                'price' => $data['price'] ?? $room->price,
                'area' => $data['area'] ?? $room->area,
                'status' => $data['status'] ?? $room->status,
                'priority' => $data['priority'] ?? $room->priority,
                'payment_date' => $data['payment_date'] ?? $room->payment_date,
                'late_days' => $data['late_days'] ?? $room->late_days,
            ];

            $room->update($roomData);


            if(isset($data['services'])){
                $serviceIds = collect($data['services'])->pluck('id')->toArray();

                ModelsRoomService::where('room_id', $id)
                    ->whereIn('lodging_service_id', $serviceIds)
                    ->update(['is_enabled' => true]);

                ModelsRoomService::where('room_id', $id)
                    ->whereNotIn('lodging_service_id', $serviceIds)
                    ->update(['is_enabled' => false]);


                $existingServices = ModelsRoomService::where('room_id', $id)
                    ->whereIn('lodging_service_id', $serviceIds)
                    ->get()
                    ->keyBy('lodging_service_id');

                $newServices = [];
                foreach ($data['services'] as $service) {
                    if (isset($existingServices[$service['id']])) {
                        $existingServices[$service['id']]->update([
                            'last_recorded_value' => $service['value'] ?? 0
                        ]);
                    } else {
                        $newServices[] = [
                            'id' => Str::uuid(),
                            'room_id' => $id,
                            'lodging_service_id' => $service['id'],
                            'last_recorded_value' => $service['value'] ?? 0,
                        ];
                    }
                }

                if (!empty($newServices)) {
                    (new RoomServiceManagerService())->insert($newServices);
                }
            }

            DB::commit();
            return $room->refresh();
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'errors' => [['message' => $exception->getMessage()]]
            ];
        }
    }

    public function softDelete($id)
    {
        try {
            $model = Room::findOrFail($id);
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


    static function isOwnerRoom($roomId, $userId)
    {
        return Room::whereHas('lodging', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('id', $roomId)->exists();
    }

}
