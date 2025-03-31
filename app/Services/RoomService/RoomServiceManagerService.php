<?php

namespace App\Services\RoomService;

use App\Models\LodgingService;
use App\Models\Room;
use App\Models\RoomService;
use App\Services\Contract\ContractService;
use App\Services\LodgingService\LodgingServiceManagerService;
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

    public function detailByRoomAndService($roomId, $lodgingServiceId)
    {
        return RoomService::where([
            'room_id' => $roomId,
            'lodging_service_id' => $lodgingServiceId
        ])->first();
    }

    public function updateAndCreateByLodgingService(array $roomIds, $lodgingServiceId)
    {
        // Lấy danh sách các RoomService đã tồn tại trong CSDL
        $roomServiceExisted = RoomService::where('lodging_service_id', $lodgingServiceId)->get();

        $existedRoomIds = $roomServiceExisted->pluck('room_id')->toArray();

        RoomService::where('lodging_service_id', $lodgingServiceId)
            ->whereNotIn('room_id', $roomIds)
            ->update(['is_enabled' => false]);

        RoomService::where('lodging_service_id', $lodgingServiceId)
            ->whereIn('room_id', $roomIds)
            ->update(['is_enabled' => true]);

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

    public function createBillForContract($contractId, $roomId, array $lodgingServices, $usageAmount, $extractDate = [])
    {
        $contract = (new ContractService())->detail($contractId);
        try {
            $roomService = RoomService::where('room_id', $roomId)->with('lodgingService')->get();
            $lodgingService = new LodgingServiceManagerService();

            $usageAmount = max(0, $usageAmount);
            foreach ($roomService as $room) {
                $service = $lodgingService->getServiceCalculator($room->lodging_service);

                $filtered = array_filter($lodgingServices, function ($item) use ($room) {
                    return $item['id'] == $room->lodging_service_id;
                });

                $value = !empty($filtered) ? current($filtered)['value'] : 0;

                $paymentMethod = $usageAmount > 0 ? config('constant.payment.method.system') : null;
                $result = $service->processRoomUsageForContract($contract->room, $contract, $usageAmount,$paymentMethod, $value, $extractDate);

                if(isset($result['errors'])){
                    throw new \Exception($result['errors']['0']['message']);
                }

                $usageAmount = max(0, $result);
            }

            return $usageAmount;
        }catch (\Exception $exception){
            return ["errors" => [[
                'message' => $exception->getMessage(),
            ]]];
        }
    }
}
