<?php

namespace App\Services\Contract;

use App\Models\Contract;
use App\Models\RentalHistory;
use App\Models\Room;
use App\Models\User;
use App\Services\RentalHistory\RentalHistoryService;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContractService
{
    public function createContract($data)
    {
        try {
            DB::beginTransaction();
            $user = User::where('phone', $data['phone'])->first();
            if (!$user) {
                $userService = new UserService();
                $user = $userService->create([
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'password' => Hash::make(""),
                    'identity_card' => $data['identity_card'],
                    'date_of_birth' => $data['date_of_birth'],
                    'gender' => $data['gender'],
                    'relatives' => $data['relatives'] ?? null,
                    'is_completed' => true
                ]);
            }
            //Số lượng người ở trên hợp đồng này
            $quantity = $data['quantity'] ?? 1;

            //
            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();

            $insertData = [
                'user_id' => (string)$user->id,
                'room_id' => $data['room_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'monthly_rent' => $data['monthly_rent'] ?? null,
                'deposit_amount' => $data['deposit_amount'],
                'remain_amount' => $data['deposit_amount'],
                'status' => $data['status'] ?? config('constant.contract.status.pending'),
                'lease_duration' => $data['lease_duration'],
                'quantity' => $quantity,
                'full_name' => $data['full_name'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'identity_card' => $data['identity_card'],
                'date_of_birth' => $data['date_of_birth'],
                'relatives' => $data['relatives'] ?? null,
                'address' => $data['address'],
            ];
//            dd($insertData);
            $contract = Contract::create($insertData);
            $room = Room::find($data['room_id']);
            if ($startDate->toDateString() == Carbon::now()->toDateString()) {
                $newTenantCount = $room->current_tenants + $data['quantity'];
                if ($newTenantCount > $room->max_tenants) {
                    throw new \Exception("Số lượng người thuê vượt quá sức chứa của phòng");
                }
                $room->status = $newTenantCount == $room->max_tenants ? config('constant.room.status.filled') : $room->status;
                $room->current_tenants = $newTenantCount;
                $room->save();
            }


            DB::commit();
            return $contract;
        }catch (\Exception $exception) {
            DB::rollback();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    public function listContract($data){

        $contracts = Contract::select([
            'id', 'user_id', 'room_id', 'start_date', 'end_date',
            'monthly_rent', 'status', 'lease_duration', 'full_name', 'code'
        ])
            ->when(isset($data['room_id']),
                fn($query) => $query->where('room_id', $data['room_id']),
                function ($query) use ($data) {
                    $roomIds = Room::where("lodging_id", $data['lodging_id'])->pluck("id")->toArray();
                    return $query->whereIn('room_id', $roomIds);
                }
            )
            ->with("room")
            ->when(isset($data['status']), fn($query) => $query->where('status', $data['status']))
            ->withCount(['rentalHistories as due_months' => function ($query) {
                $query->whereColumn('amount_paid', '<', 'payment_amount');
            }])
            ->withSum(['rentalHistories as total_due' => function ($query) {
                $query->whereColumn('amount_paid', '<', 'payment_amount');
            }], DB::raw('payment_amount - amount_paid'));

        $total = (clone $contracts)->count();

        $contracts = $contracts->offset($data['offset'] ?? 0)
            ->limit($data['limit'] ?? 20)
            ->get();

        return [
            'total' => $total,
            'data' => $contracts,
        ];
    }

    public function calculateContract($contract, $amountNeedPayment, $lateDays)
    {
        if($amountNeedPayment == 0) return;

        $difference = $contract->remain_amount - $amountNeedPayment;
        try{
            DB::beginTransaction();
            $contract->remain_amount = max(0, $difference);
            $contract->save();

            if ($difference < 0) {
                $status = ($difference == -$amountNeedPayment)
                    ? config('constant.payment.status.unpaid')
                    : config('constant.payment.status.partial');
            } else {
                $status = config('constant.payment.status.paid');
            }


            $amountPaid = match ($status) {
                config('constant.payment.status.unpaid')  => 0,
                config('constant.payment.status.partial') => $difference + $amountNeedPayment,
                $status = config('constant.payment.status.paid') => $amountNeedPayment,
            };

            $now = Carbon::now();
            $dataHistory = [
                'contract_id' => $contract->id,
                'payment_amount' => $amountNeedPayment,
                'amount_paid' => $amountPaid,
                'status' => $status,
                'payment_method' =>  $status == config('constant.payment.status.paid') ? config('constant.payment.method.system') : null,
                'payment_date' => $now,
                'last_payment_date' => $now,
                'due_date' => $now->addDays($lateDays),
            ];

            $service = new RentalHistoryService();
            $result = $service->createRentalHistory($dataHistory);

            if (!empty($result['errors'])) {
                throw new \Exception($result['errors'][0]['message']);
            }

            DB::commit();
            return $contract;
        }catch (\Exception $exception) {
            DB::rollback();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }
}
