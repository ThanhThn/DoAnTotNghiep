<?php

namespace App\Services\Client;

use App\Models\Lodging;

class ClientService
{

    public function listLodgingAndRoomToContractByUser($data, $userId)
    {
        $includeContracts = isset($data['with_contracts']) && $data['with_contracts'];

        // Khai báo query cơ bản
        $query = Lodging::on('pgsqlReplica')->with(['province', 'district', 'ward', 'type']);

        if(isset($data['search'])){
            $query->where('name', 'ilike', '%'.$data['search'].'%');
        }

        $query->whereHas('rooms', function ($query) use ($userId) {
            $query->whereHas('contracts', function ($query) use ($userId) {
                $query->where([
                    'user_id' => $userId,
                    'status' => config('constant.contract.status.active'),
                ]);
            });
        });

        $relations = [
            'rooms' => function ($query) use ($userId, $includeContracts) {
                $query->whereHas('contracts', function ($query) use ($userId) {
                    $query->where([
                        'user_id' => $userId,
                    ])->whereIn('status', [config('constant.contract.status.active'), config('constant.contract.status.overdue')]);
                });

                if ($includeContracts) {
                    $query->with(['contracts' => function ($query) use ($userId) {
                        $query->where([
                            'user_id' => $userId,
                        ])->whereIn('status', [config('constant.contract.status.active'), config('constant.contract.status.overdue')]);;
                    }]);
                }
            },
        ];

        return $query->with($relations)->get();
    }
}
