<?php

namespace App\Http\Controllers;

use App\Ai\Agents\PayrollDiscrepancyAnalystAgent;
use App\Models\PayrollCycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollDiscrepancyController extends Controller
{
    public function analyze(Request $request, PayrollCycle $payrollCycle): JsonResponse
    {
        $request->validate([
            'accounting_data' => 'required|array',
        ]);

        $payrollData = $payrollCycle->entries()
            ->with('collaborator')
            ->get()
            ->groupBy(fn ($e) => $e->collaborator->legal_entity_id ?? 'unknown')
            ->map(fn ($entries) => [
                'total_salarios' => $entries->sum('salario_base'),
                'total_colaboradores' => $entries->count(),
            ])
            ->toArray();

        $report = (new PayrollDiscrepancyAnalystAgent)->analyze(
            $payrollData,
            $request->accounting_data
        );

        return response()->json(['report' => $report]);
    }
}
