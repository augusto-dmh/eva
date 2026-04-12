<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payroll\UpdatePayrollEntryRequest;
use App\Models\PayrollCycle;
use App\Models\PayrollEntry;
use Illuminate\Http\RedirectResponse;

class PayrollEntryController extends Controller
{
    public function update(UpdatePayrollEntryRequest $request, PayrollCycle $payrollCycle, PayrollEntry $payrollEntry): RedirectResponse
    {
        abort_unless($payrollEntry->payroll_cycle_id === $payrollCycle->id, 404);

        $payrollEntry->update($request->validated());

        return back()->with('success', 'Entrada atualizada.');
    }
}
