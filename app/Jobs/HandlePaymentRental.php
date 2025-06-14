<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Room;
use App\Services\Contract\ContractService;
use App\Services\RoomRentInvoice\RoomRentInvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class HandlePaymentRental implements ShouldQueue
{
    use Queueable;

    private $_roomId;

    /**
     * Create a new job instance.
     */
    public function __construct($roomId)
    {
        $this->_roomId = $roomId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $status = [config('constant.contract.status.active'), config('constant.contract.status.overdue')];
        $room = Room::with(['contracts' => function ($query) use ($status) {
            $query->whereIn('status', $status);
        }])->find($this->_roomId);

        $roomRentalHistoryService = new RoomRentInvoiceService();
        $roomRent = $roomRentalHistoryService->processRoomRentInvoice($room);

        if(!$roomRent) return;

        $contracts = $room->contracts->sortByDesc(fn($contract) => $contract->monthly_rent ?? -1);
        $quantity = $room->current_tenants;

        $contractService = new ContractService();
        foreach ($contracts as $contract) {
            // Ngưng khi đã tính tiền cần đóng hết cho mọi khách
            if($quantity <= 0) break;

            if(is_numeric($contract->monthly_rent)){
                $amountNeedPayment = min($contract->monthly_rent, $roomRent['price']);
            }else{
                $diff = max(1, $quantity);
                $amountNeedPayment = ($roomRent['price'] / $diff) * $contract->quantity;
            }

            $roomRent['price'] -= $amountNeedPayment;

            if($amountNeedPayment){
                $contractService->calculateContract($contract->id, $amountNeedPayment, $room->late_days, $roomRent['history']->id);
            }

            $quantity -= $contract->quantity;
        }
    }
}
