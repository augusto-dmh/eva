<?php

namespace App\Jobs;

use App\Models\PayrollCycle;
use App\Services\Payroll\PayrollCycleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayrollConsolidationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly PayrollCycle $cycle) {}

    public function handle(PayrollCycleService $service): void
    {
        // Totals already aggregated on transition to Fechado in PayrollCycleService
        // This job exists for async processing/notification hooks
    }
}
