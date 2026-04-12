<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\VacationBatchStatus;
use App\Enums\VacationBatchType;
use App\Enums\VacationCollaboratorStatus;
use App\Models\Collaborator;
use App\Models\User;
use App\Models\VacationBatch;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VacationBatchSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->first();

        $cltEligible = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->where('status', CollaboratorStatus::Ativo)
            ->where('data_admissao', '<=', now()->subMonths(12))
            ->get();

        $estagEligible = Collaborator::where('tipo_contrato', ContractType::Estagiario)
            ->where('status', CollaboratorStatus::Ativo)
            ->where('data_admissao', '<=', now()->subMonths(6))
            ->get();

        $batches = [
            // 2025 - completed batches
            [
                'mes_referencia' => '2025-07',
                'tipo'           => VacationBatchType::Clt,
                'status'         => VacationBatchStatus::Concluido,
                'data_abertura'  => Carbon::create(2025, 6, 1),
                'collaborators'  => $cltEligible->random(min(40, $cltEligible->count())),
                'collab_status'  => VacationCollaboratorStatus::Concluido,
            ],
            [
                'mes_referencia' => '2025-09',
                'tipo'           => VacationBatchType::Estagiario,
                'status'         => VacationBatchStatus::Concluido,
                'data_abertura'  => Carbon::create(2025, 8, 1),
                'collaborators'  => $estagEligible->random(min(15, $estagEligible->count())),
                'collab_status'  => VacationCollaboratorStatus::Concluido,
            ],
            [
                'mes_referencia' => '2025-12',
                'tipo'           => VacationBatchType::Clt,
                'status'         => VacationBatchStatus::Concluido,
                'data_abertura'  => Carbon::create(2025, 11, 1),
                'collaborators'  => $cltEligible->random(min(35, $cltEligible->count())),
                'collab_status'  => VacationCollaboratorStatus::Concluido,
            ],
            // 2026 - active batches
            [
                'mes_referencia' => '2026-02',
                'tipo'           => VacationBatchType::Clt,
                'status'         => VacationBatchStatus::Confirmado,
                'data_abertura'  => Carbon::create(2026, 1, 10),
                'collaborators'  => $cltEligible->random(min(28, $cltEligible->count())),
                'collab_status'  => VacationCollaboratorStatus::Confirmado,
            ],
            [
                'mes_referencia' => '2026-05',
                'tipo'           => VacationBatchType::Clt,
                'status'         => VacationBatchStatus::Calculado,
                'data_abertura'  => Carbon::create(2026, 4, 5),
                'collaborators'  => $cltEligible->random(min(32, $cltEligible->count())),
                'collab_status'  => VacationCollaboratorStatus::Agendado,
            ],
            [
                'mes_referencia' => '2026-06',
                'tipo'           => VacationBatchType::Estagiario,
                'status'         => VacationBatchStatus::Rascunho,
                'data_abertura'  => now(),
                'collaborators'  => collect(),
                'collab_status'  => VacationCollaboratorStatus::Pendente,
            ],
        ];

        $now = now();

        foreach ($batches as $batchData) {
            $tipo  = $batchData['tipo'];
            $batch = VacationBatch::create([
                'mes_referencia'                  => $batchData['mes_referencia'],
                'tipo'                            => $tipo,
                'periodo_aquisitivo_minimo_meses' => $tipo->periodoAquisitivoMeses(),
                'dias_ferias'                     => $tipo->diasFerias(),
                'status'                          => $batchData['status'],
                'data_abertura'                   => $batchData['data_abertura'],
                'criado_por_id'                   => $admin?->id,
            ]);

            $pivotRows = [];
            foreach ($batchData['collaborators'] as $collab) {
                $admissao   = Carbon::parse($collab->data_admissao);
                $meses      = (int) $admissao->diffInMonths(now());
                $salario    = (float) ($collab->salario_base ?? 3000);
                $valorFer   = round($salario / 30 * $tipo->diasFerias(), 2);
                $terco      = round($valorFer / 3, 2);

                $pivotRows[] = [
                    'vacation_batch_id'        => $batch->id,
                    'collaborator_id'          => $collab->id,
                    'data_admissao'            => $collab->data_admissao,
                    'periodo_aquisitivo_inicio' => $admissao->toDateString(),
                    'periodo_aquisitivo_fim'   => $admissao->copy()->addMonths($meses)->toDateString(),
                    'meses_acumulados'         => $meses,
                    'elegivel'                 => true,
                    'valor_ferias'             => $valorFer,
                    'valor_terco_constitucional' => $terco,
                    'status'                   => $batchData['collab_status']->value,
                    'aviso_enviado'            => $batchData['status']->value === 'concluido',
                    'aviso_assinado'           => $batchData['status']->value === 'concluido',
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ];
            }

            foreach (array_chunk($pivotRows, 100) as $chunk) {
                DB::table('vacation_batch_collaborators')->insert($chunk);
            }
        }
    }
}
