<?php

namespace App\Services\Payroll;

use App\Enums\PayrollCycleStatus;
use App\Exceptions\InvalidTransitionException;
use App\Models\PayrollCycle;
use App\Models\User;

class PayrollCycleService
{
    private const ALLOWED_TRANSITIONS = [
        PayrollCycleStatus::Aberto->value => PayrollCycleStatus::AguardandoNfPj,
        PayrollCycleStatus::AguardandoNfPj->value => PayrollCycleStatus::AguardandoComissoes,
        PayrollCycleStatus::AguardandoComissoes->value => PayrollCycleStatus::EmRevisao,
        PayrollCycleStatus::EmRevisao->value => PayrollCycleStatus::ConferidoContabilidade,
        PayrollCycleStatus::ConferidoContabilidade->value => PayrollCycleStatus::Fechado,
    ];

    public function transition(PayrollCycle $cycle, PayrollCycleStatus $to, ?User $triggeredBy = null): PayrollCycle
    {
        $allowed = self::ALLOWED_TRANSITIONS[$cycle->status->value] ?? null;

        if ($allowed === null || $allowed !== $to) {
            throw new InvalidTransitionException(
                "Cannot transition from {$cycle->status->value} to {$to->value}"
            );
        }

        $from = $cycle->status;

        $cycle->status = $to;

        if ($to === PayrollCycleStatus::Fechado) {
            $this->aggregateTotals($cycle);
            $cycle->data_fechamento = now();
            $cycle->fechado_por_id = $triggeredBy?->id;
        }

        $cycle->save();

        $cycle->events()->create([
            'from_status' => $from->value,
            'to_status' => $to->value,
            'triggered_by_id' => $triggeredBy?->id,
            'created_at' => now(),
        ]);

        return $cycle;
    }

    private function aggregateTotals(PayrollCycle $cycle): void
    {
        $entries = $cycle->entries;

        $cycle->salarios_brutos = $entries->sum('salario_bruto');
        $cycle->comissoes = $entries->sum('valor_comissao_total');
        $cycle->deducoes = $entries->sum(fn ($e) => $e->desconto_inss + $e->desconto_irrf + $e->desconto_contribuicao_assistencial +
            $e->desconto_petlove + $e->desconto_outros
        );
        $cycle->liquido = $entries->sum('valor_liquido');
        $cycle->pj = $entries->whereNotNull('valor_nota_fiscal_pj')->sum('valor_nota_fiscal_pj');
    }
}
