<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contract:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check overdue contract';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::today();

        $contracts = Contract::where("status", config('constant.contract.status.active'))->whereRaw("COALESCE(end_date, start_date + (lease_duration || 'months')::interval)::date < ?", [$now])->chunk(1000, function ($contracts) {
            foreach ($contracts as $contract) {

            }
        });
    }
}
