<?php

namespace App\Services\Lodging;

use App\Models\Lodging;

class LodgingService
{
    function listByUserID($userID)
    {
        $lodging = Lodging::where(['user_id' => $userID, 'is_enabled' => true])->get();
        return $lodging;
    }
}
