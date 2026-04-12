<?php

namespace Database\Factories;

use App\Enums\TerminationStatus;
use App\Enums\TerminationType;
use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerminationRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaborator_id' => Collaborator::factory(),
            'tipo_desligamento' => TerminationType::PedidoDemissao,
            'data_comunicacao' => now()->toDateString(),
            'data_efetivacao' => now()->addDays(30)->toDateString(),
            'status' => TerminationStatus::Iniciado,
            'salario_proporcional_dias' => 15,
            'salario_proporcional_valor' => 1500.00,
            'ferias_proporcionais_valor' => 500.00,
            'terco_ferias_proporcionais' => 166.67,
            'decimo_terceiro_proporcional' => 250.00,
            'multa_fgts' => 0.00,
            'aviso_previo_valor' => 0.00,
            'indenizacao_rescisoria' => 0.00,
            'valor_total_rescisao' => 2416.67,
            'ajuste_flash_valor' => 0.00,
            'processado_por_id' => User::factory(),
        ];
    }

    public function iniciado(): static
    {
        return $this->state(['status' => TerminationStatus::Iniciado]);
    }

    public function simulacaoRealizada(): static
    {
        return $this->state(['status' => TerminationStatus::SimulacaoRealizada]);
    }

    public function concluido(): static
    {
        return $this->state(['status' => TerminationStatus::Concluido]);
    }
}
