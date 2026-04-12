<?php

namespace Database\Factories;

use App\Enums\PlrRoundStatus;
use App\Enums\PlrSyndicateStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlrRoundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ano_referencia' => $this->faker->numberBetween(2024, 2026),
            'documento_politica_path' => null,
            'documento_politica_revisado' => false,
            'status_sindicato' => PlrSyndicateStatus::NaoIniciado->value,
            'data_aprovacao_sindicato' => null,
            'valor_total_distribuido' => null,
            'status' => PlrRoundStatus::Rascunho->value,
            'observacoes' => null,
            'criado_por_id' => User::factory(),
        ];
    }

    public function rascunho(): static
    {
        return $this->state(['status' => PlrRoundStatus::Rascunho->value]);
    }

    public function simulado(): static
    {
        return $this->state([
            'status' => PlrRoundStatus::Simulado->value,
            'valor_total_distribuido' => 100000.00,
        ]);
    }
}
