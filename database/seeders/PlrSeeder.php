<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Enums\PlrEntryStatus;
use App\Enums\PlrRoundStatus;
use App\Enums\PlrSyndicateStatus;
use App\Models\Collaborator;
use App\Models\PlrCommitteeMember;
use App\Models\PlrEntry;
use App\Models\PlrRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PlrSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@clubedovalor.com.br')->firstOrFail();

        $round = PlrRound::create([
            'ano_referencia' => 2025,
            'status' => PlrRoundStatus::Simulado,
            'status_sindicato' => PlrSyndicateStatus::Aprovado,
            'data_aprovacao_sindicato' => '2025-09-15',
            'valor_total_distribuido' => 500000.00,
            'documento_politica_revisado' => true,
            'criado_por_id' => $admin->id,
        ]);

        // Eligible: CLT with data_admissao before 2025-07-01
        $eligibleCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->whereDate('data_admissao', '<', '2025-07-01')
            ->get();

        $endOf2025 = Carbon::create(2025, 12, 31);

        // Calculate weights
        $weights = $eligibleCollaborators->map(function ($collaborator) use ($endOf2025) {
            $admissao = Carbon::parse($collaborator->data_admissao);
            $meses = min(12, (int) $admissao->diffInMonths($endOf2025) + 1);

            return [
                'collaborator' => $collaborator,
                'meses' => $meses,
                'peso' => (float) $collaborator->salario_base * $meses,
            ];
        });

        $pesoTotal = $weights->sum('peso');

        foreach ($weights as $item) {
            $collaborator = $item['collaborator'];
            $meses = $item['meses'];
            $peso = $item['peso'];

            $valorSimulado = $pesoTotal > 0
                ? round(($peso / $pesoTotal) * 500000, 2)
                : 0;

            $descontoIrrf = $this->calcularIrrfPlr($valorSimulado);

            PlrEntry::create([
                'plr_round_id' => $round->id,
                'collaborator_id' => $collaborator->id,
                'media_salarios_ano' => $collaborator->salario_base,
                'meses_trabalhados' => $meses,
                'valor_simulado' => $valorSimulado,
                'valor_pago' => null,
                'desconto_irrf' => $descontoIrrf,
                'status' => PlrEntryStatus::Simulado,
            ]);
        }

        // Committee members: pick up to 3 CLT collaborators
        $committeePool = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->limit(3)
            ->get();

        $papeis = ['Representante dos Trabalhadores', 'Representante dos Trabalhadores', 'Secretário'];

        foreach ($committeePool as $index => $collaborator) {
            PlrCommitteeMember::create([
                'plr_round_id' => $round->id,
                'collaborator_id' => $collaborator->id,
                'legal_entity_id' => $collaborator->legal_entity_id,
                'papel' => $papeis[$index] ?? 'Representante dos Trabalhadores',
                'ativo' => true,
            ]);
        }
    }

    private function calcularIrrfPlr(float $valor): float
    {
        // PLR specific IRRF table 2025
        if ($valor <= 6000.00) {
            return 0;
        } elseif ($valor <= 9000.00) {
            return round($valor * 0.075 - 450, 2);
        } elseif ($valor <= 12000.00) {
            return round($valor * 0.15 - 1125, 2);
        } elseif ($valor <= 15000.00) {
            return round($valor * 0.225 - 2025, 2);
        } else {
            return round($valor * 0.275 - 2775, 2);
        }
    }
}
