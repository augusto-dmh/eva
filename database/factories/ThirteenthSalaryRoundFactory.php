<?php

namespace Database\Factories;

use App\Enums\ThirteenthRoundStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThirteenthSalaryRoundFactory extends Factory
{
    public function definition(): array
    {
        $year = $this->faker->numberBetween(2024, 2026);

        return [
            'ano_referencia' => $year,
            'status' => ThirteenthRoundStatus::Aberto->value,
            'primeira_parcela_data_limite' => "{$year}-11-30",
            'segunda_parcela_data_limite' => "{$year}-12-20",
            'observacoes' => null,
            'criado_por_id' => User::factory(),
        ];
    }

    public function aberto(): static
    {
        return $this->state(['status' => ThirteenthRoundStatus::Aberto->value]);
    }

    public function simulado(): static
    {
        return $this->state(['status' => ThirteenthRoundStatus::PrimeiraParcelaSimulada->value]);
    }
}
