<?php

namespace App\Jobs;

use App\Models\Contract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class HandleOverdueContract implements ShouldQueue
{
    use Queueable;

    private Contract $contract;
    /**
     * Create a new job instance.
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->contract->update([
            'status' => config('constant.contract.status.overdue')
        ]);
    }
}
