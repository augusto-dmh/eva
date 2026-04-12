<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Enums\ThirteenthRoundStatus;
use App\Models\Collaborator;
use App\Models\ThirteenthSalaryRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThirteenthSalarySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->first();

        $collaborators = Collaborator::whereIn('tipo_contrato', [ContractType::Clt, ContractType::Estagiario])
            ->where('status', CollaboratorStatus::Ativo)
            ->get();

        $rounds = [
            [
                'ano_referencia'               => 2024,
                'status'                       => ThirteenthRoundStatus::Concluido,
                'primeira_parcela_data_limite' => '2024-11-30',
                'segunda_parcela_data_limite'  => '2024-12-20',
                'observacoes'                  => '13º salário 2024 concluído.',
                'entry_status'                 => 'pago',
            ],
            [
                'ano_referencia'               => 2025,
                'status'                       => ThirteenthRoundStatus::Concluido,
                'primeira_parcela_data_limite' => '2025-11-30',
                'segunda_parcela_data_limite'  => '2025-12-20',
                'observacoes'                  => '13º salário 2025 concluído.',
                'entry_status'                 => 'pago',
            ],
            [
                'ano_referencia'               => 2026,
                'status'                       => ThirteenthRoundStatus::Aberto,
                'primeira_parcela_data_limite' => '2026-11-30',
                'segunda_parcela_data_limite'  => '2026-12-20',
                'observacoes'                  => null,
                'entry_status'                 => 'pendente',
            ],
        ];

        $now = now();

        foreach ($rounds as $roundData) {
            $round = ThirteenthSalaryRound::create([
                'ano_referencia'               => $roundData['ano_referencia'],
                'status'                       => $roundData['status']->value,
                'primeira_parcela_data_limite' => $roundData['primeira_parcela_data_limite'],
                'segunda_parcela_data_limite'  => $roundData['segunda_parcela_data_limite'],
                'observacoes'                  => $roundData['observacoes'],
                'criado_por_id'                => $admin?->id,
            ]);

            $entries = [];
            foreach ($collaborators as $collab) {
                $salario = (float) ($collab->salario_base ?? 3000);
                $admissao = Carbon::parse($collab->data_admissao);
                $referenceEnd = Carbon::create($roundData['ano_referencia'], 12, 31);
                $meses = min(12, max(1, (int) $admissao->diffInMonths($referenceEnd)));
                $integral = round($salario * $meses / 12, 2);
                $primeira = round($integral / 2, 2);
                $segunda  = round($integral - $primeira, 2);
                $inss     = round($integral * 0.09, 2);
                $irrf     = $integral > 4664 ? round(($integral - 4664) * 0.15, 2) : 0;

                $entries[] = [
                    'thirteenth_salary_round_id' => $round->id,
                    'collaborator_id'             => $collab->id,
                    'meses_trabalhados'           => $meses,
                    'salario_base'                => $salario,
                    'media_comissoes'             => 0,
                    'base_calculo'                => $integral,
                    'valor_integral'              => $integral,
                    'primeira_parcela_valor'      => $primeira,
                    'segunda_parcela_valor'       => $segunda,
                    'desconto_inss'               => $inss,
                    'desconto_irrf'               => $irrf,
                    'primeira_parcela_status'     => $roundData['entry_status'],
                    'segunda_parcela_status'      => $roundData['entry_status'],
                    'created_at'                  => $now,
                    'updated_at'                  => $now,
                ];
            }

            foreach (array_chunk($entries, 100) as $chunk) {
                DB::table('thirteenth_salary_entries')->insert($chunk);
            }
        }
    }
}
