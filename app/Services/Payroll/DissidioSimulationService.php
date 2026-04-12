<?php

namespace App\Services\Payroll;

use App\Enums\AdjustmentReason;
use App\Enums\ContractType;
use App\Enums\DissidioRoundStatus;
use App\Enums\ProfessionalEventType;
use App\Models\Collaborator;
use App\Models\DissidioEntry;
use App\Models\DissidioRound;
use App\Models\ProfessionalHistoryEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DissidioSimulationService
{
    public function simulate(DissidioRound $round): void
    {
        $collaborators = Collaborator::where('status', 'ativo')
            ->whereIn('tipo_contrato', $this->eligibleContractTypes($round))
            ->get();

        foreach ($collaborators as $collaborator) {
            $salarioAnterior = (float) $collaborator->salario_base;
            $salarioNovo = round($salarioAnterior * (1 + (float) $round->percentual), 2);
            $mesesRetroativos = max(0, Carbon::parse($round->data_base)->diffInMonths(now(), false));
            $diferencaRetroativa = round(($salarioNovo - $salarioAnterior) * $mesesRetroativos, 2);

            DissidioEntry::updateOrCreate(
                [
                    'dissidio_round_id' => $round->id,
                    'collaborator_id' => $collaborator->id,
                ],
                [
                    'salario_anterior' => $salarioAnterior,
                    'percentual_aplicado' => $round->percentual,
                    'salario_novo' => $salarioNovo,
                    'diferenca_retroativa' => $diferencaRetroativa,
                    'meses_retroativos' => $mesesRetroativos,
                    'status' => 'simulado',
                ]
            );
        }

        $round->update(['status' => DissidioRoundStatus::Simulado->value]);
    }

    public function apply(DissidioRound $round, User $admin): void
    {
        DB::transaction(function () use ($round, $admin) {
            $entries = $round->entries()->where('status', 'simulado')->with('collaborator')->get();

            foreach ($entries as $entry) {
                $collaborator = $entry->collaborator;

                // Update salary
                $collaborator->update(['salario_base' => $entry->salario_novo]);

                // Create immutable history entry
                ProfessionalHistoryEntry::create([
                    'collaborator_id' => $collaborator->id,
                    'tipo_evento' => ProfessionalEventType::Dissidio->value,
                    'data_efetivacao' => now()->toDateString(),
                    'campo_alterado' => 'salario_base',
                    'valor_anterior' => (string) $entry->salario_anterior,
                    'valor_novo' => (string) $entry->salario_novo,
                    'motivo' => AdjustmentReason::Dissidio->value,
                    'dissidio_round_id' => $round->id,
                    'observacoes' => "Dissídio {$round->ano_referencia} — {$round->percentual}%",
                    'registrado_por_id' => $admin->id,
                ]);

                $entry->update(['status' => 'aplicado']);
            }

            $round->update([
                'status' => DissidioRoundStatus::Aplicado->value,
                'aplicado_por_id' => $admin->id,
                'aplicado_em' => now(),
            ]);
        });
    }

    private function eligibleContractTypes(DissidioRound $round): array
    {
        $types = [ContractType::Clt->value];

        if ($round->aplica_estagiarios) {
            $types[] = ContractType::Estagiario->value;
        }

        return $types;
    }
}
