<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\VacationBatchStatus;
use App\Enums\VacationBatchType;
use App\Enums\VacationCollaboratorStatus;
use App\Models\Collaborator;
use App\Models\User;
use App\Models\VacationBatch;
use App\Models\VacationBatchCollaborator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VacationBatchSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->firstOrFail();

        $batch = VacationBatch::create([
            'mes_referencia' => '2025-03',
            'tipo' => VacationBatchType::Clt,
            'periodo_aquisitivo_minimo_meses' => 12,
            'dias_ferias' => 30,
            'status' => VacationBatchStatus::Calculado,
            'data_abertura' => now(),
            'criado_por_id' => $admin->id,
        ]);

        $cltCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->get();

        $now = Carbon::now();

        foreach ($cltCollaborators as $collaborator) {
            $admissao = Carbon::parse($collaborator->data_admissao);
            $mesesAcumulados = (int) $admissao->diffInMonths($now);
            $elegivel = $mesesAcumulados >= 12;

            $periodoFim = $admissao->copy()->addMonths(12);

            $entry = [
                'vacation_batch_id' => $batch->id,
                'collaborator_id' => $collaborator->id,
                'data_admissao' => $admissao->toDateString(),
                'periodo_aquisitivo_inicio' => $admissao->toDateString(),
                'periodo_aquisitivo_fim' => $periodoFim->toDateString(),
                'meses_acumulados' => $mesesAcumulados,
                'elegivel' => $elegivel,
                'status' => $elegivel
                    ? VacationCollaboratorStatus::Agendado
                    : VacationCollaboratorStatus::Pendente,
            ];

            if ($elegivel) {
                $entry['data_inicio_ferias'] = '2025-04-07';
                $entry['data_fim_ferias'] = '2025-05-06';
                $entry['valor_ferias'] = $collaborator->salario_base;
                $entry['valor_terco_constitucional'] = round($collaborator->salario_base / 3, 2);
            }

            VacationBatchCollaborator::create($entry);
        }
    }
}
