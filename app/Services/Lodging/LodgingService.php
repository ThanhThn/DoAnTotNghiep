<?php

namespace App\Services\Lodging;

use App\Models\Lodging;
use App\Models\User;

class LodgingService
{

    function get($lodgingId)
    {
        return Lodging::find($lodgingId);
    }
    function listByUserID($userId)
    {
        $lodging = Lodging::where('user_id', $userId)
            ->where('is_enabled', true)->get();
        return $lodging;
    }

    function create($data, $userId)
    {
        $user = User::find($userId);

        if(!$user) {
            return [
              'errors' => [
                  'message' => 'User not found'
              ],
            ];
        }

        $insertData = [
            'user_id' => $user->id,
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'province_id' => $data['province_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'ward_id' => $data['ward_id'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'type_id' => $data['type_id'] ?? null,
            'payment_date' => $data['payment_date'] ?? null,
            'late_days' => $data['late_days'] ?? null,
            'area_room_default' => $data['area_room_default'] ?? null,
            'price_room_default' => $data['price_room_default'] ?? null,
            'phone_contact' => $data['phone'] ?? ($user->phone ?? null),
            'email_contact' => $data['email'] ?? ($user->email ?? null),
        ];

        $lodging = Lodging::create($insertData);
//        dd($lodging);
        return $lodging;
    }

    static function isOwnerLodging($lodgingId, $userId){
        return Lodging::where(['id' => $lodgingId, 'user_id' => $userId])->exists();
    }
}
