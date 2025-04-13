<?php

namespace App\Services\Payment;

use App\Jobs\HandleTransactionPayment;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Contract\ContractService;
use App\Services\Notification\NotificationService;
use Google\Service\Translate\Translation;
use Illuminate\Support\Facades\DB;

abstract class PaymentServiceFactory
{
    public function paymentByContract($data)
    {
        [$servicePayment, $relatedId, $extraData] = match ($data['payment_type']) {
          'rent' => [new RoomPaymentFactory(), $data['rental_history_id'] ?? "", ["type" => $data["rent_payment_type"] ?? "debt"]],
          'service' => [new ServicePaymentFactory(), $data['service_payment_id'] ?? "", ["type" => $data["service_payment_type"] ?? "debt"]],
        };

        return $servicePayment->processPaymentByContract($data['contract_id'], $relatedId, $data['amount'], $data['payment_method'] , $extraData);
    }

    abstract function processPaymentByContract(string $contractId, ?string $relatedId, float $amount, string $paymentMethod, $extraData = []);

    public function paymentByUser($data, $userId)
    {
        try{
            DB::beginTransaction();
            $walletFrom = Wallet::where(['object_id' => $userId, 'object_type' => config('constant.object.type.user')])->firstOrFail();

            if($walletFrom->balance < $data['amount']) {
                return ['errors' => [
                    'message' => 'Số dư tài khoản không đủ'
                ]];
            }

            $dataPayment = [
                'amount' => $data['amount'],
                'payment_type' => $data['object_type'],
                'payment_method' => config('constant.payment.method.transfer'),
                'contract_id' => $data['contract_id'],
            ];

            match ($data['object_type']) {
                'rent' => $dataPayment['rental_history_id']  = $data['object_id'],
                'service' => $dataPayment['service_payment_id']  = $data['object_id'],
            };
            $payment = $this->paymentByContract($dataPayment);

            if(isset($payment['errors'])){
                throw new \Exception($payment['errors'][0]['message']);
            }

            $contract = (new ContractService())->detail($data['contract_id'], 'pgsqlReplica');

            $msgFrom = match ($data['object_type']) {
                'rent' => "Thanh toán tiền thuê của hợp đồng #{$contract->code}",
                'service' => "Thanh toán tiền dịch vụ của hợp đồng #{$contract->code}",
            };
            // Tạo giao dịch trên ví người dùng
            Transaction::create([
                'wallet_id' => $walletFrom->id,
                'transaction_type' => config("constant.transaction.type.payment"),
                'amount' => $data['amount'],
                'balance_before' => $walletFrom->balance,
                'balance_after' => $walletFrom->balance - $data['amount'],
                'description' => $msgFrom,
            ]);

            $walletFrom->balance -= $data['amount'];
            $walletFrom->save();

            $walletTo = Wallet::where(['object_id' => $contract->room->lodging_id, 'object_type' => config('constant.object.type.lodging')])->firstOrFail();

            $msgTo = match ($data['object_type']) {
                'rent' => "Nhận tiền thuê của hợp đồng #{$contract->code}",
                'service' => "Nhận tiền dịch vụ của hợp đồng #{$contract->code}",
            };

            // Tạo giao dịch trên ví nhà trọ
            Transaction::create([
                'wallet_id' => $walletTo->id,
                'transaction_type' => config("constant.transaction.type.transfer_in"),
                'amount' => $data['amount'],
                'balance_before' => $walletTo->balance,
                'balance_after' => $walletTo->balance + $data['amount'],
                'description' => $msgTo,
            ]);

            $walletTo->balance += $data['amount'];
            $walletTo->save();

            $notifyService = new NotificationService();

            // Tạo thông báo biến động số dư
            $notifyService->createNotification([
                'title' => 'Biến động số dư',
                'body' => $msgFrom,
                'target_endpoint' => "/"
            ], config('constant.object.type.user'), $userId, $userId);

            $notifyService->createNotification([
                'title' => 'Biến động số dư',
                'body' => $msgTo,
                'target_endpoint' => "/"
            ], config('constant.object.type.user'), $contract->room->lodging_id, $userId);

            DB::commit();
            return true;
        }catch (\Exception $exception){
            DB::rollBack();
            return ['errors' => [[
                'message' => $exception->getMessage()
            ]]];
        }

    }
}
