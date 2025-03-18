<?php

namespace App\Services\Lodging;

use App\Models\Contract;
use App\Models\Lodging;
use App\Models\Room;
use App\Models\User;
use App\Services\RentalHistory\RentalHistoryService;
use App\Services\RoomUsageService\RoomUsageService;
use Carbon\Carbon;

class LodgingService
{

    function softDelete($lodgingId)
    {
        try{
            Lodging::find($lodgingId)->delete();
            return true;
        }catch (\Exception $exception){
            return false;
        }

    }

    function detailLodging($lodgingId)
    {
        return Lodging::with(['province', 'district', 'ward', 'type'])->find($lodgingId);
    }

    function updateLodging($data)
    {
        $lodging = $this->detailLodging($data['id']);

        $fields = [
            'name',
            'address',
            'province_id',
            'district_id',
            'ward_id',
            'latitude',
            'longitude',
            'type_id',
            'payment_date',
            'late_days',
            'area_room_default',
            'price_room_default',
            'phone_contact' => 'phone',
            'email_contact' => 'email',
        ];

        $updateData = [];

        foreach ($fields as $dbField => $inputField) {

            if (is_string($dbField)) {
                $updateData[$dbField] = $data[$inputField] ?? $lodging->$dbField;
            } else {
                $updateData[$inputField] = $data[$inputField] ?? $lodging->$inputField;
            }
        }
        $lodging->update($updateData);
        return $this->detailLodging($lodging->id);
    }

    function listByUserID($userId)
    {
        $lodging = Lodging::with(['province','ward', 'district'])->where('user_id', $userId)
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

    function overview($data)
    {
        return match ($data['section']) {
            'statistical' => $this->statistical($data),
            default => $this->overviewRoom($data),
        };
    }


    public function statistical($data)
    {

        $month = $data['month'] ?? Carbon::now()->month;
        $year = $data['year'] ?? Carbon::now()->year;

        $service = (new RoomUsageService())->statisticalAmount($month, $year, $data['lodging_id']);
        $room = (new RentalHistoryService())->statisticalAmount($month, $year, $data['lodging_id']);

        return [
            'service' => $service,
            'room' => $room
        ];
    }

    public function overviewRoom($data)
    {

        $rooms = Room::where('lodging_id', $data['lodging_id'])->get();
        $roomIds = $rooms->pluck('id')->toArray();

        $roomRentingQuery = Room::where('lodging_id', $data['lodging_id'])
            ->whereHas('contracts', function ($query) {
                $query->where('status', config('constant.contract.status.active'));
            });

        $roomRenting = $roomRentingQuery->count();

        $unpaidRooms = Room::where('lodging_id', $data['lodging_id'])
            ->whereHas('contracts', function ($query) {
                $query->where('status', 2)
                    ->whereHas('rentalHistories', function ($q) {
                        $q->select('contract_id')
                            ->groupBy('contract_id')
                            ->havingRaw('SUM(amount_paid) < SUM(payment_amount)');
                    });
            })
            ->count();

        return [
            'total' => count($roomIds),
            'unpaid' => $unpaidRooms,
            'renting' => $roomRenting,
            'empty' => count($roomIds) - $roomRenting,
        ];
    }

    static function isOwnerLodging($lodgingId, $userId){
        return Lodging::where(['id' => $lodgingId, 'user_id' => $userId])->exists();
    }
}
