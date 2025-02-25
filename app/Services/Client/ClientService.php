<?php

namespace App\Services\Client;

use App\Models\Lodging;

class ClientService
{

    public function listLodgingAndRoomToContractByUser($userId)
    {
        $result = Lodging::with(['rooms' => function($query) use ($userId) {
            $query->whereHas('contracts', function($subQuery) use ($userId) {
                $subQuery->where(['user_id' => $userId, 'status' => config('constant.contract.status.active')]);
            });
        }])->get();
        return $result;
    }
}
