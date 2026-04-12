<?php

use App\Enums\PayrollCycleStatus;
use App\Jobs\SendPjInvoiceChannelReminderJob;
use App\Jobs\SendPjInvoiceIndividualRemindersJob;
use App\Models\PayrollCycle;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $cycle = PayrollCycle::where('status', PayrollCycleStatus::AguardandoNfPj)->latest()->first();
    if ($cycle) {
        SendPjInvoiceChannelReminderJob::dispatch($cycle);
    }
})->weekly()->when(fn () => now()->day >= 24);

Schedule::call(function () {
    $cycle = PayrollCycle::where('status', PayrollCycleStatus::AguardandoNfPj)->latest()->first();
    if ($cycle) {
        SendPjInvoiceIndividualRemindersJob::dispatch($cycle);
    }
})->daily();
