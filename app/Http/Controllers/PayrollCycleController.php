<?php

namespace App\Http\Controllers;

use App\Enums\PayrollCycleStatus;
use App\Exceptions\InvalidTransitionException;
use App\Http\Requests\Payroll\StorePayrollCycleRequest;
use App\Models\Collaborator;
use App\Models\PayrollCycle;
use App\Services\Payroll\PayrollCycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollCycleController extends Controller
{
    public function __construct(private PayrollCycleService $service) {}

    public function index(): Response
    {
        $cycles = PayrollCycle::orderByDesc('ano')
            ->orderByDesc('mes')
            ->paginate(12);

        return Inertia::render('payroll-cycles/Index', [
            'cycles' => $cycles,
        ]);
    }

    public function store(StorePayrollCycleRequest $request): RedirectResponse
    {
        [$ano, $mes] = explode('-', $request->mes_referencia);

        $cycle = PayrollCycle::create([
            'mes_referencia' => $request->mes_referencia,
            'ano' => (int) $ano,
            'mes' => (int) $mes,
            'status' => PayrollCycleStatus::Aberto,
            'data_abertura' => now(),
            'data_pagamento_folha' => $request->data_pagamento_folha,
            'data_pagamento_comissao' => $request->data_pagamento_comissao,
            'observacoes' => $request->observacoes,
        ]);

        // Auto-create entries for all active collaborators
        Collaborator::where('status', 'ativo')->each(function ($collaborator) use ($cycle) {
            $cycle->entries()->create([
                'collaborator_id' => $collaborator->id,
                'tipo_contrato' => $collaborator->tipo_contrato->value,
                'legal_entity_id' => $collaborator->legal_entity_id,
                'salario_bruto' => $collaborator->salario_base ?? 0,
            ]);
        });

        return redirect()->route('payroll-cycles.show', $cycle)
            ->with('success', 'Ciclo de folha aberto com sucesso.');
    }

    public function show(PayrollCycle $payrollCycle): Response
    {
        $payrollCycle->load(['entries.collaborator', 'entries.legalEntity', 'events.triggeredBy', 'pjInvoices.collaborator']);

        return Inertia::render('payroll-cycles/Show', [
            'cycle' => $payrollCycle,
            'allStatuses' => array_map(
                fn ($s) => ['value' => $s->value, 'label' => $s->label()],
                PayrollCycleStatus::cases()
            ),
        ]);
    }

    public function update(Request $request, PayrollCycle $payrollCycle): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $to = PayrollCycleStatus::from($request->status);

        try {
            $this->service->transition($payrollCycle, $to, $request->user());
        } catch (InvalidTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Status do ciclo atualizado.');
    }
}
