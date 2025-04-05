<?php

namespace App\Console\Commands;

use App\Models\Lodging;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ChangeTotalDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:change-total';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new DashboardService();
        $service->updateTotal();
    }
}
