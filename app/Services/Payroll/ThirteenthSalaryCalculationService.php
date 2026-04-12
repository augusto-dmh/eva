<?php

namespace App\Services\Payroll;

use App\Enums\ContractType;
use App\Enums\ThirteenthRoundStatus;
use App\Models\Collaborator;
use App\Models\ThirteenthSalaryEntry;
use App\Models\ThirteenthSalaryRound;
use Carbon\Carbon;

class ThirteenthSalaryCalculationService
{
    public function simulate(ThirteenthSalaryRound $round): void
    {
        $year = $round->ano_referencia;
        $collaborators = Collaborator::where('status', 'ativo')
            ->where('tipo_contrato', ContractType::Clt->value)
            ->get();

        foreach ($collaborators as $collaborator) {
            $mesesTrabalhados = $this->calcularMesesTrabalhados($collaborator, $year);
            if ($mesesTrabalhados === 0) {
                continue;
            }

            $salarioBase = (float) $collaborator->salario_base;
            $mediaComissoes = 0.0;
            $baseCalculo = $salarioBase + $mediaComissoes;
            $valorIntegral = round(($baseCalculo / 12) * $mesesTrabalhados, 2);
            $primeiraParcela = round($valorIntegral * 0.5, 2);
            $descontoInss = $this->calcularInss($valorIntegral);
            $descontoIrrf = $this->calcularIrrf($valorIntegral - $descontoInss);
            $segundaParcela = round($valorIntegral - $primeiraParcela - $descontoInss - $descontoIrrf, 2);

            ThirteenthSalaryEntry::updateOrCreate(
                [
                    'thirteenth_salary_round_id' => $round->id,
                    'collaborator_id' => $collaborator->id,
                ],
                [
                    'meses_trabalhados' => $mesesTrabalhados,
                    'salario_base' => $salarioBase,
                    'media_comissoes' => $mediaComissoes,
                    'base_calculo' => $baseCalculo,
                    'valor_integral' => $valorIntegral,
                    'primeira_parcela_valor' => $primeiraParcela,
                    'segunda_parcela_valor' => $segundaParcela,
                    'desconto_inss' => $descontoInss,
                    'desconto_irrf' => $descontoIrrf,
                    'primeira_parcela_status' => 'simulado',
                    'segunda_parcela_status' => 'simulado',
                ]
            );
        }

        $round->update(['status' => ThirteenthRoundStatus::PrimeiraParcelaSimulada->value]);
    }

    private function calcularMesesTrabalhados(Collaborator $collaborator, int $year): int
    {
        $admissao = Carbon::parse($collaborator->data_admissao);
        $inicio = max($admissao, Carbon::create($year, 1, 1));
        $fim = Carbon::create($year, 12, 31);

        if ($admissao->year > $year) {
            return 0;
        }

        $meses = 0;
        $current = $inicio->copy()->startOfMonth();

        while ($current->lte($fim)) {
            if ($current->isSameMonth($admissao) && $admissao->day > 15) {
                $current->addMonth();

                continue;
            }
            $meses++;
            $current->addMonth();
        }

        return min($meses, 12);
    }

    private function calcularInss(float $salario): float
    {
        // Progressive INSS table 2025
        $faixas = [
            [1518.00, 0.075],
            [2793.88, 0.09],
            [4190.83, 0.12],
            [8157.41, 0.14],
        ];

        $desconto = 0.0;
        $anterior = 0.0;

        foreach ($faixas as [$limite, $aliquota]) {
            if ($salario <= $anterior) {
                break;
            }
            $faixa = min($salario, $limite) - $anterior;
            $desconto += $faixa * $aliquota;
            $anterior = $limite;
            if ($salario <= $limite) {
                break;
            }
        }

        if ($salario > 8157.41) {
            $desconto = 8157.41 * 0.14;
        }

        return round($desconto, 2);
    }

    private function calcularIrrf(float $base): float
    {
        // IRRF table 2025
        $faixas = [
            [2824.00, 0.0, 0.0],
            [3751.05, 0.075, 211.80],
            [4664.68, 0.15, 494.02],
            [5583.56, 0.225, 844.97],
        ];

        foreach ($faixas as [$limite, $aliquota, $deducao]) {
            if ($base <= $limite) {
                return round(max(0, $base * $aliquota - $deducao), 2);
            }
        }

        return round($base * 0.275 - 1399.28, 2);
    }
}
