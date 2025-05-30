<?php

namespace App\Services\ServicePayment;

use App\Models\Contract;
use App\Models\PaymentHistory;
use App\Models\RentPayment;
use App\Models\RoomServiceInvoice;
use App\Models\ServicePayment;
use App\Services\Contract\ContractService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class ServicePaymentService
{
    function detail($rentalPaymentId)
    {
        try {
            $history = ServicePayment::on('pgsqlReplica')->with(['contract', 'roomServiceUsage',
            ])->findOrFail($rentalPaymentId);
            return $history;
        }catch (Exception $exception){
            return ["errors" => [[
                "message" => $exception->getMessage(),
            ]]];
        }
    }

    public function listServicePaymentByContract($data)
    {
        $servicePayments = ServicePayment::with('roomServiceUsage:id,month_billing,year_billing,service_id,service_name,unit_id,initial_index,final_index,value,total_price')->where('contract_id', $data['contract_id']);


        $total = $servicePayments->count();

        $servicePayments = $servicePayments
            ->orderByRaw('(payment_amount - amount_paid) DESC')
            ->orderBy(
                RoomServiceInvoice::select('year_billing')
                    ->whereColumn('room_service_invoices.id', 'service_payments.room_service_invoice_id'),
                'desc'
            )
            ->orderBy(
                RoomServiceInvoice::select('month_billing')
                    ->whereColumn('room_service_invoices.id', 'service_payments.room_service_invoice_id'),
                'desc'
            )
            ->orderBy('payment_date', 'desc')
            ->offset($data['offset'] ?? 0)
            ->limit($data['limit'] ?? 20)
            ->get();

        return [
            'total' => $total,
            'data' => $servicePayments,
        ];
    }

    function sumDebtByContract($contractId)
    {
        $total = ServicePayment::select('payment_amount', 'amount_paid')->where(['contract_id' => $contractId])
            ->whereColumn('payment_amount', '>', 'amount_paid')
            ->get()
            ->sum(function ($rentalHistory) {
                return $rentalHistory->payment_amount - $rentalHistory->amount_paid;
            });
        return $total;
    }

    function paymentByContract($contractId, $serviceId, $amount, $method)
    {
        try{
            DB::beginTransaction();
            $contract = (new ContractService())->detail($contractId);
            $bill = ServicePayment::where(['contract_id' => $contractId, 'id' => $serviceId])->first();

            $amountToBePaid = $bill->payment_amount - $bill->amount_paid;

            $amountPaid = min($amountToBePaid, $amount);
            $refund = max(0, $amount - $amountToBePaid);

            $bill->update([
                'amount_paid' => $bill->amount_paid + $amountPaid,
                'payment_method' => $method,
                'last_payment_date' => Carbon::now()
            ]);

            PaymentHistory::create([
                'contract_id' => $contractId,
                'room_id' => $contract->room_id,
                'lodging_id' => $contract->room->lodging_id,
                'object_id' => $bill->id,
                'object_type' => config('constant.object.type.service'),
                'amount' => $amountPaid,
                'payment_method' => $method,
                'paid_at' => Carbon::now(),
            ]);

            // Cập nhật số tiền đã thanh toán trong RoomServiceUsage
            RoomServiceInvoice::where('id', $bill->room_service_invoice_id)->increment('amount_paid', $amountPaid);

            // Nếu có số dư, cập nhật số tiền còn lại trong hợp đồng
            if ($refund > 0) {
               $contract->update([
                    'remain_amount' => DB::raw('remain_amount + ' . $refund),
                ]);
            }

            DB::commit();
            return $bill->refresh();
        }catch (\Exception $exception) {
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    function checkAccessUser($id, $userId) : bool
    {
        try {
            $history = ServicePayment::on('pgsqlReplica')->with('contract')->findOrFail($id);

            $contract = $history->contract;
            $lodging = $contract->room->lodging;
            if($contract->user_id == $userId || $lodging->user_id == $userId){
                return true;
            }
            return false;
        }catch (Exception $exception){
            return false;
        }
    }
}
