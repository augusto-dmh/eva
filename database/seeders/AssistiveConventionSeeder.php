<?php

namespace Database\Seeders;

use App\Enums\ContractType;
use App\Models\AssistiveConventionRecord;
use App\Models\Collaborator;
use Illuminate\Database\Seeder;

class AssistiveConventionSeeder extends Seeder
{
    public function run(): void
    {
        $cltCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->whereNull('data_desligamento')
            ->get();

        // Pick ~30% of CLT collaborators
        $count = max(1, (int) round($cltCollaborators->count() * 0.30));
        $selected = $cltCollaborators->random($count);

        foreach ($selected as $index => $collaborator) {
            // ~40% made opposition
            $fezOposicao = ($index % 5) < 2;

            AssistiveConventionRecord::create([
                'collaborator_id' => $collaborator->id,
                'ano_referencia' => 2025,
                'fez_oposicao' => $fezOposicao,
                'data_oposicao' => $fezOposicao ? '2025-02-20' : null,
                'confirmado_sindicato' => $fezOposicao,
                'valor_parcela' => 50.00,
                'total_parcelas' => 12,
                'parcelas_descontadas' => $fezOposicao ? 0 : 3,
                'observacoes' => null,
            ]);
        }
    }
}
