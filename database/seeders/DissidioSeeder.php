<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\DissidioRoundStatus;
use App\Models\Collaborator;
use App\Models\DissidioRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DissidioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->first();

        $rounds = [
            [
                'ano_referencia'     => 2024,
                'data_base'          => '2024-03-01',
                'data_publicacao'    => '2024-03-10',
                'percentual'         => 0.0552,
                'aplica_estagiarios' => false,
                'status'             => DissidioRoundStatus::Aplicado,
                'observacoes'        => 'Dissídio 2024 — SINTRAFI/SP. Índice INPC acumulado 2023.',
                'aplicado_em'        => Carbon::create(2024, 3, 15),
            ],
            [
                'ano_referencia'     => 2025,
                'data_base'          => '2025-03-01',
                'data_publicacao'    => '2025-03-12',
                'percentual'         => 0.0481,
                'aplica_estagiarios' => false,
                'status'             => DissidioRoundStatus::RelatorioGerado,
                'observacoes'        => 'Dissídio 2025 — SINTRAFI/SP. Relatório enviado à contabilidade.',
                'aplicado_em'        => Carbon::create(2025, 3, 20),
            ],
            [
                'ano_referencia'     => 2026,
                'data_base'          => '2026-03-01',
                'data_publicacao'    => null,
                'percentual'         => 0.0520,
                'aplica_estagiarios' => false,
                'status'             => DissidioRoundStatus::Simulado,
                'observacoes'        => 'Simulação preliminar — aguardando publicação do acordo coletivo.',
                'aplicado_em'        => null,
            ],
        ];

        $cltCollaborators = Collaborator::whereIn('tipo_contrato', [ContractType::Clt, ContractType::Estagiario])
            ->where('status', CollaboratorStatus::Ativo)
            ->get();

        $now = now();

        foreach ($rounds as $roundData) {
            $round = DissidioRound::create([
                'ano_referencia'     => $roundData['ano_referencia'],
                'data_base'          => $roundData['data_base'],
                'data_publicacao'    => $roundData['data_publicacao'],
                'percentual'         => $roundData['percentual'],
                'aplica_estagiarios' => $roundData['aplica_estagiarios'],
                'status'             => $roundData['status']->value,
                'observacoes'        => $roundData['observacoes'],
                'criado_por_id'      => $admin?->id,
                'aplicado_por_id'    => $roundData['aplicado_em'] ? $admin?->id : null,
                'aplicado_em'        => $roundData['aplicado_em'],
            ]);

            $entries = [];
            $isApplied = in_array($roundData['status'], [
                DissidioRoundStatus::Aplicado,
                DissidioRoundStatus::RelatorioGerado,
            ]);

            foreach ($cltCollaborators as $collab) {
                $salarioAnterior = (float) ($collab->salario_base ?? 3000);
                $salarioNovo     = round($salarioAnterior * (1 + $roundData['percentual']), 2);

                $entries[] = [
                    'dissidio_round_id'   => $round->id,
                    'collaborator_id'     => $collab->id,
                    'salario_anterior'    => $salarioAnterior,
                    'percentual_aplicado' => $roundData['percentual'],
                    'salario_novo'        => $salarioNovo,
                    'diferenca_retroativa' => 0,
                    'meses_retroativos'   => 0,
                    'status'              => $isApplied ? 'aplicado' : 'simulado',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }

            foreach (array_chunk($entries, 100) as $chunk) {
                DB::table('dissidio_entries')->insert($chunk);
            }
        }
    }
}
