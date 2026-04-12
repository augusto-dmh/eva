<?php

namespace App\Services\Payroll;

use App\Enums\ContractType;
use App\Models\Collaborator;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VacationEligibilityService
{
    public function computeEligibility(Collaborator $c, Carbon $referenceDate): array
    {
        $requiredMonths = match ($c->tipo_contrato) {
            ContractType::Clt => 12,
            ContractType::Estagiario => 6,
            default => null, // PJ, Socio excluded
        };

        if ($requiredMonths === null) {
            return [
                'elegivel' => false,
                'meses_acumulados' => 0,
                'periodo_aquisitivo_inicio' => $c->data_admissao,
                'periodo_aquisitivo_fim' => $c->data_admissao,
            ];
        }

        $admissao = Carbon::parse($c->data_admissao);
        $mesesAcumulados = (int) $admissao->diffInMonths($referenceDate);
        $elegivel = $mesesAcumulados >= $requiredMonths;

        return [
            'elegivel' => $elegivel,
            'meses_acumulados' => $mesesAcumulados,
            'periodo_aquisitivo_inicio' => $admissao,
            'periodo_aquisitivo_fim' => $admissao->copy()->addMonths($requiredMonths),
        ];
    }

    public function computeVacationPay(Collaborator $c): array
    {
        $diasFerias = match ($c->tipo_contrato) {
            ContractType::Clt => 30,
            ContractType::Estagiario => 15,
            default => 0,
        };

        $salario = (float) ($c->salario_base ?? 0);
        $valorFerias = ($salario / 30) * $diasFerias;
        $valorTerco = $valorFerias / 3;

        return [
            'valor_ferias' => round($valorFerias, 2),
            'valor_terco_constitucional' => round($valorTerco, 2),
        ];
    }

    public function filterEligibleCollaborators(Collection $collaborators, Carbon $referenceDate, string $type): Collection
    {
        return $collaborators->filter(function (Collaborator $c) use ($referenceDate, $type) {
            if ($c->tipo_contrato->value !== $type) {
                return false;
            }
            $result = $this->computeEligibility($c, $referenceDate);

            return $result['elegivel'];
        })->values();
    }
}
