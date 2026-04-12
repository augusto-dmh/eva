<?php

namespace App\Services\Payroll;

use App\Enums\TerminationType;
use App\Models\Collaborator;
use Carbon\Carbon;

class TerminationCalculationService
{
    public function simulate(Collaborator $c, TerminationType $type, Carbon $effectiveDate): array
    {
        $salario = (float) ($c->salario_base ?? 0);
        $admissao = Carbon::parse($c->data_admissao);

        // 1. Proportional salary (days worked in final month)
        $diasTrabalhados = $effectiveDate->day;
        $salarioProporcional = round(($salario / 30) * $diasTrabalhados, 2);

        // 2. Proportional vacation
        // Accrual period: from last anniversary to effective date
        $yearsWorked = (int) $admissao->diffInYears($effectiveDate);
        $lastAnniversary = $admissao->copy()->addYears($yearsWorked);
        $mesesPeriodoAquisitivo = (int) $lastAnniversary->diffInMonths($effectiveDate);
        $feriasProporcional = round(($salario / 12) * $mesesPeriodoAquisitivo, 2);
        $tercoFerias = round($feriasProporcional / 3, 2);

        // 3. Proportional 13th salary (Jan 1 or admission, whichever later, to effective date)
        $inicioAno = Carbon::create($effectiveDate->year, 1, 1);
        $referenciaInicio = $admissao->gt($inicioAno) ? $admissao : $inicioAno;
        $mesesTrabalhados = (int) $referenciaInicio->diffInMonths($effectiveDate);
        // Partial month > 15 days counts as full
        if ($effectiveDate->day > 15 && $referenciaInicio->month !== $effectiveDate->month) {
            $mesesTrabalhados++;
        }
        $decimoTerceiro = round(($salario / 12) * min($mesesTrabalhados, 12), 2);

        // 4. FGTS fine
        // Note: FGTS balance not tracked in Eva; use salario_base as proxy for calculation
        // Real implementation would use actual FGTS balance from the record
        $fgtsBase = $salario * ($admissao->diffInMonths($effectiveDate)); // simplified FGTS estimate
        $multaFgts = round((float) $this->calculateFgtsFine((string) $fgtsBase, $type), 2);

        // 5. Notice period indemnity
        $avisoPrevio = round((float) $this->calculateNoticeIndemnity($c, $type), 2);

        // 6. Flash benefit adjustment (days remaining in month)
        $flashTotal = (float) ($c->flash_total ?? 0);
        $daysInMonth = $effectiveDate->daysInMonth;
        $remainingDays = $daysInMonth - $diasTrabalhados;
        $ajusteFlash = round(($flashTotal / $daysInMonth) * $remainingDays, 2);

        $total = $salarioProporcional + $feriasProporcional + $tercoFerias +
                 $decimoTerceiro + $multaFgts + $avisoPrevio;

        return [
            'salario_proporcional_dias' => $diasTrabalhados,
            'salario_proporcional_valor' => $salarioProporcional,
            'ferias_proporcionais_valor' => $feriasProporcional,
            'terco_ferias_proporcionais' => $tercoFerias,
            'decimo_terceiro_proporcional' => $decimoTerceiro,
            'multa_fgts' => $multaFgts,
            'aviso_previo_valor' => $avisoPrevio,
            'indenizacao_rescisoria' => 0.0,
            'ajuste_flash_valor' => $ajusteFlash,
            'valor_total_rescisao' => round($total, 2),
        ];
    }

    public function calculateFgtsFine(string $fgtsBalance, TerminationType $type): string
    {
        $balance = (float) $fgtsBalance;
        $rate = match ($type) {
            TerminationType::DispensaSemJustaCausa => 0.40,
            TerminationType::MutuoAcordo => 0.20,
            default => 0.0,
        };

        return (string) round($balance * $rate, 2);
    }

    public function calculateNoticeIndemnity(Collaborator $c, TerminationType $type): string
    {
        // Only involuntary dismissals generate notice indemnity
        if (! in_array($type, [TerminationType::DispensaSemJustaCausa, TerminationType::MutuoAcordo])) {
            return '0.00';
        }

        $salario = (float) ($c->salario_base ?? 0);
        $admissao = Carbon::parse($c->data_admissao);
        $yearsOfService = (int) $admissao->diffInYears(now());
        $noticeDays = min(30 + (3 * $yearsOfService), 90);
        $noticeValue = ($salario / 30) * $noticeDays;

        // Mútuo Acordo: half the notice
        if ($type === TerminationType::MutuoAcordo) {
            $noticeValue /= 2;
        }

        return (string) round($noticeValue, 2);
    }
}
