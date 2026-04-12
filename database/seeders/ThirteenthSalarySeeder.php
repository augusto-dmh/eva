<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\InstallmentStatus;
use App\Enums\ThirteenthRoundStatus;
use App\Models\Collaborator;
use App\Models\ThirteenthSalaryEntry;
use App\Models\ThirteenthSalaryRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ThirteenthSalarySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->firstOrFail();

        $round = ThirteenthSalaryRound::create([
            'ano_referencia' => 2025,
            'status' => ThirteenthRoundStatus::SegundaParcelaSimulada,
            'primeira_parcela_data_limite' => '2025-11-30',
            'segunda_parcela_data_limite' => '2025-12-20',
            'criado_por_id' => $admin->id,
        ]);

        $cltCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->get();

        foreach ($cltCollaborators as $collaborator) {
            $admissao = Carbon::parse($collaborator->data_admissao);
            $anoInicio = Carbon::create(2025, 1, 1);
            $anoFim = Carbon::create(2025, 12, 31);

            if ($admissao->year < 2025) {
                $mesesTrabalhados = 12;
            } else {
                $mesesTrabalhados = min(12, (int) $admissao->diffInMonths($anoFim) + 1);
            }

            $salarioBase = (float) $collaborator->salario_base;
            $mediaComissoes = 0;
            $baseCalculo = $salarioBase;
            $valorIntegral = round($baseCalculo * ($mesesTrabalhados / 12), 2);
            $primeiraParcela = round($valorIntegral * 0.5, 2);

            $descontoInss = $this->calcularInss($valorIntegral);
            $descontoIrrf = $this->calcularIrrf($valorIntegral - $descontoInss);
            $segundaParcela = round($valorIntegral - $primeiraParcela - $descontoInss - $descontoIrrf, 2);

            ThirteenthSalaryEntry::create([
                'thirteenth_salary_round_id' => $round->id,
                'collaborator_id' => $collaborator->id,
                'meses_trabalhados' => $mesesTrabalhados,
                'salario_base' => $salarioBase,
                'media_comissoes' => $mediaComissoes,
                'base_calculo' => $baseCalculo,
                'valor_integral' => $valorIntegral,
                'primeira_parcela_valor' => $primeiraParcela,
                'segunda_parcela_valor' => $segundaParcela,
                'desconto_inss' => $descontoInss,
                'desconto_irrf' => $descontoIrrf,
                'primeira_parcela_status' => InstallmentStatus::Simulado,
                'segunda_parcela_status' => InstallmentStatus::Simulado,
            ]);
        }
    }

    private function calcularInss(float $base): float
    {
        // 2025 progressive INSS table
        $faixas = [
            ['limite' => 1518.00, 'aliquota' => 0.075],
            ['limite' => 2793.88, 'aliquota' => 0.09],
            ['limite' => 4190.83, 'aliquota' => 0.12],
            ['limite' => 8157.41, 'aliquota' => 0.14],
        ];

        $teto = 8157.41;
        $baseCalculo = min($base, $teto);
        $inss = 0;
        $anterior = 0;

        foreach ($faixas as $faixa) {
            if ($baseCalculo <= $faixa['limite']) {
                $inss += ($baseCalculo - $anterior) * $faixa['aliquota'];
                break;
            }
            $inss += ($faixa['limite'] - $anterior) * $faixa['aliquota'];
            $anterior = $faixa['limite'];

            if ($faixa['limite'] === 8157.41) {
                break;
            }
        }

        if ($baseCalculo > 8157.41) {
            $inss += (8157.41 - $anterior) * 0.14;
        }

        return round($inss, 2);
    }

    private function calcularIrrf(float $base): float
    {
        // 2025 IRRF table
        if ($base <= 2824.00) {
            return 0;
        } elseif ($base <= 3751.05) {
            return round($base * 0.075 - 211.80, 2);
        } elseif ($base <= 4664.68) {
            return round($base * 0.15 - 493.13, 2);
        } elseif ($base <= 5583.56) {
            return round($base * 0.225 - 843.13, 2);
        } else {
            return round($base * 0.275 - 1122.77, 2);
        }
    }
}
