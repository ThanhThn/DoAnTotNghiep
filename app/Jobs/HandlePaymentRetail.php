<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Models\Room;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HandlePaymentRetail implements ShouldQueue
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
        $room = Room::with(['contracts' => function ($query) {
            $query->where(['status'  => config('constant.contract.status.active')]);
        }])->find($this->_roomId);

        $roomRent = $room->price;

        $contracts = $room->contracts->sortByDesc(fn($contract) => $contract->month_rent ?? -1);
        $quantity = $room->current_tenants;

        foreach ($contracts as $contract) {
            // Ngưng khi đã tính tiền cần đóng hết cho mọi khách
            if($quantity <= 0) break;

            if(is_numeric($contract->month_rent)){
                $moneyNeedPaid = min($contract->month_rent, $roomRent);
                $roomRent -= $moneyNeedPaid;
            }else{
                $diff = max(1, $quantity);
                $moneyNeedPaid = ($roomRent / $diff) * $contract->quantity;
                $roomRent -= $moneyNeedPaid;
            }

            if($moneyNeedPaid){
                ///Code
            }

            $quantity -= $contract->quantity;
        }
    }
}
