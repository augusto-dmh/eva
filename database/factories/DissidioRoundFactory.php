<?php

namespace Database\Factories;

use App\Enums\DissidioRoundStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DissidioRoundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ano_referencia' => $this->faker->numberBetween(2024, 2026),
            'data_base' => $this->faker->date(),
            'data_publicacao' => null,
            'percentual' => $this->faker->randomFloat(4, 0.03, 0.10),
            'aplica_estagiarios' => false,
            'status' => DissidioRoundStatus::Rascunho->value,
            'observacoes' => null,
            'criado_por_id' => User::factory(),
            'aplicado_por_id' => null,
            'aplicado_em' => null,
        ];
    }

    public function rascunho(): static
    {
        return $this->state(['status' => DissidioRoundStatus::Rascunho->value]);
    }

    public function simulado(): static
    {
        return $this->state(['status' => DissidioRoundStatus::Simulado->value]);
    }

    public function aplicado(): static
    {
        return $this->state([
            'status' => DissidioRoundStatus::Aplicado->value,
            'aplicado_por_id' => User::factory(),
            'aplicado_em' => now(),
        ]);
    }
}
