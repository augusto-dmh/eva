<?php

namespace Database\Factories;

use App\Models\AssistiveConventionRecord;
use App\Models\Collaborator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistiveConventionRecord>
 */
class AssistiveConventionRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaborator_id' => Collaborator::factory(),
            'ano_referencia' => 2025,
            'fez_oposicao' => false,
            'data_oposicao' => null,
            'comprovante_ar_path' => null,
            'confirmado_sindicato' => false,
            'parcelas_descontadas' => 0,
            'total_parcelas' => 4,
            'valor_parcela' => null,
            'observacoes' => null,
        ];
    }
}
