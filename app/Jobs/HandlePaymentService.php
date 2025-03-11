<?php

namespace App\Jobs;

use App\Models\LodgingService;
use App\Services\LodgingService\LodgingServiceManagerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandlePaymentService implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private string $_serviceId;

    public function __construct(string $serviceId)
    {
        $this->_serviceId = $serviceId;
    }

    public function handle(): void
    {
        $lodgingService = LodgingService::find($this->_serviceId);

        if (!$lodgingService) {
            return;
        }

        $serviceManager = new LodgingServiceManagerService();
        $serviceCalculator = $serviceManager->getServiceCalculator($lodgingService);

        if ($serviceCalculator) {
            $serviceCalculator->calculateCost();
        }
    }
}
