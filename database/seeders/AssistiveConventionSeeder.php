<?php

namespace Database\Seeders;

use App\Enums\CollaboratorStatus;
use App\Enums\ContractType;
use App\Models\Collaborator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssistiveConventionSeeder extends Seeder
{
    public function run(): void
    {
        $cltCollaborators = Collaborator::where('tipo_contrato', ContractType::Clt)
            ->where('status', CollaboratorStatus::Ativo)
            ->get();

        $now  = now();
        $rows = [];

        foreach ($cltCollaborators as $collab) {
            // ~12% make opposition
            $fezOposicao = fake()->boolean(12);
            $valorParcela = fake()->randomFloat(2, 80, 140);

            $rows[] = [
                'collaborator_id'        => $collab->id,
                'ano_referencia'         => 2025,
                'fez_oposicao'           => $fezOposicao,
                'data_oposicao'          => $fezOposicao
                    ? fake()->dateTimeBetween('2025-03-01', '2025-04-30')->format('Y-m-d')
                    : null,
                'comprovante_ar_path'    => $fezOposicao ? 'assistive/2025/ar-' . $collab->id . '.pdf' : null,
                'confirmado_sindicato'   => $fezOposicao ? fake()->boolean(80) : false,
                'parcelas_descontadas'   => $fezOposicao ? 0 : fake()->numberBetween(0, 4),
                'total_parcelas'         => 4,
                'valor_parcela'          => $fezOposicao ? null : $valorParcela,
                'observacoes'            => null,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('assistive_convention_records')->insert($chunk);
        }
    }
}
