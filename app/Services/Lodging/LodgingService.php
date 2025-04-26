<?php

namespace App\Services\Lodging;

use App\Models\Contract;
use App\Models\Lodging;
use App\Models\Room;
use App\Models\User;
use App\Services\RentPayment\RentPaymentService;
use App\Services\RoomServiceInvoice\RoomServiceInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

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

    function hardDelete($lodgingId)
    {
        try {
            $lodging = Lodging::withTrashed()->find($lodgingId);
            if (!$lodging) {
                throw new \Exception("Lodging not found");
            }

            $lodging->forceDelete();
            $scope = Redis::zscore('dashboard', 'current_lodgings');
            if($scope != null){
                $scope -= 1;
                Redis::zadd('dashboard', $scope, 'current_lodgings');
            }
            return true;
        } catch (\Exception $exception) {
            return ["errors" => [["message" => $exception->getMessage()]]];
        }
    }

    function restore($lodgingId)
    {
        try {
            $lodging = Lodging::withTrashed()->find($lodgingId);

            if (!$lodging) {
                throw new \Exception("Lodging not found");
            }

            $lodging->restore();
            return true;
        } catch (\Exception $exception) {
            return ["errors" => [["message" => $exception->getMessage()]]];
        }
    }



    function detailLodging($lodgingId)
    {
        return Lodging::with(['province', 'district', 'ward', 'type', 'wallet'])->find($lodgingId);
    }

    function updateLodging($data, $isAdmin = false)
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
            'is_enabled',
            'phone_contact' => $isAdmin ? 'phone_contact' : 'phone',
            'email_contact' => $isAdmin ? 'email_contact' :'email',
        ];

        if($isAdmin){
            $fields[] = 'user_id';
        }

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
        $lodging = Lodging::on('pgsqlReplica')->with(['province','ward', 'district', 'wallet', 'type'])->where('user_id', $userId)->get();
        return $lodging;
    }

    function list($data)
    {
        $lodgings = Lodging::on('pgsqlReplica')->with(['province','ward', 'district', 'type', 'user']);
        if($data['is_trash']){
            $lodgings->onlyTrashed();
        }

        if(isset($data['filters'])){
            if(isset($data['filters']['name'])){
                $lodgings = $lodgings->where('name', 'ilike', '%'.$data['filters']['name'].'%');
            }

            if(isset($data['filters']['address'])){
                $lodgings = $lodgings->where('address', 'like', '%'.$data['filters']['address'].'%');
            }

            if(isset($data['filters']['type_id'])){
                $lodgings = $lodgings->where('type_id', $data['filters']['type_id']);
            }
        }

        $total = $lodgings->count();
        $lodgings = $lodgings->limit($data['limit'] ?? 10)->offset($data['offset'] ?? 0)->get();

        return [
            'total' => $total,
            'data' => $lodgings,
        ];
    }

    static function lodgingIdsOfUser($userId)
    {
        return Lodging::where('user_id', $userId)->pluck('lodging_id')->toArray();
    }

    function create($data, $userId)
    {
        $user = User::on("pgsqlReplica")->find($userId);

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
            'phone_contact' => $data['phone_contact'] ?? $data['phone'] ??  $user->phone ?? null,
            'email_contact' => $data['email_contact'] ?? $data['email'] ?? $user->email ?? null,
            'is_enabled' => $data['is_enabled'] ?? true,
        ];

        $lodging = Lodging::create($insertData);

        $scope = Redis::zscore('dashboard', 'current_lodgings');
        if($scope != null){
            $scope += 1;
            Redis::zadd('dashboard', $scope, 'current_lodgings');
        }
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

        $service = (new RoomServiceInvoiceService())->statisticalAmount($month, $year, $data['lodging_id']);
        $room = (new RentPaymentService())->statisticalAmount($month, $year, $data['lodging_id']);

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
        return Lodging::on('pgsqlReplica')->where(['id' => $lodgingId, 'user_id' => $userId])->exists();
    }
}
