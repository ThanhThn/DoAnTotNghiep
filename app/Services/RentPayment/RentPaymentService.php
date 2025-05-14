<?php

namespace App\Services\RentPayment;

use App\Models\Contract;
use App\Models\RentPayment;
use App\Models\RoomRentInvoice;
use App\Services\Notification\NotificationService;
use App\Services\RoomRentInvoice\RoomRentInvoiceService;
use App\Services\Token\TokenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use function tests\data;

class RentPaymentService
{
    function createRentalHistory($data)
    {
        $insertData = [
            'contract_id' => $data['contract_id'],
            'payment_amount' => $data['payment_amount'],
            'amount_paid' => $data['amount_paid'],
            'status' => $data['status'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_date' => $data['payment_date'] ?? now(),
            'last_payment_date' => $data['last_payment_date'] ?? now(),
            'due_date' => $data['due_date'] ?? now(),
            'room_rent_invoice_id' => $data['room_rent_invoice_id'] ?? null,
        ];

        try {
            DB::beginTransaction();
            $rentalHistory = RentPayment::create($insertData);

            if ($data['amount_paid'] < $data['payment_amount']) {
                $contract = Contract::find($data['contract_id']);

                $rentInvoice = RoomRentInvoice::on('pgsqlReplica')->find($insertData['room_rent_invoice_id']);

                $currentMonth = $rentInvoice ? $rentInvoice->month_billing : Carbon::today()->month;

                $dif = $data['payment_amount'] - $data['amount_paid'];

                $contract->load('room');
                $formattedDif = rtrim(rtrim(number_format($dif, 2, ',', '.'), '0'), ',');

                $roomName = $contract->room->room_code ?? 'Phòng không xác định';
                $lodging = $contract->room->lodging;
                $lodgingName = $lodging->name ?? "Nhà trọ không xác định";
                $lodgingType = strtolower($lodging->type->name ?? "");

                $notificationService = new NotificationService();
                $mess = [
                    'title' => "Nhắc nhở thanh toán tiền trọ tháng $currentMonth",
                    'body' => "Bạn còn thiếu $formattedDif đ tiền trọ tháng $currentMonth cho phòng $roomName, $lodgingType $lodgingName. Vui lòng thanh toán sớm để tránh phát sinh phí trễ hạn.",
                    'target_endpoint' => "/payment_history/rental/$rentalHistory->id?redirect_to=user",
                    'type' => config('constant.notification.type.important')
                ];
                $notificationService->createNotification($mess, config('constant.object.type.user'),$contract->user_id, $contract->user_id, config('constant.rule.user'));
            }
            DB::commit();
            return $rentalHistory;
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'errors' => [[
                    'message' => $exception->getMessage(),
                ]]
            ];
        }
    }

    function listRentPayment($data)
    {
        $rentalHistory = RentPayment::where('contract_id', $data['contract_id']);

        if(isset($data['status'])){
            $rentalHistory->where('status', $data['status']);
        }

        $total = $rentalHistory->count();

        $rentalHistories = $rentalHistory->orderBy('payment_date', 'desc')->offset($data['offset'] ?? 0)->limit($data['limit'] ?? 20)->get();

        return [
            'total' => $total,
            'data' => $rentalHistories,
        ];
    }

    function sumDebtByContract($contractId)
    {
        $total = RentPayment::select('payment_amount', 'amount_paid')->where(['contract_id' => $contractId])
            ->whereIn('status', [config('constant.payment.status.partial'), config('constant.payment.status.unpaid')])
            ->get()
            ->sum(function ($rentalHistory) {
            return $rentalHistory->payment_amount - $rentalHistory->amount_paid;
        });
        return $total;
    }

    function statisticalAmount($month, $year, $lodgingId)
    {
        $rental = RentPayment::whereHas('contract.room.lodging', function ($query) use ($lodgingId) {
            $query->where('id', $lodgingId);
        })->whereMonth('payment_date', $month)->whereYear('payment_date', $year)
            ->selectRaw('SUM(payment_amount) as total_payment, SUM(amount_paid) as total_paid')
            ->first();
        return $rental;
    }

    function  getLastHistory($contractId)
    {
        return RentPayment::on('pgsqlReplica')->where('contract_id', $contractId)->orderBy('payment_date', 'desc')->first();
    }

    function detail($rentalPaymentId)
    {
        try {
            $history = RentPayment::on('pgsqlReplica')->with('contract')->findOrFail($rentalPaymentId);
            return $history;
        }catch (Exception $exception){
            return ["errors" => [[
                "message" => $exception->getMessage(),
            ]]];
        }
    }

    function checkAccessUser($id, $userId) : bool
    {
        try {
            $history = RentPayment::on('pgsqlReplica')->with('contract')->findOrFail($id);

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
