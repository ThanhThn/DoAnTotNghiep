<?php

namespace App\Console\Commands;

use App\Jobs\HandlePaymentService;
use App\Models\LodgingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDatePaymentService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:check-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra hạn thanh toán của các dịch vụ sử dụng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now();

        LodgingService::where('payment_date', $today->day)
            ->whereHas('roomServices', function ($query) {
                $query->where('is_enabled', true)
                    ->whereHas('room.contracts', function ($queryInner) {
                        $queryInner->where('status', config('constant.contract.status.active'));
                    });
            })
            ->chunk(100, function ($services) {

                foreach ($services as $service) {
                    HandlePaymentService::dispatch($service->id);
                }
            });
    }
}
