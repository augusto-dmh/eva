<?php

namespace Database\Factories;

use App\Enums\PayrollCycleStatus;
use App\Models\PayrollCycle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollCycle>
 */
class PayrollCycleFactory extends Factory
{
    public function definition(): array
    {
        $now = now();

        return [
            'mes_referencia' => $now->format('Y-m'),
            'ano' => (int) $now->format('Y'),
            'mes' => (int) $now->format('m'),
            'status' => PayrollCycleStatus::Aberto,
            'data_abertura' => $now,
            'data_fechamento' => null,
            'data_pagamento_folha' => null,
            'data_pagamento_comissao' => null,
            'salarios_brutos' => 0,
            'comissoes' => 0,
            'deducoes' => 0,
            'liquido' => 0,
            'pj' => 0,
            'observacoes' => null,
        ];
    }

    public function forMonth(int $year, int $month): static
    {
        return $this->state(fn () => [
            'mes_referencia' => sprintf('%04d-%02d', $year, $month),
            'ano' => $year,
            'mes' => $month,
        ]);
    }

    public function fechado(): static
    {
        return $this->state(fn () => [
            'status' => PayrollCycleStatus::Fechado,
            'data_fechamento' => now(),
        ]);
    }
}
