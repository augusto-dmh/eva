<?php

namespace App\Services\Payroll;

use App\Enums\PayrollEntryStatus;
use App\Models\PayrollCycle;

class PayrollConsolidationService
{
    /**
     * Consolidate payroll cycle data grouped by legal entity.
     *
     * @return array<int, array{entity_id: int, entity_name: string, total_colaboradores: int, total_bruto: float|string, total_comissoes: float|string, total_liquido: float|string}>
     */
    public function consolidate(PayrollCycle $cycle): array
    {
        $entries = $cycle->entries()->with(['collaborator', 'legalEntity'])->get();

        $grouped = $entries->groupBy('legal_entity_id');

        return $grouped->map(function ($groupEntries, $legalEntityId) {
            $firstEntry = $groupEntries->first();
            $entityName = $firstEntry->legalEntity?->nome ?? 'Sem entidade';

            return [
                'entity_id' => $legalEntityId,
                'entity_name' => $entityName,
                'total_colaboradores' => $groupEntries->count(),
                'total_bruto' => $groupEntries->sum(fn ($e) => (float) $e->salario_bruto),
                'total_comissoes' => $groupEntries->sum(fn ($e) => (float) $e->valor_comissao_total),
                'total_liquido' => $groupEntries->sum(fn ($e) => (float) $e->valor_liquido),
            ];
        })->values()->toArray();
    }

    /**
     * Get PJ invoice status per collaborator for the cycle.
     *
     * @return array<int, array{collaborator_id: int, collaborator_name: string, invoice_status: string|null}>
     */
    public function getPjInvoiceStatus(PayrollCycle $cycle): array
    {
        $invoices = $cycle->pjInvoices()->with('collaborator')->get();

        return $invoices->map(fn ($invoice) => [
            'collaborator_id' => $invoice->collaborator_id,
            'collaborator_name' => $invoice->collaborator?->nome_completo ?? '—',
            'invoice_status' => $invoice->status instanceof \BackedEnum
                ? $invoice->status->value
                : (string) $invoice->status,
        ])->toArray();
    }

    /**
     * Get entry completion status for the cycle.
     *
     * @return array{total: int, completados: int}
     */
    public function getEntryCompletionStatus(PayrollCycle $cycle): array
    {
        $entries = $cycle->entries;
        $total = $entries->count();
        $completados = $entries->filter(
            fn ($e) => $e->status !== PayrollEntryStatus::Pendente
        )->count();

        return [
            'total' => $total,
            'completados' => $completados,
        ];
    }
}
