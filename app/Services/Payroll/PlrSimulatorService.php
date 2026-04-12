<?php

namespace App\Services\Payroll;

use App\Enums\ContractType;
use App\Enums\PlrRoundStatus;
use App\Models\Collaborator;
use App\Models\PlrEntry;
use App\Models\PlrRound;
use Carbon\Carbon;

class PlrSimulatorService
{
    public function simulate(PlrRound $round, float $totalAmount): void
    {
        $year = $round->ano_referencia;
        $collaborators = Collaborator::where('status', 'ativo')
            ->where('tipo_contrato', ContractType::Clt->value)
            ->get()
            ->filter(fn ($c) => $this->mesesTrabalhados($c, $year) >= 6);

        $pesos = $collaborators->mapWithKeys(function ($c) use ($year) {
            $meses = $this->mesesTrabalhados($c, $year);

            return [$c->id => (float) $c->salario_base * $meses];
        });

        $totalPeso = $pesos->sum();

        if ($totalPeso <= 0) {
            return;
        }

        foreach ($collaborators as $collaborator) {
            $meses = $this->mesesTrabalhados($collaborator, $year);
            $peso = $pesos[$collaborator->id];
            $valorSimulado = round(($peso / $totalPeso) * $totalAmount, 2);
            $descontoIrrf = $this->calcularIrrfPlr($valorSimulado);

            PlrEntry::updateOrCreate(
                [
                    'plr_round_id' => $round->id,
                    'collaborator_id' => $collaborator->id,
                ],
                [
                    'media_salarios_ano' => (float) $collaborator->salario_base,
                    'meses_trabalhados' => $meses,
                    'valor_simulado' => $valorSimulado,
                    'valor_pago' => null,
                    'desconto_irrf' => $descontoIrrf,
                    'status' => 'simulado',
                ]
            );
        }

        $round->update([
            'status' => PlrRoundStatus::Simulado->value,
            'valor_total_distribuido' => $totalAmount,
        ]);
    }

    private function mesesTrabalhados(Collaborator $collaborator, int $year): int
    {
        $admissao = Carbon::parse($collaborator->data_admissao);
        if ($admissao->year > $year) {
            return 0;
        }

        $inicio = max($admissao, Carbon::create($year, 1, 1));
        $fim = Carbon::create($year, 12, 31);

        $meses = 0;
        $current = $inicio->copy()->startOfMonth();
        while ($current->lte($fim)) {
            $meses++;
            $current->addMonth();
        }

        return min($meses, 12);
    }

    public function calcularIrrfPlr(float $valor): float
    {
        if ($valor <= 6000) {
            return 0.0;
        }
        if ($valor <= 9000) {
            return round($valor * 0.075 - 450, 2);
        }
        if ($valor <= 12000) {
            return round($valor * 0.15 - 1125, 2);
        }
        if ($valor <= 15000) {
            return round($valor * 0.225 - 2025, 2);
        }

        return round($valor * 0.275 - 2775, 2);
    }
}
