<?php

namespace Database\Factories;

use App\Enums\VacationBatchStatus;
use App\Enums\VacationBatchType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacationBatchFactory extends Factory
{
    public function definition(): array
    {
        $tipo = $this->faker->randomElement(VacationBatchType::cases());

        return [
            'mes_referencia' => $this->faker->date('Y-m'),
            'tipo' => $tipo,
            'periodo_aquisitivo_minimo_meses' => $tipo->periodoAquisitivoMeses(),
            'dias_ferias' => $tipo->diasFerias(),
            'status' => VacationBatchStatus::Rascunho,
            'data_abertura' => now(),
            'criado_por_id' => User::factory(),
        ];
    }

    public function forMonth(int $year, int $month): static
    {
        return $this->state(['mes_referencia' => sprintf('%04d-%02d', $year, $month)]);
    }

    public function clt(): static
    {
        return $this->state([
            'tipo' => VacationBatchType::Clt,
            'periodo_aquisitivo_minimo_meses' => 12,
            'dias_ferias' => 30,
        ]);
    }

    public function estagiario(): static
    {
        return $this->state([
            'tipo' => VacationBatchType::Estagiario,
            'periodo_aquisitivo_minimo_meses' => 6,
            'dias_ferias' => 15,
        ]);
    }

    public function calculado(): static
    {
        return $this->state(['status' => VacationBatchStatus::Calculado]);
    }
}
