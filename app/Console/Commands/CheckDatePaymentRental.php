<?php

namespace App\Console\Commands;

use App\Jobs\HandlePaymentRental;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDatePaymentRental extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rental:check-payments';
    protected $description = 'Kiểm tra hạn thanh toán của các hợp đồng thuê';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        Room::where('payment_date', $today->day)->whereHas('contracts', function ($query) {
            $query->where('status', config('constant.contract.status.active'));
        })->chunk(100, function ($rooms) {
            foreach ($rooms as $room) {
                HandlePaymentRental::dispatch($room->id);
            }
        });
    }
}
