<?php

namespace App\Services\Contract;

use App\Helpers\Helper;
use App\Models\Contract;
use App\Models\RentalHistory;
use App\Models\Room;
use App\Models\User;
use App\Services\LodgingService\LodgingServiceManagerService;
use App\Services\Payment\ServicePaymentFactory;
use App\Services\RentalHistory\RentalHistoryService;
use App\Services\Room\RoomService;
use App\Services\RoomService\RoomServiceManagerService;
use App\Services\ServicePayment\ServicePaymentService;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ContractService
{
    public function createContract($data)
    {
        try {
            DB::beginTransaction();
            $user = null;
            if($data['status'] == config('constant.contract.status.active')){
                $user = User::firstOrCreate(
                    ['phone' => $data['phone']],
                    [
                        'full_name' => $data['full_name'],
                        'address' => $data['address'],
                        'password' => Hash::make(""),
                        'identity_card' => $data['identity_card'],
                        'date_of_birth' => $data['date_of_birth'],
                        'gender' => $data['gender'],
                        'relatives' => $data['relatives'] ?? null,
                        'is_completed' => true
                    ]
                );
            }


            //Số lượng người ở trên hợp đồng này
            $quantity = $data['quantity'] ?? 1;

            //
            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();


            $status = $data['status'] ?? config('constant.contract.status.pending');
            $insertData = [
                'user_id' => $user ? (string)$user->id : null ,
                'room_id' => $data['room_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'monthly_rent' => $data['monthly_rent'] ?? null,
                'deposit_amount' => $data['deposit_amount'],
                'remain_amount' => 0,
                'status' => $status,
                'lease_duration' => $data['lease_duration'],
                'quantity' => $quantity,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
            ];

            if($status == config('constant.contract.status.active')){
                $insertData = array_merge($insertData, [
                    'gender' => $data['gender'],
                    'identity_card' => $data['identity_card'],
                    'date_of_birth' => $data['date_of_birth'],
                    'relatives' => $data['relatives'] ?? null,
                    'address' => $data['address'],
                ]);
            }

//            dd($insertData);
            $contract = Contract::create($insertData);
            $room = Room::find($data['room_id']);
            if ($data['status'] == config('constant.contract.status.active') && $startDate->toDateString() == Carbon::now()->toDateString()) {
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

        $contracts = Contract::on('pgsqlReplica')->select([
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

    public function detail($id, $connection = "pgsql", $hasLodging = false)
    {
        return Contract::on($connection)
            ->with(['room' => function ($query) use ($hasLodging) {
                if (!$hasLodging) {
                    $query->without('lodging');
                }
            }])->find($id);
    }

    public function calculateContract($contract, $amountNeedPayment, $lateDays)
    {
        if($amountNeedPayment == 0) return null;

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
                'payment_date' => $now->copy(),
                'last_payment_date' => $now->copy(),
                'due_date' => $now->copy()->addDays($lateDays),
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

    public function update($data)
    {
        $contract = $this->detail($data['contract_id']);

        if($contract->room->lodging_id != $data['lodging_id']){
            return [
                'errors' => [[
                    'message' => 'Hợp đồng không thuộc nhà cho thuê',
                ]]
            ];
        }

        try {
            DB::beginTransaction();
            $statusOld = $contract->status;
            $statusNew = $data['status'];

            $dataUpdate = [
                'status' => $statusNew,
            ];


            $quantity = $data['quantity'] ?? $contract->quantity ?? null;
            if ($statusOld == config('constant.contract.status.pending')) {
                if ($statusNew == config('constant.contract.status.active')) {
                    $dataUpdate += [
                        'start_date' => $data['start_date'] ?? null,
                        'lease_duration' => $data['lease_duration'] ?? null,
                        'remain_amount' => $data['remain_amount'] ?? null,
                        'deposit_amount' => $data['deposit_amount'] ?? null,
                        'full_name' => $data['full_name'] ?? $contract->full_name ?? null,
//                        'monthly_rent' => $data['monthly_rent'] ?? $contract->monthly_rent ?? null,
                        'quantity' => $quantity,
                        'gender' => $data['gender'] ?? $contract->gender ?? null,
                        'address' => $data['address'] ?? $contract->address ?? null,
                        'identity_card' => $data['identity_card'] ?? $contract->identity_card ?? null,
                        'date_of_birth' => $data['date_of_birth'] ?? $contract->date_of_birth ?? null,
                    ];

                    // Kiểm tra các trường bị null
                    $missingFields = array_keys(array_filter($dataUpdate, fn($value) => is_null($value)));

                    if (!empty($missingFields)) {
                        throw new \Exception("Các trường sau không được để trống: " . implode(", ", $missingFields));
                    }

                    // Tạo User nếu chưa có
                    $user = User::firstOrCreate(
                        ['phone' => $contract->phone],
                        [
                            'full_name' => $data['full_name'] ?? $contract->full_name,
                            'address' => $data['address'],
                            'password' => Hash::make(""),
                            'identity_card' => $data['identity_card'],
                            'date_of_birth' => $data['date_of_birth'],
                            'gender' => $data['gender'],
                            'relatives' =>  $data['relatives'] ?? $contract->relatives ?? null,
                            'is_completed' => true
                        ]
                    );

                    $dataUpdate['user_id'] = $user->id;
                }
            }

            $contract->update($dataUpdate);

            if ($statusNew != $statusOld) {


                $delta = $quantity ?? 0;
                $currentTenants = $contract->room->current_tenants;

                if ($statusNew == config('constant.contract.status.active')) {
                    $currentTenants += $delta;
                } else {
                    $currentTenants -= $delta;
                }

                // Chỉ update trường cần thiết
                $roomService = new RoomService();
                $result = $roomService->update(array_merge($contract->room->toArray(), ['current_tenants' => $currentTenants]), $contract->room_id);

                if (!empty($result['errors'])) {
                    throw new \Exception($result['errors'][0]['message']);
                }
            }

            DB::commit();
            return $this->detail($contract->id);
        }catch (\Exception $exception) {
            DB::rollback();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    public function debtContract($id){
        $rentalPaymentService = new RentalHistoryService();
        $servicePaymentService = new ServicePaymentService();

        $roomDebt = $rentalPaymentService->sumDebtByContract($id);
        $serviceDebt = $servicePaymentService->sumDebtByContract($id);

        return [
            'room' => $roomDebt,
            'service' => $serviceDebt,
        ];
    }

    public function createFinalBillForContract($data)
    {
        try{
            DB::beginTransaction();
            // Lấy thông tin hợp đồng và phòng
            $contract = $this->detail($data['contract_id']);
            $room = $contract->room;
            $now = Carbon::now();

            // Số tiền cọc còn lại sau khi trừ khoản hoàn trả
            $usableAmount = $contract->deposit_amount - $data['deposit_amount_refund'];

            // Lấy lịch sử thanh toán cuối cùng
            $rentalHistoryService = new RentalHistoryService();
            $lastHistory = $rentalHistoryService->getLastHistory($data['contract_id']);
            $paymentDateLast = Carbon::parse($lastHistory->payment_date);

            // Tính toán thời gian thuê (tháng, ngày)
            $durationRoom = Helper::calculateDuration($paymentDateLast, $now, $paymentDateLast->isSameDay($now));

            // Tính số tiền thuê phòng
            if (!empty($data['is_monthly_billing'])) {
                $paymentAmountRoom = $room->price * $durationRoom['months'];

                // Nếu số ngày dương, tính thêm một tháng tiền thuê
                if ($durationRoom['days'] > 0) {
                    $paymentAmountRoom += $room->price;
                }
            } else {
                $dailyRate = $room->price / $now->daysInMonth;
                $paymentAmountRoom = ($room->price * $durationRoom['months']) + ($dailyRate * $durationRoom['days']);
            }

            // Tính số tiền thanh toán dựa trên số người thuê hiện tại
            $tenants = max($room->current_tenants, 1);
            $paymentAmount = ($paymentAmountRoom / $tenants) * $contract->quantity;

            // Xác định trạng thái thanh toán
            $paymentStatus = $usableAmount >= $paymentAmount
                ? config('constant.payment.status.paid')
                : ($usableAmount <= 0
                    ? config('constant.payment.status.unpaid')
                    : config('constant.payment.status.partial'));

            // Xác định phương thức thanh toán
            $paymentMethod = $usableAmount > 0 ? config('constant.payment.method.system') : null;


            // Tạo lịch sử thanh toán
            $rentalHistoryService->createRentalHistory([
                'contract_id' => $data['contract_id'],
                'payment_amount' => $paymentAmount,
                'amount_paid' => max(min($usableAmount, $paymentAmount), 0),
                'status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'due_date' => $now->clone()->addDays($room->late_days),
            ]);

            $usableAmount -= $paymentAmount;

            $roomService = new RoomServiceManagerService();

            $result = $roomService->createBillForContract($data['contract_id'], $contract->room_id, $data['services'], $usableAmount, [
                'is_monthly_billing' => $data['is_monthly_billing'] ?? null
            ]);

            if(isset($result['errors'])){
                throw new \Exception($result['errors'][0]['message']);
            }

            $contract->update([
                'remain_amount' => $contract->remain_amount + $data['deposit_amount_refund'] + max($result, 0),
                'has_been_billed' => true,
            ]);

            DB::commit();
            return  true;
        }catch (\Exception $exception){
            DB::rollBack();
            return ["errors" => [[
                'message' => $exception->getMessage(),
            ]]];
        }
    }

    public function paymentAmountRemainByContract($data, $contractId)
    {
        $contract = $this->detail($contractId);
        $debt = $this->debtContract($contractId);
        $totalDebt = $debt['room'] + $debt['service'];
        $remainAmount = $contract->remain_amount;
        if($remainAmount < $totalDebt && $data['type'] == "refund"){
            return ['errors' => [[
                'message' => 'Không thể hoàn tiền vì tiền cọc còn lại nhỏ hơn tổng số tiền còn nợ.'
            ]]];
        }

        $additionalAmount = $data['type'] === 'refund' ? 0 : $data['amount'];
        $usageAmount = $remainAmount + $additionalAmount;
        try {
            DB::beginTransaction();
            $paymentService = new ServicePaymentFactory();

            // Thanh toán tiền phòng
            $resultPaymentRoom = $paymentService->paymentByContract([
                'payment_type' => 'rent',
                'rent_payment_type' => 'full',
                'payment_method' => config("constant.payment.method.system"),
                'contract_id' => $contractId,
                'amount' => min($debt['room'], $usageAmount)
            ]);

            if(isset($resultPaymentRoom['errors'])){
                throw new \Exception($resultPaymentRoom['errors'][0]['message']);
            }

            $usageAmount = max($usageAmount - $debt['room'],0);

            //Thanh toán dịch vụ
            $resultPaymentService  = $paymentService->paymentByContract([
                'payment_type' => 'service',
                'service_payment_type' => 'full',
                'payment_method' => config("constant.payment.method.system"),
                'contract_id' => $contractId,
                'amount' => min($debt['service'], $usageAmount)
            ]);

            if(isset($resultPaymentService['errors'])){
                throw new \Exception($resultPaymentService['errors'][0]['message']);
            }


            if($data['type'] == "refund"){
                $contract->update([
                    'remain_amount' => 0,
                ]);
            }
            else{
                $contract->update([
                    'remain_amount' => max($usageAmount - $debt['service'],0)
                ]);
            }

            DB::commit();
            return true;
        }catch (\Exception $exception){
            DB::rollBack();
            return ["errors" => [[
                'message' => $exception->getMessage(),
            ]]];
        }

    }

    public function endContract($contractId, $data)
    {
        $contract = $this->detail($contractId);
        if (!isset($data['skip']['payment'])) {
            $debt = $this->debtContract($contractId);

            $stillOwesRoom = $debt['room'] != 0;
            $stillOwesService = $debt['service'] != 0;
            $hasRemainingDeposit = $contract['remain_amount'] != 0;

            if ($stillOwesRoom || $stillOwesService || $hasRemainingDeposit) {
                $messages = [];

                if ($stillOwesRoom) {
                    $messages[] = "Khách thuê còn nợ tiền phòng: " . number_format($debt['room']) . "đ.";
                }

                if ($stillOwesService) {
                    $messages[] = "Khách thuê còn nợ tiền dịch vụ: " . number_format($debt['service']) . "đ.";
                }

                if ($hasRemainingDeposit) {
                    $messages[] = "Tiền cọc chưa được quyết toán: " . number_format($contract['remain_amount']) . "đ.";
                }

                return [
                    'errors' => [$messages]
                ];
            }
    }

        if (!isset($data['skip']['bill']) && !$contract->has_been_billed) {
            return ['errors' => [['message' => 'Không thể kết thúc hợp đồng vì chưa xuất hóa đơn cuối cùng.']]];
        }

        $contract->update([
            'status' => config('constant.contract.status.finished')
        ]);

        return true;
    }

    static function isContractOwner($contract_id, $user_id)
    {
        return Contract::where([
            'id' => $contract_id,
            'user_id' => $user_id,
        ])->exists();
    }
}
