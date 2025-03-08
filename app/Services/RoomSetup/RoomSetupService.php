<?php

namespace App\Services\RoomSetup;

use App\Models\RoomSetup;

class RoomSetupService
{
    public function insert(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $dataInsert = array_map(fn($item) => [
            ...$item,
            'status' => 1,
            'installation_date' => now(),
        ], $data);

        RoomSetup::insert($dataInsert);

        return true;
    }

}
