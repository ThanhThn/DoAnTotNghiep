<?php

namespace App\Services\LodgingService;

use App\Models\Room;

class MonthlyService extends BaseServiceCalculator
{
    public function calculateCost()
    {
        $rooms = Room::where('lodging_id', $this->lodgingService->lodging_id)
            ->whereHas('contracts', function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            })
            ->with(['contracts' => function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            }])
            ->get();

        foreach ($rooms as $room) {
            $totalPrice = $this->lodgingService->price_per_unit;
            $this->processRoomUsage($room, $totalPrice, 1);
        }
    }
}
