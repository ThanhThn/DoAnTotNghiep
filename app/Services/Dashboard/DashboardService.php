<?php

namespace App\Services\Dashboard;

use App\Models\Lodging;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class DashboardService
{
    public function overview($data){
        return match ($data['section']){
            'total' => $this->getTotal(),
            'latest_users' => $this->getUsersLatest($data['quantity'] ?? 5)
        };
    }

    public function getTotal()
    {
        $total = Redis::zrange('dashboard', 0, -1, ['withscores' => true]);

        $initialTotalUser = $total['initial_users'] ?? User::count();
        $currentTotalUser = $total['current_users'] ?? $initialTotalUser;

        $initialTotalLodging = $total['initial_lodgings'] ?? Lodging::count();
        $currentTotalLodging = $total['current_lodgings'] ?? $initialTotalLodging;

        $result = [
            'initial_users' => $initialTotalUser,
            'current_users' => $currentTotalUser,
            'initial_lodgings' => $initialTotalLodging,
            'current_lodgings' => $currentTotalLodging,
        ];

        Redis::zadd('dashboard', $result);

        return $result;
    }

    public function updateTotal()
    {
        $total = Redis::zrange('dashboard', 0, -1, ['withscores' => true]);

        $initialTotalUser = $total['current_users'] ?? User::count();
        $currentTotalUser = $initialTotalUser;

        $initialTotalLodging = $total['current_lodgings'] ?? Lodging::count();
        $currentTotalLodging = $initialTotalLodging;

        $result = [
            'initial_users' => $initialTotalUser,
            'current_users' => $currentTotalUser,
            'initial_lodgings' => $initialTotalLodging,
            'current_lodgings' => $currentTotalLodging,
        ];

        Redis::zadd('dashboard', $result);

        return $result;
    }

    public function getUsersLatest(int $quantity = 5)
    {
        $users = User::select('id', 'full_name', 'created_at')->orderBy('created_at', 'desc')->take($quantity)->get();

        return $users;
    }
}
