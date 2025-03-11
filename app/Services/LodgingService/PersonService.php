<?php

namespace App\Services\LodgingService;

use App\Models\Room;
use Illuminate\Support\Facades\Log;

class PersonService extends BaseServiceCalculator
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
            if ($room->current_tenants == 0) {
                continue;
            }

            $totalPrice = $this->lodgingService->price_per_unit * $room->current_tenants;
            $this->processRoomUsage($room, $totalPrice, $room->current_tenants);
        }
    }
}
