<?php

namespace App\Jobs;

use App\Models\PayrollCycle;
use App\Services\Payroll\PayrollConsolidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayrollConsolidationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly PayrollCycle $cycle)
    {
        $this->onQueue('high');
    }

    public function handle(PayrollConsolidationService $service): void
    {
        $service->consolidate($this->cycle);
    }
}
