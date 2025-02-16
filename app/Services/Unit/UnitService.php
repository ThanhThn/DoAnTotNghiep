<?php

namespace App\Services\Unit;

use App\Models\ServiceUnit;
use App\Models\Unit;

class UnitService
{
    public function listAll()
    {
        return Unit::all();
    }

    public function listByService($serviceId)
    {
        $units = ServiceUnit::with('unit')->where('service_id', $serviceId)->get()->pluck('unit')->toArray();
        return $units;
    }
}
