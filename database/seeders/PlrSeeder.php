<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\PlrRoundStatus;
use App\Enums\PlrSyndicateStatus;
use App\Models\Collaborator;
use App\Models\PlrRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlrSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->first();

        $collaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->where('status', CollaboratorStatus::Ativo)
            ->get();

        $rounds = [
            [
                'ano_referencia'             => 2024,
                'status'                     => PlrRoundStatus::Pago,
                'status_sindicato'           => PlrSyndicateStatus::Aprovado,
                'data_aprovacao_sindicato'   => '2024-11-15',
                'valor_total_distribuido'    => 850_000.00,
                'documento_politica_revisado' => true,
                'observacoes'               => 'PLR 2024 — distribuído em dezembro/2024.',
                'entry_status'              => 'pago',
            ],
            [
                'ano_referencia'             => 2025,
                'status'                     => PlrRoundStatus::Simulado,
                'status_sindicato'           => PlrSyndicateStatus::Aprovado,
                'data_aprovacao_sindicato'   => '2025-11-10',
                'valor_total_distribuido'    => 920_000.00,
                'documento_politica_revisado' => true,
                'observacoes'               => 'Simulação finalizada — aguardando aprovação diretoria.',
                'entry_status'              => 'simulado',
            ],
            [
                'ano_referencia'             => 2026,
                'status'                     => PlrRoundStatus::ComiteCriado,
                'status_sindicato'           => PlrSyndicateStatus::NaoIniciado,
                'data_aprovacao_sindicato'   => null,
                'valor_total_distribuido'    => null,
                'documento_politica_revisado' => false,
                'observacoes'               => 'Comitê de trabalhadores constituído em 2026-03-10.',
                'entry_status'              => null,
            ],
        ];

        $now = now();

        foreach ($rounds as $roundData) {
            $round = PlrRound::create([
                'ano_referencia'              => $roundData['ano_referencia'],
                'status'                      => $roundData['status']->value,
                'status_sindicato'            => $roundData['status_sindicato']->value,
                'data_aprovacao_sindicato'    => $roundData['data_aprovacao_sindicato'],
                'valor_total_distribuido'     => $roundData['valor_total_distribuido'],
                'documento_politica_path'     => null,
                'documento_politica_revisado' => $roundData['documento_politica_revisado'],
                'observacoes'                 => $roundData['observacoes'],
                'criado_por_id'               => $admin?->id,
            ]);

            // Create entries only for rounds with a simulation/payment
            if ($roundData['entry_status'] !== null && $roundData['valor_total_distribuido']) {
                $entries = [];
                $countCollab  = $collaborators->count();
                $baseDistrib  = $roundData['valor_total_distribuido'] / max($countCollab, 1);
                $referenceEnd = Carbon::create($roundData['ano_referencia'], 12, 31);

                foreach ($collaborators as $collab) {
                    $salario  = (float) ($collab->salario_base ?? 3000);
                    $admissao = Carbon::parse($collab->data_admissao);
                    $meses    = min(12, max(1, (int) $admissao->diffInMonths($referenceEnd)));
                    $fator    = $salario / 5000;
                    $valor    = round($baseDistrib * $fator, 2);
                    $irrf     = $valor > 1903 ? round(($valor - 1903) * 0.075, 2) : 0;

                    $entries[] = [
                        'plr_round_id'      => $round->id,
                        'collaborator_id'   => $collab->id,
                        'media_salarios_ano' => $salario,
                        'meses_trabalhados' => $meses,
                        'valor_simulado'    => $valor,
                        'valor_pago'        => $roundData['entry_status'] === 'pago' ? $valor : null,
                        'desconto_irrf'     => $irrf,
                        'status'            => $roundData['entry_status'],
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }

                foreach (array_chunk($entries, 100) as $chunk) {
                    DB::table('plr_entries')->insert($chunk);
                }
            }

            // PLR committee members (5–7 workers per round, required: legal_entity_id, papel)
            $committeeSize = rand(5, 7);
            $committee     = $collaborators->random(min($committeeSize, $collaborators->count()));
            $commitRows    = [];
            foreach ($committee as $member) {
                $commitRows[] = [
                    'plr_round_id'    => $round->id,
                    'collaborator_id' => $member->id,
                    'legal_entity_id' => $member->legal_entity_id,
                    'papel'           => fake()->randomElement(['Titular', 'Suplente']),
                    'ativo'           => true,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }
            DB::table('plr_committee_members')->insert($commitRows);
        }
    }
}
