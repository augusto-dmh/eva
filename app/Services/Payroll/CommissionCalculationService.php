<?php

namespace App\Services\Payroll;

class CommissionCalculationService
{
    /**
     * Calculate commission for Closer type collaborators.
     * DSR = rawCommission / businessDays * restDays
     */
    public function calculateCloserCommission(string $rawCommission, int $businessDays, int $restDays): array
    {
        $raw = (float) $rawCommission;

        $dsr = $businessDays > 0
            ? $raw / $businessDays * $restDays
            : 0.0;

        $total = $raw + $dsr;

        return [
            'comissao_bruta' => $rawCommission,
            'valor_dsr' => $dsr,
            'valor_comissao_total' => $total,
        ];
    }

    /**
     * Calculate commission for Advisor type collaborators.
     * effective = max(rawCommission, garantido) - proLabore
     * Total = max(0, effective)
     */
    public function calculateAdvisorCommission(
        string $rawCommission,
        string $garantido,
        string $proLabore,
        int $b3Days,
        int $b3TotalDays
    ): array {
        $raw = (float) $rawCommission;
        $minGarantido = (float) $garantido;
        $proLaboreVal = (float) $proLabore;

        $effective = max($raw, $minGarantido) - $proLaboreVal;
        $total = max(0.0, $effective);

        return [
            'comissao_bruta' => $rawCommission,
            'valor_dsr' => 0,
            'valor_comissao_total' => $total,
        ];
    }
}
